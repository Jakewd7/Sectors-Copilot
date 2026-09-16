{{--
    Global toast stack (Preline alert styling, theme tokens only).
    Floating top-right, stacked newest-first, auto-dismiss with manual close.
    Driven by the `toastStack` Alpine store so any page can call:

        $store.toast.push({ variant: 'error', title: '...', message: '...' })

    Variants: 'error' (rose) | 'success' (accent) | 'info' (neutral).
--}}
<div x-data x-cloak
     class="pointer-events-none fixed top-4 right-4 z-[100] flex flex-col gap-2.5 w-[min(24rem,calc(100vw-2rem))]"
     aria-live="polite" aria-atomic="false">

    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div x-show="toast.visible" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-card p-4 shadow-lg"
             :class="{
                 'border-rose-500/50': toast.variant === 'error',
                 'border-primary-line': toast.variant === 'success',
                 'border-card-line': toast.variant === 'info'
             }"
             role="alert">

            <!-- Variant icon -->
            <svg x-show="toast.variant === 'error'" class="size-5 shrink-0 text-rose-500 mt-0.5" fill="none"
                 stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            </svg>
            <svg x-show="toast.variant === 'success'" class="size-5 shrink-0 text-primary-active mt-0.5" fill="none"
                 stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <svg x-show="toast.variant === 'info'" class="size-5 shrink-0 text-muted-foreground-1 mt-0.5" fill="none"
                 stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11.25 11.25h.75v4.5h.75M12 7.5h.008v.008H12V7.5Z" />
                <circle cx="12" cy="12" r="9" />
            </svg>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-foreground" x-text="toast.title"></p>
                <p class="mt-0.5 text-sm leading-relaxed text-muted-foreground-1" x-text="toast.message"></p>

                {{-- Inline action, e.g. "Resend prompt" --}}
                <button x-show="toast.action" type="button"
                        @click="toast.action.handler(); $store.toast.dismiss(toast.id)"
                        class="mt-2.5 inline-flex items-center gap-x-1.5 text-xs font-medium text-primary-active hover:text-primary-hover transition">
                    <span x-text="toast.action.label"></span>
                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>

            <!-- Dismiss -->
            <button type="button" @click="$store.toast.dismiss(toast.id)"
                    class="shrink-0 -m-1 p-1 rounded-lg text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition"
                    aria-label="Dismiss notification">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
