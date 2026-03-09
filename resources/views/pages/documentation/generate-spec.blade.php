<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Generate API Spec') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('After connecting a data source and selecting tables, G8Connect generates an OpenAPI 3.1 specification.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('What Happens During Generation') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900 dark:text-purple-300">1</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Table names are cleaned up') }}</p>
            <p class="mt-1">{{ __('e.g., "tbl_emp_records" becomes "employees"') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900 dark:text-purple-300">2</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('PII columns are excluded') }}</p>
            <p class="mt-1">{{ __('Columns matching sensitive patterns (password, ic_number, etc.) are automatically removed') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900 dark:text-purple-300">3</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Operations are assigned') }}</p>
            <p class="mt-1">{{ __('Database sources get list + show by default (write operations are opt-in); file sources get read-only') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900 dark:text-purple-300">4</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('OpenAPI spec is created') }}</p>
            <p class="mt-1">{{ __('A complete spec with schemas, paths, and request/response definitions') }}</p>
        </div>
    </li>
</ol>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('After Generation') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Your spec is saved and you can:') }}</p>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('View the interactive API documentation (Scalar viewer)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Edit resource names, fields, and operations') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Configure field-level settings (Guided Mode)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Deploy the spec to make the API live') }}
    </li>
</ul>
