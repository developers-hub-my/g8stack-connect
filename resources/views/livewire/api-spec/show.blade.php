<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $apiSpec->name }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ $apiSpec->wizard_mode->label() }} &middot;
                <flux:badge size="sm" :color="match($apiSpec->status->value) { 'pending' => 'yellow', 'pushed' => 'blue', 'approved' => 'green', 'rejected' => 'red', 'deployed' => 'emerald', default => 'zinc' }">
                    {{ $apiSpec->status->label() }}
                </flux:badge>
            </p>
        </div>
        <div class="flex gap-2">
            <flux:button variant="primary" size="sm" :href="route('api-specs.edit', ['uuid' => $apiSpec->uuid])" wire:navigate icon="pencil">
                Edit
            </flux:button>
            @if($apiSpec->wizard_mode->value === 'guided')
                <flux:button variant="ghost" :href="route('api-specs.configure', ['uuid' => $apiSpec->uuid])" wire:navigate icon="settings">
                    Configure
                </flux:button>
            @endif
            <flux:button variant="ghost" :href="route('api-specs.versions', ['uuid' => $apiSpec->uuid])" wire:navigate icon="history">
                Versions
            </flux:button>
            <flux:button variant="ghost" :href="route('api-specs.index')" wire:navigate icon="arrow-left">
                Back
            </flux:button>
        </div>
    </div>

    {{-- OpenAPI Spec --}}
    @if($apiSpec->openapi_spec)
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">OpenAPI Specification</flux:heading>
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-4 overflow-auto max-h-[600px]">
                <pre class="text-xs text-zinc-700 dark:text-zinc-300"><code>{{ json_encode($apiSpec->openapi_spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    @endif

    {{-- Version History --}}
    @if($versions->isNotEmpty())
        <div>
            <flux:heading size="lg" class="mb-4">Recent Versions</flux:heading>
            <div class="space-y-2">
                @foreach($versions->take(5) as $version)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                        <div>
                            <span class="font-medium text-zinc-900 dark:text-white">v{{ $version->version_number }}</span>
                            <span class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $version->change_summary }}</span>
                        </div>
                        <span class="text-sm text-zinc-400">{{ $version->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
