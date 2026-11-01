// Mockup behavior only: tree expand/collapse, tabs, mobile drawer, terminal collapse.
// The real site renders each file server-side at its own URL; here the URL hash stands in for it.

// Displayed files follow the owner's code style (spec section 6): @Symfony-like PHP, chopped argument lists
// and arrays with trailing commas, 4-space indentation everywhere. The editor adds the final newline.
const phpClass = (namespace, name, data) => {
    const {start, end, ...fields} = data.fields;
    const period = [start, end].filter(Boolean).map((date) => `'${date}'`).join(', ');

    return [
        '<?php',
        '',
        `namespace App\\${namespace};`,
        '',
        `use App\\Resume\\${namespace};`,
        'use App\\Resume\\Period;',
        '',
        `final class ${name} extends ${namespace}`,
        '{',
        '    public function __construct()',
        '    {',
        '        parent::__construct(',
        ...Object.entries(fields).map(([key, value]) => `            ${key}: '${value}',`),
        `            period: new Period(${period}),`,
        ...data.lists.flatMap(([key, items]) => [
            `            ${key}: [`,
            ...items.map((item) => `                '${item}',`),
            '            ],',
        ]),
        '        );',
        '    }',
        '}',
    ];
};

const files = {
    'README.md': {
        language: 'Markdown',
        breadcrumb: ['Jane Doe'],
        lines: [
            '# Jane Doe',
            '',
            'Développeuse PHP / Symfony senior, basée à Lyon.',
            '',
            'Plus de dix ans à construire des applications web, des API',
            'et des outils internes. J’aime le code lisible, les tests utiles',
            'et les équipes qui livrent souvent.',
            '',
            '## Expériences',
            '',
            '- **AcmeCorp**, lead développeuse PHP (2022 – aujourd’hui)',
            '- **Hooli**, développeuse Symfony senior (2019 – 2022)',
            '- **Initech**, développeuse PHP (2016 – 2019)',
            '- **Globex**, développeuse web junior (2013 – 2016)',
            '',
            '## Contact',
            '',
            'Voir `.env`, ou téléchargez le CV en PDF.',
        ],
    },
    'composer.json': {
        language: 'JSON',
        breadcrumb: ['require'],
        lines: [
            '{',
            '    "name": "jane-doe/resume",',
            '    "description": "Compétences : la version indique le niveau (1 à 5)",',
            '    "require": {',
            '        "php": "^5.0",',
            '        "symfony/symfony": "^5.0",',
            '        "doctrine/orm": "^4.0",',
            '        "phpunit/phpunit": "^4.0",',
            '        "api-platform/core": "^3.0",',
            '        "docker/compose": "^3.0"',
            '    },',
            '    "require-dev": {',
            '        "javascript/vanilla": "^2.0"',
            '    }',
            '}',
        ],
    },
    '.env': {
        language: '.env',
        breadcrumb: [],
        lines: [
            '# Contact',
            'EMAIL=jane.doe@example.com',
            'LINKEDIN=https://www.linkedin.com/in/jane-doe-example',
            'GITHUB=https://github.com/jane-doe-example',
        ],
    },
    'config/interests.yaml': {
        language: 'YAML',
        breadcrumb: ['interests'],
        lines: [
            'interests:',
            '    - Randonnée en montagne',
            '    - Photographie argentique',
            '    - Contributions open source',
        ],
    },
    'config/languages.yaml': {
        language: 'YAML',
        breadcrumb: ['languages'],
        lines: [
            'languages:',
            '    français: langue maternelle',
            '    anglais: C1',
            '    espagnol: B1',
        ],
    },
    'projects/dotfiles.md': {
        language: 'Markdown',
        breadcrumb: ['dotfiles'],
        lines: [
            '# dotfiles',
            '',
            'Configuration shell et éditeur, installable en une commande.',
            '',
            'https://github.com/jane-doe-example/dotfiles',
        ],
    },
    'projects/resume-ide.md': {
        language: 'Markdown',
        breadcrumb: ['resume-ide'],
        lines: [
            '# resume-ide',
            '',
            'Ce CV, présenté comme un projet ouvert dans un IDE.',
            '',
            'https://github.com/jane-doe-example/resume-ide',
        ],
    },
    'projects/tiny-router.md': {
        language: 'Markdown',
        breadcrumb: ['tiny-router'],
        lines: [
            '# tiny-router',
            '',
            'Un routeur PHP de 200 lignes, écrit pour comprendre ceux des frameworks.',
            '',
            'https://github.com/jane-doe-example/tiny-router',
        ],
    },
    'src/Experience/AcmeCorp.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Experience', 'AcmeCorp'],
        lines: phpClass('Experience', 'AcmeCorp', {
            fields: {role: 'Lead développeuse PHP', location: 'Lyon, France', start: '2022-03', end: null},
            lists: [
                ['missions', [
                    'Refonte de l’API de facturation (Symfony, API Platform)',
                    'Mise en place de la CI et des tests de bout en bout',
                    'Encadrement d’une équipe de quatre développeurs',
                ]],
                ['stack', ['PHP 8.4', 'Symfony 8', 'PostgreSQL', 'Docker']],
            ],
        }),
    },
    'src/Experience/Hooli.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Experience', 'Hooli'],
        lines: phpClass('Experience', 'Hooli', {
            fields: {role: 'Développeuse Symfony senior', location: 'Paris, France', start: '2019-01', end: '2022-02'},
            lists: [
                ['missions', ['Découpage d’un monolithe en services', 'Migration de Symfony 3 à Symfony 5']],
                ['stack', ['PHP 7.4', 'Symfony 5', 'RabbitMQ', 'MySQL']],
            ],
        }),
    },
    'src/Experience/Initech.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Experience', 'Initech'],
        lines: phpClass('Experience', 'Initech', {
            fields: {role: 'Développeuse PHP', location: 'Lyon, France', start: '2016-06', end: '2018-12'},
            lists: [
                ['missions', ['Développement d’un back-office e-commerce', 'Optimisation des requêtes SQL critiques']],
                ['stack', ['PHP 7.0', 'Symfony 3', 'MySQL']],
            ],
        }),
    },
    'src/Experience/Globex.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Experience', 'Globex'],
        lines: phpClass('Experience', 'Globex', {
            fields: {role: 'Développeuse web junior', location: 'Grenoble, France', start: '2013-09', end: '2016-05'},
            lists: [
                ['missions', ['Sites vitrines et intégration HTML/CSS', 'Premiers modules PHP sur mesure']],
                ['stack', ['PHP 5.6', 'jQuery', 'MySQL']],
            ],
        }),
    },
    'src/Education/CityUniversity.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Education', 'CityUniversity'],
        lines: phpClass('Education', 'CityUniversity', {
            fields: {degree: 'Licence informatique', location: 'Grenoble, France', start: '2008-09', end: '2011-06'},
            lists: [['topics', ['Algorithmique', 'Bases de données', 'Réseaux']]],
        }),
    },
    'src/Education/TechInstitute.php': {
        language: 'PHP: 8.4',
        breadcrumb: ['App\\Education', 'TechInstitute'],
        lines: phpClass('Education', 'TechInstitute', {
            fields: {degree: 'Master génie logiciel', location: 'Lyon, France', start: '2011-09', end: '2013-06'},
            lists: [['topics', ['Architecture logicielle', 'Tests et qualité', 'Gestion de projet agile']]],
        }),
    },
};

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

