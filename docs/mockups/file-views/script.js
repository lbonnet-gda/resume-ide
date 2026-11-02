// Mockup behavior: view modes on top of the IDE layout mockup (tree, tabs, mobile drawer, terminal collapse).
// The real site renders each file server-side at its own URL; here the URL hash stands in for it.

const isMobile = window.matchMedia('(max-width: 767.98px)');
const workspace = document.getElementById('workspace');
const tree = document.getElementById('tree');
const toolWindow = document.getElementById('project-tool-window');
const drawerToggle = document.getElementById('drawer-toggle');
const backdrop = document.getElementById('drawer-backdrop');
const terminal = document.getElementById('terminal');
const terminalToggle = document.getElementById('terminal-toggle');
const stripeProject = document.getElementById('stripe-project');
const stripeTerminal = document.getElementById('stripe-terminal');
const branchSelector = document.getElementById('branch-selector');

const fileView = document.getElementById('file-view');
const viewModes = document.getElementById('view-modes');
const editorArea = document.getElementById('editor-area');

let openTabs = ['README.md', 'src/Experience/AcmeCorp.php', 'composer.json'];
let activePath = 'README.md';

const fileName = (path) => path.split('/').pop();

const iconClass = (path) => {
    if (path.endsWith('.php')) {
        return 'icon--php';
    }
    if (path.endsWith('.md')) {
        return 'icon--md';
    }
    if (path.endsWith('.json')) {
        return 'icon--json';
    }
    if (path.endsWith('.yaml')) {
        return 'icon--yaml';
    }

    return 'icon--env';
};

const svgIcon = (classes, symbol) => `<svg class="icon ${classes}" aria-hidden="true"><use href="#${symbol}"/></svg>`;

// Default: Preview for README.md and on mobile, Code elsewhere. The last chosen mode is remembered for the session.
// Split needs an editor at least 768px wide (keep in sync with styles.css), otherwise it falls back to Preview.
function currentMode() {
    const chosen = sessionStorage.getItem('view-mode');
    const mode = chosen ?? (isMobile.matches || activePath === 'README.md' ? 'preview' : 'code');

    return mode === 'split' && editorArea.clientWidth < 768 ? 'preview' : mode;
}

function renderViewMode() {
    const mode = currentMode();
    const hasFile = Boolean(files[activePath]);

    fileView.dataset.mode = mode;
    document.getElementById('editor').hidden = !hasFile || mode === 'preview';
    document.getElementById('preview').hidden = !hasFile || mode === 'code';
    viewModes.hidden = !hasFile;
    viewModes.querySelectorAll('[data-mode]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.mode === mode));
    });
    document.getElementById('code-breadcrumb').hidden = !hasFile || mode === 'preview'
        || files[activePath].breadcrumb.length === 0;
    document.getElementById('status-caret').hidden = !hasFile || mode === 'preview';
}

function renderTabs() {
    document.getElementById('tabs').innerHTML = openTabs.map((path) => {
        const current = path === activePath ? ' aria-current="page"' : '';

        return `
            <li class="tab">
                <a class="tab__link" href="#/${path}"${current}>
                    ${svgIcon(iconClass(path), 'i-file')}
                    ${fileName(path)}
                </a>
                <button type="button" class="tab__close" data-close="${path}">
                    ${svgIcon('icon--small', 'i-close')}
                    <span class="visually-hidden">Close ${fileName(path)}</span>
                </button>
            </li>`;
    }).join('');
    document.querySelector('.tab__link[aria-current="page"]')?.scrollIntoView({block: 'nearest', inline: 'nearest'});
}

function renderStatusPath() {
    const segments = ['resume-ide', ...activePath.split('/')];
    const last = segments.pop();

    document.getElementById('status-path').innerHTML = segments.map((segment) => `<li>${segment}</li>`).join('')
        + `<li><span aria-current="page">${last}</span></li>`;
}

