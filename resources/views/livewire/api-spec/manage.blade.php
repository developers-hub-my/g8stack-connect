<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $isEditing ? 'Edit API Spec' : 'Create API Spec' }}</flux:heading>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ $isEditing ? 'Update spec configuration, resources, and operations.' : 'Create a new API spec from an existing data source.' }}
        </p>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- LEFT: Resources (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Resources</flux:heading>
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

                    @if(empty($availableTables) && !$dataSourceId)
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 py-8 text-center">Select a data source first to see available tables.</p>
                    @elseif(empty($availableTables) && $dataSourceId)
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 py-8 text-center">No introspected tables found. Try re-introspecting the data source.</p>
                    @endif

                    @if(!empty($resources))
                        <div class="space-y-3">
                            @foreach($resources as $index => $resource)
                                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Database Table</label>
                                                <p class="text-sm font-mono text-zinc-700 dark:text-zinc-300">{{ $resource['table_name'] }}</p>
                                            </div>
                                            <flux:input
                                                wire:model="resources.{{ $index }}.resource_name"
                                                label="API Resource Name"
                                                placeholder="e.g. employees"
                                                size="sm"
                                            />
                                        </div>
                                        <flux:button variant="ghost" size="sm" icon="x" wire:click="removeResource({{ $index }})" class="ml-2 mt-4" />
                                    </div>

                                    {{-- CRUD Toggles --}}
                                    <div class="flex flex-wrap gap-4 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400 self-center">Operations:</label>
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.list" label="List" />
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.show" label="Show" />
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.create" label="Create" />
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.update" label="Update" />
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.delete" label="Delete" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('resources')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- RIGHT: Basic Info + Configuration (1/3 width) --}}
            <div class="space-y-6">
                {{-- Basic Info --}}
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
                    <flux:heading size="lg">Basic Information</flux:heading>

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
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4">
                    <flux:heading size="lg">Configuration</flux:heading>

                    <flux:checkbox wire:model="authEnabled" label="Require API Key" description="Enforce X-API-Key header" />
                    <flux:checkbox wire:model="pagination" label="Enable Pagination" description="Paginate list responses" />

                    <flux:input wire:model="rateLimit" label="Rate Limit (req/min)" type="number" min="1" max="10000" />

                    @if($pagination)
                        <flux:input wire:model="perPage" label="Per Page" type="number" min="1" max="100" />
                    @endif
                </div>

                {{-- Actions --}}
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-3">
                    @if($isEditing && $apiSpec)
                        @if($apiSpec->status->value === 'deployed')
                            <flux:button variant="danger" wire:click="undeploy" type="button" icon="power" class="w-full">
                                Undeploy
                            </flux:button>
                        @else
                            <flux:button variant="filled" color="emerald" wire:click="deploy" type="button" icon="rocket" class="w-full">
                                Deploy
                            </flux:button>
                        @endif
                    @endif

                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ $isEditing ? 'Save Changes' : 'Create Spec' }}
                    </flux:button>

                    <flux:button variant="ghost" :href="route('api-specs.index')" wire:navigate class="w-full">
                        Cancel
                    </flux:button>
                </div>
            </div>

        </div>
    </form>
</div>
