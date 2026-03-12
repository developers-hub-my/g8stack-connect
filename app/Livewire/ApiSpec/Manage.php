<?php

declare(strict_types=1);

namespace App\Livewire\ApiSpec;

use App\Concerns\InteractsWithLivewireAlert;
use App\Enums\SpecStatus;
use App\Models\ApiSpec;
use App\Models\ApiSpecKey;
use App\Models\ApiSpecTable;
use App\Models\DataSource;
use App\Services\ApiRuntime\ResourceNameSuggester;
use App\Services\SpecGenerator\SpecRegenerationService;
use Illuminate\View\View;
use Livewire\Component;

class Manage extends Component
{
    use InteractsWithLivewireAlert;

    public ?ApiSpec $apiSpec = null;

    public bool $isEditing = false;

    // Form fields
    public string $name = '';

    public ?int $dataSourceId = null;

    public string $status = 'pending';

    public bool $authEnabled = false;

    public int $rateLimit = 60;

    public bool $pagination = true;

    public int $perPage = 15;

    // Resource table configuration
    public array $resources = [];

    public array $availableTables = [];

    // API Key management
    public string $newKeyName = '';

    public ?string $newlyCreatedKey = null;

    public function mount(?string $uuid = null): void
    {
        if ($uuid) {
            $this->apiSpec = ApiSpec::where('uuid', $uuid)->firstOrFail();
            $this->authorize('update', $this->apiSpec);
            $this->isEditing = true;
            $this->fillFromSpec();
        } else {
            $this->authorize('create', ApiSpec::class);
        }
    }

    public function fillFromSpec(): void
    {
        $this->name = $this->apiSpec->name;
        $this->dataSourceId = $this->apiSpec->data_source_id;
        $this->status = $this->apiSpec->status->value;

        $config = $this->apiSpec->configuration ?? [];
        $this->authEnabled = $config['auth_enabled'] ?? false;
        $this->rateLimit = $config['rate_limit'] ?? 60;
        $this->pagination = $config['pagination'] ?? true;
        $this->perPage = $config['per_page'] ?? 15;

        $this->loadResources();
        $this->loadAvailableTables();
    }

    public function loadResources(): void
    {
        if (! $this->apiSpec) {
            return;
        }

        $this->resources = $this->apiSpec->tables()->get()->map(fn (ApiSpecTable $t) => [
            'id' => $t->id,
            'table_name' => $t->table_name,
            'resource_name' => $t->resource_name,
            'operations' => $t->operations ?? $t->getDefaultOperations(),
        ])->toArray();
    }

    public function loadAvailableTables(): void
    {
        if (! $this->dataSourceId) {
            $this->availableTables = [];

            return;
        }

        $dataSource = DataSource::find($this->dataSourceId);
        if (! $dataSource) {
            return;
        }

        $this->availableTables = $dataSource->schemas()
            ->pluck('table_name')
            ->sort()
            ->values()
            ->all();
    }

    public function updatedDataSourceId(): void
    {
        $this->loadAvailableTables();
    }

    public function addResource(string $tableName): void
    {
        // Don't add duplicates
        foreach ($this->resources as $r) {
            if ($r['table_name'] === $tableName) {
                return;
            }
        }

        $suggester = new ResourceNameSuggester;

        $this->resources[] = [
            'id' => null,
            'table_name' => $tableName,
            'resource_name' => $suggester->suggest($tableName),
            'operations' => [
                'list' => true,
                'show' => true,
                'create' => false,
                'update' => false,
                'delete' => false,
            ],
        ];
    }

