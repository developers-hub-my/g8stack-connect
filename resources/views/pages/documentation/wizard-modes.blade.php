<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Wizard Modes') }}</h1>
<p class="mt-4 mb-8 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('G8Connect offers different wizard modes to match your technical level and needs.') }}</p>

<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <div class="rounded-lg border-2 border-blue-200 p-6 dark:border-blue-800">
        <div class="mb-4 flex items-center gap-2">
            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">{{ __('Simple') }}</span>
        </div>
        <p class="mb-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('For non-technical users') }}</p>
        <ul class="space-y-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                {{ __('Pick tables → auto CRUD') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                {{ __('Zero configuration needed') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                {{ __('Clean defaults applied') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                {{ __('PII auto-excluded') }}
            </li>
        </ul>
    </div>

    <div class="rounded-lg border-2 border-green-200 p-6 dark:border-green-800">
        <div class="mb-4 flex items-center gap-2">
            <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">{{ __('Guided') }}</span>
        </div>
        <p class="mb-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('For backend developers') }}</p>
        <ul class="space-y-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
                {{ __('Choose fields to expose') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
                {{ __('Select HTTP methods') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
                {{ __('Configure filters & sorting') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
                {{ __('Rename fields for the API') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
                {{ __('Set pagination options') }}
            </li>
        </ul>
    </div>

    <div class="rounded-lg border-2 border-zinc-200 p-6 dark:border-zinc-700">
        <div class="mb-4 flex items-center gap-2">
            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-bold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ __('Advanced') }}</span>
            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('Coming soon') }}</span>
        </div>
        <p class="mb-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('For data scientists') }}</p>
        <ul class="space-y-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
                {{ __('Write custom SQL queries') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
                {{ __('Complex joins & aggregations') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
                {{ __('Named GET endpoints') }}
            </li>
            <li class="flex gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
                {{ __('Parameter binding') }}
            </li>
        </ul>
    </div>
</div>
