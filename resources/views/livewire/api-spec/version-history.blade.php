<div>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Version History: {{ $apiSpec->name }}</flux:heading>
        <flux:button variant="ghost" :href="route('api-specs.show', ['uuid' => $apiSpec->uuid])" wire:navigate icon="arrow-left">
            Back
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        {{-- Version List --}}
        <div class="lg:col-span-1">
            <div class="space-y-2">
                @foreach($versions as $version)
                    <button type="button" wire:click="selectVersion({{ $version->version_number }})"
                        class="w-full rounded-lg border-2 p-3 text-left transition
                            {{ ($currentVersion && $currentVersion->version_number === $version->version_number) ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">v{{ $version->version_number }}</div>
                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $version->created_at->diffForHumans() }}</div>
                        @if($version->change_summary)
                            <div class="mt-1 text-xs text-zinc-400">{{ Str::limit($version->change_summary, 50) }}</div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Spec Viewer --}}
        <div class="lg:col-span-3">
            @if($currentVersion)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-white">
                        Version {{ $currentVersion->version_number }}
                        @if($currentVersion->change_summary)
                            — {{ $currentVersion->change_summary }}
                        @endif
                    </h3>
                </div>
                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-4 overflow-auto max-h-[600px]">
                    <pre class="text-xs text-zinc-700 dark:text-zinc-300"><code>{{ json_encode($currentVersion->openapi_spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </div>
            @else
                <div class="py-12 text-center text-zinc-500">
                    No versions available.
                </div>
            @endif
        </div>
    </div>
</div>
