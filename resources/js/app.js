// Theme switcher: applies stored theme (dark default) before paint
import './theme-switcher';

// Preline UI — non-auto entry: initialization is explicit (after DOM ready)
import { HSStaticMethods } from 'preline/non-auto';

document.addEventListener('DOMContentLoaded', () => {
    HSStaticMethods.autoInit();
});