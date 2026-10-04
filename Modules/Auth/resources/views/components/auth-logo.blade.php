@php
    $class = $class ?? 'mx-auto mb-8';
@endphp

<div class="flex items-center justify-center {{ $class }}">
    <div class="flex items-center gap-3">
        <div
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/25 to-accent/5 border border-accent/30 flex items-center justify-center text-accent">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6">
                </path>
            </svg>
        </div>
        <span class="text-lg font-semibold text-text-primary tracking-tight">{{ config('app.name', 'SynthEX') }}</span>
    </div>
</div>