<x-layouts.app title="API Spec Versions">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @livewire('api-spec.version-history', ['specUuid' => $uuid])
    </div>
</x-layouts.app>
