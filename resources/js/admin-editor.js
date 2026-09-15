/**
 * WYSIWYG article editor for the admin Market Articles page.
 *
 * Loaded ONLY by Modules/Admin/resources/views/market-insights/index.blade.php
 * (its own Vite entry, see vite.config.js) so Quill never ships in the shared
 * bundle.
 *
 * Contract with the page:
 *   - the view exposes `window.createArticleEditor(el, initialHtml)` which returns
 *     an object with `getHtml()` / `setHtml()` / `focus()`.
 *   - Alpine calls `getHtml()` on submit and `setHtml()` when opening the modal.
 *
 * Content format: Quill produces HTML (`<p>`, `<strong>`, `<ul>`…), NOT Markdown.
 * The `market_insights.content` column is `text`, so no migration is needed — but
 * the backend MUST sanitize this HTML before rendering it publicly.
 * TODO(backend): sanitize `content` on store/update and on public render (e.g.
 * HTMLPurifier) — a rich-text field is an XSS vector by design.
 */
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// MUST come after quill.snow.css: Quill's rules win ties, and this file can only
// override them by appearing later in the cascade. See its header for the three
// dark-mode bugs this ordering fixes.
import '../css/admin-editor.css';

// Toolbar: intentionally limited to the formatting an article actually needs.
// No image/file upload (there is no media storage wired up yet) and no font/colour
// pickers (they would let an author break the app's theme).
const TOOLBAR = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

/**
 * Create a Quill instance bound to a host element.
 *
 * @param {HTMLElement} host       container the editor mounts into
 * @param {string}      initialHtml content to load (HTML, not Markdown)
 */
window.createArticleEditor = function (host, initialHtml = '') {
    const quill = new Quill(host, {
        theme: 'snow',
        modules: { toolbar: TOOLBAR },
        placeholder: 'Write the article body…',
    });

    if (initialHtml) {
        // `dangerouslyPasteHTML` accepts an HTML string. Content comes from our own
        // admin form, but it is still pasted as *content* — Quill parses it through
        // its own clipboard matcher rather than trusting raw DOM.
        quill.clipboard.dangerouslyPasteHTML(initialHtml);
    }

    return {
        getHtml: () => (quill.getText().trim() ? quill.root.innerHTML : ''),
        setHtml: (html) => {
            quill.setContents([]);
            if (html) quill.clipboard.dangerouslyPasteHTML(html);
        },
        focus: () => quill.focus(),
        instance: quill,
    };
};
