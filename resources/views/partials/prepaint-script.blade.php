{{--
    PRE-PAINT APP CHROME — every piece of state that must already be correct at the
    moment of the FIRST paint.

    Why this file exists
    --------------------
    Some state lives only in `localStorage`, which the server cannot read, and the
    Vite bundle is an ES module that the browser executes only AFTER the HTML is
    parsed. Anything applied from the bundle therefore lands too late: the page has
    already painted with the server's default. For the theme that is a dark→light
    flicker; for the sidebar it is a rail that opens for ~0.3s and then snaps shut.

    Both were fixed with the same mechanism, so they now live in the same script:
    one inline, NON-deferred block in <head> that runs before the body is parsed.

    This is the SINGLE definition of how app chrome is applied. The bundled runtime
    (resources/js/theme-switcher.js) reuses the exposed API rather than duplicating
    the class-toggle logic — see "Ownership" below.

    Usage
    -----
        include('partials.prepaint-script')                          -> theme only
        include('partials.prepaint-script', ['withSidebar' => true]) -> theme + sidebar

    Auth pages (login/register) have no sidebar, so they omit that half entirely
    instead of shipping dead code.

    NOTE: usage examples above are written without Blade comment delimiters on
    purpose — a Blade comment cannot contain the closing delimiter, it would
    terminate this block on the spot and leak the rest into the compiled PHP.

    Ownership / ordering
    --------------------
      * <html> classes + --sidebar-w are written by THIS script (first paint) and
        by the runtime API below (after boot). No other place may write them.
        In particular there must be no `:style` binding on the <aside> element, or
        the two writers fight during drag-resize.
      * Theme is applied BEFORE the sidebar so any theme-dependent token is already
        in place when the rail is sized — the order is explicit here rather than
        depending on @include order across layouts.
--}}
<script>
    (function () {
        var MIN_W = 190, MAX_W = 420;
        var root = document.documentElement;

        /**
         * The one definition of "how app chrome is applied". Exposed globally so
         * the bundled runtime can call it instead of re-implementing it (that
         * duplication is exactly how the theme cookie drifted out of sync before).
         */
        var chrome = {
            /** The theme actually painted right now — read from the DOM, which is
                the visual source of truth, not from storage (which may be blocked). */
            currentTheme: function () {
                return root.classList.contains('dark') ? 'dark' : 'light';
            },

            /** The theme the user last chose, independent of what is painted. */
            readTheme: function () {
                var stored = null;
                try {
                    stored = localStorage.getItem('theme');
                } catch (e) { /* storage blocked (private mode) */ }

                return stored === 'light' ? 'light' : 'dark';
            },

            /**
             * Apply a theme to <html>. Both classes are always set explicitly — an
             * unclassed <html> would fall into the dark default baseline.
             *
             * `persist` means "the user just changed this": write localStorage, mirror
             * the cookie so the SERVER can render the right class on the next request,
             * and announce the change. The pre-paint call passes false, because there
             * are no listeners yet and the cookie/localStorage are already correct.
             */
            applyTheme: function (theme, persist) {
                var value = theme === 'light' ? 'light' : 'dark';
                root.classList.toggle('dark', value === 'dark');
                root.classList.toggle('light', value === 'light');

                if (persist) {
                    try {
                        localStorage.setItem('theme', value);
                    } catch (e) { /* storage blocked: the cookie below still works */ }

                    document.cookie = 'theme=' + value + ';path=/;max-age=31536000;SameSite=Lax';
                    document.dispatchEvent(new CustomEvent('theme-changed', {
                        detail: { theme: value }
                    }));
                }

                return value;
            },

            /** Collapsed state + width of the desktop sidebar rail (see preline-bridge.css). */
            applySidebarVars: function (collapsed, width) {
                root.classList.toggle('sidebar-collapsed', !!collapsed);
                root.style.setProperty('--sidebar-w', collapsed ? '4.5rem' : width + 'px');
            },

            /** Clamp a stored sidebar width into the range the layout supports. */
            clampSidebarWidth: function (px) {
                var n = parseInt(px, 10);
                if (isNaN(n)) n = 256;

                return Math.min(MAX_W, Math.max(MIN_W, n));
            },
        };

        window.SectorsChrome = chrome;

        // ---- 1. THEME (every layout) -------------------------------------------
        try {
            chrome.applyTheme(chrome.readTheme(), false);
        } catch (e) {
            /* fall back to the server-rendered class on <html> */
        }

        @if ($withSidebar ?? false)
            // ---- 2. SIDEBAR (only where a sidebar is rendered) ---------------------
            try {
                var collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                var width = chrome.clampSidebarWidth(localStorage.getItem('sidebarWidth'));

                chrome.applySidebarVars(collapsed, width);
            } catch (e) {
                /* fall back to the CSS default (expanded rail) */
            }
        @endif
    })();
</script>
