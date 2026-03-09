<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('G8Connect Documentation') }}</h1>
<p class="mt-4 text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">
    {{ __('G8Connect turns your data sources into production-ready APIs. Connect a database or upload a file, and G8Connect will generate an OpenAPI specification for you — ready to deploy or submit for governance approval.') }}
</p>

<h2 class="mt-10 mb-5 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('How It Works') }}</h2>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">1</span>
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Connect') }}</h3>
        </div>
        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Connect a database (MySQL, PostgreSQL, MSSQL, SQLite) or upload a file (CSV, JSON, Excel).') }}</p>
    </div>
    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">2</span>
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Introspect') }}</h3>
        </div>
        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('G8Connect reads your schema — tables, columns, data types — and scans for sensitive data automatically.') }}</p>
    </div>
    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300">3</span>
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Generate') }}</h3>
        </div>
        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('An OpenAPI 3.1 spec is generated with clean resource names, field mappings, and PII exclusions.') }}</p>
    </div>
</div>

<h2 class="mt-10 mb-5 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Supported Data Sources') }}</h2>
<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-zinc-200 dark:border-zinc-700">
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Type') }}</th>
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Source') }}</th>
            <th class="py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('Operations') }}</th>
        </tr>
    </thead>
    <tbody class="text-zinc-600 dark:text-zinc-400">
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4">{{ __('Database') }}</td>
            <td class="py-3 pr-4">MySQL, PostgreSQL, MSSQL, SQLite</td>
            <td class="py-3">{{ __('Read & Write (configurable)') }}</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4">{{ __('File') }}</td>
            <td class="py-3 pr-4">CSV, JSON, Excel (.xlsx)</td>
            <td class="py-3">{{ __('Read-only') }}</td>
        </tr>
    </tbody>
</table>

<h2 class="mt-10 mb-5 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Quick Links') }}</h2>
<div class="grid gap-3 sm:grid-cols-2">
    <button @click="navigate('connect-database')" class="flex items-center gap-3 rounded-lg border border-zinc-200 p-4 text-left text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
        <x-icon name="database" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
        <span class="font-medium text-zinc-900 dark:text-white">{{ __('Connect a Database') }}</span>
    </button>
    <button @click="navigate('connect-file')" class="flex items-center gap-3 rounded-lg border border-zinc-200 p-4 text-left text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
        <x-icon name="file-up" class="h-5 w-5 text-green-600 dark:text-green-400" />
        <span class="font-medium text-zinc-900 dark:text-white">{{ __('Upload a File') }}</span>
    </button>
    <button @click="navigate('file-formats')" class="flex items-center gap-3 rounded-lg border border-zinc-200 p-4 text-left text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
        <x-icon name="file-text" class="h-5 w-5 text-purple-600 dark:text-purple-400" />
        <span class="font-medium text-zinc-900 dark:text-white">{{ __('Accepted File Formats') }}</span>
    </button>
    <button @click="navigate('wizard-modes')" class="flex items-center gap-3 rounded-lg border border-zinc-200 p-4 text-left text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
        <x-icon name="wand-2" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
        <span class="font-medium text-zinc-900 dark:text-white">{{ __('Wizard Modes') }}</span>
    </button>
</div>