function renderEditor() {
    const editor = document.getElementById('editor');
    const preview = document.getElementById('preview');
    const file = files[activePath];

    document.getElementById('editor-empty').hidden = Boolean(file);
    document.getElementById('status-language').textContent = file ? file.language : '';
    renderViewMode();

    if (!file) {
        document.getElementById('status-path').innerHTML = '<li>resume-ide</li>';
        document.title = 'resume-ide (mockup)';

        return;
    }

    const lineCount = file.lines.length + 1;
    document.getElementById('editor-gutter').textContent = Array.from({length: lineCount}, (_, i) => i + 1).join('\n');
    document.getElementById('editor-code').innerHTML = `${highlight(file.syntax, file.lines)}\n`;
    document.getElementById('preview-content').innerHTML = file.preview;
    document.getElementById('preview-content').dataset.kind = file.kind;
    editor.setAttribute('aria-label', `${fileName(activePath)}, source code`);
    preview.setAttribute('aria-label', `${fileName(activePath)}, preview`);
    editor.scrollTo(0, 0);
    preview.scrollTo(0, 0);
    document.querySelector('#code-breadcrumb ol').innerHTML = file.breadcrumb
        .map((part) => `<li>${part}</li>`)
        .join('');
    renderStatusPath();
    document.title = `${fileName(activePath)} – resume-ide (mockup)`;
}

function renderTreeSelection() {
    tree.querySelectorAll('a.tree__row').forEach((row) => {
        row.removeAttribute('aria-current');
        if (row.getAttribute('href') !== `#/${activePath}`) {
            return;
        }
        row.setAttribute('aria-current', 'page');
        let group = row.closest('.tree__group');
        while (group) {
            group.hidden = false;
            group.previousElementSibling?.setAttribute('aria-expanded', 'true');
            group = group.parentElement.closest('.tree__group');
        }
    });
}

function openFile(path) {
    if (!files[path]) {
        return;
    }
    if (!openTabs.includes(path)) {
        openTabs.push(path);
    }
    activePath = path;
    renderTabs();
    renderEditor();
    renderTreeSelection();
}

function closeTab(path) {
    const index = openTabs.indexOf(path);
    openTabs = openTabs.filter((tab) => tab !== path);
    if (path === activePath) {
        activePath = openTabs[Math.min(index, openTabs.length - 1)] ?? null;
        history.replaceState(null, '', activePath ? `#/${activePath}` : '#');
    }
    renderTabs();
    renderEditor();
    renderTreeSelection();
    const nextTab = document.querySelector('.tab__link[aria-current="page"]') ?? fileView;
    nextTab.focus();
}

viewModes.addEventListener('click', (event) => {
    const button = event.target.closest('[data-mode]');
    if (button) {
        sessionStorage.setItem('view-mode', button.dataset.mode);
        renderViewMode();
    }
});

// A recruiter following a link from a Preview must not land on source code: the Preview becomes the chosen mode.
document.getElementById('preview').addEventListener('click', (event) => {
    if (event.target.closest('a[href^="#/"]') && currentMode() === 'preview') {
        sessionStorage.setItem('view-mode', 'preview');
    }
});

new ResizeObserver(renderViewMode).observe(editorArea);

// Browsers neither start a text selection inside a link nor select while dragging one: the link drops its href
// during the press. A drag that selected text then ends with a click on the link, which must not follow it.
document.getElementById('editor').addEventListener('mousedown', (event) => {
    const link = event.target.closest('a');
    if (link) {
        const href = link.getAttribute('href');
        link.removeAttribute('href');
        document.addEventListener('mouseup', () => link.setAttribute('href', href), {once: true});
    }
});

document.getElementById('editor').addEventListener('click', (event) => {
    if (event.target.closest('a') && !window.getSelection().isCollapsed) {
        event.preventDefault();
    }
});

