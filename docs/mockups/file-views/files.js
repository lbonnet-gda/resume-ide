// The virtual project: every file is built from the fake data below, as the real site builds it from the database.
// Same data as src/DataFixtures/AppFixtures.php; the phone number is left out, it never appears on the website.

const profile = {
    name: 'Jane Doe',
    title: 'Développeuse PHP / Symfony senior',
    summary: [
        'Développeuse back-end depuis dix ans, je conçois des applications Symfony robustes et lisibles.',
        "J'aime les bases de code propres, les tests qui ont du sens et les équipes qui partagent leurs connaissances.",
    ],
    email: 'jane.doe@example.com',
    location: 'Lyon, France',
    linkedinUrl: 'https://linkedin.example.com/in/jane-doe',
    githubUrl: 'https://github.example.com/jane-doe',
};

const experiences = [
    {
        className: 'AcmeCorp',
        company: 'Acme Corp',
        role: 'Lead développeuse Symfony',
        location: 'Lyon (hybride)',
        startDate: '2021-09-01',
        endDate: null,
        missions: [
            "Migration progressive d'un monolithe Symfony 4 vers Symfony 7",
            "Mise en place de l'intégration continue et de l'analyse statique",
            "Encadrement d'une équipe de quatre développeurs",
        ],
        stack: ['PHP', 'Symfony', 'PostgreSQL', 'Docker', 'GitHub Actions'],
    },
    {
        className: 'Globex',
        company: 'Globex',
        role: 'Développeuse back-end',
        location: 'Paris',
        startDate: '2018-03-01',
        endDate: '2021-08-31',
        missions: [
            "Conception d'une API REST consommée par les applications mobiles",
            "Optimisation des requêtes Doctrine sur un catalogue d'un million de produits",
            'Écriture des tests fonctionnels des parcours de paiement',
        ],
        stack: ['PHP', 'Symfony', 'API Platform', 'MySQL', 'Redis'],
    },
    {
        className: 'Initech',
        company: 'Initech',
        role: 'Développeuse web',
        location: 'Grenoble',
        startDate: '2016-01-01',
        endDate: '2018-02-28',
        missions: [
            "Développement de modules métier pour l'intranet de l'entreprise",
            "Maintenance d'applications PHP historiques",
        ],
        stack: ['PHP', 'Symfony', 'jQuery', 'MySQL'],
    },
    {
        className: 'UmbrellaStudio',
        company: 'Umbrella Studio',
        role: 'Développeuse stagiaire',
        location: 'Grenoble',
        startDate: '2015-03-01',
        endDate: '2015-08-31',
        missions: ["Réalisation d'un outil interne de suivi des temps"],
        stack: ['PHP', 'JavaScript'],
    },
];

const educations = [
    {
        className: 'UniversiteGrenobleAlpes',
        school: 'Université Grenoble Alpes',
        degree: 'Master informatique',
        location: 'Grenoble',
        startDate: '2013-09-01',
        endDate: '2015-08-31',
        description: 'Spécialité génie logiciel.',
    },
    {
        className: 'IutDeLyon',
        school: 'IUT de Lyon',
        degree: 'DUT informatique',
        location: 'Lyon',
        startDate: '2011-09-01',
        endDate: '2013-06-30',
        description: null,
    },
];

const skills = [
    {name: 'PHP', category: 'Langages', level: 5},
    {name: 'SQL', category: 'Langages', level: 4},
    {name: 'JavaScript', category: 'Langages', level: 3},
    {name: 'Symfony', category: 'Frameworks', level: 5},
    {name: 'Doctrine', category: 'Frameworks', level: 5},
    {name: 'API Platform', category: 'Frameworks', level: 4},
    {name: 'Stimulus', category: 'Frameworks', level: 2},
    {name: 'PHPUnit', category: 'Qualité', level: 4},
    {name: 'PHPStan', category: 'Qualité', level: 4},
    {name: 'Docker', category: 'Outils', level: 4},
    {name: 'Git', category: 'Outils', level: 5},
    {name: 'GitHub Actions', category: 'Outils', level: 3},
];

