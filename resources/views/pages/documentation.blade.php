<x-layouts.app :title="__('Documentation')">
    <div x-data="{
        section: new URLSearchParams(window.location.search).get('section') || 'overview',
        navigate(s) {
            this.section = s;
            const url = new URL(window.location);
            url.searchParams.set('section', s);
            window.history.replaceState({}, '', url);
        }
    }" class="flex h-full w-full flex-1 gap-8">

        {{-- Sidebar Navigation --}}
        <nav class="hidden w-64 shrink-0 lg:block">
            <div class="sticky top-6 space-y-1">
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('User Guide') }}
                </p>

                @php
                    $navItems = [
                        ['key' => 'overview', 'icon' => 'home', 'label' => 'Overview'],
                        ['key' => 'connect-database', 'icon' => 'database', 'label' => 'Connect a Database'],
                        ['key' => 'connect-file', 'icon' => 'file-up', 'label' => 'Upload a File'],
                        ['key' => 'file-formats', 'icon' => 'file-text', 'label' => 'Accepted File Formats'],
                        ['key' => 'generate-spec', 'icon' => 'file-code', 'label' => 'Generate API Spec'],
                        ['key' => 'configure', 'icon' => 'settings', 'label' => 'Configure Resources'],
                        ['key' => 'deploy', 'icon' => 'rocket', 'label' => 'Deploy & Use API'],
                        ['key' => 'pii', 'icon' => 'shield', 'label' => 'PII & Data Privacy'],
                        ['key' => 'wizard-modes', 'icon' => 'wand-2', 'label' => 'Wizard Modes'],
                        ['key' => 'sql-mode', 'icon' => 'terminal', 'label' => 'Advanced SQL Mode'],
                        ['key' => 'faq', 'icon' => 'help-circle', 'label' => 'FAQ'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <button @click="navigate('{{ $item['key'] }}')"
                        :class="section === '{{ $item['key'] }}' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition">
                        <x-icon :name="$item['icon']" class="h-4 w-4" />
                        {{ __($item['label']) }}
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- Mobile Section Selector --}}
        <div class="mb-4 w-full lg:hidden">
            <select x-model="section" @change="navigate($event.target.value)"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @foreach($navItems as $item)
                    <option value="{{ $item['key'] }}">{{ __($item['label']) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Content Area --}}
        <div class="min-w-0 flex-1">
            @foreach($navItems as $item)
                <div x-show="section === '{{ $item['key'] }}'" x-cloak>
                    @include('pages.documentation.' . $item['key'])
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
