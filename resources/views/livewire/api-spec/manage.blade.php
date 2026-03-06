<div>
    <div class="mb-6">
        <flux:heading size="xl">{{ $isEditing ? 'Edit API Spec' : 'Create API Spec' }}</flux:heading>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ $isEditing ? 'Update spec configuration, resources, and operations.' : 'Create a new API spec from an existing data source.' }}
        </p>
    </div>

    <form wire:submit="save">

        {{-- Top bar: Basic Info + Configuration --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-6">
            {{-- Basic Info --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
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
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                <flux:heading size="lg">Configuration</flux:heading>
                <flux:checkbox wire:model="authEnabled" label="Require API Key" description="Enforce X-API-Key header" />
                <flux:checkbox wire:model="pagination" label="Enable Pagination" description="Paginate list responses" />
                <flux:input wire:model="rateLimit" label="Rate Limit (req/min)" type="number" min="1" max="10000" />
                @if($pagination)
                    <flux:input wire:model="perPage" label="Per Page" type="number" min="1" max="100" />
                @endif
            </div>

            {{-- Actions --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3">
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

        {{-- Resources (full width) --}}
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="py-3 pr-3 pl-4 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider sm:pl-6">Table</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Resource Name</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">List</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Show</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Create</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Update</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Delete</th>
                                <th class="py-3 pr-4 pl-3 sm:pr-6"><span class="sr-only">Remove</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @foreach($resources as $index => $resource)
                                <tr>
                                    <td class="py-3 pr-3 pl-4 text-sm sm:pl-6">
                                        <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $resource['table_name'] }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <flux:input wire:model="resources.{{ $index }}.resource_name" placeholder="e.g. employees" size="sm" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.list" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.show" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.create" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.update" />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <flux:checkbox wire:model="resources.{{ $index }}.operations.delete" />
                                    </td>
                                    <td class="py-3 pr-4 pl-3 text-right sm:pr-6">
                                        <flux:button variant="ghost" size="sm" icon="x" wire:click="removeResource({{ $index }})" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @error('resources')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </form>
</div>
