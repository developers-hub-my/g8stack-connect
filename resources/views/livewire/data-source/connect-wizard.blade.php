<div>
    {{-- Step Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach(['Source', 'Connect', 'Validate', 'Introspect', 'Select Tables', 'PII Scan', 'Review'] as $i => $label)
                <div class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                    @if($i > 0)
                        <div class="mx-2 h-0.5 flex-1 {{ $currentStep > $i ? 'bg-blue-600' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endif
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold
                        {{ $currentStep > $i + 1 ? 'bg-blue-600 text-white' : ($currentStep === $i + 1 ? 'bg-blue-600 text-white' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400') }}">
                        {{ $i + 1 }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
            @foreach(['Source', 'Connect', 'Validate', 'Introspect', 'Select', 'PII', 'Review'] as $label)
                <span class="w-8 text-center">{{ $label }}</span>
            @endforeach
        </div>
    </div>

    {{-- Step 1: Data Source Type --}}
    @if($currentStep === 1)
        <div class="space-y-6">
            <flux:input label="Data Source Name" wire:model="name" placeholder="My Database" />

            <div>
                <label class="block text-sm font-medium text-zinc-900 dark:text-white mb-3">Wizard Mode</label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(\App\Enums\WizardMode::cases() as $mode)
                        @if($mode->value !== 'advanced')
                            <button type="button" wire:click="$set('wizardMode', '{{ $mode->value }}')"
                                class="rounded-lg border-2 p-4 text-left transition
                                    {{ $wizardMode === $mode->value ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $mode->label() }}</div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $mode->description() }}</div>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-900 dark:text-white mb-3">Data Source Type</label>

                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-2">Databases</label>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-4">
                    @foreach(\App\Enums\DataSourceType::cases() as $dsType)
                        @if($dsType->isDatabase())
                            <button type="button" wire:click="$set('type', '{{ $dsType->value }}')"
                                class="rounded-lg border-2 p-4 text-center transition
                                    {{ $type === $dsType->value ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $dsType->label() }}</div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $dsType->description() }}</div>
                            </button>
                        @endif
                    @endforeach
                </div>

                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-2">Files</label>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach(\App\Enums\DataSourceType::cases() as $dsType)
                        @if($dsType->isFile())
                            <button type="button" wire:click="$set('type', '{{ $dsType->value }}')"
                                class="rounded-lg border-2 p-4 text-center transition
                                    {{ $type === $dsType->value ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $dsType->label() }}</div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $dsType->description() }}</div>
                            </button>
                        @endif
                    @endforeach
                </div>

                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    @endif

    {{-- Step 2: Credentials / File Upload --}}
    @if($currentStep === 2)
        <div class="space-y-4">
            @if(in_array($type, ['csv', 'json', 'excel']))
                <flux:heading size="lg">Upload File</flux:heading>
                <flux:text class="text-sm">Upload your {{ strtoupper($type) }} file. Maximum size: 50MB.</flux:text>

                <div>
                    <input type="file" wire:model="uploadedFile"
                        accept="{{ match($type) { 'csv' => '.csv', 'json' => '.json', 'excel' => '.xlsx,.xls', default => '*' } }}"
                        class="block w-full text-sm text-zinc-500 dark:text-zinc-400
                            file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                            file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                            dark:file:bg-zinc-700 dark:file:text-zinc-300
                            hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600
                            file:cursor-pointer cursor-pointer" />

                    @error('uploadedFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div wire:loading wire:target="uploadedFile" class="mt-2 text-sm text-zinc-500">
                        Uploading...
                    </div>

                    @if($uploadedFile && !$errors->has('uploadedFile'))
                        <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                            File ready: {{ $uploadedFile->getClientOriginalName() }}
                            ({{ number_format($uploadedFile->getSize() / 1024, 1) }} KB)
                        </div>
                    @endif
                </div>
            @else
                <flux:heading size="lg">Connection Credentials</flux:heading>

                @if(in_array($type, ['mysql', 'postgresql', 'mssql']))
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:input label="Host" wire:model="credentials.host" placeholder="127.0.0.1" />
                        <flux:input label="Port" wire:model="credentials.port" placeholder="{{ $type === 'postgresql' ? '5432' : ($type === 'mssql' ? '1433' : '3306') }}" />
                    </div>
                    <flux:input label="Database" wire:model="credentials.database" placeholder="my_database" />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:input label="Username" wire:model="credentials.username" placeholder="readonly_user" />
                        <flux:input label="Password" wire:model="credentials.password" type="password" />
                    </div>
                @else
                    <flux:input label="Database Path" wire:model="credentials.database" placeholder="/path/to/database.sqlite" />
                @endif

                @error('credentials.database') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>
    @endif

    {{-- Step 3: Test Connection --}}
    @if($currentStep === 3)
        <div class="text-center py-8">
            @if($connectionTested)
                <div class="text-green-600 dark:text-green-400">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium">Connection Successful</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ready to introspect the data source.</p>
                </div>
            @else
                <div class="text-zinc-400">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-zinc-600 dark:text-zinc-300">Testing Connection...</p>
                </div>
                <flux:button wire:click="testConnection" variant="primary" class="mt-4">
                    Test Connection
                </flux:button>
            @endif
        </div>
    @endif

    {{-- Step 4: Introspect --}}
    @if($currentStep === 4)
        <div>
            <flux:heading size="lg" class="mb-4">Available Tables</flux:heading>
            @if(count($tables) > 0)
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Found {{ count($tables) }} {{ count($tables) === 1 ? 'table' : 'tables' }}. Select tables to include in the API spec.</p>
                    <div class="flex gap-2">
                        <flux:button wire:click="selectAllTables" variant="ghost" size="sm">Select All</flux:button>
                        @if(count($selectedTables) > 0)
                            <flux:button wire:click="deselectAllTables" variant="ghost" size="sm">Clear</flux:button>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($tables as $table)
                        <button type="button" wire:click="toggleTable('{{ $table }}')"
                            class="rounded-lg border-2 p-3 text-left text-sm transition
                                {{ in_array($table, $selectedTables) ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                            <div class="flex items-center gap-2">
                                <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded border
                                    {{ in_array($table, $selectedTables) ? 'border-blue-600 bg-blue-600' : 'border-zinc-300 dark:border-zinc-600' }}">
                                    @if(in_array($table, $selectedTables))
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    @endif
                                </div>
                                {{ $table }}
                            </div>
                        </button>
                    @endforeach
                </div>
                @if(count($selectedTables) > 0)
                    <p class="mt-3 text-sm text-blue-600 dark:text-blue-400">{{ count($selectedTables) }} {{ count($selectedTables) === 1 ? 'table' : 'tables' }} selected</p>
                @endif
            @else
                <p class="text-sm text-zinc-500">No tables found in this data source.</p>
            @endif
        </div>
    @endif

    {{-- Step 5: Schema Preview --}}
    @if($currentStep === 5)
        <div class="space-y-6">
            <flux:heading size="lg">Schema Preview</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Showing columns for {{ count($selectedTables) }} selected {{ count($selectedTables) === 1 ? 'table' : 'tables' }}.</p>

            @foreach($schemaColumns as $tableName => $columns)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $tableName }}</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ count($columns) }} columns</p>
                    </div>
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="py-2.5 pr-3 pl-4 text-left text-xs font-semibold text-zinc-900 dark:text-zinc-100 sm:pl-6">Column</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-zinc-900 dark:text-zinc-100">Type</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-zinc-900 dark:text-zinc-100">Nullable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @foreach($columns as $col)
                                <tr>
                                    <td class="py-3 pr-3 pl-4 text-sm font-medium text-zinc-900 dark:text-white sm:pl-6">{{ $col['name'] }}</td>
                                    <td class="px-3 py-3 text-sm text-zinc-700 dark:text-zinc-300">{{ $col['type'] }}</td>
                                    <td class="px-3 py-3 text-sm text-zinc-700 dark:text-zinc-300">{{ $col['nullable'] ? 'Yes' : 'No' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Step 6: PII Scan --}}
    @if($currentStep === 6)
        <div>
            <flux:heading size="lg" class="mb-4">PII Detection Results</flux:heading>

            @if(count($piiResult['flagged']) > 0)
                <div class="mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Sensitive Columns Detected</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        The following columns have been flagged as potentially containing PII and will be excluded from the API spec.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($piiResult['flagged'] as $col)
                            <flux:badge color="yellow">{{ $col }}</flux:badge>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                    <p class="text-sm text-green-800 dark:text-green-200">No PII columns detected. All columns will be included in the spec.</p>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Safe Columns (will be included)</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($piiResult['safe'] as $col)
                        <flux:badge color="green">{{ $col }}</flux:badge>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Step 7: Review / Generated --}}
    @if($currentStep === 7)
        <div>
            <flux:heading size="lg" class="mb-4">API Spec Generated</flux:heading>

            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-4 overflow-auto max-h-96">
                <pre class="text-xs text-zinc-700 dark:text-zinc-300"><code>{{ json_encode($generatedSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>

            <div class="mt-4 flex gap-2">
                <flux:button :href="route('data-sources.index')" wire:navigate variant="primary">
                    View Data Sources
                </flux:button>
                <flux:button :href="route('api-specs.index')" wire:navigate variant="ghost">
                    View API Specs
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Navigation Buttons --}}
    @if($currentStep < 7)
        <div class="mt-8 flex justify-between">
            @if($currentStep > 1)
                <flux:button wire:click="previousStep" variant="ghost">
                    Previous
                </flux:button>
            @else
                <div></div>
            @endif

            @if($currentStep === 6)
                <flux:button wire:click="generateSpec" variant="primary">
                    Generate Spec
                </flux:button>
            @elseif($currentStep === 4 && count($selectedTables) > 0)
                <flux:button wire:click="nextStep" variant="primary">
                    Next
                </flux:button>
            @elseif($currentStep !== 4)
                <flux:button wire:click="nextStep" variant="primary">
                    Next
                </flux:button>
            @endif
        </div>
    @endif
</div>
