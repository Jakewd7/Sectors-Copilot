<script>
    (function () {
        var MIN_W = 190, MAX_W = 420;
        var root = document.documentElement;

        var chrome = {
            currentTheme: function () {
                return root.classList.contains('dark') ? 'dark' : 'light';
            },

            readTheme: function () {
                var stored = null;
                try {
                    stored = localStorage.getItem('theme');
                } catch (e) {}

                if (stored !== 'light' && stored !== 'dark') {
                    var match = document.cookie.match(/(?:^|;\s*)theme=(light|dark)/);
                    stored = match ? match[1] : null;
                }

                return stored === 'light' ? 'light' : 'dark';
            },

            applyTheme: function (theme, persist) {
                var value = theme === 'light' ? 'light' : 'dark';
                root.classList.toggle('dark', value === 'dark');
                root.classList.toggle('light', value === 'light');

                if (persist) {
                    try {
                        localStorage.setItem('theme', value);
                    } catch (e) {}

                    document.cookie = 'theme=' + value + ';path=/;max-age=31536000;SameSite=Lax';
                    document.dispatchEvent(new CustomEvent('theme-changed', {
                        detail: { theme: value }
                    }));
                }

                return value;
            },

            applySidebarVars: function (collapsed, width) {
                root.classList.toggle('sidebar-collapsed', !!collapsed);
                root.style.setProperty('--sidebar-w', collapsed ? '4.5rem' : width + 'px');
            },

            clampSidebarWidth: function (px) {
                var n = parseInt(px, 10);
                if (isNaN(n)) n = 256;

                return Math.min(MAX_W, Math.max(MIN_W, n));
            },
        };

        window.SectorsChrome = chrome;

        try {
            var resolved = chrome.applyTheme(chrome.readTheme(), false);

            document.cookie = 'theme=' + resolved + ';path=/;max-age=31536000;SameSite=Lax';
        } catch (e) {}

        @if ($withSidebar ?? false)
            try {
                var collapsed = localStorage.getItem('sidebarCollapsed') === '1';
                var width = chrome.clampSidebarWidth(localStorage.getItem('sidebarWidth'));

                chrome.applySidebarVars(collapsed, width);
            } catch (e) {}
        @endif
    })();
</script>
