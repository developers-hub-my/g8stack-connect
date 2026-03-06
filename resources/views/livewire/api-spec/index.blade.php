<div>
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search API specs..." icon="search" class="w-64" />
            <flux:select wire:model.live="statusFilter" class="w-48">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\SpecStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="mt-4 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden outline-1 -outline-offset-1 outline-zinc-200 dark:outline-zinc-700 sm:rounded-lg">
                    <table class="relative min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Mode</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Created</th>
                                <th scope="col" class="py-3.5 pr-4 pl-3 sm:pr-6"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @forelse ($specs as $spec)
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-zinc-900 dark:text-white sm:pl-6">
                                        {{ $spec->name }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $spec->wizard_mode->label() }}
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <flux:badge size="sm" :color="match($spec->status->value) { 'pending' => 'yellow', 'pushed' => 'blue', 'approved' => 'green', 'rejected' => 'red', 'deployed' => 'emerald', default => 'zinc' }">
                                            {{ $spec->status->label() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $spec->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-6">
                                        <flux:button variant="ghost" size="sm" :href="route('api-specs.show', ['uuid' => $spec->uuid])" wire:navigate>
                                            View
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <x-empty-state
                                            title="No API specs found"
                                            description="Generate specs by connecting a data source."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $specs->links() }}
    </div>
</div>
