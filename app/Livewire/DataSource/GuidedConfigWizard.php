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

    public array $methods = ['GET', 'POST', 'PUT', 'DELETE'];

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

        $this->loadFieldsForTable();
    }

    public function updatedSelectedTableId(): void
    {
        $this->loadFieldsForTable();
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

    public function toggleMethod(string $method): void
    {
        if (in_array($method, $this->methods)) {
            $this->methods = array_values(array_diff($this->methods, [$method]));
        } else {
            $this->methods[] = $method;
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

        $schema = DataSourceSchema::where('data_source_id', $this->apiSpec->data_source_id)
            ->where('table_name', $table->table_name)
            ->first();

        if ($schema) {
            $generator = new GuidedSpecGenerator;
            $spec = $generator->generate($schema, [
                'fields' => $this->fields,
                'methods' => $this->methods,
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
                    'methods' => $this->methods,
                    'pagination' => $this->pagination,
                    'per_page' => $this->perPage,
                ],
                "Field configuration updated for {$table->resource_name}.",
            );
        }

        $this->alert('Success', "Configuration saved for {$table->resource_name}.");
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.guided-config-wizard', [
            'tables' => $this->apiSpec->tables()->get(),
        ]);
    }
}
