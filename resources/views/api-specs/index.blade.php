<x-layouts.app title="API Specs">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item>API Specs</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex items-end justify-between">
            <div>
                <flux:heading size="xl" level="1">API Specs</flux:heading>
                <flux:text class="mt-2">Manage your API specifications and endpoints.</flux:text>
            </div>
            <flux:button variant="primary" :href="route('api-specs.create')" wire:navigate icon="plus">
                Create Spec
            </flux:button>
        </div>

        <flux:separator variant="subtle" class="my-6" />

        @livewire('api-spec.index')
    </div>
</x-layouts.app>
