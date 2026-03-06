<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $dataSource->name }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $dataSource->type->label() }} &middot; {{ $dataSource->status->label() }}</p>
        </div>
        <flux:button variant="ghost" :href="route('data-sources.index')" wire:navigate icon="arrow-left">
            Back
        </flux:button>
    </div>

    {{-- Schema Info --}}
    @if($schemas->isNotEmpty())
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Introspected Tables</flux:heading>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($schemas as $schema)
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                        <h3 class="font-medium text-zinc-900 dark:text-white">{{ $schema->table_name }}</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ count($schema->columns ?? []) }} columns
                        </p>
                        @if($schema->columns)
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach(array_slice($schema->columns, 0, 5) as $col)
                                    <flux:badge size="sm" variant="outline">{{ $col['name'] }}</flux:badge>
                                @endforeach
                                @if(count($schema->columns) > 5)
                                    <flux:badge size="sm" variant="outline" color="zinc">+{{ count($schema->columns) - 5 }} more</flux:badge>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Related API Specs --}}
    @if($specs->isNotEmpty())
        <div>
            <flux:heading size="lg" class="mb-4">API Specs</flux:heading>
            <div class="overflow-hidden outline-1 -outline-offset-1 outline-zinc-200 dark:outline-zinc-700 sm:rounded-lg">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100 sm:pl-6">Name</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Mode</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Status</th>
                            <th class="py-3.5 pr-4 pl-3 sm:pr-6"><span class="sr-only">View</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                        @foreach($specs as $spec)
                            <tr>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium text-zinc-900 dark:text-white sm:pl-6">{{ $spec->name }}</td>
                                <td class="px-3 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $spec->wizard_mode->label() }}</td>
                                <td class="px-3 py-4 text-sm">
                                    <flux:badge size="sm" :color="match($spec->status->value) { 'pending' => 'yellow', 'pushed' => 'blue', 'approved' => 'green', 'rejected' => 'red', 'deployed' => 'emerald', default => 'zinc' }">
                                        {{ $spec->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="py-4 pr-4 pl-3 text-right text-sm sm:pr-6">
                                    <flux:button variant="ghost" size="sm" :href="route('api-specs.show', ['uuid' => $spec->uuid])" wire:navigate>View</flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