    public function removeResource(int $index): void
    {
        unset($this->resources[$index]);
        $this->resources = array_values($this->resources);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'dataSourceId' => 'required|exists:data_sources,id',
            'status' => 'required|in:'.implode(',', array_column(SpecStatus::cases(), 'value')),
            'authEnabled' => 'boolean',
            'rateLimit' => 'integer|min:1|max:10000',
            'pagination' => 'boolean',
            'perPage' => 'integer|min:1|max:100',
            'resources' => 'array|min:1',
            'resources.*.table_name' => 'required|string',
            'resources.*.resource_name' => 'required|string|max:255',
        ], [
            'resources.min' => 'At least one resource table is required.',
            'dataSourceId.required' => 'Please select a data source.',
        ]);

        $configuration = [
            'auth_enabled' => $this->authEnabled,
            'rate_limit' => $this->rateLimit,
            'pagination' => $this->pagination,
            'per_page' => $this->perPage,
            'methods' => $this->getMethodsFromResources(),
        ];

        if ($this->isEditing) {
            $this->apiSpec->update([
                'name' => $this->name,
                'status' => $this->status,
                'data_source_id' => $this->dataSourceId,
                'configuration' => $configuration,
                'selected_tables' => collect($this->resources)->pluck('table_name')->all(),
            ]);
        } else {
            $this->apiSpec = ApiSpec::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'data_source_id' => $this->dataSourceId,
                'wizard_mode' => 'guided',
                'status' => $this->status,
                'configuration' => $configuration,
                'selected_tables' => collect($this->resources)->pluck('table_name')->all(),
            ]);
        }

        $this->syncResources();

        app(SpecRegenerationService::class)->regenerate($this->apiSpec, [
            'pagination' => $this->pagination,
            'per_page' => $this->perPage,
        ]);

        $this->alert('Success', $this->isEditing ? 'API spec updated.' : 'API spec created.');

        $this->redirect(route('api-specs.show', ['uuid' => $this->apiSpec->uuid]), navigate: true);
    }

    public function createApiKey(): void
    {
        $this->validate([
            'newKeyName' => 'required|string|max:255',
        ]);

        $plainKey = ApiSpecKey::generateKey();

        ApiSpecKey::create([
            'api_spec_id' => $this->apiSpec->id,
            'name' => $this->newKeyName,
            'key_hash' => ApiSpecKey::hashKey($plainKey),
            'key_prefix' => ApiSpecKey::prefixFromKey($plainKey),
            'rate_limit' => $this->rateLimit,
        ]);

        $this->newlyCreatedKey = $plainKey;
        $this->newKeyName = '';
        $this->alert('Success', 'API key created. Copy it now — it won\'t be shown again.');
    }

    public function revokeApiKey(int $keyId): void
    {
        $key = ApiSpecKey::where('id', $keyId)
            ->where('api_spec_id', $this->apiSpec->id)
            ->firstOrFail();

        $key->delete();
        $this->alert('Success', "API key \"{$key->name}\" revoked.");
    }

    public function dismissNewKey(): void
    {
        $this->newlyCreatedKey = null;
    }

    public function deploy(): void
    {
        $this->apiSpec->update(['status' => SpecStatus::DEPLOYED]);
        $this->status = 'deployed';
        $this->alert('Success', 'API spec deployed. Endpoints are now live.');
    }

    public function undeploy(): void
    {
        $this->apiSpec->update(['status' => SpecStatus::PENDING]);
        $this->status = 'pending';
        $this->alert('Success', 'API spec undeployed. Endpoints are offline.');
    }

    protected function syncResources(): void
    {
        $existingIds = [];

        foreach ($this->resources as $index => $resource) {
            $table = ApiSpecTable::updateOrCreate(
                [
                    'api_spec_id' => $this->apiSpec->id,
                    'table_name' => $resource['table_name'],
                ],
                [
                    'resource_name' => $resource['resource_name'],
                    'operations' => $resource['operations'],
                    'sort_order' => $index,
                ],
            );
            $existingIds[] = $table->id;
        }

        // Remove resources that were deleted
        $this->apiSpec->tables()
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    protected function getMethodsFromResources(): array
    {
        $methods = ['GET'];

        foreach ($this->resources as $resource) {
            $ops = $resource['operations'] ?? [];
            if ($ops['create'] ?? false) {
                $methods[] = 'POST';
            }
            if ($ops['update'] ?? false) {
                $methods[] = 'PUT';
            }
            if ($ops['delete'] ?? false) {
                $methods[] = 'DELETE';
            }
        }

        return array_unique($methods);
    }

    public function render(): View
    {
        return view('livewire.api-spec.manage', [
            'dataSources' => DataSource::orderBy('name')->get(),
            'statuses' => SpecStatus::cases(),
            'apiKeys' => $this->apiSpec?->keys()->withTrashed(false)->latest()->get() ?? collect(),
        ]);
    }
}
