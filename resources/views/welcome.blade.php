<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
        {{-- Navigation --}}
        <nav class="fixed top-0 z-50 w-full border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <x-app-logo-icon class="h-9 w-9" />
                        <span class="text-lg font-bold text-zinc-900 dark:text-white">G8Connect</span>
                    </a>

                    <div class="hidden items-center space-x-8 sm:flex">
                        <a href="#features" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">Features</a>
                        <a href="#how-it-works" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">How It Works</a>
                        <a href="#modes" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">Modes</a>
                    </div>

                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900" wire:navigate>
                                    {{ __('Get Started') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="relative overflow-hidden pt-32 pb-20 sm:pt-44 sm:pb-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="mb-6 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 dark:border-emerald-800 dark:bg-emerald-900/30">
                        <x-lucide-shield-check class="mr-2 h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Governed API Generation</span>
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-6xl lg:text-7xl dark:text-white">
                        Data Source to
                        <span class="bg-gradient-to-r from-emerald-600 to-cyan-500 bg-clip-text text-transparent">API</span>
                        in Minutes
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Connect any database or file, introspect the schema, and auto-generate OpenAPI specs.
                        Every spec goes through G8Stack governance before deployment — speed up creation without skipping approval.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900" wire:navigate>
                                Go to Dashboard
                                <x-lucide-arrow-right class="ml-2 h-5 w-5" />
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900" wire:navigate>
                                    Start Building APIs
                                    <x-lucide-arrow-right class="ml-2 h-5 w-5" />
                                </a>
                            @endif
                            <a href="#how-it-works" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-8 py-3.5 text-base font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                See How It Works
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Background Decoration --}}
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/3">
                    <div class="h-[600px] w-[600px] rounded-full bg-gradient-to-br from-emerald-400/20 to-cyan-400/20 blur-3xl dark:from-emerald-600/10 dark:to-cyan-600/10"></div>
                </div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2">
                    <div class="h-[400px] w-[400px] rounded-full bg-gradient-to-bl from-cyan-400/10 to-emerald-400/10 blur-3xl dark:from-cyan-600/5 dark:to-emerald-600/5"></div>
                </div>
            </div>
        </section>

        {{-- How It Works --}}
        <section id="how-it-works" class="border-t border-zinc-200 bg-zinc-50 py-20 sm:py-32 dark:border-zinc-800 dark:bg-zinc-800/30">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        From Data Source to Governed API
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                        Six steps from connection to deployment. You generate — G8Stack governs.
                    </p>
                </div>

                <div class="mt-16 grid gap-px overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-200 sm:grid-cols-2 lg:grid-cols-3 dark:border-zinc-700 dark:bg-zinc-700">
                    {{-- Step 1 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">1</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">Connect</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Add your data source — PostgreSQL, MySQL, MSSQL, SQLite, or upload CSV/JSON/Excel files.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">2</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">Introspect</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            G8Connect reads your schema automatically — tables, columns, data types, all detected.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">3</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">Configure</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Pick fields, choose HTTP methods, set filters — or let Simple Mode auto-configure everything.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">4</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">PII Detection</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Sensitive columns flagged automatically — passwords, IC numbers, bank details excluded by default.
                        </p>
                    </div>

                    {{-- Step 5 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-sm font-bold text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-400">5</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">Generate Spec</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            OpenAPI 3.1 spec generated instantly. Review it, version it, refine it before submission.
                        </p>
                    </div>

                    {{-- Step 6 --}}
                    <div class="relative bg-white p-8 dark:bg-zinc-800">
                        <div class="mb-4 flex items-center space-x-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-400">6</span>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">Push to G8Stack</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Submit to G8Stack for governance approval. Only approved specs deploy to Kong Gateway.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Wizard Modes --}}
        <section id="modes" class="py-20 sm:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        Three Modes, One Output
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                        Whether you're a non-technical user or a data scientist, G8Connect adapts to your workflow.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 lg:grid-cols-3">
                    {{-- Simple Mode --}}
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="border-b border-zinc-200 bg-emerald-50 px-8 py-6 dark:border-zinc-700 dark:bg-emerald-900/20">
                            <div class="mb-2 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">Easiest</div>
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Simple Mode</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">For non-technical users</p>
                        </div>
                        <div class="p-8">
                            <ul class="space-y-3">
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Pick a table</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Auto-generate full CRUD spec</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Zero configuration needed</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">PII auto-excluded</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Guided Mode --}}
                    <div class="relative overflow-hidden rounded-2xl border-2 border-emerald-500 bg-white shadow-lg shadow-emerald-500/10 dark:bg-zinc-800/50">
                        <div class="border-b border-emerald-500/30 bg-emerald-50 px-8 py-6 dark:bg-emerald-900/20">
                            <div class="mb-2 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">Most Popular</div>
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Guided Mode</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">For backend developers</p>
                        </div>
                        <div class="p-8">
                            <ul class="space-y-3">
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Choose fields to expose or exclude</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Select HTTP methods per endpoint</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Configure filters and pagination</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Rename fields for API consumers</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Preview with sample data</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Advanced Mode --}}
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="border-b border-zinc-200 bg-zinc-50 px-8 py-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="mb-2 inline-flex rounded-full bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">Power Users</div>
                            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Advanced Mode</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">For data scientists</p>
                        </div>
                        <div class="p-8">
                            <ul class="space-y-3">
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Write custom SELECT queries</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">JOINs, CTEs, aggregations</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Named GET endpoints</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Parameter binding to query params</span>
                                </li>
                                <li class="flex items-start space-x-3">
                                    <x-lucide-shield-check class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Read-only, sandboxed execution</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features Grid --}}
        <section id="features" class="border-t border-zinc-200 py-20 sm:py-32 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        Built for Governed Environments
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
                        Security, audit trails, and compliance built in from day one.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Feature: Encrypted Credentials --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-emerald-100 p-3 dark:bg-emerald-900/50">
                            <x-lucide-lock class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Encrypted Credentials</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            All data source credentials encrypted at rest. Never logged, never exposed in error messages, never stored in plaintext.
                        </p>
                    </div>

                    {{-- Feature: PII Detection --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-amber-100 p-3 dark:bg-amber-900/50">
                            <x-lucide-scan-eye class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">PII Auto-Detection</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Sensitive columns like NRIC, passwords, and bank details flagged automatically and excluded from specs by default.
                        </p>
                    </div>

                    {{-- Feature: Audit Trail --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-blue-100 p-3 dark:bg-blue-900/50">
                            <x-lucide-file-clock class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Full Audit Trail</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Every connection, introspection, and spec generation is logged. Know who accessed what, when, and why.
                        </p>
                    </div>

                    {{-- Feature: Read-Only Enforcement --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-rose-100 p-3 dark:bg-rose-900/50">
                            <x-lucide-shield-alert class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Read-Only Connections</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Data source connections enforced as read-only at the connector level. G8Connect never writes to your databases.
                        </p>
                    </div>

                    {{-- Feature: Multiple Sources --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-purple-100 p-3 dark:bg-purple-900/50">
                            <x-lucide-database class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Multiple Data Sources</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            PostgreSQL, MySQL, MSSQL, SQLite for databases. CSV, JSON, and Excel for file-based sources.
                        </p>
                    </div>

                    {{-- Feature: Spec Versioning --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 inline-flex rounded-lg bg-cyan-100 p-3 dark:bg-cyan-900/50">
                            <x-lucide-git-branch class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Spec Versioning</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Every regeneration creates a new version. Compare specs, track changes, and submit only when ready.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Governance CTA --}}
        <section class="border-t border-zinc-200 bg-zinc-50 py-20 sm:py-32 dark:border-zinc-800 dark:bg-zinc-800/30">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <div class="mb-6 inline-flex rounded-lg bg-emerald-100 p-4 dark:bg-emerald-900/50">
                    <x-lucide-workflow class="h-10 w-10 text-emerald-600 dark:text-emerald-400" />
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                    Speed Up Creation.<br>Never Skip Governance.
                </h2>
                <p class="mx-auto mt-6 max-w-xl text-lg text-zinc-600 dark:text-zinc-400">
                    G8Connect generates specs. G8Stack approves them. Kong deploys them.
                    Clear separation of concerns for teams that take API governance seriously.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    @guest
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900" wire:navigate>
                                Get Started Free
                                <x-lucide-arrow-right class="ml-2 h-5 w-5" />
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </section>

        {{-- Data Sources Strip --}}
        <section class="border-t border-zinc-200 py-16 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    Supported Data Sources
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">PostgreSQL</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">MySQL</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">MSSQL</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">SQLite</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">CSV</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">JSON</span>
                    <span class="text-lg font-semibold text-zinc-400 dark:text-zinc-500">Excel</span>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 py-12 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center space-x-2">
                        <x-app-logo-icon class="h-8 w-8" />
                        <span class="font-semibold text-zinc-900 dark:text-white">G8Connect</span>
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        &copy; {{ date('Y') }} G8Connect. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
