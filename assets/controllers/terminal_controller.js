import { Controller } from '@hotwired/stimulus';
import { visit } from '@hotwired/turbo';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    static targets = ['input', 'session'];

    history = [];
    historyPosition = 0;

    focusInput(event) {
        if (!event.target.closest('button, a') && window.getSelection().isCollapsed) {
            this.inputTarget.focus({ preventScroll: true });
        }
    }

    async run() {
        const line = this.inputTarget.value;

        if (line.trim() !== '') {
            this.history.push(line);
        }

        this.historyPosition = this.history.length;
        this.inputTarget.value = '';
        await this.callAction('run', line);
    }

    previous() {
        if (this.historyPosition > 0) {
            this.historyPosition--;
            this.setInput(this.history[this.historyPosition]);
        }
    }

    next() {
        if (this.historyPosition < this.history.length) {
            this.historyPosition++;
            this.setInput(this.history[this.historyPosition] ?? '');
        }
    }

    // Tab on an empty line still moves the focus: the terminal must not trap the keyboard.
    async complete(event) {
        const line = this.inputTarget.value;

        if (line.trim() === '') {
            return;
        }

        event.preventDefault();
        await this.callAction('complete', line);
    }

    completed({ detail: { line } }) {
        this.setInput(line);
    }

    open({ detail: { url } }) {
        visit(url);
    }

    async callAction(name, line) {
        const component = await getComponent(this.sessionTarget);

        await component.action(name, { line });
        this.sessionTarget.scrollTop = this.sessionTarget.scrollHeight;
    }

    setInput(value) {
        this.inputTarget.value = value;
        this.inputTarget.setSelectionRange(value.length, value.length);
    }
}
