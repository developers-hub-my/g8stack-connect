<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Connect a Database') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('G8Connect can connect to MySQL, PostgreSQL, MSSQL, and SQLite databases to introspect their schema and generate API specs.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Prerequisites') }}</h2>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('A database with at least one table containing data') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Database credentials (host, port, database name, username, password)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        <span><strong class="text-zinc-900 dark:text-white">{{ __('Recommended') }}</strong>: {{ __('Use a read-only database account for security') }}</span>
    </li>
</ul>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Step-by-Step') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">1</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Go to Data Sources') }}</p>
            <p class="mt-1">{{ __('Navigate to') }} <a href="{{ route('data-sources.index') }}" class="text-blue-600 underline hover:text-blue-800 dark:text-blue-400">{{ __('Data Sources') }}</a> {{ __('and click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Connect Data Source') }}</strong>.</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">2</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Enter Details') }}</p>
            <p class="mt-1">{{ __('Give your data source a name and select the database type (MySQL, PostgreSQL, MSSQL, or SQLite).') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">3</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Enter Credentials') }}</p>
            <p class="mt-1">{{ __('Fill in the connection details: host, port, database name, username, and password. For SQLite, provide the file path.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">4</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Test Connection') }}</p>
            <p class="mt-1">{{ __('Click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Test Connection') }}</strong> {{ __('to verify the credentials work. You must pass this test before proceeding.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">5</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Select Tables') }}</p>
            <p class="mt-1">{{ __('G8Connect will introspect your database and show all available tables. Select the tables you want to expose as API resources.') }}</p>
        </div>
    </li>
</ol>

<div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950">
    <div class="flex gap-3">
        <x-icon name="alert-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
        <div>
            <p class="font-medium text-amber-800 dark:text-amber-200">{{ __('Security Tip') }}</p>
            <p class="mt-2 text-sm leading-relaxed text-amber-700 dark:text-amber-300">{{ __('Always use a read-only database account. G8Connect validates credentials but cannot prevent write access if the account has write permissions. Your credentials are encrypted at rest and never logged.') }}</p>
        </div>
    </div>
</div>
