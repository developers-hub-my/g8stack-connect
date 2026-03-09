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
use App\Services\ApiRuntime\SqlQueryExecutor;
use App\Services\Connectors\AbstractFileConnector;
use App\Services\Connectors\ConnectorFactory;
use App\Services\Introspectors\DatabaseIntrospector;
use App\Services\Introspectors\FileIntrospector;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\SpecRegenerationService;
use App\Services\SpecGenerator\SqlSpecGenerator;
use App\Services\SqlValidator;
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

    // Advanced Mode (SQL) — multi-query support
    public array $sqlQueries = [];

    public int $activeSqlIndex = 0;

    protected ?DataSource $dataSource = null;

    public function mount(): void
    {
        $this->authorize('create', DataSource::class);
        $this->addSqlQuery(); // Initialize with one empty query
    }

    protected function isAdvancedMode(): bool
    {
        return $this->wizardMode === 'advanced';
    }

    public function addSqlQuery(): void
    {
        $this->sqlQueries[] = [
            'endpoint_name' => '',
            'sql_query' => '',
            'validation_errors' => [],
            'result_columns' => [],
            'parameters' => [],
            'validated' => false,
        ];
        $this->activeSqlIndex = count($this->sqlQueries) - 1;
    }

    public function removeSqlQuery(int $index): void
    {
        if (count($this->sqlQueries) <= 1) {
            return; // Must have at least one query
        }

        array_splice($this->sqlQueries, $index, 1);
        $this->sqlQueries = array_values($this->sqlQueries);

        if ($this->activeSqlIndex >= count($this->sqlQueries)) {
            $this->activeSqlIndex = count($this->sqlQueries) - 1;
        }
    }

    public function setActiveSqlIndex(int $index): void
    {
        $this->activeSqlIndex = $index;
    }

    protected function allSqlQueriesValidated(): bool
    {
        foreach ($this->sqlQueries as $query) {
            if (! $query['validated']) {
                return false;
            }
        }

        return ! empty($this->sqlQueries);
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
            5 => [], // Advanced mode validation handled separately below
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

        // Advanced Mode: all queries must be validated before proceeding past step 5
        if ($this->currentStep === 5 && $this->isAdvancedMode()) {
            if (! $this->allSqlQueriesValidated()) {
                $this->dispatch('toast', type: 'error', message: 'All SQL queries must be validated before proceeding.', duration: 5000);

                return;
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

        // Advanced mode: scan all SQL result columns across all queries
        if ($this->isAdvancedMode()) {
            $columnNames = collect($this->sqlQueries)
                ->flatMap(fn ($q) => collect($q['result_columns'])->pluck('name'))
                ->unique()
                ->values()
                ->all();
            $result = $scanner->scan($columnNames);
        } else {
            $result = $scanner->scan($this->allColumnsForPii);
        }

        $this->piiResult = [
            'flagged' => array_values(array_unique($result->flagged)),
            'safe' => array_values(array_unique($result->safe)),
            'patterns' => $result->patterns,
        ];
    }

    public function validateSql(int $index = -1): void
    {
        if ($index < 0) {
            $index = $this->activeSqlIndex;
        }

        $query = $this->sqlQueries[$index] ?? null;
        if (! $query) {
            return;
        }

        $endpointName = trim($query['endpoint_name']);
        $sqlQuery = trim($query['sql_query']);

        if (empty($endpointName)) {
            $this->sqlQueries[$index]['validation_errors'] = ['Endpoint name is required.'];
            $this->sqlQueries[$index]['validated'] = false;
            $this->dispatch('toast', type: 'error', message: 'Endpoint name is required.', duration: 5000);

            return;
        }

        if (! preg_match('/^[a-z0-9_-]+$/i', $endpointName)) {
            $this->sqlQueries[$index]['validation_errors'] = ['Endpoint name must be alphanumeric with dashes/underscores.'];
            $this->sqlQueries[$index]['validated'] = false;
            $this->dispatch('toast', type: 'error', message: 'Invalid endpoint name format.', duration: 5000);

            return;
        }

        // Check for duplicate endpoint names
        foreach ($this->sqlQueries as $i => $q) {
            if ($i !== $index && strtolower(trim($q['endpoint_name'])) === strtolower($endpointName)) {
                $this->sqlQueries[$index]['validation_errors'] = ['Duplicate endpoint name.'];
                $this->sqlQueries[$index]['validated'] = false;
                $this->dispatch('toast', type: 'error', message: 'Endpoint name must be unique.', duration: 5000);

                return;
            }
        }

        if (empty($sqlQuery)) {
            $this->sqlQueries[$index]['validation_errors'] = ['SQL query is required.'];
            $this->sqlQueries[$index]['validated'] = false;
            $this->dispatch('toast', type: 'error', message: 'SQL query is required.', duration: 5000);

            return;
        }

        $validator = new SqlValidator;
        $result = $validator->validate($sqlQuery);

        if (! $result->valid) {
            $this->sqlQueries[$index]['validation_errors'] = $result->errors;
            $this->sqlQueries[$index]['validated'] = false;
            $this->dispatch('toast', type: 'error', message: implode(' ', $result->errors), duration: 5000);

            return;
        }

        $this->sqlQueries[$index]['validation_errors'] = [];
        $this->sqlQueries[$index]['parameters'] = $result->parameters;

        // Dry-run to get result shape
        try {
            $this->dryRunSql($index, $sqlQuery);
            $this->sqlQueries[$index]['validated'] = true;
            $this->dispatch('toast', type: 'success', message: 'SQL validated. '.count($this->sqlQueries[$index]['result_columns']).' result columns detected.', duration: 3000);
        } catch (\Throwable $e) {
            $this->sqlQueries[$index]['validation_errors'] = ['Dry-run failed: '.$e->getMessage()];
            $this->sqlQueries[$index]['validated'] = false;
            $this->dispatch('toast', type: 'error', message: 'Query dry-run failed: '.$e->getMessage(), duration: 5000);
        }
    }

    protected function dryRunSql(int $index, string $sql): void
    {
        $type = DataSourceType::from($this->type);
        $tempSpec = new ApiSpec;
        $tempSpec->id = 'dry-run-'.uniqid();

        $tempDataSource = new DataSource;
        $tempDataSource->type = $type;
        $tempDataSource->credentials = $this->credentials;
        $tempSpec->setRelation('dataSource', $tempDataSource);

        $executor = app(SqlQueryExecutor::class);
        $result = $executor->dryRun($tempSpec, $sql);

        $this->sqlQueries[$index]['result_columns'] = $result['columns'] ?? [];
        $this->sqlQueries[$index]['parameters'] = $result['parameters'] ?? $this->sqlQueries[$index]['parameters'];
    }

    public function updatedSqlQueries(mixed $value, ?string $key = null): void
    {
        // Only reset validation when the sql_query text changes, not endpoint_name
        if ($key !== null && str_ends_with($key, '.sql_query')) {
            $index = (int) explode('.', $key)[0];
            if (isset($this->sqlQueries[$index])) {
                $this->sqlQueries[$index]['validated'] = false;
                $this->sqlQueries[$index]['validation_errors'] = [];
            }
        }
    }

    public function generateSpec(): void
    {
        $type = DataSourceType::from($this->type);

        if ($this->isAdvancedMode()) {
            $this->generateAdvancedSpec($type);
        } elseif ($this->isFileSource()) {
            $this->generateFileSpec($type);
        } else {
            $this->generateDatabaseSpec($type);
        }
    }

    protected function generateAdvancedSpec(DataSourceType $type): void
    {
        $dataSource = DataSource::create([
            'name' => $this->name,
            'type' => $this->type,
            'credentials' => $this->credentials,
            'status' => ConnectionStatus::INTROSPECTED,
            'user_id' => auth()->id(),
            'metadata' => [],
        ]);

        // Store introspected schemas for reference
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());
        $connector->setDataSourceId($dataSource->id);
        $connector->connect($this->credentials);

        $introspector = new DatabaseIntrospector($connector);
        $introspector->storeSchemas($dataSource);

        $connector->disconnect();

        $apiSpec = ApiSpec::create([
            'user_id' => auth()->id(),
            'data_source_id' => $dataSource->id,
            'name' => $this->name,
            'wizard_mode' => 'advanced',
            'status' => 'pending',
            'openapi_spec' => [],
            'configuration' => [
                'mode' => 'advanced',
                'pii_excluded' => $this->piiResult['flagged'],
                'pagination' => true,
                'per_page' => 15,
                'methods' => ['GET'],
            ],
        ]);

        // Create an ApiSpecTable for each SQL query
        foreach ($this->sqlQueries as $index => $query) {
            ApiSpecTable::create([
                'api_spec_id' => $apiSpec->id,
                'table_name' => '_sql_'.$query['endpoint_name'],
                'resource_name' => $query['endpoint_name'],
                'operations' => ['list' => true],
                'sql_query' => $query['sql_query'],
                'sql_parameters' => $query['parameters'],
                'result_columns' => $query['result_columns'],
                'sort_order' => $index,
            ]);
        }

        $generator = app(SqlSpecGenerator::class);
        $tables = $apiSpec->tables()->get();
        $spec = $generator->generateForTables($apiSpec, $tables);

        $apiSpec->update(['openapi_spec' => $spec]);

        $this->generatedSpec = $spec;
        $this->currentStep = 7;

        $queryCount = count($this->sqlQueries);
        $this->dispatch('toast', type: 'success', message: "{$queryCount} SQL endpoint(s) spec generated.", duration: 3000);
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

        // Reset to simple mode if switching to file source (Advanced not available for files)
        if ($this->isFileSource() && $this->wizardMode === 'advanced') {
            $this->wizardMode = 'simple';
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.connect-wizard');
    }
}
