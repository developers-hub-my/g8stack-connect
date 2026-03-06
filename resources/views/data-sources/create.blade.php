<x-layouts.app title="Connect Data Source">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <flux:heading size="xl">Connect Data Source</flux:heading>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Follow the wizard to connect a data source and generate an API spec.</p>
        </div>

        @livewire('data-source.connect-wizard')
    </div>
</x-layouts.app>
