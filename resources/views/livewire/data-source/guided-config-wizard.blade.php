<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('api-specs.index')" wire:navigate>API Specs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate>{{ $apiSpec->name }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Configure</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl" level="1">Field Configuration</flux:heading>
            <flux:text class="mt-2">Configure per-field exposure, display names, and query options for <strong>{{ $apiSpec->name }}</strong>.</flux:text>
        </div>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    {{-- Resource Settings --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 p-6 space-y-6">
        @if($tables->count() > 1)
            <flux:select wire:model.live="selectedTableId" label="Resource Table">
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->resource_name }} ({{ $table->table_name }})</option>
                @endforeach
            </flux:select>
        @else
            <div>
                <flux:text class="text-xs font-medium mb-1">Resource Table</flux:text>
                <flux:text class="font-medium">
                    {{ $tables->first()?->resource_name }}
                    <span class="ml-1 font-mono text-xs text-zinc-400">({{ $tables->first()?->table_name }})</span>
                </flux:text>
            </div>
        @endif

        <div>
            <flux:text class="text-xs font-medium mb-2">Operations</flux:text>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach(['list' => 'List', 'show' => 'Show', 'create' => 'Create', 'update' => 'Update', 'delete' => 'Delete'] as $op => $label)
                    <flux:checkbox
                        wire:click="toggleOperation('{{ $op }}')"
                        :checked="$operations[$op] ?? false"
                        :label="$label"
                    />
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <flux:checkbox wire:model.live="pagination" label="Enable Pagination" description="Paginate list responses" />
            @if($pagination)
                <flux:input wire:model.live="perPage" type="number" min="5" max="100" label="Per Page" />
            @endif
        </div>
    </div>

    {{-- Fields --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 mt-6 p-6 space-y-4">
        <flux:heading size="lg">Fields</flux:heading>

        @if(!empty($fields))
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Column</flux:table.column>
                    <flux:table.column>Display Name</flux:table.column>
                    <flux:table.column align="center">Exposed</flux:table.column>
                    <flux:table.column align="center">PII</flux:table.column>
                    <flux:table.column align="center">Required</flux:table.column>
                    <flux:table.column align="center">Filterable</flux:table.column>
                    <flux:table.column align="center">Sortable</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($fields as $i => $field)
                        <flux:table.row :key="'field-'.$i" class="{{ ($field['is_pii'] ?? false) ? 'bg-amber-50 dark:bg-amber-900/10' : '' }}">
                            <flux:table.cell variant="strong">
                                {{ $field['column_name'] }}
                                <span class="ml-1 text-xs font-normal text-zinc-400">{{ $field['data_type'] }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model.blur="fields.{{ $i }}.display_name" size="sm" />
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_exposed')" :checked="$field['is_exposed'] ?? false" />
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                @if($field['is_pii'] ?? false)
                                    <flux:badge size="sm" color="amber">PII</flux:badge>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600">&ndash;</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_required')" :checked="$field['is_required'] ?? false" />
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_filterable')" :checked="$field['is_filterable'] ?? false" />
                            </flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:checkbox wire:click="toggleField({{ $i }}, 'is_sortable')" :checked="$field['is_sortable'] ?? false" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text class="py-8 text-center">No fields found for this table. Try re-introspecting the data source.</flux:text>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-2 mt-6">
        <flux:button variant="ghost" :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate>Cancel</flux:button>
        <flux:button variant="primary" wire:click="saveConfiguration">Save & Generate Spec</flux:button>
    </div>
</div>
