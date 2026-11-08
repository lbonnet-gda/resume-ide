import { Controller } from '@hotwired/stimulus';
import { load, save } from '../session_state.js';

export default class extends Controller {
    static targets = ['popup', 'tree'];

    connect() {
        const openFolders = load('openFolders', null);

        if (openFolders) {
            this.folders().forEach((folder) => {
                folder.open = openFolders.includes(folder.dataset.path);
            });
        }

        this.sync();
    }

    // The tree is a Turbo permanent element, to keep its scroll and focus: the current file comes from the new page.
    sync() {
        const { fileUrl } = document.getElementById('editor-area').dataset;

        this.treeTarget.querySelectorAll('a.tree__row').forEach((link) => {
            if (link.getAttribute('href') !== fileUrl) {
                link.removeAttribute('aria-current');

                return;
            }

            link.setAttribute('aria-current', 'page');

            for (let folder = link.closest('details'); folder; folder = folder.parentElement.closest('details')) {
                folder.open = true;
            }

            this.reveal(link);
        });
    }

    // Not scrollIntoView(): it moves the browser's starting point for the Tab key to the element.
    reveal(link) {
        const box = link.getBoundingClientRect();
        const panel = this.treeTarget.getBoundingClientRect();

        if (box.top < panel.top) {
            this.treeTarget.scrollTop -= panel.top - box.top;
        } else if (box.bottom > panel.bottom) {
            this.treeTarget.scrollTop += box.bottom - panel.bottom;
        }
    }

    rememberFolders() {
        save('openFolders', this.folders().filter((folder) => folder.open).map((folder) => folder.dataset.path));
    }

    folders() {
        return [...this.treeTarget.querySelectorAll('details')];
    }

    navigate(event) {
        if (event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        const rows = [...this.treeTarget.querySelectorAll('.tree__row')].filter((row) => row.getClientRects().length > 0);
        const row = event.target.closest('.tree__row');
        const index = rows.indexOf(row);
        const isFolder = row.matches('summary');
        const expanded = isFolder && row.parentElement.open;
        let target;

        switch (event.key) {
            case 'ArrowDown':
                target = rows[index + 1];
                break;
            case 'ArrowUp':
                target = rows[index - 1];
                break;
            case 'Home':
                target = rows[0];
                break;
            case 'End':
                target = rows.at(-1);
                break;
            case 'ArrowRight':
                if (isFolder && !expanded) {
                    row.click();
                } else if (isFolder) {
                    target = rows[index + 1];
                }
                break;
            case 'ArrowLeft':
                if (expanded) {
                    row.click();
                } else {
                    const item = isFolder ? row.parentElement : row;

                    target = item.parentElement.closest('details')?.querySelector(':scope > summary');
                }
                break;
            default:
                return;
        }

        event.preventDefault();
        target?.focus();
    }

    showPopupOnHover(event) {
        const row = event.target.closest('.tree__row');

        if (row) {
            this.showPopup(row);
        } else {
            this.hidePopup();
        }
    }

    showPopupOnFocus(event) {
        this.showPopup(event.target.closest('.tree__row'));
    }

    // Focusing a row can scroll the tree: the popup then follows the focused row instead of disappearing.
    movePopup() {
        const focused = this.treeTarget.querySelector('.tree__row:focus-visible');

        if (focused) {
            this.showPopup(focused);
        } else {
            this.hidePopup();
        }
    }

    hidePopup() {
        this.popupTarget.hidden = true;
    }

    showPopup(row) {
        const box = row.getBoundingClientRect();
        const panel = this.treeTarget.getBoundingClientRect();
        const popup = this.popupTarget;

        const scrolledOut = box.top < panel.top || box.bottom > panel.bottom;

        if (!window.matchMedia('(hover: hover)').matches || scrolledOut || !this.isCutOff(row)) {
            this.hidePopup();

            return;
        }

        popup.innerHTML = row.innerHTML;
        popup.style.setProperty('--depth', row.style.getPropertyValue('--depth'));
        popup.style.left = `${box.left}px`;
        popup.style.top = `${box.top}px`;
        popup.style.minWidth = `${box.width}px`;
        popup.classList.toggle('is-selected', row.hasAttribute('aria-current'));
        popup.classList.toggle('is-focused', row.matches(':focus-visible'));
        popup.classList.toggle('is-expanded', row.matches('details[open] > summary'));
        popup.hidden = false;
    }

    isCutOff(row) {
        const name = row.querySelector('.tree__name');
        const hint = row.querySelector('.tree__hint');

        return name.scrollWidth > name.clientWidth
            || Boolean(hint && hint.getBoundingClientRect().top >= name.getBoundingClientRect().bottom);
    }
}
