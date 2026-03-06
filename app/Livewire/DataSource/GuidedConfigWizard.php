<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Concerns\InteractsWithLivewireAlert;
use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\ApiSpecTable;
use App\Models\DataSourceSchema;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\GuidedSpecGenerator;
use App\Services\SpecVersioning\SpecVersioningService;
use Livewire\Component;

class GuidedConfigWizard extends Component
{
    use InteractsWithLivewireAlert;

    public ApiSpec $apiSpec;

    public ?string $selectedTableId = null;

    public array $fields = [];

    public array $operations = [];

    public bool $pagination = true;

    public int $perPage = 15;

    public function mount(string $specUuid): void
    {
        $this->apiSpec = ApiSpec::where('uuid', $specUuid)->firstOrFail();
        $this->authorize('update', $this->apiSpec);

        $tables = $this->apiSpec->tables()->get();
        if ($tables->isNotEmpty()) {
            $this->selectedTableId = (string) $tables->first()->id;
        }

        $this->loadTableData();
    }

    public function updatedSelectedTableId(): void
    {
        $this->loadTableData();
    }

    protected function loadTableData(): void
    {
        $this->loadOperationsForTable();
        $this->loadFieldsForTable();
    }

    protected function loadOperationsForTable(): void
    {
        if (! $this->selectedTableId) {
            $this->operations = [];

            return;
        }

        $table = ApiSpecTable::find($this->selectedTableId);
        if (! $table) {
            $this->operations = [];

            return;
        }

        $this->operations = $table->operations ?? $table->getDefaultOperations();
    }

    protected function loadFieldsForTable(): void
    {
        if (! $this->selectedTableId) {
            $this->fields = [];

            return;
        }

        $table = ApiSpecTable::find($this->selectedTableId);
        if (! $table) {
            $this->fields = [];

            return;
        }

        $existingFields = $table->fields()->get();

        if ($existingFields->isNotEmpty()) {
            $this->fields = $existingFields->map(fn (ApiSpecField $f) => [
                'column_name' => $f->column_name,
                'display_name' => $f->display_name ?? $f->column_name,
                'data_type' => $f->data_type,
                'is_exposed' => $f->is_exposed,
                'is_pii' => $f->is_pii,
                'is_required' => $f->is_required,
                'is_filterable' => $f->is_filterable,
                'is_sortable' => $f->is_sortable,
                'sort_order' => $f->sort_order,
            ])->all();
        } else {
            $this->loadFieldsFromSchema($table->table_name);
        }
    }

    protected function loadFieldsFromSchema(string $tableName): void
    {
        $schema = DataSourceSchema::where('data_source_id', $this->apiSpec->data_source_id)
            ->where('table_name', $tableName)
            ->first();

        if (! $schema) {
            $this->fields = [];

            return;
        }

        $piiScanner = new PiiDetectionService;
        $columnNames = collect($schema->columns)->pluck('name')->all();
        $piiResult = $piiScanner->scan($columnNames);

        $this->fields = collect($schema->columns)->map(fn ($col, $i) => [
            'column_name' => $col['name'],
            'display_name' => $col['name'],
            'data_type' => $col['type'] ?? 'varchar',
            'is_exposed' => ! in_array($col['name'], $piiResult->flagged),
            'is_pii' => in_array($col['name'], $piiResult->flagged),
            'is_required' => false,
            'is_filterable' => false,
            'is_sortable' => false,
            'sort_order' => $i,
        ])->all();
    }

    public function toggleField(int $index, string $property): void
    {
        if (isset($this->fields[$index][$property])) {
            $this->fields[$index][$property] = ! $this->fields[$index][$property];
        }
    }

    public function toggleOperation(string $operation): void
    {
        if (array_key_exists($operation, $this->operations)) {
            $this->operations[$operation] = ! $this->operations[$operation];
        }
    }

    public function saveConfiguration(): void
    {
        $this->authorize('update', $this->apiSpec);

        if (! $this->selectedTableId) {
            return;
        }

        $table = ApiSpecTable::find($this->selectedTableId);
        if (! $table) {
            return;
        }

        // Save operations to the table
        $table->update(['operations' => $this->operations]);

        // Delete existing fields for this table and recreate
        $table->fields()->delete();

        foreach ($this->fields as $field) {
            ApiSpecField::create([
                'api_spec_id' => $this->apiSpec->id,
                'api_spec_table_id' => $table->id,
                'column_name' => $field['column_name'],
                'display_name' => $field['display_name'],
                'data_type' => $field['data_type'],
                'is_exposed' => $field['is_exposed'],
                'is_pii' => $field['is_pii'],
                'is_required' => $field['is_required'],
                'is_filterable' => $field['is_filterable'],
                'is_sortable' => $field['is_sortable'],
                'sort_order' => $field['sort_order'],
            ]);
        }

        // Derive methods from operations for spec generation
        $methods = $this->deriveMethodsFromOperations();

        $schema = DataSourceSchema::where('data_source_id', $this->apiSpec->data_source_id)
            ->where('table_name', $table->table_name)
            ->first();

        if ($schema) {
            $generator = new GuidedSpecGenerator;
            $spec = $generator->generate($schema, [
                'fields' => $this->fields,
                'methods' => $methods,
                'pagination' => $this->pagination,
                'per_page' => $this->perPage,
            ]);

            $versioningService = new SpecVersioningService;
            $versioningService->createVersion(
                $this->apiSpec,
                $spec,
                [
                    'table' => $table->table_name,
                    'fields' => $this->fields,
                    'operations' => $this->operations,
                    'pagination' => $this->pagination,
                    'per_page' => $this->perPage,
                ],
                "Field configuration updated for {$table->resource_name}.",
            );
        }

        $this->alert('Success', "Configuration saved for {$table->resource_name}.");
    }

    protected function deriveMethodsFromOperations(): array
    {
        $methods = [];

        if ($this->operations['list'] ?? false) {
            $methods[] = 'GET';
        }
        if ($this->operations['show'] ?? false) {
            $methods[] = 'GET';
        }
        if ($this->operations['create'] ?? false) {
            $methods[] = 'POST';
        }
        if ($this->operations['update'] ?? false) {
            $methods[] = 'PUT';
        }
        if ($this->operations['delete'] ?? false) {
            $methods[] = 'DELETE';
        }

        return array_unique($methods);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.guided-config-wizard', [
            'tables' => $this->apiSpec->tables()->get(),
        ]);
    }
}
