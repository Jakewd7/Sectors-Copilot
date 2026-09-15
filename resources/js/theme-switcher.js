/**
 * Theme switcher — the RUNTIME half of the theme.
 *
 * The FIRST paint is not this file's job: `resources/views/partials/prepaint-script.blade.php`
 * applies the stored theme inline in <head>, before the body is parsed. This module
 * runs later (it is part of the Vite bundle, an ES module), so it only handles what
 * happens AFTER boot:
 *
 *   - re-assert the theme so the runtime and the pre-paint script agree
 *   - expose `window.toggleTheme()` for the `onclick` buttons
 *   - persist the choice (localStorage + the `theme` cookie the server reads)
 *
 * The class-toggle logic is NOT duplicated here — it calls `window.SectorsChrome`,
 * defined by the pre-paint script. That single definition is what keeps the cookie,
 * localStorage and the DOM from drifting apart.
 *
 * `SectorsChrome` is absent only if the inline script was stripped from the layout;
 * the fallback below keeps the toggle working in that case (just without the
 * guaranteed no-flash first paint).
 */
(function () {
    if (!window.SectorsChrome) {
        // Fallback: mirror the pre-paint script's behaviour so a layout that forgot
        // to include it still gets a working toggle.
        const root = document.documentElement;
        window.SectorsChrome = {
            currentTheme: () => (root.classList.contains('dark') ? 'dark' : 'light'),
            readTheme: () => (localStorage.getItem('theme') === 'light' ? 'light' : 'dark'),
            applyTheme(theme, persist) {
                const value = theme === 'light' ? 'light' : 'dark';
                root.classList.toggle('dark', value === 'dark');
                root.classList.toggle('light', value === 'light');

                if (persist) {
                    localStorage.setItem('theme', value);
                    document.cookie =
                        'theme=' + value + ';path=/;max-age=31536000;SameSite=Lax';
                    document.dispatchEvent(
                        new CustomEvent('theme-changed', { detail: { theme: value } }),
                    );
                }

                return value;
            },
        };
    }

    // Re-assert: the pre-paint script already painted this, so this is a no-op in
    // the normal case. It matters when the bundle loads into a document whose <html>
    // was mutated by something else (e.g. a hot-reload or a restored bfcache entry).
    window.SectorsChrome.applyTheme(window.SectorsChrome.readTheme(), false);

    window.toggleTheme = function () {
        const next = window.SectorsChrome.currentTheme() === 'dark' ? 'light' : 'dark';
        window.SectorsChrome.applyTheme(next, true);
    };
})();
