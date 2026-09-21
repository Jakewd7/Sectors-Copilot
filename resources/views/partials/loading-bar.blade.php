<style>
    #sectorsProgress {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--theme-accent, #a9ddbe);
        box-shadow: 0 0 8px var(--theme-accent, #a9ddbe), 0 0 3px var(--theme-accent, #a9ddbe);
        transform: scaleX(0);
        transform-origin: 0 50%;
        opacity: 0;
        z-index: 9999;
        pointer-events: none;
        will-change: transform, opacity;
    }

    #sectorsProgress.is-done {
        transition: transform 0.18s ease-out, opacity 0.32s ease-out 0.12s;
    }

    @media (prefers-reduced-motion: reduce) {
        #sectorsProgress {
            transition: none;
        }
    }
</style>

<script>
    (function () {
        var KEY_NAV = 'sp_nav';
        var KEY_TAU = 'sp_tau';
        var DEFAULT_TAU = 900;

        var bar = null;
        var rafId = null;
        var startedAt = 0;
        var progress = 0;
        var finishing = false;
        var tau = readTau();

        function readTau() {
            try {
                var stored = parseFloat(sessionStorage.getItem(KEY_TAU));
                if (stored > 120 && stored < 20000) {
                    return stored;
                }
            } catch (e) {}

            return DEFAULT_TAU;
        }

        function rememberTau(value) {
            try {
                sessionStorage.setItem(KEY_TAU, String(Math.round(value)));
            } catch (e) {}
        }

        function clearNavFlag() {
            try {
                sessionStorage.removeItem(KEY_NAV);
            } catch (e) {}
        }

        function ensureBar() {
            if (bar && bar.isConnected) {
                return bar;
            }

            bar = document.getElementById('sectorsProgress');

            if (!bar) {
                bar = document.createElement('div');
                bar.id = 'sectorsProgress';
                bar.setAttribute('role', 'progressbar');
                bar.setAttribute('aria-hidden', 'true');
                bar.style.transform = 'scaleX(0)';
                bar.style.opacity = '0';
            }

            (document.body || document.documentElement).appendChild(bar);

            return bar;
        }

        function mountBar() {
            var el = ensureBar();
            el.style.transform = 'scaleX(0)';
            el.style.opacity = '0';

            return el;
        }

        function paint(value) {
            ensureBar().style.transform = 'scaleX(' + value.toFixed(4) + ')';
        }

        function ease(elapsed) {
            var span = Math.max(tau, 250) / 3;

            return 0.92 * (1 - Math.exp(-elapsed / span));
        }

        function tick(now) {
            var elapsed = now - startedAt;

            if (finishing) {
                return;
            }

            progress = ease(elapsed);

            if (progress > 0.92) {
                progress = 0.92;
            }

            paint(progress);
            rafId = window.requestAnimationFrame(tick);
        }

        function start() {
            if (finishing) {
                return;
            }

            var el = ensureBar();

            el.classList.remove('is-done');

            if (rafId !== null) {
                window.cancelAnimationFrame(rafId);
                rafId = null;
            }

            startedAt = performance.now();
            progress = 0.06;
            paint(progress);

            el.style.opacity = '1';
            rafId = window.requestAnimationFrame(tick);
        }

        function done() {
            if (!bar) {
                return;
            }

            if (rafId !== null) {
                window.cancelAnimationFrame(rafId);
                rafId = null;
            }

            finishing = true;

            var elapsed = startedAt ? performance.now() - startedAt : tau;
            if (elapsed > 120) {
                rememberTau(elapsed);
            }

            var el = ensureBar();
            el.classList.add('is-done');
            el.style.transform = 'scaleX(1)';

            window.setTimeout(function () {
                el.style.opacity = '0';

                window.setTimeout(function () {
                    el.style.transform = 'scaleX(0)';
                    finishing = false;
                    startedAt = 0;
                }, 340);
            }, 120);
        }

        function isInternalLink(anchor, event) {
            if (!anchor || !anchor.href) {
                return false;
            }

            if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0)) {
                return false;
            }

            if (anchor.target && anchor.target !== '_self') {
                return false;
            }

            if (anchor.hasAttribute('download') || anchor.dataset.noProgress !== undefined) {
                return false;
            }

            var url = anchor.getAttribute('href') || '';

            if (url === '' || url.charAt(0) === '#') {
                return false;
            }

            if (/^(mailto:|tel:|javascript:)/i.test(url)) {
                return false;
            }

            try {
                var resolved = new URL(anchor.href, location.href);
                if (resolved.origin !== location.origin) {
                    return false;
                }
                if (resolved.pathname === location.pathname && resolved.search === location.search) {
                    return false;
                }
            } catch (e) {
                return false;
            }

            return true;
        }

        function flagNavigation() {
            try {
                sessionStorage.setItem(KEY_NAV, '1');
            } catch (e) {}
        }

        window.SectorsProgress = {
            start: start,
            done: done
        };

        document.addEventListener('click', function (event) {
            var anchor = event.target && event.target.closest ? event.target.closest('a[href]') : null;

            if (!isInternalLink(anchor, event)) {
                return;
            }

            flagNavigation();
            start();
        }, true);

        document.addEventListener('submit', function (event) {
            if (event.target && event.target.dataset && event.target.dataset.noProgress !== undefined) {
                return;
            }

            flagNavigation();
            start();
        }, true);

        window.addEventListener('popstate', function () {
            flagNavigation();
            start();
        });

        function finishPageLoad() {
            if (!bar) {
                return;
            }

            done();
            clearNavFlag();
        }

        window.addEventListener('load', finishPageLoad);
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                clearNavFlag();
                if (bar) {
                    done();
                }
            }
        });

        var pendingStart = false;

        try {
            pendingStart = sessionStorage.getItem(KEY_NAV) === '1';
        } catch (e) {}

        if (pendingStart) {
            start();
        }
    })();
</script>
