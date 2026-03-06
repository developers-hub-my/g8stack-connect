<div>
    <div class="mb-6">
        <flux:heading size="xl">Field Configuration</flux:heading>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Configure per-field exposure, display names, and query options for <strong>{{ $apiSpec->name }}</strong>.
        </p>
    </div>

    {{-- HTTP Methods --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4 mb-6">
        <flux:heading size="lg">HTTP Methods</flux:heading>
        <div class="flex flex-wrap gap-2">
            @foreach(['GET', 'POST', 'PUT', 'DELETE'] as $method)
                <flux:button
                    wire:click="toggleMethod('{{ $method }}')"
                    :variant="in_array($method, $methods) ? 'primary' : 'ghost'"
                    size="sm"
                >
                    {{ $method }}
                </flux:button>
            @endforeach
        </div>
    </div>

    {{-- Pagination --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4 mb-6">
        <flux:heading size="lg">Pagination</flux:heading>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <flux:checkbox wire:model.live="pagination" label="Enable Pagination" description="Paginate list responses" />
            @if($pagination)
                <flux:input wire:model.live="perPage" type="number" min="5" max="100" label="Per Page" />
            @endif
        </div>
    </div>

    {{-- Field Configuration --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 space-y-4 mb-6">
        <flux:heading size="lg">Fields</flux:heading>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Control which columns are exposed, set display names, and mark fields as filterable or sortable. PII-flagged fields are highlighted.
        </p>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="py-3 pr-3 pl-4 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider sm:pl-6">Column</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Display Name</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Exposed</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">PII</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Required</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Filterable</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Sortable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                    @foreach($fields as $i => $field)
                        <tr class="{{ ($field['is_pii'] ?? false) ? 'bg-amber-50 dark:bg-amber-900/10' : '' }}">
                            <td class="py-3 pr-3 pl-4 text-sm sm:pl-6">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $field['column_name'] }}</span>
                                <span class="ml-1 text-xs text-zinc-400">{{ $field['data_type'] }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <flux:input wire:model.blur="fields.{{ $i }}.display_name" size="sm" />
                            </td>
                            <td class="px-3 py-3 text-center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_exposed')" :checked="$field['is_exposed'] ?? false" />
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($field['is_pii'] ?? false)
                                    <flux:badge size="sm" color="amber">PII</flux:badge>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600">&ndash;</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_required')" :checked="$field['is_required'] ?? false" />
                            </td>
                            <td class="px-3 py-3 text-center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_filterable')" :checked="$field['is_filterable'] ?? false" />
                            </td>
                            <td class="px-3 py-3 text-center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_sortable')" :checked="$field['is_sortable'] ?? false" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <div></div>
        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate>
                Cancel
            </flux:button>
            <flux:button variant="primary" wire:click="saveConfiguration">
                Save & Generate Spec
            </flux:button>
        </div>
    </div>
</div>