const projects = [
    {
        name: 'resume-ide',
        description: [
            'Un CV présenté comme un projet ouvert dans un IDE.',
            'Symfony, Live Components et aucune étape de build.',
        ],
        url: 'https://resume.example.com',
        repositoryUrl: 'https://git.example.com/jane-doe/resume-ide',
    },
    {
        name: 'recipe-box',
        description: ['Gestion de recettes de cuisine, avec une liste de courses générée automatiquement.'],
        url: null,
        repositoryUrl: 'https://git.example.com/jane-doe/recipe-box',
    },
    {
        name: 'trail-stats',
        description: ['Tableau de bord des sorties de course à pied, alimenté par des fichiers GPX.'],
        url: 'https://trail-stats.example.com',
        repositoryUrl: null,
    },
];

const languages = [
    {name: 'Français', level: 'Langue maternelle'},
    {name: 'Anglais', level: 'Courant (C1)'},
    {name: 'Espagnol', level: 'Intermédiaire (B1)'},
];

const interests = [
    {name: 'Trail', description: 'Courses en montagne, du 20 au 50 km'},
    {name: 'Cuisine', description: null},
    {name: 'Open source', description: 'Contributions ponctuelles à des projets PHP'},
];

const MONTHS = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

const html = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

const year = (date) => date.slice(0, 4);

const yearRange = ({startDate, endDate}) => {
    if (year(startDate) === endDate?.slice(0, 4)) {
        return year(startDate);
    }

    return `${year(startDate)} – ${endDate ? year(endDate) : "aujourd'hui"}`;
};

const monthYear = (date) => `${MONTHS[Number(date.slice(5, 7)) - 1]} ${year(date)}`;

const monthRange = ({startDate, endDate}) => `${monthYear(startDate)} – ${endDate ? monthYear(endDate) : "aujourd'hui"}`;

