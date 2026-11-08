import { Controller } from '@hotwired/stimulus';
import { load, save } from '../session_state.js';

const mobile = window.matchMedia('(max-width: 767.98px)');

function focusOpenedFile() {
    [...document.querySelectorAll('.file-view > .scrollable')].find((pane) => pane.getClientRects().length > 0)?.focus();
}

export default class extends Controller {
    static targets = [
        'backdrop',
        'branchSelector',
        'drawerToggle',
        'inertWithDrawer',
        'stripeProject',
        'stripeTerminal',
        'terminal',
        'terminalInput',
        'terminalToggle',
        'toolWindow',
        'workspace',
    ];

    connect() {
        this.closeDrawerOnDesktop = () => {
            if (!mobile.matches) {
                this.closeDrawer();
            }
        };
        mobile.addEventListener('change', this.closeDrawerOnDesktop);

        this.setProjectVisible(load('projectVisible', true));
    }

    // Not in connect(): Turbo puts its permanent elements, like the terminal, back into the page after this connects.
    terminalTargetConnected() {
        this.setTerminalExpanded(load('terminalExpanded', !mobile.matches));
    }

    disconnect() {
        mobile.removeEventListener('change', this.closeDrawerOnDesktop);
    }

    toggleProject() {
        const visible = this.stripeProjectTarget.getAttribute('aria-pressed') !== 'true';

        this.setProjectVisible(visible);
        save('projectVisible', visible);
    }

    setProjectVisible(visible) {
        this.stripeProjectTarget.setAttribute('aria-pressed', String(visible));
        this.workspaceTarget.classList.toggle('is-project-hidden', !visible);
    }

    toggleTerminal() {
        const expanded = this.terminalToggleTarget.getAttribute('aria-expanded') !== 'true';

        this.setTerminalExpanded(expanded);
        save('terminalExpanded', expanded);
    }

    toggleTerminalAndFocus() {
        this.toggleTerminal();
        this.terminalInputTarget.focus();
    }

    setTerminalExpanded(expanded) {
        this.terminalTarget.classList.toggle('is-expanded', expanded);
        this.terminalTarget.classList.toggle('is-collapsed', !expanded);
        this.terminalToggleTarget.setAttribute('aria-expanded', String(expanded));
        this.stripeTerminalTarget.setAttribute('aria-pressed', String(expanded));
    }

    openDrawer() {
        this.toolWindowTarget.classList.add('is-open');
        this.backdropTarget.hidden = false;
        this.drawerToggleTarget.setAttribute('aria-expanded', 'true');
        this.inertWithDrawerTargets.forEach((element) => {
            element.inert = true;
        });

        const currentRow = this.toolWindowTarget.querySelector('[aria-current="page"]');

        (currentRow ?? this.toolWindowTarget.querySelector('.tree__row')).focus();
    }

    closeDrawer() {
        if (this.hideDrawer()) {
            this.drawerToggleTarget.focus();
        }
    }

    // The new page replaces the drawer toggle: the focus goes to the opened file instead.
    closeDrawerAfterFileChoice() {
        if (this.hideDrawer()) {
            document.addEventListener('turbo:load', focusOpenedFile, { once: true });
        }
    }

    hideDrawer() {
        if (!this.toolWindowTarget.classList.contains('is-open')) {
            return false;
        }

        this.toolWindowTarget.classList.remove('is-open');
        this.backdropTarget.hidden = true;
        this.drawerToggleTarget.setAttribute('aria-expanded', 'false');
        this.inertWithDrawerTargets.forEach((element) => {
            element.inert = false;
        });

        return true;
    }

    closeOnEscape() {
        if (this.branchSelectorTarget.open) {
            this.branchSelectorTarget.open = false;
            this.branchSelectorTarget.querySelector('summary').focus();
        }

        this.closeDrawer();
    }

    closeBranchSelectorOnOutsideClick(event) {
        if (!this.branchSelectorTarget.contains(event.target) || event.target.closest('.popup__item')) {
            this.branchSelectorTarget.open = false;
        }
    }
}
