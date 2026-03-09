<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('PII & Data Privacy') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('G8Connect automatically scans your data for personally identifiable information (PII) before generating any API spec.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('What Gets Flagged') }}</h2>
<p class="mb-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Columns matching these patterns are flagged as PII and excluded from the API by default:') }}</p>
<div class="flex flex-wrap gap-2">
    @foreach(['password', 'secret', 'token', 'ic_number', 'nric', 'mykad', 'passport', 'ssn', 'credit_card', 'card_number', 'cvv', 'bank_account', 'pin', 'private_key', 'api_key'] as $pattern)
        <span class="rounded-md bg-red-100 px-2.5 py-1 font-mono text-xs text-red-700 dark:bg-red-900 dark:text-red-300">{{ $pattern }}</span>
    @endforeach
</div>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('How It Works') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700 dark:bg-red-900 dark:text-red-300">1</span>
        <span>{{ __('During the wizard, after selecting tables, G8Connect scans all column names') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700 dark:bg-red-900 dark:text-red-300">2</span>
        <span>{{ __('Columns matching PII patterns are flagged and shown to you') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700 dark:bg-red-900 dark:text-red-300">3</span>
        <span>{{ __('Flagged columns are excluded from the generated spec by default') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700 dark:bg-red-900 dark:text-red-300">4</span>
        <span>{{ __('In Guided Mode, you can override this — explicitly expose a PII column if needed') }}</span>
    </li>
</ol>

<div class="mt-8 rounded-lg border border-red-200 bg-red-50 p-5 dark:border-red-800 dark:bg-red-950">
    <div class="flex gap-3">
        <x-icon name="shield-alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
        <div>
            <p class="font-medium text-red-800 dark:text-red-200">{{ __('Important') }}</p>
            <p class="mt-2 text-sm leading-relaxed text-red-700 dark:text-red-300">{{ __('PII detection is pattern-based and may not catch all sensitive data. Always review your API spec before deploying to ensure no sensitive information is exposed.') }}</p>
        </div>
    </div>
</div>
