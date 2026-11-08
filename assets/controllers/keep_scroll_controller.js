import { Controller } from '@hotwired/stimulus';

// Turbo moves its permanent elements into the new page, and moving an element resets its scroll position.
export default class extends Controller {
    save() {
        this.position = { top: this.element.scrollTop, left: this.element.scrollLeft };
    }

    restore() {
        if (this.position) {
            this.element.scrollTo(this.position);
        }
    }
}
