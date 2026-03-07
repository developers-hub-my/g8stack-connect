<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Concerns\InteractsWithLivewireAlert;
use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Models\ApiSpec;
use App\Models\ApiSpecTable;
use App\Models\DataSource;
use App\Services\ApiRuntime\ResourceNameSuggester;
use App\Services\Connectors\AbstractFileConnector;
use App\Services\Connectors\ConnectorFactory;
use App\Services\Introspectors\DatabaseIntrospector;
use App\Services\Introspectors\FileIntrospector;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\SpecRegenerationService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConnectWizard extends Component
{
    use InteractsWithLivewireAlert;
    use WithFileUploads;

    public int $currentStep = 1;

    public string $name = '';

    public string $type = '';

    public string $wizardMode = 'simple';

    public array $credentials = [
        'host' => '',
        'port' => '',
        'database' => '',
        'username' => '',
        'password' => '',
    ];

    public $uploadedFile = null;

    public bool $connectionTested = false;

    public array $tables = [];

    public array $selectedTables = [];

    public array $piiResult = ['flagged' => [], 'safe' => [], 'patterns' => []];

    public array $allColumnsForPii = [];

    public array $schemaColumns = [];

    public array $generatedSpec = [];

    protected ?DataSource $dataSource = null;

    public function mount(): void
    {
        $this->authorize('create', DataSource::class);
    }

    protected function isFileSource(): bool
    {
        if (! $this->type) {
            return false;
        }

        return DataSourceType::from($this->type)->isFile();
    }

    public function nextStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:'.implode(',', array_column(DataSourceType::cases(), 'value')),
            ],
            2 => $this->isFileSource()
                ? ['uploadedFile' => 'required|file|max:51200'] // 50MB max
                : ['credentials.database' => 'required|string'],
            default => [],
        };

        if (! empty($rules)) {
            $this->validate($rules);
        }

        if ($this->currentStep === 2) {
            if ($this->isFileSource()) {
                if (! $this->connectionTested) {
                    $this->testFileUpload();
                    if (! $this->connectionTested) {
                        return;
                    }
                }
            } elseif (! $this->connectionTested) {
                $this->testConnection();
                if (! $this->connectionTested) {
                    return;
                }
            }
        }

        $this->currentStep = min($this->currentStep + 1, 7);

        if ($this->currentStep === 4) {
            $this->introspect();
        }

        if ($this->currentStep === 6) {
            $this->runPiiScan();
        }
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function testConnection(): void
    {
        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());

        $result = $connector->connect($this->credentials);

        if ($result->success) {
            $this->connectionTested = true;
            $this->dispatch('toast', type: 'success', message: 'Connection successful!', duration: 3000);
        } else {
            $this->connectionTested = false;
            $this->dispatch('toast', type: 'error', message: $result->message, duration: 5000);
        }

        $connector->disconnect();
    }

    public function testFileUpload(): void
    {
        if (! $this->uploadedFile) {
            $this->dispatch('toast', type: 'error', message: 'Please upload a file.', duration: 5000);

            return;
        }

        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());

        $result = $connector->connect([
            'file_path' => $this->uploadedFile->getRealPath(),
            'original_filename' => $this->uploadedFile->getClientOriginalName(),
        ]);

        if ($result->success) {
            $this->connectionTested = true;
            $this->dispatch('toast', type: 'success', message: 'File loaded successfully! '.$result->metadata['row_count'].' rows, '.$result->metadata['column_count'].' columns.', duration: 3000);
        } else {
            $this->connectionTested = false;
            $this->dispatch('toast', type: 'error', message: $result->message, duration: 5000);
        }
    }

    public function introspect(): void
    {
        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());

        if ($this->isFileSource()) {
            $result = $connector->connect([
                'file_path' => $this->uploadedFile->getRealPath(),
                'original_filename' => $this->uploadedFile->getClientOriginalName(),
            ]);
        } else {
            $result = $connector->connect($this->credentials);
        }

        if (! $result->success) {
            $this->dispatch('toast', type: 'error', message: 'Cannot connect for introspection.', duration: 5000);

            return;
        }

        $schemaResult = $connector->introspect();

        if ($schemaResult->success) {
            $this->tables = $schemaResult->tables;
        }

        if (! $this->isFileSource()) {
            $connector->disconnect();
        }
    }

    public function toggleTable(string $table): void
    {
        if (in_array($table, $this->selectedTables)) {
            $this->selectedTables = array_values(array_diff($this->selectedTables, [$table]));
        } else {
            $this->selectedTables[] = $table;
        }

        $this->loadColumnsForSelectedTables();
    }

    public function selectAllTables(): void
    {
        $this->selectedTables = $this->tables;
        $this->loadColumnsForSelectedTables();
    }

    public function deselectAllTables(): void
    {
        $this->selectedTables = [];
        $this->schemaColumns = [];
        $this->allColumnsForPii = [];
    }

    protected function loadColumnsForSelectedTables(): void
    {
        if (empty($this->selectedTables)) {
            $this->schemaColumns = [];
            $this->allColumnsForPii = [];

            return;
        }

        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());

        if ($this->isFileSource()) {
            $connector->connect([
                'file_path' => $this->uploadedFile->getRealPath(),
                'original_filename' => $this->uploadedFile->getClientOriginalName(),
            ]);
        } else {
            $connector->connect($this->credentials);
        }

        $grouped = [];
        $allColumns = [];

        foreach ($this->selectedTables as $table) {
            $columns = $connector->getColumnsForTable($table);
            $mapped = collect($columns)->map(fn ($col) => [
                'name' => $col['name'],
                'type' => $this->isFileSource()
                    ? ($col['type'] ?? 'varchar')
                    : ($col['type_name'] ?? $col['type'] ?? 'unknown'),
                'nullable' => $col['nullable'] ?? ($this->isFileSource() ? true : false),
            ])->all();

            $grouped[$table] = $mapped;
            $allColumns = array_merge($allColumns, $mapped);
        }

        $this->schemaColumns = $grouped;
        $this->allColumnsForPii = $allColumns;

        if (! $this->isFileSource()) {
            $connector->disconnect();
        }
    }

    protected function runPiiScan(): void
    {
        $scanner = new PiiDetectionService;
        $result = $scanner->scan($this->allColumnsForPii);

        $this->piiResult = [
            'flagged' => array_values(array_unique($result->flagged)),
            'safe' => array_values(array_unique($result->safe)),
            'patterns' => $result->patterns,
        ];
    }

    public function generateSpec(): void
    {
        $type = DataSourceType::from($this->type);

        if ($this->isFileSource()) {
            $this->generateFileSpec($type);
        } else {
            $this->generateDatabaseSpec($type);
        }
    }

    protected function generateDatabaseSpec(DataSourceType $type): void
    {
        $dataSource = DataSource::create([
            'name' => $this->name,
            'type' => $this->type,
            'credentials' => $this->credentials,
            'status' => ConnectionStatus::INTROSPECTED,
            'user_id' => auth()->id(),
            'metadata' => [],
        ]);

        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());
        $connector->setDataSourceId($dataSource->id);
        $connector->connect($this->credentials);

        $introspector = new DatabaseIntrospector($connector);
        $schemas = $introspector->storeSchemas($dataSource);

        $connector->disconnect();

        $targetSchemas = collect($schemas)->whereIn('table_name', $this->selectedTables)->all();

        if (empty($targetSchemas)) {
            $this->dispatch('toast', type: 'error', message: 'Schema not found for selected tables.', duration: 5000);

            return;
        }

        $this->createSpecFromSchemas($dataSource, $targetSchemas);
    }

    protected function generateFileSpec(DataSourceType $type): void
    {
        // Capture metadata before storing (temp file may be cleaned up after store)
        $originalFilename = $this->uploadedFile->getClientOriginalName();

        // Store the uploaded file permanently
        $storedPath = $this->uploadedFile->store('data-sources', 'local');
        $fullPath = storage_path('app/'.$storedPath);

        $dataSource = DataSource::create([
            'name' => $this->name,
            'type' => $this->type,
            'credentials' => ['file_path' => $fullPath, 'original_filename' => $originalFilename],
            'status' => ConnectionStatus::INTROSPECTED,
            'user_id' => auth()->id(),
            'metadata' => [
                'original_filename' => $originalFilename,
                'file_size' => filesize($fullPath),
            ],
        ]);

        /** @var AbstractFileConnector $connector */
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());
        $connector->setDataSourceId($dataSource->id);
        $connector->connect(['file_path' => $fullPath, 'original_filename' => $originalFilename]);

        $introspector = new FileIntrospector($connector, $dataSource);
        $introspector->storeSchemas();

        $targetSchemas = $dataSource->schemas()
            ->whereIn('table_name', $this->selectedTables)
            ->get()
            ->all();

        if (empty($targetSchemas)) {
            $this->dispatch('toast', type: 'error', message: 'Schema not found for selected tables.', duration: 5000);

            return;
        }

        $this->createSpecFromSchemas($dataSource, $targetSchemas);
    }

    protected function createSpecFromSchemas(DataSource $dataSource, array $targetSchemas): void
    {
        $suggester = new ResourceNameSuggester;
        $isReadOnly = $this->isFileSource();
        $tableNames = collect($targetSchemas)->pluck('table_name')->all();

        $apiSpec = ApiSpec::create([
            'user_id' => auth()->id(),
            'data_source_id' => $dataSource->id,
            'name' => $this->name,
            'wizard_mode' => $this->wizardMode,
            'status' => 'pending',
            'openapi_spec' => [],
            'selected_tables' => $tableNames,
            'configuration' => [
                'mode' => $this->wizardMode,
                'pii_excluded' => $this->piiResult['flagged'],
                'read_only' => $isReadOnly,
            ],
        ]);

        $resourceNames = $suggester->suggestMany($tableNames);

        foreach ($targetSchemas as $index => $schema) {
            $tableName = $schema->table_name ?? $schema['table_name'];
            $operations = [
                'list' => true,
                'show' => true,
                'create' => ! $isReadOnly,
                'update' => ! $isReadOnly,
                'delete' => false,
            ];

            ApiSpecTable::create([
                'api_spec_id' => $apiSpec->id,
                'table_name' => $tableName,
                'resource_name' => $resourceNames[$tableName] ?? $suggester->suggest($tableName),
                'operations' => $operations,
                'sort_order' => $index,
            ]);
        }

        $spec = app(SpecRegenerationService::class)->regenerate($apiSpec);

        $this->generatedSpec = $spec;
        $this->currentStep = 7;

        $this->dispatch('toast', type: 'success', message: 'API spec generated with '.count($targetSchemas).' resource(s).', duration: 3000);
    }

    public function updatedType(): void
    {
        $this->connectionTested = false;
        $this->uploadedFile = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.connect-wizard');
    }
}
