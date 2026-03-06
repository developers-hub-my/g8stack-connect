<div>
    <flux:heading size="lg" class="mb-6">Guided Configuration: {{ $apiSpec->name }}</flux:heading>

    {{-- HTTP Methods --}}
    <div class="mb-8">
        <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-3">HTTP Methods</h3>
        <div class="flex gap-2">
            @foreach(['GET', 'POST', 'PUT', 'DELETE'] as $method)
                <button type="button" wire:click="toggleMethod('{{ $method }}')"
                    class="rounded-lg border-2 px-4 py-2 text-sm font-medium transition
                        {{ in_array($method, $methods) ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'border-zinc-200 text-zinc-500 dark:border-zinc-700' }}">
                    {{ $method }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mb-8 flex items-center gap-4">
        <div class="flex items-center">
            <input type="checkbox" id="pagination" wire:model.live="pagination"
                class="h-4 w-4 rounded border-zinc-300 text-brand-600 focus:ring-brand-600">
            <label for="pagination" class="ml-2 text-sm font-medium text-zinc-900 dark:text-white">Enable Pagination</label>
        </div>
        @if($pagination)
            <flux:input wire:model.live="perPage" type="number" min="5" max="100" class="w-24" label="Per Page" />
        @endif
    </div>

    {{-- Field Configuration --}}
    <div class="mb-8">
        <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-3">Field Configuration</h3>
        <div class="overflow-hidden outline-1 -outline-offset-1 outline-zinc-200 dark:outline-zinc-700 sm:rounded-lg">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100 sm:pl-6">Column</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Display Name</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">Exposed</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">PII</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">Required</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">Filterable</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">Sortable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                    @foreach($fields as $i => $field)
                        <tr class="{{ ($field['is_pii'] ?? false) ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                            <td class="py-3 pr-3 pl-4 text-sm font-medium text-zinc-900 dark:text-white sm:pl-6">
                                {{ $field['column_name'] }}
                                <span class="ml-1 text-xs text-zinc-400">{{ $field['data_type'] }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" wire:model.blur="fields.{{ $i }}.display_name"
                                    class="block w-full rounded-md border-zinc-300 text-sm shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:click="toggleField({{ $i }}, 'is_exposed')" {{ ($field['is_exposed'] ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-zinc-300 text-brand-600">
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($field['is_pii'] ?? false)
                                    <flux:badge size="sm" color="yellow">PII</flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:click="toggleField({{ $i }}, 'is_required')" {{ ($field['is_required'] ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-zinc-300 text-brand-600">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:click="toggleField({{ $i }}, 'is_filterable')" {{ ($field['is_filterable'] ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-zinc-300 text-brand-600">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:click="toggleField({{ $i }}, 'is_sortable')" {{ ($field['is_sortable'] ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-zinc-300 text-brand-600">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-2">
        <flux:button :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate variant="ghost">
            Cancel
        </flux:button>
        <flux:button wire:click="saveConfiguration" variant="primary">
            Save & Generate Spec
        </flux:button>
    </div>
</div>
