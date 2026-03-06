<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Concerns\InteractsWithLivewireAlert;
use App\Enums\ConnectionStatus;
use App\Enums\DataSourceType;
use App\Models\ApiSpec;
use App\Models\DataSource;
use App\Services\Connectors\ConnectorFactory;
use App\Services\Introspectors\DatabaseIntrospector;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\CrudSpecGenerator;
use Livewire\Component;

class ConnectWizard extends Component
{
    use InteractsWithLivewireAlert;

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

    public bool $connectionTested = false;

    public array $tables = [];

    public string $selectedTable = '';

    public array $piiResult = ['flagged' => [], 'safe' => [], 'patterns' => []];

    public array $schemaColumns = [];

    public array $generatedSpec = [];

    protected ?DataSource $dataSource = null;

    public function mount(): void
    {
        $this->authorize('create', DataSource::class);
    }

    public function nextStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:'.implode(',', array_column(DataSourceType::cases(), 'value')),
            ],
            2 => [
                'credentials.database' => 'required|string',
            ],
            default => [],
        };

        if (! empty($rules)) {
            $this->validate($rules);
        }

        if ($this->currentStep === 2 && ! $this->connectionTested) {
            $this->testConnection();

            if (! $this->connectionTested) {
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

    public function introspect(): void
    {
        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());

        $result = $connector->connect($this->credentials);

        if (! $result->success) {
            $this->dispatch('toast', type: 'error', message: 'Cannot connect for introspection.', duration: 5000);

            return;
        }

        $schemaResult = $connector->introspect();

        if ($schemaResult->success) {
            $this->tables = $schemaResult->tables;
        }

        $connector->disconnect();
    }

    public function selectTable(string $table): void
    {
        $this->selectedTable = $table;
        $this->loadTableColumns();
    }

    protected function loadTableColumns(): void
    {
        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());
        $connector->connect($this->credentials);

        $columns = $connector->getColumnsForTable($this->selectedTable);
        $this->schemaColumns = collect($columns)->map(fn ($col) => [
            'name' => $col['name'],
            'type' => $col['type_name'] ?? $col['type'] ?? 'unknown',
            'nullable' => $col['nullable'] ?? false,
        ])->all();

        $connector->disconnect();
    }

    protected function runPiiScan(): void
    {
        $scanner = new PiiDetectionService;
        $result = $scanner->scan($this->schemaColumns);

        $this->piiResult = [
            'flagged' => $result->flagged,
            'safe' => $result->safe,
            'patterns' => $result->patterns,
        ];
    }

    public function generateSpec(): void
    {
        $dataSource = DataSource::create([
            'name' => $this->name,
            'type' => $this->type,
            'credentials' => $this->credentials,
            'status' => ConnectionStatus::INTROSPECTED,
            'user_id' => auth()->id(),
            'metadata' => [],
        ]);

        $type = DataSourceType::from($this->type);
        $connector = ConnectorFactory::make($type);
        $connector->setUserId(auth()->id());
        $connector->setDataSourceId($dataSource->id);
        $connector->connect($this->credentials);

        $introspector = new DatabaseIntrospector($connector);
        $schemas = $introspector->storeSchemas($dataSource);

        $connector->disconnect();

        $targetSchema = collect($schemas)->firstWhere('table_name', $this->selectedTable);

        if (! $targetSchema) {
            $this->dispatch('toast', type: 'error', message: 'Schema not found for selected table.', duration: 5000);

            return;
        }

        $generator = new CrudSpecGenerator(new PiiDetectionService);
        $spec = $generator->generate($targetSchema);

        ApiSpec::create([
            'user_id' => auth()->id(),
            'data_source_id' => $dataSource->id,
            'name' => $this->name.' - '.$this->selectedTable,
            'wizard_mode' => $this->wizardMode,
            'status' => 'pending',
            'openapi_spec' => $spec,
            'selected_tables' => [$this->selectedTable],
            'configuration' => [
                'mode' => $this->wizardMode,
                'pii_excluded' => $this->piiResult['flagged'],
            ],
        ]);

        $this->generatedSpec = $spec;
        $this->currentStep = 7;

        $this->dispatch('toast', type: 'success', message: 'API spec generated successfully!', duration: 3000);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.connect-wizard');
    }
}
