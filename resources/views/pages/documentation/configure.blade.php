<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Configure Resources') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('After generating a spec, you can fine-tune each resource (table) and its fields.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Resource Settings') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Each table in your spec becomes an API resource. You can configure:') }}</p>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Resource Name') }}</strong> — {{ __('the clean API name (e.g., "employees" instead of "tbl_emp")') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Operations') }}</strong> — {{ __('toggle list, show, create, update, delete per resource') }}</span>
    </li>
</ul>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Field Settings (Guided Mode)') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('In Guided Mode, you get per-field control:') }}</p>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Exposed') }}</strong> — {{ __('whether the field appears in API responses') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Display Name') }}</strong> — {{ __('the name shown in the API (instead of raw column name)') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Required') }}</strong> — {{ __('whether the field is required on create/update') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Filterable') }}</strong> — {{ __('allow filtering via query parameters') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Sortable') }}</strong> — {{ __('allow sorting by this field') }}</span>
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('PII') }}</strong> — {{ __('flagged as sensitive data (excluded by default)') }}</span>
    </li>
</ul>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('How to Configure') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">1</span>
        <span>{{ __('Go to your API Spec and click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Configure') }}</strong></span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">2</span>
        <span>{{ __('Select a resource from the dropdown') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">3</span>
        <span>{{ __('Toggle operations and field settings') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">4</span>
        <span>{{ __('Click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Save Configuration') }}</strong> — {{ __('the spec regenerates automatically') }}</span>
    </li>
</ol>