function openFromHash() {
    const path = decodeURIComponent(location.hash.replace(/^#\//, ''));
    if (files[path]) {
        openFile(path);
    }
}

document.getElementById('tabs').addEventListener('click', (event) => {
    const close = event.target.closest('[data-close]');
    if (close) {
        closeTab(close.dataset.close);
    }
});

// Middle click closes a tab, as in the IDE.
document.getElementById('tabs').addEventListener('auxclick', (event) => {
    const link = event.target.closest('.tab__link');
    if (link && event.button === 1) {
        event.preventDefault();
        closeTab(link.getAttribute('href').slice(2));
    }
});

// The tab bar has no scrollbar: the mouse wheel scrolls it horizontally, as in the IDE.
document.querySelector('.tabs').addEventListener('wheel', (event) => {
    if (event.deltaX === 0) {
        event.preventDefault();
        event.currentTarget.scrollLeft += event.deltaY;
    }
}, {passive: false});

window.addEventListener('hashchange', openFromHash);

// Tree: click toggles folders, arrow keys move between visible rows (Up/Down, Left/Right, Home/End).
tree.addEventListener('click', (event) => {
    const folder = event.target.closest('button.tree__row');
    if (folder) {
        const expanded = folder.getAttribute('aria-expanded') === 'true';
        folder.setAttribute('aria-expanded', String(!expanded));
        folder.nextElementSibling.hidden = expanded;
    }
    if (event.target.closest('a.tree__row') && isMobile.matches) {
        closeDrawer();
    }
});

tree.addEventListener('keydown', (event) => {
    const visibleRows = [...tree.querySelectorAll('.tree__row')].filter((row) => !row.closest('[hidden]'));
    const row = event.target.closest('.tree__row');
    const index = visibleRows.indexOf(row);
    const isFolder = row?.tagName === 'BUTTON';
    const expanded = row?.getAttribute('aria-expanded') === 'true';
    let target;

    switch (event.key) {
        case 'ArrowDown':
            target = visibleRows[index + 1];
            break;
        case 'ArrowUp':
            target = visibleRows[index - 1];
            break;
        case 'Home':
            target = visibleRows[0];
            break;
        case 'End':
            target = visibleRows[visibleRows.length - 1];
            break;
        case 'ArrowRight':
            if (isFolder && !expanded) {
                row.click();
            } else if (isFolder) {
                target = visibleRows[index + 1];
            }
            break;
        case 'ArrowLeft':
            if (isFolder && expanded) {
                row.click();
            } else {
                target = row.closest('.tree__group')?.previousElementSibling;
            }
            break;
        default:
            return;
    }
    event.preventDefault();
    target?.focus();
});

// Tree: a row cut off by the panel shows in full in a popup over the editor. Decorative, the row keeps the name.
const treePopup = document.createElement('div');
treePopup.className = 'tree__row tree-popup';
treePopup.setAttribute('aria-hidden', 'true');
treePopup.hidden = true;
document.body.append(treePopup);

const hideTreePopup = () => {
    treePopup.hidden = true;
};

function isTruncated(row) {
    const name = row.querySelector('.tree__name');
    const hint = row.querySelector('.tree__hint');

    return name.scrollWidth > name.clientWidth
        || Boolean(hint && hint.getBoundingClientRect().top >= name.getBoundingClientRect().bottom);
}

function showTreePopup(row) {
    const box = row.getBoundingClientRect();
    const panel = tree.getBoundingClientRect();
    if (!isTruncated(row) || box.top < panel.top || box.bottom > panel.bottom) {
        hideTreePopup();

        return;
    }
    treePopup.innerHTML = row.innerHTML;
    treePopup.style.setProperty('--depth', row.style.getPropertyValue('--depth'));
    treePopup.style.left = `${box.left}px`;
    treePopup.style.top = `${box.top}px`;
    treePopup.style.minWidth = `${box.width}px`;
    treePopup.classList.toggle('is-selected', row.hasAttribute('aria-current'));
    treePopup.classList.toggle('is-focused', row.matches(':focus-visible'));
    treePopup.toggleAttribute('aria-expanded', row.hasAttribute('aria-expanded'));
    if (row.hasAttribute('aria-expanded')) {
        treePopup.setAttribute('aria-expanded', row.getAttribute('aria-expanded'));
    }
    treePopup.hidden = false;
}

if (window.matchMedia('(hover: hover)').matches) {
    const rowOf = (event) => event.target.closest('.tree__row');

    tree.addEventListener('mouseover', (event) => (rowOf(event) ? showTreePopup(rowOf(event)) : hideTreePopup()));
    tree.addEventListener('mouseleave', hideTreePopup);
    tree.addEventListener('focusin', (event) => showTreePopup(rowOf(event)));
    tree.addEventListener('focusout', hideTreePopup);
    tree.addEventListener('click', hideTreePopup);
    // Focusing a row can scroll the tree: the popup then follows the focused row instead of disappearing.
    tree.addEventListener('scroll', () => {
        const focused = tree.querySelector('.tree__row:focus-visible');
        if (focused) {
            showTreePopup(focused);
        } else {
            hideTreePopup();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideTreePopup();
        }
    });
}

// Desktop: the stripe button shows or hides the Project tool window.
stripeProject.addEventListener('click', () => {
    const visible = stripeProject.getAttribute('aria-pressed') === 'true';
    stripeProject.setAttribute('aria-pressed', String(!visible));
    workspace.classList.toggle('is-project-hidden', visible);
});

// Mobile: the Project tool window becomes a drawer; the rest of the page is inert while it is open.
const inertWhenDrawerOpen = () => [
    document.querySelector('.top-bar'),
    document.getElementById('editor-area'),
    terminal,
    document.querySelector('.status-bar'),
];

function openDrawer() {
    toolWindow.classList.add('is-open');
    backdrop.hidden = false;
    drawerToggle.setAttribute('aria-expanded', 'true');
    inertWhenDrawerOpen().forEach((element) => {
        element.inert = true;
    });
    (tree.querySelector('[aria-current="page"]') ?? tree.querySelector('.tree__row')).focus();
}

function closeDrawer() {
    if (!toolWindow.classList.contains('is-open')) {
        return;
    }
    toolWindow.classList.remove('is-open');
    backdrop.hidden = true;
    drawerToggle.setAttribute('aria-expanded', 'false');
    inertWhenDrawerOpen().forEach((element) => {
        element.inert = false;
    });
    drawerToggle.focus();
}

drawerToggle.addEventListener('click', openDrawer);
document.getElementById('drawer-close').addEventListener('click', closeDrawer);
backdrop.addEventListener('click', closeDrawer);
isMobile.addEventListener('change', (event) => {
    if (!event.matches) {
        closeDrawer();
    }
});

// Terminal: collapsible panel, collapsed by default on mobile.
const isTerminalCollapsed = () => terminal.classList.contains('is-collapsed');

function setTerminalExpanded(expanded) {
    terminal.classList.toggle('is-collapsed', !expanded);
    terminalToggle.setAttribute('aria-expanded', String(expanded));
    stripeTerminal.setAttribute('aria-pressed', String(expanded));
}

terminalToggle.addEventListener('click', () => setTerminalExpanded(isTerminalCollapsed()));
stripeTerminal.addEventListener('click', () => {
    setTerminalExpanded(isTerminalCollapsed());
    if (!isTerminalCollapsed()) {
        document.getElementById('terminal-input').focus();
    }
});
setTerminalExpanded(!isMobile.matches);

// A click in the panel focuses the input, unless it ends a text selection or lands on one of the panel's controls.
terminal.addEventListener('click', (event) => {
    if (!event.target.closest('button, a') && window.getSelection().isCollapsed) {
        document.getElementById('terminal-input').focus({preventScroll: true});
    }
});

// The mockup does not run commands: it only echoes them, and handles "clear".
document.getElementById('terminal-form').addEventListener('submit', (event) => {
    event.preventDefault();
    const input = document.getElementById('terminal-input');
    const log = document.getElementById('terminal-log');
    const command = input.value.trim();
    input.value = '';
    if (command === 'clear') {
        log.innerHTML = '';

        return;
    }
    log.insertAdjacentHTML('beforeend', `
        <p><span class="terminal__prompt">jane@resume-ide:~/src/Experience$</span> ${html(command)}</p>
        <pre>(mockup) Commands run server-side in the real site.</pre>`);
    document.getElementById('terminal-body').scrollTop = Number.MAX_SAFE_INTEGER;
});

// Branch selector and drawer: Escape closes, and a click outside closes the branch popup.
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }
    if (branchSelector.open) {
        branchSelector.open = false;
        branchSelector.querySelector('summary').focus();
    }
    closeDrawer();
});

document.addEventListener('click', (event) => {
    if (!branchSelector.contains(event.target) || event.target.closest('.popup__item')) {
        branchSelector.open = false;
    }
});

openFile(activePath);
openFromHash();
