<x-layouts.app title="API Specs">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">API Specs</flux:heading>
            <flux:button variant="primary" :href="route('api-specs.create')" wire:navigate icon="plus">
                Create Spec
            </flux:button>
        </div>

        @livewire('api-spec.index')
    </div>
</x-layouts.app>
