import './theme-switcher';

import { HSStaticMethods } from 'preline/non-auto';

const SIDEBAR_MIN = 190;
const SIDEBAR_MAX = 420;

document.addEventListener('alpine:init', () => {
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
            this.applySidebarVars();

            window.addEventListener('resize', () => this.clampWidth());
        },

        applySidebarVars() {
            if (!window.SectorsChrome) return;

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