const slug = (text) => text
    .normalize('NFD')
    .replace(/\p{M}/gu, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-');

// Single quotes, except when the string contains one: same rule as PHP-CS-Fixer's single_quote.
const phpString = (text) => (text.includes("'") ? `"${text}"` : `'${text}'`);

const phpDate = (date) => (date ? `new DateTimeImmutable('${date}')` : 'null');

const phpClass = (namespace, className, scalars, dates, lists = {}, after = {}) => {
    const scalarArgument = ([key, value]) => `            ${key}: ${value === null ? 'null' : phpString(value)},`;

    return [
        '<?php',
        '',
        `namespace App\\${namespace};`,
        '',
        `use App\\Resume\\${namespace};`,
        'use DateTimeImmutable;',
        '',
        `final class ${className} extends ${namespace}`,
        '{',
        '    public function __construct()',
        '    {',
        '        parent::__construct(',
        ...Object.entries(scalars).map(scalarArgument),
        `            startDate: ${phpDate(dates.startDate)},`,
        `            endDate: ${phpDate(dates.endDate)},`,
        ...Object.entries(lists).flatMap(([key, items]) => [
            `            ${key}: [`,
            ...items.map((item) => `                ${phpString(item)},`),
            '            ],',
        ]),
        ...Object.entries(after).map(scalarArgument),
        '        );',
        '    }',
        '}',
    ];
};

const icon = (symbol, classes = '') => `<svg class="icon ${classes}" aria-hidden="true"><use href="#${symbol}"/></svg>`;

const externalMark = `${icon('i-external', 'icon--small preview__external')}<span class="visually-hidden"> (nouvel onglet)</span>`;

const externalLink = (url, label) => `<a href="${html(url)}" target="_blank" rel="noopener noreferrer">${html(label)}${externalMark}</a>`;

const fileLink = (path, label) => `<a href="#/${path}">${html(label)}</a>`;

const experiencePath = (experience) => `src/Experience/${experience.className}.php`;
const educationPath = (education) => `src/Education/${education.className}.php`;
const projectPath = (project) => `projects/${project.name}.md`;

const previewMeta = (items) => `
    <ul class="preview__meta">
        ${items.map(([symbol, content]) => `<li>${icon(symbol)}<span>${content}</span></li>`).join('')}
    </ul>`;

const previewHeader = ({path, tile, title, titleClass = '', subtitle = '', meta = []}) => `
    <header class="preview__header">
        <div class="preview__tile" aria-hidden="true">${tile}</div>
        <div class="preview__identity">
            <p class="preview__path">${html(path)}</p>
            <h1 class="${titleClass}">${html(title)}</h1>
            ${subtitle ? `<p class="preview__subtitle">${html(subtitle)}</p>` : ''}
            ${meta.length ? previewMeta(meta) : ''}
        </div>
    </header>`;

const previewSection = (title, body) => `
    <section class="preview__section">
        <h2>${html(title)}</h2>
        ${body}
    </section>`;

const previewTimeline = (items) => `
    <ol class="preview__timeline">
        ${items.map(({path, title, subtitle, dates, current}) => `
            <li${current ? ' aria-current="true"' : ''}>
                <span class="preview__timeline-dates">${html(dates)}</span>
                <span class="preview__timeline-title">${current ? html(title) : fileLink(path, title)}</span>
                <span class="preview__timeline-subtitle">${html(subtitle)}</span>
            </li>`).join('')}
    </ol>`;

const previewLinkCard = (path, symbol, label) => `
    <a class="preview__link-card" href="#/${path}">
        ${icon(symbol)}
        <span>${html(label)}</span>
    </a>`;

const previewDots = (filled, total) => `
    <span class="preview__dots" aria-hidden="true">
        ${Array.from({length: total}, (_, i) => `<span class="preview__dot${i < filled ? ' is-filled' : ''}"></span>`).join('')}
    </span>`;

const initials = (name) => name.split(' ').map((word) => word[0]).join('');

const experienceTimeline = (current) => previewTimeline(experiences.map((experience) => ({
    path: experiencePath(experience),
    title: experience.company,
    subtitle: experience.role,
    dates: yearRange(experience),
    current: experience === current,
})));

const educationTimeline = (current) => previewTimeline(educations.map((education) => ({
    path: educationPath(education),
    title: education.school,
    subtitle: education.degree,
    dates: yearRange(education),
    current: education === current,
})));

const readmeFile = () => ({
    syntax: 'markdown',
    language: 'Markdown',
    kind: 'markdown',
    breadcrumb: [profile.name],
    lines: [
        `# ${profile.name}`,
        '',
        `**${profile.title}**`,
        '',
        `${profile.location} ·`,
        `[${profile.email}](mailto:${profile.email}) ·`,
        `[LinkedIn](${profile.linkedinUrl}) ·`,
        `[GitHub](${profile.githubUrl})`,
        '',
        ...profile.summary.flatMap((paragraph) => [paragraph, '']),
        '## Expériences',
        '',
        ...experiences.map((experience) => `- [${experience.company}](${experiencePath(experience)}), ${experience.role} (${yearRange(experience)})`),
        '',
        '## Formation',
        '',
        ...educations.map((education) => `- [${education.school}](${educationPath(education)}), ${education.degree} (${yearRange(education)})`),
        '',
        '## Projets',
        '',
        ...projects.map((project) => `- [${project.name}](${projectPath(project)})`),
        '',
        '## En savoir plus',
        '',
        '- [Compétences](composer.json)',
        '- [Langues](config/languages.yaml)',
        "- [Centres d'intérêt](config/interests.yaml)",
    ],
    preview: `
        ${previewHeader({
            path: 'README.md',
            tile: initials(profile.name),
            title: profile.name,
            subtitle: profile.title,
            meta: [
                ['i-pin', html(profile.location)],
                ['i-mail', `<a href="mailto:${html(profile.email)}">${html(profile.email)}</a>`],
                ['i-user', externalLink(profile.linkedinUrl, 'LinkedIn')],
                ['i-branch', externalLink(profile.githubUrl, 'GitHub')],
            ],
        })}
        <div class="preview__lead">
            ${profile.summary.map((paragraph) => `<p>${html(paragraph)}</p>`).join('')}
        </div>
        <div class="preview__columns">
            ${previewSection('Expériences', experienceTimeline())}
            <div>
                ${previewSection('Formation', educationTimeline())}
                ${previewSection('Projets', `
                    <div class="preview__grid">
                        ${projects.map((project) => previewLinkCard(projectPath(project), 'i-box', project.name)).join('')}
                    </div>`)}
            </div>
        </div>
        ${previewSection('En savoir plus', `
            <div class="preview__grid">
                ${previewLinkCard('composer.json', 'i-sliders', 'Compétences')}
                ${previewLinkCard('config/languages.yaml', 'i-speech', 'Langues')}
                ${previewLinkCard('config/interests.yaml', 'i-heart', "Centres d'intérêt")}
            </div>`)}`,
});

const envFile = () => {
    const contactCard = (symbol, label, value) => `
        <li class="preview__card">
            <span class="preview__card-icon">${icon(symbol)}</span>
            <span class="preview__label">${html(label)}</span>
            ${value}
        </li>`;

    return {
        syntax: 'env',
        language: '.env',
        kind: 'contact',
        breadcrumb: [],
        lines: [
            '# Contact',
            `EMAIL=${profile.email}`,
            `LINKEDIN=${profile.linkedinUrl}`,
            `GITHUB=${profile.githubUrl}`,
        ],
        preview: `
            ${previewHeader({path: '.env', tile: icon('i-mail'), title: 'Contact', subtitle: profile.name})}
            <ul class="preview__grid">
                ${contactCard('i-mail', 'E-mail', `<a href="mailto:${html(profile.email)}">${html(profile.email)}</a>`)}
                ${contactCard('i-user', 'LinkedIn', externalLink(profile.linkedinUrl, profile.linkedinUrl.replace('https://', '')))}
                ${contactCard('i-branch', 'GitHub', externalLink(profile.githubUrl, profile.githubUrl.replace('https://', '')))}
            </ul>`,
    };
};

const packageName = (skill) => `${slug(skill.category)}/${slug(skill.name)}`;

// A skill of level N (1 to 5) requires version ^N.0.
const composerFile = () => {
    const categories = [...new Set(skills.map((skill) => skill.category))];
    const composer = {
        name: `${slug(profile.name)}/resume`,
        description: 'Compétences : la version indique le niveau (1 à 5)',
        require: Object.fromEntries(skills.map((skill) => [packageName(skill), `^${skill.level}.0`])),
    };

    return {
        syntax: 'json',
        language: 'JSON',
        kind: 'skills',
        breadcrumb: ['require'],
        lines: JSON.stringify(composer, null, 4).split('\n'),
        preview: `
            ${previewHeader({path: 'composer.json', tile: icon('i-sliders'), title: 'Compétences', subtitle: 'Niveau de 1 à 5'})}
            <div class="preview__grid preview__grid--wide">
                ${categories.map((category) => `
                    <section class="preview__card">
                        <h2>${html(category)}</h2>
                        <ul class="preview__skills">
                            ${skills.filter((skill) => skill.category === category).map((skill) => `
                                <li>
                                    <span>${html(skill.name)}</span>
                                    ${previewDots(skill.level, 5)}
                                    <span class="preview__level"><span class="visually-hidden">Niveau </span>${skill.level}/5</span>
                                </li>`).join('')}
                        </ul>
                    </section>`).join('')}
            </div>`,
    };
};

const experienceFile = (experience) => {
    const {company, role, location, missions, stack} = experience;
    const meta = [['i-calendar', html(monthRange(experience))], ['i-pin', html(location)]];
    if (experience.endDate === null) {
        meta.push(['i-check', 'En poste']);
    }

    return {
        syntax: 'php',
        language: 'PHP: 8.4',
        kind: 'experience',
        breadcrumb: ['App\\Experience', experience.className],
        lines: phpClass('Experience', experience.className, {company, role, location}, experience, {missions, stack}),
        preview: `
            ${previewHeader({
                path: experiencePath(experience),
                tile: icon('i-briefcase'),
                title: role,
                subtitle: company,
                meta,
            })}
            <div class="preview__columns">
                <div>
                    ${previewSection('Missions', `
                        <ul class="preview__card preview__list">
                            ${missions.map((mission) => `<li>${html(mission)}</li>`).join('')}
                        </ul>`)}
                    ${previewSection('Technologies', `
                        <ul class="preview__tags">
                            ${stack.map((item) => `<li>${html(item)}</li>`).join('')}
                        </ul>`)}
                </div>
                ${previewSection('Parcours', experienceTimeline(experience))}
            </div>`,
    };
};

const educationFile = (education) => {
    const {school, degree, location, description} = education;
    const fact = (symbol, label, value) => `
        <li class="preview__card">
            <span class="preview__card-icon">${icon(symbol)}</span>
            <span class="preview__label">${html(label)}</span>
            <span>${html(value)}</span>
        </li>`;

    return {
        syntax: 'php',
        language: 'PHP: 8.4',
        kind: 'education',
        breadcrumb: ['App\\Education', education.className],
        lines: phpClass('Education', education.className, {school, degree, location}, education, {}, {description}),
        preview: `
            ${previewHeader({
                path: educationPath(education),
                tile: icon('i-education'),
                title: degree,
                subtitle: school,
            })}
            <ul class="preview__grid">
                ${fact('i-calendar', 'Période', monthRange(education))}
                ${fact('i-pin', 'Lieu', location)}
                ${description ? fact('i-check', 'Détails', description) : ''}
            </ul>
            ${previewSection('Cursus', educationTimeline(education))}`,
    };
};

const projectFile = (project) => {
    const links = [
        project.url ? [project.url, 'Site', 'i-globe'] : null,
        project.repositoryUrl ? [project.repositoryUrl, 'Code source', 'i-code'] : null,
    ].filter(Boolean);
    const others = projects.filter((other) => other !== project);

    return {
        syntax: 'markdown',
        language: 'Markdown',
        kind: 'markdown',
        breadcrumb: [project.name],
        lines: [
            `# ${project.name}`,
            '',
            ...project.description.flatMap((paragraph) => [paragraph, '']),
            ...links.map(([url, label]) => `- [${label}](${url})`),
        ],
        preview: `
            ${previewHeader({
                path: projectPath(project),
                tile: icon('i-box'),
                title: project.name,
                titleClass: 'preview__mono',
            })}
            <div class="preview__lead">
                ${project.description.map((paragraph) => `<p>${html(paragraph)}</p>`).join('')}
            </div>
            <ul class="preview__actions">
                ${links.map(([url, label, symbol]) => `<li>${icon(symbol)}${externalLink(url, label)}</li>`).join('')}
            </ul>
            ${previewSection('Autres projets', `
                <ul class="preview__grid">
                    ${others.map((other) => `
                        <li class="preview__card">
                            <span class="preview__mono">${fileLink(projectPath(other), other.name)}</span>
                            <span class="preview__card-text">${html(other.description[0])}</span>
                        </li>`).join('')}
                </ul>`)}`,
    };
};

const yamlList = (rootKey, items, keys) => [
    `${rootKey}:`,
    ...items.flatMap((item) => keys.map((key, index) => {
        const prefix = index === 0 ? '    -   ' : '        ';

        return `${prefix}${key}: ${item[key] ?? 'null'}`;
    })),
];

// The level is free text: dots follow the CEFR scale when it can be read from the text, otherwise there are none.
const CEFR_LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

const languageDots = (level) => {
    const position = /maternelle/i.test(level)
        ? CEFR_LEVELS.length
        : CEFR_LEVELS.indexOf(level.match(/\b[ABC][12]\b/)?.[0]) + 1;

    return position > 0 ? previewDots(position, CEFR_LEVELS.length) : '';
};

const languagesFile = () => ({
    syntax: 'yaml',
    language: 'YAML',
    kind: 'languages',
    breadcrumb: ['languages'],
    lines: yamlList('languages', languages, ['name', 'level']),
    preview: `
        ${previewHeader({path: 'config/languages.yaml', tile: icon('i-speech'), title: 'Langues'})}
        <ul class="preview__grid">
            ${languages.map(({name, level}) => `
                <li class="preview__card preview__language">
                    <strong>${html(name)}</strong>
                    <span class="preview__card-text">${html(level)}</span>
                    ${languageDots(level)}
                </li>`).join('')}
        </ul>`,
});

const interestsFile = () => ({
    syntax: 'yaml',
    language: 'YAML',
    kind: 'interests',
    breadcrumb: ['interests'],
    lines: yamlList('interests', interests, ['name', 'description']),
    preview: `
        ${previewHeader({path: 'config/interests.yaml', tile: icon('i-heart'), title: "Centres d'intérêt"})}
        <ul class="preview__grid">
            ${interests.map(({name, description}) => `
                <li class="preview__card preview__interest">
                    <span class="preview__initial" aria-hidden="true">${html(name[0])}</span>
                    <div>
                        <strong>${html(name)}</strong>
                        ${description ? `<span class="preview__card-text">${html(description)}</span>` : ''}
                    </div>
                </li>`).join('')}
        </ul>`,
});

const files = {
    'README.md': readmeFile(),
    '.env': envFile(),
    'composer.json': composerFile(),
    'config/interests.yaml': interestsFile(),
    'config/languages.yaml': languagesFile(),
    ...Object.fromEntries(projects.map((project) => [projectPath(project), projectFile(project)])),
    ...Object.fromEntries(experiences.map((experience) => [experiencePath(experience), experienceFile(experience)])),
    ...Object.fromEntries(educations.map((education) => [educationPath(education), educationFile(education)])),
};

// Stand-in for the server-side highlighter: one regular expression per token type, first match wins.
const grammars = {
    php: {
        string: /'[^']*'|"[^"]*"/,
        keyword: /<\?php|\b(?:namespace|use|final|class|extends|public|function|parent|new|null)\b/,
        function: /\b__construct\b/,
        parameter: /\b[a-zA-Z]+(?=: )/,
    },
    markdown: {
        heading: /^#{1,6} .*$/,
        bullet: /^- /,
        bold: /\*\*[^*]+\*\*/,
        link: /\[[^\]]*\]\([^)]*\)/,
    },
    json: {
        property: /"[^"]*"(?=:)/,
        string: /"[^"]*"/,
    },
    yaml: {
        keyword: /(?<=: )null$/,
        string: /(?<=: ).+$/,
        yamlKey: /[a-z]+(?=:)/,
    },
    env: {
        comment: /^#.*$/,
        property: /^[A-Z_]+(?==)/,
        string: /(?<==).*$/,
    },
};

