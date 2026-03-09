<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Deploy & Use API') }}</h1>
<p class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Once your spec is ready, deploy it to make the API endpoints live.') }}</p>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Deploying') }}</h2>
<ol class="space-y-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">1</span>
        <span>{{ __('Go to your API Spec page') }}</span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">2</span>
        <span>{{ __('Click') }} <strong class="text-zinc-900 dark:text-white">{{ __('Deploy') }}</strong></span>
    </li>
    <li class="flex gap-3">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900 dark:text-green-300">3</span>
        <span>{{ __('Your API is now live at the displayed URL') }}</span>
    </li>
</ol>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('API Endpoints') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Deployed specs serve live CRUD endpoints:') }}</p>
<pre class="mb-6 overflow-x-auto rounded-lg bg-zinc-900 p-5 text-sm leading-loose text-zinc-100"><code>GET    /api/connect/{slug}/{resource}       {{ __('→ List records (paginated)') }}
GET    /api/connect/{slug}/{resource}/{id}  {{ __('→ Show single record') }}
POST   /api/connect/{slug}/{resource}       {{ __('→ Create record') }}
PUT    /api/connect/{slug}/{resource}/{id}  {{ __('→ Update record') }}
DELETE /api/connect/{slug}/{resource}/{id}  {{ __('→ Delete record') }}</code></pre>

<h2 class="mt-10 mb-5 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Query Parameters') }}</h2>
<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-zinc-200 dark:border-zinc-700">
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Parameter') }}</th>
            <th class="py-3 pr-4 text-left font-medium text-zinc-900 dark:text-white">{{ __('Example') }}</th>
            <th class="py-3 text-left font-medium text-zinc-900 dark:text-white">{{ __('Description') }}</th>
        </tr>
    </thead>
    <tbody class="text-zinc-600 dark:text-zinc-400">
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">page</td>
            <td class="py-3 pr-4 font-mono text-xs">?page=2</td>
            <td class="py-3">{{ __('Page number') }}</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">per_page</td>
            <td class="py-3 pr-4 font-mono text-xs">?per_page=25</td>
            <td class="py-3">{{ __('Results per page') }}</td>
        </tr>
        <tr class="border-b border-zinc-100 dark:border-zinc-800">
            <td class="py-3 pr-4 font-mono text-xs">sort</td>
            <td class="py-3 pr-4 font-mono text-xs">?sort=-created_at</td>
            <td class="py-3">{{ __('Sort by field (prefix - for descending)') }}</td>
        </tr>
        <tr>
            <td class="py-3 pr-4 font-mono text-xs">filter[field]</td>
            <td class="py-3 pr-4 font-mono text-xs">?filter[status]=active</td>
            <td class="py-3">{{ __('Filter by field value') }}</td>
        </tr>
    </tbody>
</table>

<h2 class="mt-10 mb-4 text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Authentication') }}</h2>
<p class="mb-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('API endpoints require an API key. Include it in the request header:') }}</p>
<pre class="overflow-x-auto rounded-lg bg-zinc-900 p-5 text-sm leading-relaxed text-zinc-100"><code>X-API-Key: your-api-key-here</code></pre>
