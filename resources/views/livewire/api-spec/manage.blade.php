<div x-data="{
    tab: new URLSearchParams(window.location.search).get('tab') || 'basic-info',
    setTab(name) {
        this.tab = name;
        const url = new URL(window.location);
        url.searchParams.set('tab', name);
        window.history.replaceState({}, '', url);
    }
}">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('api-specs.index')" wire:navigate>API Specs</flux:breadcrumbs.item>
        @if($isEditing)
            <flux:breadcrumbs.item :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate>{{ $name ?: 'Spec' }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        @endif
    </flux:breadcrumbs>

    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ $isEditing ? $name ?: 'Edit API Spec' : 'New API Spec' }}</flux:heading>
            <flux:text class="mt-2">
                {{ $isEditing ? 'Update spec configuration, resources, and operations.' : 'Create a new API spec from an existing data source.' }}
            </flux:text>
        </div>
        <div class="flex items-center gap-2">
            @if($isEditing && $apiSpec)
                @if($apiSpec->status->value === 'deployed')
                    <flux:button variant="danger" wire:click="undeploy" type="button" size="sm">Undeploy</flux:button>
                @else
                    <flux:button wire:click="deploy" type="button" size="sm">Deploy</flux:button>
                @endif
            @endif
        </div>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    {{-- Tabs --}}
    <nav class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700" role="tablist">
        <template x-for="t in [
            { id: 'basic-info', label: 'Basic Information' },
            { id: 'configuration', label: 'Configuration' },
            { id: 'resources', label: 'Resources' },
            @if($isEditing) { id: 'api-keys', label: 'API Keys' }, @endif
        ]" :key="t.id">
            <button type="button" role="tab"
                @click="setTab(t.id)"
                :aria-selected="tab === t.id"
                :class="tab === t.id
                    ? 'border-zinc-800 text-zinc-800 dark:border-white dark:text-white'
                    : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300 dark:hover:text-white dark:hover:border-zinc-600'"
                class="cursor-pointer px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                x-text="t.label">
            </button>
        </template>
    </nav>

    <form wire:submit="save" id="spec-form">
        {{-- Basic Information --}}
        <div x-show="tab === 'basic-info'" role="tabpanel"
            class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 mt-6 p-6 space-y-5">
            <flux:input wire:model="name" label="Name" placeholder="e.g. HR System API" required />
            <flux:select wire:model.live="dataSourceId" label="Data Source" required>
                <option value="">Select a data source...</option>
                @foreach($dataSources as $ds)
                    <option value="{{ $ds->id }}">{{ $ds->name }} ({{ $ds->type->label() }})</option>
                @endforeach
            </flux:select>
            @if($isEditing)
                <flux:select wire:model="status" label="Status">
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        {{-- Configuration --}}
        <div x-show="tab === 'configuration'" x-cloak role="tabpanel"
            class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 mt-6 p-6 space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:checkbox wire:model="authEnabled" label="Require API Key" description="Enforce X-API-Key header" />
                <flux:checkbox wire:model="pagination" label="Enable Pagination" description="Paginate list responses" />
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:input wire:model="rateLimit" label="Rate Limit (req/min)" type="number" min="1" max="10000" />
                @if($pagination)
                    <flux:input wire:model="perPage" label="Per Page" type="number" min="1" max="100" />
                @endif
            </div>
        </div>

        {{-- Resources --}}
        <div x-show="tab === 'resources'" x-cloak role="tabpanel"
            class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 mt-6 p-6 space-y-4">
            <div class="flex items-center justify-end">
                @if(!empty($availableTables))
                    <flux:dropdown>
                        <flux:button variant="primary" size="sm" icon="plus">Add Table</flux:button>
                        <flux:menu>
                            @foreach($availableTables as $tableName)
                                <flux:menu.item wire:click="addResource('{{ $tableName }}')">
                                    {{ $tableName }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                @endif
            </div>

            @if(empty($resources) && !$dataSourceId)
                <flux:text class="py-10 text-center">Select a data source to see available tables.</flux:text>
            @elseif(empty($resources) && $dataSourceId)
                <flux:text class="py-10 text-center">No tables added yet. Click "Add Table" to get started.</flux:text>
            @endif

            @if(!empty($resources))
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Source</flux:table.column>
                        <flux:table.column>Endpoint Name</flux:table.column>
                        <flux:table.column align="center">List</flux:table.column>
                        <flux:table.column align="center">Show</flux:table.column>
                        <flux:table.column align="center">Create</flux:table.column>
                        <flux:table.column align="center">Update</flux:table.column>
                        <flux:table.column align="center">Delete</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($resources as $index => $resource)
                            <flux:table.row :key="'resource-'.$index">
                                <flux:table.cell variant="strong">
                                    <code class="text-xs">{{ $resource['table_name'] }}</code>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input wire:model="resources.{{ $index }}.resource_name" placeholder="e.g. employees" size="sm" />
                                </flux:table.cell>
                                <flux:table.cell align="center"><flux:checkbox wire:model="resources.{{ $index }}.operations.list" /></flux:table.cell>
                                <flux:table.cell align="center"><flux:checkbox wire:model="resources.{{ $index }}.operations.show" /></flux:table.cell>
                                <flux:table.cell align="center"><flux:checkbox wire:model="resources.{{ $index }}.operations.create" /></flux:table.cell>
                                <flux:table.cell align="center"><flux:checkbox wire:model="resources.{{ $index }}.operations.update" /></flux:table.cell>
                                <flux:table.cell align="center"><flux:checkbox wire:model="resources.{{ $index }}.operations.delete" /></flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeResource({{ $index }})" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif

            @error('resources')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    {{-- API Keys (outside form — has its own actions) --}}
    @if($isEditing)
        <div x-show="tab === 'api-keys'" x-cloak role="tabpanel"
            class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 mt-6 p-6 space-y-6">

            {{-- Auth status reminder --}}
            @if(!$authEnabled)
                <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
                    <x-icon name="alert-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                    <div>
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-200">API key authentication is disabled</p>
                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">Go to the <button type="button" @click="setTab('configuration')" class="underline font-medium">Configuration</button> tab and enable "Require API Key" to enforce authentication on your endpoints.</p>
                    </div>
                </div>
            @endif

            {{-- Newly created key banner --}}
            @if($newlyCreatedKey)
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">Your new API key</p>
                            <p class="mt-1 text-xs text-green-700 dark:text-green-300">Copy this key now. It won't be shown again.</p>
                            <div class="mt-3 flex items-center gap-2">
                                <code class="block rounded bg-green-100 px-3 py-2 font-mono text-sm text-green-900 dark:bg-green-900 dark:text-green-100 select-all">{{ $newlyCreatedKey }}</code>
                                <flux:button size="sm" variant="ghost" icon="copy"
                                    x-on:click="navigator.clipboard.writeText('{{ $newlyCreatedKey }}'); $dispatch('toast', { type: 'success', message: 'Copied!', duration: 2000 })" />
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="x" wire:click="dismissNewKey" />
                    </div>
                </div>
            @endif

            {{-- Create new key --}}
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <flux:input wire:model="newKeyName" label="Key Name" placeholder="e.g. Production Key, Dev Key" />
                </div>
                <flux:button variant="primary" size="sm" icon="plus" wire:click="createApiKey" class="shrink-0">
                    Create Key
                </flux:button>
            </div>

            {{-- Keys list --}}
            @if($apiKeys->isNotEmpty())
                <div>
                    <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Active Keys</p>
                    <div class="space-y-2">
                        @foreach($apiKeys as $key)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $key->name }}</p>
                                        @if($key->isExpired())
                                            <flux:badge size="sm" color="red">Expired</flux:badge>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span class="font-mono">{{ $key->key_prefix }}...</span>
                                        <span>{{ $key->rate_limit }} req/min</span>
                                        @if($key->last_used_at)
                                            <span>Last used {{ $key->last_used_at->diffForHumans() }}</span>
                                        @else
                                            <span>Never used</span>
                                        @endif
                                        <span>Created {{ $key->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <flux:button variant="ghost" size="sm" icon="trash-2"
                                    wire:click="revokeApiKey({{ $key->id }})"
                                    wire:confirm="Revoke this API key? Any requests using it will immediately fail." />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                @if(!$newlyCreatedKey)
                    <div class="py-6 text-center">
                        <x-icon name="key" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No API keys yet. Create one to authenticate API requests.</p>
                    </div>
                @endif
            @endif
        </div>
    @endif

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2 mt-6">
            <flux:button variant="ghost" :href="route('api-specs.index')" wire:navigate>Cancel</flux:button>
            <flux:button variant="primary" type="submit">
                {{ $isEditing ? 'Save Changes' : 'Create Spec' }}
            </flux:button>
        </div>
    </form>
</div>
