<x-layouts.app title="Data Sources">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">Data Sources</flux:heading>
            <flux:button variant="primary" icon="plus" :href="route('data-sources.create')" wire:navigate>
                Connect Data Source
            </flux:button>
        </div>

        @livewire('data-source.index')
    </div>
</x-layouts.app>
