/**
 * Theme switcher — persists choice in localStorage and applies
 * Tailwind v4 dark mode via explicit `dark`/`light` classes on <html>.
 * Both classes are always set: CSS keys on html.dark / html.light
 * (an unclassed html would fall into the dark default baseline).
 */
(function () {
    const stored = localStorage.getItem('theme') || 'dark';
    apply(stored, false);

    function apply(theme, persist) {
        const root = document.documentElement;
        root.classList.toggle('dark', theme === 'dark');
        root.classList.toggle('light', theme === 'light');
        if (persist) localStorage.setItem('theme', theme);
        document.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    }

    window.toggleTheme = function () {
        const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        apply(next, true);
    };
})();