let openTabs = ['README.md', 'src/Experience/AcmeCorp.php', 'composer.json'];
let activePath = 'src/Experience/AcmeCorp.php';

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

const escapeHtml = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

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
    const gutter = document.getElementById('editor-gutter');
    const code = document.getElementById('editor-code');
    const empty = document.getElementById('editor-empty');
    const file = files[activePath];

    gutter.hidden = code.hidden = !file;
    empty.hidden = Boolean(file);
    document.getElementById('code-breadcrumb').hidden = !file || file.breadcrumb.length === 0;
    document.getElementById('status-language').textContent = file ? file.language : '';

    if (!file) {
        editor.setAttribute('aria-label', 'Editor, no files are open');
        document.getElementById('status-path').innerHTML = '<li>resume-ide</li>';
        document.title = 'resume-ide (mockup)';

        return;
    }

    const lines = [...file.lines, ''];
    gutter.textContent = lines.map((line, index) => index + 1).join('\n');
    code.innerHTML = `<code>${escapeHtml(lines.join('\n'))}</code>`;
    editor.setAttribute('aria-label', `${fileName(activePath)}, source code`);
    editor.scrollTo(0, 0);
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
    const nextTab = document.querySelector('.tab__link[aria-current="page"]') ?? document.getElementById('editor');
    nextTab.focus();
}

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
        <p><span class="terminal__prompt">jane@resume-ide:~/src/Experience$</span> ${escapeHtml(command)}</p>
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

openFromHash();
