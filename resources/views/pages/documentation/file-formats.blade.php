<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Accepted File Formats') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Your files must follow these structures for G8Connect to parse them correctly.') }}</p>

{{-- CSV --}}
<h2 class="mt-10 mb-3 text-xl font-semibold text-zinc-900 dark:text-white">CSV</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Standard CSV with a header row as the first line. All subsequent rows are data.') }}</p>
<pre class="mb-5 overflow-x-auto rounded-lg bg-zinc-900 p-5 text-sm leading-relaxed text-zinc-100"><code>name,email,department,salary,join_date,is_active
Ahmad Hassan,ahmad@example.com,Engineering,5500.00,2024-01-15,true
Siti Aminah,siti@example.com,Marketing,4800.50,2023-06-01,true
Razak Ali,razak@example.com,Finance,6200.00,2022-11-20,false</code></pre>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('First row must be column headers') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Comma-delimited (standard CSV)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('UTF-8 encoding recommended') }}
    </li>
</ul>

{{-- JSON --}}
<h2 class="mt-10 mb-3 text-xl font-semibold text-zinc-900 dark:text-white">JSON</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Two formats are accepted:') }}</p>

<h3 class="mt-6 mb-3 text-lg font-medium text-zinc-800 dark:text-zinc-200">{{ __('Format 1 — Array of objects') }}</h3>
<pre class="mb-5 overflow-x-auto rounded-lg bg-zinc-900 p-5 text-sm leading-relaxed text-zinc-100"><code>[
  {
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "department": "Engineering",
    "salary": 5500.00
  },
  {
    "name": "Siti Aminah",
    "email": "siti@example.com",
    "department": "Marketing",
    "salary": 4800.50
  }
]</code></pre>

<h3 class="mt-6 mb-3 text-lg font-medium text-zinc-800 dark:text-zinc-200">{{ __('Format 2 — Wrapper object') }}</h3>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('If your data is wrapped in an object, G8Connect will find the first key containing an array of objects.') }}</p>
<pre class="mb-5 overflow-x-auto rounded-lg bg-zinc-900 p-5 text-sm leading-relaxed text-zinc-100"><code>{
  "data": [
    {
      "sensor_id": "SENS-001",
      "temperature": 28.5,
      "timestamp": "2024-01-15 08:30:00"
    },
    {
      "sensor_id": "SENS-002",
      "temperature": 31.2,
      "timestamp": "2024-01-15 08:31:00"
    }
  ]
}</code></pre>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Valid JSON (UTF-8)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('All objects must have consistent keys') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Nested arrays/objects are flattened to JSON strings') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('The wrapper key can be any name — "data", "results", "records", etc.') }}
    </li>
</ul>

{{-- Excel --}}
<h2 class="mt-10 mb-3 text-xl font-semibold text-zinc-900 dark:text-white">Excel (.xlsx)</h2>
<p class="mb-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Standard Excel workbook. Each sheet becomes a separate table.') }}</p>
<div class="mb-5 overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
    <table class="w-full text-sm">
        <caption class="bg-zinc-100 px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
            {{ __('Sheet: "Employees"') }}
        </caption>
        <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                <th class="px-4 py-3 text-left font-medium text-zinc-900 dark:text-white">name</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-900 dark:text-white">email</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-900 dark:text-white">department</th>
            </tr>
        </thead>
        <tbody class="text-zinc-600 dark:text-zinc-400">
            <tr class="border-b border-zinc-100 dark:border-zinc-800">
                <td class="px-4 py-3">Ahmad Hassan</td>
                <td class="px-4 py-3">ahmad@example.com</td>
                <td class="px-4 py-3">Engineering</td>
            </tr>
            <tr>
                <td class="px-4 py-3">Siti Aminah</td>
                <td class="px-4 py-3">siti@example.com</td>
                <td class="px-4 py-3">Marketing</td>
            </tr>
        </tbody>
    </table>
</div>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Must be .xlsx format (not .xls)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('First row of each sheet must be column headers') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Sheets with fewer than 2 rows (header + 1 data row) are skipped') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-400"></span>
        {{ __('Sheet names become table names (converted to snake_case)') }}
    </li>
</ul>

{{-- Column Type Detection --}}
<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Column Type Detection') }}</h2>
<p class="mb-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('G8Connect automatically detects column types by sampling your data:') }}</p>
<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-zinc-200 dark:border-zinc-700">
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Detected Type') }}</th>
            <th class="py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('Example Values') }}</th>
        </tr>
    </thead>
    <tbody class="text-zinc-600 dark:text-zinc-400">
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">integer</td>
            <td class="py-3">1, 42, 1000</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">decimal</td>
            <td class="py-3">3.14, 99.99, 0.5</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">boolean</td>
            <td class="py-3">true/false, yes/no, 1/0</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">date</td>
            <td class="py-3">2024-01-15</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">datetime</td>
            <td class="py-3">2024-01-15 08:30:00</td>
        </tr>
        <tr>
            <td class="py-3 pr-4 font-mono text-xs">varchar</td>
            <td class="py-3">{{ __('Any text (fallback)') }}</td>
        </tr>
    </tbody>
</table>
