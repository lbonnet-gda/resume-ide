import { Controller } from '@hotwired/stimulus';
import { cache, visit } from '@hotwired/turbo';
import { load, save } from '../session_state.js';

export default class extends Controller {
    static targets = ['list', 'tab', 'template'];

    connect() {
        this.sync();
    }

    // The tab bar is a Turbo permanent element: the server only knows the current file, read from the new page.
    sync() {
        const { fileUrl, fileName } = document.getElementById('editor-area').dataset;
        const tabs = load('tabs', []);

        if (fileUrl && !tabs.some((tab) => tab.url === fileUrl)) {
            tabs.push({ url: fileUrl, name: fileName });
            save('tabs', tabs);
        }

        this.render(tabs, fileUrl);
    }

    render(tabs, activeUrl) {
        this.tabTargets
            .filter((element) => !tabs.some((tab) => tab.url === element.dataset.url))
            .forEach((element) => element.remove());

        // Only moves what is misplaced: moving an element would drop the keyboard focus it holds.
        tabs.forEach((tab, index) => {
            const element = this.tabElement(tab.url) ?? this.createTab(tab);

            if (this.listTarget.children[index] !== element) {
                this.listTarget.insertBefore(element, this.listTarget.children[index] ?? null);
            }
        });

        this.tabTargets.forEach((element) => {
            const link = element.querySelector('.tab__link');

            if (element.dataset.url === activeUrl) {
                link.setAttribute('aria-current', 'page');
                this.reveal(link);
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    // Not scrollIntoView(): it moves the browser's starting point for the Tab key to the element.
    reveal(link) {
        const box = link.getBoundingClientRect();
        const bar = this.element.getBoundingClientRect();

        if (box.left < bar.left) {
            this.element.scrollLeft -= bar.left - box.left;
        } else if (box.right > bar.right) {
            this.element.scrollLeft += box.right - bar.right;
        }
    }

    tabElement(url) {
        return this.tabTargets.find((element) => element.dataset.url === url);
    }

    createTab({ url, name }) {
        const element = this.templateTarget.content.firstElementChild.cloneNode(true);
        const link = element.querySelector('.tab__link');

        element.dataset.url = url;
        link.href = url;
        link.querySelector('svg').setAttribute('class', `icon icon--${name.split('.').pop()}`);
        link.querySelector('.tab__name').textContent = name;
        element.querySelector('.tab__close .visually-hidden').textContent = `Close ${name}`;

        return element;
    }

    close(event) {
        this.closeTab(event.currentTarget.closest('.tab'));
    }

    closeOnMiddleClick(event) {
        if (event.button === 1) {
            event.preventDefault();
            this.closeTab(event.currentTarget);
        }
    }

    closeTab(element) {
        const tabs = load('tabs', []);
        const index = tabs.findIndex((tab) => tab.url === element.dataset.url);
        const remaining = tabs.filter((tab) => tab.url !== element.dataset.url);
        const wasActive = element.querySelector('.tab__link').hasAttribute('aria-current');

        save('tabs', remaining);
        element.remove();

        if (!wasActive) {
            return;
        }

        const neighbor = remaining[Math.min(index, remaining.length - 1)];

        if (neighbor) {
            this.tabElement(neighbor.url).querySelector('.tab__link').focus();
            visit(neighbor.url, { action: 'replace' });

            return;
        }

        this.showNoOpenFile();
    }

    // There is no page for "no file open": the URL stays on the closed file, which reopens if the page is reloaded.
    showNoOpenFile() {
        cache.exemptPageFromCache();
        document.getElementById('ide').classList.add('is-editor-empty');
        document.title = 'resume-ide';
        document.querySelector('.tree a[aria-current]')?.removeAttribute('aria-current');

        const editor = document.getElementById('editor');

        editor.setAttribute('aria-label', 'Editor, no files are open');
        editor.focus();
    }

    // Mouse wheels only scroll vertically, and the tab bar has no scrollbar to drag.
    scroll(event) {
        if (event.deltaX === 0) {
            event.preventDefault();
            event.currentTarget.scrollLeft += event.deltaY;
        }
    }
}
