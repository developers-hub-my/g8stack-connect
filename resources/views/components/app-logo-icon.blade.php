@props(['class' => 'h-8 w-8'])

{{-- G8Connect Lightning Bolt Icon --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="g8bolt-icon" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#10B981"/>
            <stop offset="100%" stop-color="#06B6D4"/>
        </linearGradient>
    </defs>
    <path d="M112 24 L72 98 L96 98 L80 176 L140 88 L112 88 L132 24 Z" fill="url(#g8bolt-icon)"/>
    <path d="M115 38 L84 92 L100 92 L88 160 L132 94 L114 94 L126 38 Z" fill="currentColor" class="text-white/10 dark:text-white/10"/>
</svg>
