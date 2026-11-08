import { Controller } from '@hotwired/stimulus';

// Browsers neither start a text selection inside a link nor select while dragging one: the link drops its href
// during the press. A drag that selected text then ends with a click on the link, which must not follow it.
export default class extends Controller {
    allowSelection(event) {
        const link = event.target.closest('a[href]');

        if (!link || event.button !== 0) {
            return;
        }

        const href = link.getAttribute('href');

        link.removeAttribute('href');
        document.addEventListener('mouseup', () => link.setAttribute('href', href), { once: true });
    }

    // A click from the keyboard has no detail: Enter on a focused link always follows it.
    ignoreClickEndingSelection(event) {
        if (event.target.closest('a') && event.detail > 0 && !window.getSelection().isCollapsed) {
            event.preventDefault();
        }
    }
}
