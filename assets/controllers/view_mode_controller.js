import { Controller } from '@hotwired/stimulus';
import { load, save } from '../session_state.js';

const mobile = window.matchMedia('(max-width: 767.98px)');

export default class extends Controller {
    static targets = ['button', 'fileView'];
    static values = { mode: String, inUrl: Boolean };

    // Rendered right away too: the observer only calls back after the next layout, when the focus may already be in
    // the pane that this hides.
    connect() {
        this.render();
        this.resizeObserver = new ResizeObserver(() => this.render());
        this.resizeObserver.observe(this.element);
    }

    disconnect() {
        this.resizeObserver.disconnect();
    }

    // A mode in the URL wins, so that a shared link opens as intended. Otherwise, the browser adds the session choice
    // and the mobile default to the server's default. Split needs an editor at least 768px wide, as in app.css: below,
    // it falls back to the Preview.
    currentMode() {
        const fallback = mobile.matches ? 'preview' : this.modeValue;
        const mode = this.inUrlValue ? this.modeValue : load('viewMode', fallback);

        return mode === 'split' && this.element.clientWidth < 768 ? 'preview' : mode;
    }

    render() {
        const mode = this.currentMode();

        this.fileViewTarget.dataset.mode = mode;
        this.buttonTargets.forEach((button) => {
            if (button.dataset.mode === mode) {
                button.setAttribute('aria-current', 'true');
            } else {
                button.removeAttribute('aria-current');
            }
        });
    }

    choose(event) {
        const { href, dataset } = event.currentTarget;

        event.preventDefault();
        save('viewMode', dataset.mode);

        // Otherwise, reloading the page would bring back the mode of the URL. history.state keeps Turbo's own state.
        if (this.inUrlValue) {
            history.replaceState(history.state, '', href);
            this.modeValue = dataset.mode;
        }

        this.render();
    }

    // Following a link from the Preview keeps the mode on screen, so a recruiter never lands on source code. These
    // links carry ?view=preview when the server rendered the Preview alone, for browsers without JavaScript.
    followPreviewLink(event) {
        const link = event.target.closest('a[href^="/"]');
        const mode = this.currentMode();

        if (!link) {
            return;
        }

        save('viewMode', mode);

        if (mode === 'preview') {
            return;
        }

        const url = new URL(link.href);

        url.searchParams.delete('view');
        link.href = url;
    }
}
