// TODO(backend): sanitize `content` on store/update and on public render (HTMLPurifier) — rich text is an XSS vector.
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

import '../css/admin-editor.css';

const TOOLBAR = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

window.createArticleEditor = function (host, initialHtml = '') {
    const quill = new Quill(host, {
        theme: 'snow',
        modules: { toolbar: TOOLBAR },
        placeholder: 'Write the article body…',
    });

    if (initialHtml) {
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
