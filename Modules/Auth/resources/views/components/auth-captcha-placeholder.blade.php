@php
    /** @var string $provider */
    $provider = $provider ?? 'turnstile'; // turnstile | recaptcha
@endphp

{{--
    CAPTCHA PLACEHOLDER — DISPLAY ONLY (no backend verification).
    Renders a static widget mock matching Cloudflare Turnstile's visual
    footprint so the layout is final; wiring the real challenge is a
    backend task (inject sitekey + verify token server-side).
--}}

<div class="select-none" aria-hidden="true">
    <div class="w-[300px] max-w-full h-[65px] bg-surface border border-border rounded-md flex items-center px-4 gap-3">
        <!-- spinner in loading state -->
        <svg class="w-6 h-6 text-text-muted shrink-0" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20"></circle>
            <path d="M12 2a10 10 0 019.54 7" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
        </svg>

        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-text-primary leading-tight">
                {{ $provider === 'turnstile' ? 'Verifying you are human...' : 'Verifying...' }}
            </p>
            <p class="text-[10px] text-text-muted leading-tight mt-0.5">
                {{ $provider === 'turnstile' ? 'Cloudflare' : 'reCAPTCHA' }}
                <span class="mx-1">&bull;</span>
                <a href="#" class="hover:text-text-primary" tabindex="-1">Privacy</a>
                <span class="mx-1">&bull;</span>
                <a href="#" class="hover:text-text-primary" tabindex="-1">Terms</a>
            </p>
        </div>

        <svg class="w-6 h-6 text-text-muted shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            @if ($provider === 'turnstile')
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" opacity=".4"/>
                <circle cx="12" cy="12" r="4"/>
            @else
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" opacity=".25"/>
                <path d="M12 6.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zm0 2a3.5 3.5 0 110 7 3.5 3.5 0 010-7z"/>
            @endif
        </svg>
    </div>
</div>