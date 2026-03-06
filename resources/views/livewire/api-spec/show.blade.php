<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('api-specs.index')" wire:navigate>API Specs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $apiSpec->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex items-end justify-between">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">{{ $apiSpec->name }}</flux:heading>
                <flux:badge size="sm" :color="match($apiSpec->status->value) { 'pending' => 'yellow', 'pushed' => 'blue', 'approved' => 'green', 'rejected' => 'red', 'deployed' => 'emerald', default => 'zinc' }">
                    {{ $apiSpec->status->label() }}
                </flux:badge>
            </div>
            <flux:text class="mt-2">{{ $apiSpec->wizard_mode->label() }} mode</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" size="sm" :href="route('api-specs.edit', ['uuid' => $apiSpec->uuid])" wire:navigate icon="pencil">
                Edit
            </flux:button>
            @if($apiSpec->wizard_mode->value === 'guided')
                <flux:button variant="ghost" size="sm" :href="route('api-specs.configure', ['uuid' => $apiSpec->uuid])" wire:navigate icon="settings">
                    Configure
                </flux:button>
            @endif
            <flux:button variant="ghost" size="sm" :href="route('api-specs.versions', ['uuid' => $apiSpec->uuid])" wire:navigate icon="history">
                Versions
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    {{-- OpenAPI Spec --}}
    @if($apiSpec->openapi_spec)
        <div x-data="{ view: 'preview' }" class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">OpenAPI Specification</flux:heading>
                <div class="flex gap-1">
                    <flux:button size="sm" :variant="'ghost'" @click="view = 'preview'" ::class="view === 'preview' && '!bg-zinc-100 dark:!bg-zinc-700'">
                        Preview
                    </flux:button>
                    <flux:button size="sm" :variant="'ghost'" @click="view = 'json'" ::class="view === 'json' && '!bg-zinc-100 dark:!bg-zinc-700'">
                        JSON
                    </flux:button>
                    <flux:button size="sm" variant="ghost" :href="route('api-specs.preview', ['uuid' => $apiSpec->uuid])" target="_blank" icon="arrow-up-right">
                        Open
                    </flux:button>
                </div>
            </div>

            {{-- Scalar API Reference (iframe) --}}
            <div x-show="view === 'preview'" class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 overflow-hidden">
                <iframe
                    src="{{ route('api-specs.preview', ['uuid' => $apiSpec->uuid]) }}"
                    class="w-full border-0"
                    style="height: 700px;"
                    loading="lazy"
                ></iframe>
            </div>

            {{-- Raw JSON --}}
            <div x-show="view === 'json'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-zinc-50 dark:bg-zinc-800 p-4 overflow-auto max-h-[600px]">
                <pre class="text-xs text-zinc-700 dark:text-zinc-300"><code>{{ json_encode($apiSpec->openapi_spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 p-10 text-center mb-8">
            <flux:text>No OpenAPI spec generated yet. Configure your resources and save to generate.</flux:text>
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
