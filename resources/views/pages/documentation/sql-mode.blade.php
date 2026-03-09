<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Advanced SQL Mode') }}</h1>
<p class="mt-4 mb-8 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Write custom SELECT queries that become named GET endpoints. Ideal for complex reads — joins, aggregations, computed columns — that go beyond simple CRUD.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('How It Works') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900 dark:text-amber-300">1</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Connect a database') }}</p>
            <p class="mt-1">{{ __('Advanced mode works with database sources only (MySQL, PostgreSQL, MSSQL, SQLite). File sources are not supported.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900 dark:text-amber-300">2</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Write your SQL query') }}</p>
            <p class="mt-1">{{ __('Write a SELECT query in the SQL editor. You can reference any table shown in the introspection step.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900 dark:text-amber-300">3</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Validate & test') }}</p>
            <p class="mt-1">{{ __('Each query is dry-run with LIMIT 1 to detect result columns and parameter bindings. No data is returned during validation.') }}</p>
        </div>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900 dark:text-amber-300">4</span>
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Generate spec') }}</p>
            <p class="mt-1">{{ __('Each query becomes a named GET endpoint in the OpenAPI spec. PII columns are automatically excluded.') }}</p>
        </div>
    </li>
</ol>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Multiple Endpoints') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('You can define multiple SQL queries per spec. Each query becomes its own GET endpoint:') }}</p>
<div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <th class="px-4 py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('Endpoint Name') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('URL') }}</th>
            </tr>
        </thead>
        <tbody class="text-zinc-600 dark:text-zinc-400">
            <tr class="border-b border-zinc-100 dark:border-zinc-800">
                <td class="px-4 py-3"><code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">active-employees</code></td>
                <td class="px-4 py-3"><code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">GET /api/connect/{slug}/active-employees</code></td>
            </tr>
            <tr class="border-b border-zinc-100 dark:border-zinc-800">
                <td class="px-4 py-3"><code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">sales-summary</code></td>
                <td class="px-4 py-3"><code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800">GET /api/connect/{slug}/sales-summary</code></td>
            </tr>
        </tbody>
    </table>
</div>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Parameter Binding') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Use :named parameters in your query. They become required query parameters in the API:') }}</p>
<div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
    <pre class="text-sm text-zinc-700 dark:text-zinc-300"><code>SELECT name, email, department
FROM employees
WHERE department = :department AND active = :active</code></pre>
</div>
<p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Calling: GET /api/connect/{slug}/active-employees?department=Engineering&active=1') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Security Constraints') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('All SQL queries are subject to strict safety limits:') }}</p>
<div class="grid gap-3 sm:grid-cols-2">
    <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <x-icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" />
        <div>
            <p class="font-medium text-zinc-900 dark:text-white">{{ __('SELECT only') }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Only SELECT and WITH (CTE) are allowed. INSERT, UPDATE, DELETE, DROP, and other write operations are blocked.') }}</p>
        </div>
    </div>
    <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <x-icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
        <div>
            <p class="font-medium text-zinc-900 dark:text-white">{{ __('10-second timeout') }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Queries are terminated after 10 seconds. This limit is hardcoded and cannot be changed.') }}</p>
        </div>
    </div>
    <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <x-icon name="list" class="mt-0.5 h-5 w-5 shrink-0 text-purple-600 dark:text-purple-400" />
        <div>
            <p class="font-medium text-zinc-900 dark:text-white">{{ __('1,000 row cap') }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Maximum 1,000 rows per query execution. Results are paginated within this cap.') }}</p>
        </div>
    </div>
    <div class="flex items-start gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <x-icon name="lock" class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
        <div>
            <p class="font-medium text-zinc-900 dark:text-white">{{ __('System tables blocked') }}</p>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Access to information_schema, pg_catalog, sys, mysql, and sqlite_master is prohibited.') }}</p>
        </div>
    </div>
</div>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Supported SQL Features') }}</h2>
<ul class="space-y-2 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('JOINs (INNER, LEFT, RIGHT, CROSS)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('Aggregate functions (COUNT, SUM, AVG, MIN, MAX)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('GROUP BY, HAVING, ORDER BY') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('Subqueries and Common Table Expressions (WITH / CTE)') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('CASE expressions and computed columns') }}
    </li>
    <li class="flex gap-2">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-green-400"></span>
        {{ __('Named (:param) and positional (?) parameter binding') }}
    </li>
</ul>
