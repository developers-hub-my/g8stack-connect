<?php

declare(strict_types=1);

namespace App\Livewire\DataSource;

use App\Concerns\InteractsWithLivewireAlert;
use App\Models\ApiSpec;
use App\Models\ApiSpecField;
use App\Models\DataSourceSchema;
use App\Services\PiiDetection\PiiDetectionService;
use App\Services\SpecGenerator\GuidedSpecGenerator;
use App\Services\SpecVersioning\SpecVersioningService;
use Livewire\Component;

class GuidedConfigWizard extends Component
{
    use InteractsWithLivewireAlert;

    public ApiSpec $apiSpec;

    public array $fields = [];

    public array $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    public bool $pagination = true;

    public int $perPage = 15;

    public function mount(string $specUuid): void
    {
        $this->apiSpec = ApiSpec::where('uuid', $specUuid)->firstOrFail();
        $this->authorize('update', $this->apiSpec);

        $this->loadFields();
    }

    protected function loadFields(): void
    {
        if ($this->apiSpec->fields->isNotEmpty()) {
            $this->fields = $this->apiSpec->fields->map(fn (ApiSpecField $f) => [
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
            $schema = DataSourceSchema::where('data_source_id', $this->apiSpec->data_source_id)
                ->whereIn('table_name', $this->apiSpec->selected_tables ?? [])
                ->first();

            if ($schema) {
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
        }
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

        $this->apiSpec->fields()->delete();

        foreach ($this->fields as $field) {
            ApiSpecField::create([
                'api_spec_id' => $this->apiSpec->id,
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
            ->whereIn('table_name', $this->apiSpec->selected_tables ?? [])
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
                    'fields' => $this->fields,
                    'methods' => $this->methods,
                    'pagination' => $this->pagination,
                    'per_page' => $this->perPage,
                ],
                'Guided mode configuration updated.',
            );
        }

        $this->alert('Success', 'Spec configuration saved and new version generated.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-source.guided-config-wizard');
    }
}
