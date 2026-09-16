// Theme switcher: applies stored theme (dark default) before paint
import './theme-switcher';

// Preline UI — non-auto entry: initialization is explicit (after DOM ready)
import { HSStaticMethods } from 'preline/non-auto';

/**
 * App chrome state shared by app-shell + sidebar.
 * - sidebarCollapsed : desktop icon-only rail (persisted in localStorage)
 * - sidebarOpen      : mobile drawer visibility
 * - sidebarWidth     : desktop sidebar width in px, drag-resizable (persisted)
 */
const SIDEBAR_MIN = 190;
const SIDEBAR_MAX = 420;

document.addEventListener('alpine:init', () => {
    /**
     * Global toast stack. Any page can raise a notification with:
     *   $store.toast.push({ variant: 'error', title: '…', message: '…' })
     * Rendered by resources/views/partials/toast.blade.php (mounted in app-shell).
     */
    Alpine.store('toast', {
        items: [],
        _seq: 0,

        push({ variant = 'info', title = '', message = '', detail = '', action = null, duration = 6000 } = {}) {
            const id = ++this._seq;
            this.items.push({ id, variant, title, message, detail, action, visible: true });

            if (duration > 0) {
                setTimeout(() => this.dismiss(id), duration);
            }

            return id;
        },

        dismiss(id) {
            const item = this.items.find((t) => t.id === id);
            if (!item) return;
            item.visible = false;
            // Let the leave transition finish before dropping it from the DOM.
            setTimeout(() => {
                this.items = this.items.filter((t) => t.id !== id);
            }, 200);
        },
    });

    Alpine.data('appChrome', () => ({
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === '1',
        sidebarWidth: Math.min(
            SIDEBAR_MAX,
            Math.max(SIDEBAR_MIN, parseInt(localStorage.getItem('sidebarWidth') || '256', 10)),
        ),
        _resizing: false,

        init() {
            // The pre-paint script (partials/prepaint-script) already applied the
            // stored state before the first paint; re-assert it here so Alpine
            // state and the DOM start out in agreement.
            this.applySidebarVars();

            // Keep the drag honest when the window shrinks.
            window.addEventListener('resize', () => this.clampWidth());
        },

        /**
         * Single writer for the sidebar chrome after boot.
         *
         * Delegates to `window.SectorsChrome.applySidebarVars()` — the SAME
         * definition the inline pre-paint script uses (partials/prepaint-script),
         * so the first paint and every later change can never disagree.
         *
         * The pre-paint script owns the FIRST paint (localStorage is not readable
         * by the server, and the Vite bundle is an ES module that only runs after
         * the HTML is parsed — so anything Alpine-only would paint expanded, then
         * snap shut).
         */
        applySidebarVars() {
            if (!window.SectorsChrome) return; // pre-paint script missing from the layout

            window.SectorsChrome.applySidebarVars(this.sidebarCollapsed, this.sidebarWidth);
        },

        toggleCollapsed() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
            this.applySidebarVars();
        },

        clampWidth() {
            if (this.sidebarWidth > window.innerWidth * 0.5) {
                this.sidebarWidth = Math.round(window.innerWidth * 0.5);
            }
            this.applySidebarVars();
        },

        startResize(event) {
            if (this.sidebarCollapsed) return;
            event.preventDefault();
            this._resizing = true;
            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'col-resize';

            const startX = event.clientX;
            const startWidth = this.sidebarWidth;
            const onMove = (e) => {
                this.sidebarWidth = Math.min(
                    SIDEBAR_MAX,
                    Math.max(SIDEBAR_MIN, startWidth + (e.clientX - startX)),
                );
                this.applySidebarVars();
            };
            const onUp = () => {
                this._resizing = false;
                document.body.style.userSelect = '';
                document.body.style.cursor = '';
                localStorage.setItem('sidebarWidth', String(this.sidebarWidth));
                window.removeEventListener('pointermove', onMove);
                window.removeEventListener('pointerup', onUp);
            };

            window.addEventListener('pointermove', onMove);
            window.addEventListener('pointerup', onUp);
        },
    }));
});

document.addEventListener('DOMContentLoaded', () => {
    HSStaticMethods.autoInit();
});
