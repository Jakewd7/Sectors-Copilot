(function () {
    if (!window.SectorsChrome) {
        const root = document.documentElement;
        window.SectorsChrome = {
            currentTheme: () => (root.classList.contains('dark') ? 'dark' : 'light'),
            readTheme: () => {
                let stored = localStorage.getItem('theme');

                if (stored !== 'light' && stored !== 'dark') {
                    const match = document.cookie.match(/(?:^|;\s*)theme=(light|dark)/);
                    stored = match ? match[1] : null;
                }

                return stored === 'light' ? 'light' : 'dark';
            },
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

    window.SectorsChrome.applyTheme(window.SectorsChrome.readTheme(), false);

    window.toggleTheme = function () {
        const next = window.SectorsChrome.currentTheme() === 'dark' ? 'light' : 'dark';
        window.SectorsChrome.applyTheme(next, true);
    };
})();
