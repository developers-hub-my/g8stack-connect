<div class="flex-shrink-0 flex items-center px-4">
    <a href="{{ auth()->user() ? route('dashboard') : route('home') }}"
       class="flex items-center gap-2 font-bold text-lg text-zinc-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200">
        <x-app-logo-icon class="h-8 w-8" />
        <span>{{ config('app.name') }}</span>
    </a>
</div>
