<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Upload a File') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Upload a CSV, JSON, or Excel file to generate a read-only API from your data. No database required.') }}</p>

<h2 class="mt-10 mb-5 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Supported Files') }}</h2>
<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-zinc-200 dark:border-zinc-700">
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Format') }}</th>
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Max Size') }}</th>
            <th class="py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('Multi-Table') }}</th>
        </tr>
    </thead>
    <tbody class="text-zinc-600 dark:text-zinc-400">
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4">CSV (.csv)</td>
            <td class="py-3 pr-4">50 MB</td>
            <td class="py-3">{{ __('No — one table per file') }}</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4">JSON (.json)</td>
            <td class="py-3 pr-4">50 MB</td>
            <td class="py-3">{{ __('No — one table per file') }}</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4">Excel (.xlsx)</td>
            <td class="py-3 pr-4">50 MB</td>
            <td class="py-3">{{ __('Yes — each sheet becomes a table') }}</td>
        </tr>
    </tbody>
</table>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Step-by-Step') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">1</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Go to Data Sources') }}</p>
            <p class="mt-1">{{ __('Navigate to') }} <a href="{{ route('data-sources.index') }}" class="text-blue-600 underline hover:text-blue-800 dark:text-blue-400">{{ __('Data Sources') }}</a> {{ __('and click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Connect Data Source') }}</strong>.</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">2</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Select File Type') }}</p>
            <p class="mt-1">{{ __('Enter a name and choose CSV, JSON, or Excel as the data source type.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">3</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Upload Your File') }}</p>
            <p class="mt-1">{{ __('Click the upload area to select your file, or drag and drop. G8Connect will parse the file and show you the row/column count.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">4</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Select Tables') }}</p>
            <p class="mt-1">{{ __('For CSV and JSON, one table is created from the file. For Excel, each sheet appears as a separate table — select the ones you want.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">5</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Preview & Generate') }}</p>
            <p class="mt-1">{{ __('Review the detected columns and data types, then generate your API spec. File sources always produce read-only APIs (GET only).') }}</p>
        </div>
    </li>
</ol>

<div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950">
    <div class="flex gap-3">
        <x-icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
        <div>
            <p class="font-medium text-blue-800 dark:text-blue-200">{{ __('File Sources are Read-Only') }}</p>
            <p class="mt-2 text-sm leading-relaxed text-blue-700 dark:text-blue-300">{{ __('APIs generated from files only support GET operations (list and show). To create APIs with write operations (POST, PUT, DELETE), connect a database instead.') }}</p>
        </div>
    </div>
</div>
