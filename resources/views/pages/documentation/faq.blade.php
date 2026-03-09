<h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Frequently Asked Questions') }}</h1>
<p class="mt-4 mb-8 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">{{ __('Common questions about using G8Connect.') }}</p>

@php
    $faqs = [
        [
            'q' => 'Can I connect to a remote database?',
            'a' => 'Yes — as long as G8Connect can reach the host and port. Ensure your database allows connections from the server where G8Connect is running.',
        ],
        [
            'q' => 'Are my database credentials safe?',
            'a' => 'Yes. Credentials are encrypted at rest using Laravel\'s encryption and are never logged or displayed after initial entry.',
        ],
        [
            'q' => 'Can I create write operations from a file upload?',
            'a' => 'No. File-based data sources only support read operations (GET list and GET single). For write operations, connect a database.',
        ],
        [
            'q' => 'What happens to my uploaded file?',
            'a' => 'Your file is stored securely on the server. It\'s used to serve the API data when your spec is deployed.',
        ],
        [
            'q' => 'Can I update the file data after creating the API?',
            'a' => 'Currently, you need to create a new data source with the updated file. In-place file replacement is planned for a future release.',
        ],
        [
            'q' => 'What is PII detection?',
            'a' => 'PII (Personally Identifiable Information) detection scans your column names for patterns like "password", "ic_number", "credit_card", etc. Matching columns are excluded from the API by default to protect sensitive data.',
        ],
        [
            'q' => 'Can I expose a PII-flagged column?',
            'a' => 'Yes, but only in Guided Mode. You can manually toggle PII-flagged fields to be exposed. Use this with caution.',
        ],
        [
            'q' => 'What Excel formats are supported?',
            'a' => 'Only .xlsx files are supported. Legacy .xls format is not supported.',
        ],
        [
            'q' => 'My JSON file has nested objects. Will they work?',
            'a' => 'Nested objects and arrays within each row are flattened to JSON strings. The top-level structure must be an array of objects or a wrapper like { "data": [...] }.',
        ],
        [
            'q' => 'Can I select multiple tables from a database?',
            'a' => 'Yes. During the wizard, you can select multiple tables. Each table becomes a separate resource under one API spec.',
        ],
    ];
@endphp

<div class="space-y-3">
    @foreach($faqs as $faq)
        <details class="group rounded-lg border border-zinc-200 dark:border-zinc-700">
            <summary class="flex cursor-pointer items-center justify-between px-5 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                {{ __($faq['q']) }}
                <x-icon name="chevron-down" class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" />
            </summary>
            <div class="border-t border-zinc-200 px-5 py-4 text-sm leading-relaxed text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                {{ __($faq['a']) }}
            </div>
        </details>
    @endforeach
</div>