function sourceLink(target, text) {
    if (target.startsWith('http')) {
        return `<a class="is-external" href="${html(target)}" target="_blank" rel="noopener noreferrer"
            aria-label="${html(text)} (new tab)">${html(text)}</a>`;
    }
    if (target.startsWith('mailto:')) {
        return `<a class="is-external" href="${html(target)}">${html(text)}</a>`;
    }

    return `<a href="#/${html(target)}">${html(text)}</a>`;
}

const syntaxToken = (type, text) => `<span class="syntax-${type}">${html(text)}</span>`;

function renderToken(syntax, type, text) {
    if (type === 'link') {
        const [, label, target] = text.match(/^(\[.*\])\((.*)\)$/);
        const link = `<span class="syntax-target">${sourceLink(target, target)}</span>`;

        return `${syntaxToken('linkText', label)}<span class="syntax-string">(${link})</span>`;
    }
    if (syntax === 'env' && type === 'string') {
        const link = sourceLink(text.includes('@') ? `mailto:${text}` : text, text);

        return `<span class="syntax-string">${link}</span>`;
    }

    return syntaxToken(type, text);
}

function highlight(syntax, lines) {
    const tokens = Object.entries(grammars[syntax]);
    const pattern = new RegExp(tokens.map(([name, regex]) => `(?<${name}>${regex.source})`).join('|'), 'g');

    return lines.map((line) => {
        let result = '';
        let position = 0;
        for (const match of line.matchAll(pattern)) {
            const [type] = Object.entries(match.groups).find(([, value]) => value !== undefined);
            result += html(line.slice(position, match.index));
            result += renderToken(syntax, type, match[0]);
            position = match.index + match[0].length;
        }

        return result + html(line.slice(position));
    }).join('\n');
}
