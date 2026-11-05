import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];

    focusInput(event) {
        if (!event.target.closest('button, a') && window.getSelection().isCollapsed) {
            this.inputTarget.focus({ preventScroll: true });
        }
    }

    ignoreCommand(event) {
        event.preventDefault();
        this.inputTarget.value = '';
    }
}
