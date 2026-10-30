# resume-ide — Specifications

Status: **approved by the client**, updated along the way with their feedback. Progress is tracked on the
[GitHub Project](https://github.com/users/lbonnet-gda/projects/1).

## 1. Context and goals

A personal resume website whose interface is inspired by a JetBrains IDE (PhpStorm): the résumé is browsed as a project
opened in the IDE.

Goals:

- Present the résumé memorably to technical recruiters.
- Stay readable for non-technical recruiters (HR) and on mobile.
- Showcase a modern, clean Symfony codebase: the public repository is a deliverable as much as the website.

The interface is *inspired by* PhpStorm: no JetBrains logo or product name in the UI.

## 2. Audiences

| Audience         | Expectation                               | Answer                                        |
|------------------|-------------------------------------------|-----------------------------------------------|
| Developer / CTO  | Fun, credible IDE, quality code on GitHub | Code view, terminal, easter eggs, public repo |
| HR recruiter     | Find experience, skills, contact fast     | Preview mode, `README.md` landing, PDF button |
| Anyone on mobile | Usable on a 375px screen                  | Degraded mobile layout (see 3.7)              |

## 3. V1 scope

### 3.1 Layout

- **Project tool window** (left): file tree, folders can be expanded/collapsed.
- **Editor** (center): tabs of opened files, breadcrumb, line numbers, syntax-highlighted content.
- **Terminal** (bottom): resizable or collapsible panel.
- **Status bar** (bottom): breadcrumb, fake metadata (`PHP: 8.4`, `UTF-8`, `LF`, `4 spaces`…).
- **Top bar**: project name, fake branch selector, PDF download button.
- Dark theme only in V1.
- Resume content in French in V1. The IDE interface (menus, labels, terminal commands and messages) stays in English,
  like the real IDE.

### 3.2 Navigation

- Clicking a file opens it in a tab and makes it active. Closing tabs is supported.
- Each file has its own URL (e.g. `/src/Experience/AcmeCorp.php`): links can be shared, the page is server-rendered and
  works without JavaScript, browser back/forward behaves as expected.
- `README.md` is opened on landing.

### 3.3 View modes

Same switch as PhpStorm's Markdown editor, top-right of the editor: **Code**, **Split**, **Preview**.

- Code: the file as a source, syntax-highlighted.
- Preview: a clean rendering of the same data, readable by anyone.
- Default: Preview for `README.md` and on mobile, Code elsewhere. The last chosen mode is remembered for the session.

### 3.4 Files

Each file is generated from the database; the content is never stored as raw text. Proposed tree (final version decided
with the mockups):

```
resume-ide/
├── config/
│   ├── interests.yaml
│   └── languages.yaml         # spoken languages
├── projects/
│   └── <project>.md           # side projects, with links
├── src/
│   ├── Education/
│   │   └── <School>.php
│   └── Experience/
│       └── <Company>.php      # one class per job: dates, role, missions, stack
├── .env                       # contact: email, LinkedIn, GitHub (no phone)
├── composer.json              # skills, as "require" dependencies with versions as levels
└── README.md                  # summary: name, title, short pitch
```

- The tree is sorted like PhpStorm: folders first, then files, each group alphabetically (case-insensitive).
- Side effect: jobs are listed by company name, not by date. The chronological order is given by the `README.md`
  preview and the PDF. To be confirmed with the mockups.

### 3.5 Terminal

Fake shell with a fixed set of commands. Unknown commands print `command not found` and suggest `help`.

| Command       | Effect                                       |
|---------------|----------------------------------------------|
| `help`        | Lists available commands                     |
| `ls [path]`   | Lists a folder                               |
| `cd <path>`   | Changes the current folder (shown in prompt) |
| `cat <file>`  | Prints a file in the terminal                |
| `open <file>` | Opens a file in the editor                   |
| `whoami`      | Short presentation                           |
| `download cv` | Downloads the PDF resume                     |
| `clear`       | Clears the terminal                          |

- Command history with up/down arrows, tab completion on paths.
- Commands are executed server-side (Live Component), so the terminal and the tree share the same data.

### 3.6 PDF resume

- Downloadable from the top bar button and the `download cv` command.
- Classic, sober layout (not the IDE look): it must work for HR and ATS software.
- Contains the phone number, which is not displayed on the website.
- Generated from Twig templates, cached, regenerated when data changes in the admin.

### 3.7 Mobile (< 768px)

- File tree in a drawer opened from a button.
- Terminal collapsed by default.
- Preview mode by default.

### 3.8 Admin

- `/admin`, EasyAdmin, one single account (the owner).
- CRUD on all resume data, with sortable lists (experience order, skills order).
- Login form, rate-limited; credentials from environment variables, password hashed.

### 3.9 Non-functional

- **Privacy**: no personal data and no secret in the repository, git history included. The SQLite database, `.env.local`
  and generated PDFs are git-ignored from the first commit. The repository ships fixtures with fake data ("Jane Doe")
  so anyone can clone and run it.
- **No tracking**, no third-party cookies, so no consent banner. The only cookie is the admin session.
- **Accessibility**: semantic HTML, full keyboard navigation (tree, tabs, terminal), visible focus,
  `prefers-reduced-motion` respected.
- **SEO**: server-rendered pages, one title/description per file, Open Graph tags for link previews (LinkedIn).
- **Performance**: no JavaScript build step, pages usable before JavaScript loads.

## 4. Out of V1 scope

### 4.1 V2 — Achievements

Steam-like achievements, stored in the visitor's browser (localStorage: no server state, no cookie size limit).

- Unlock notification: toast bottom-right, with icon, title, short description.
- Progress in the top bar: unlocked/total counter.
- Details page (or a dedicated "file") listing all achievements; locked hidden ones show as `???`.
- Examples:
    - easy: open the README, open every experience file, use the terminal for the first time, download the PDF;
    - medium: use `Split` mode, close every tab, navigate only with the keyboard;
    - hidden: `sudo` in the terminal, Konami code, `rm -rf /`, `git blame`, opening the site at 3 AM.

Final list to be defined in its own issue.

### 4.2 Ideas, not planned

- Light theme (could be an achievement reward).
- English version of the résumé, with a language switch in the UI and a terminal command. Planned after the release,
  not designed for in V1.
- `bin/console resume:show`: the résumé rendered in a real terminal.
- Visit analytics (privacy-friendly, self-hosted).

## 5. Technical choices

| Need                 | Choice                                                  | Why                                              |
|----------------------|---------------------------------------------------------|--------------------------------------------------|
| Language / framework | PHP 8.4, Symfony 8.1 (8.2 upgrade planned)              | Current Symfony version, shows up-to-date skills |
| Templates            | Twig, Twig Components                                   | Tree, tabs, status bar are reusable UI pieces    |
| Navigation           | Symfony UX Turbo                                        | No full reload, real URLs, works without JS      |
| Terminal             | Live Component                                          | Commands handled in PHP, same data as the pages  |
| Client-side behavior | Stimulus                                                | Keyboard shortcuts, history, resizing            |
| Assets               | AssetMapper                                             | No Node.js, no build step                        |
| Icons                | Symfony UX Icons                                        | File-type icons as inline SVG                    |
| Syntax highlighting  | Server-side PHP library (tempest/highlight, to confirm) | No JS highlighter, works without JS              |
| Data                 | Doctrine ORM + SQLite                                   | Single file, git-ignored, no database server     |
| Admin                | EasyAdmin + Security                                    | Standard, little code                            |
| PDF                  | Gotenberg via sensiolabs/gotenberg-bundle               | Chrome rendering from Twig templates             |
| Local environment    | Docker Compose, FrankenPHP                              | Same stack in dev and production                 |
| Hosting              | Small VPS with Docker (to decide)                       | Gotenberg needs a container                      |

Versions are checked when the project is initialized.

## 6. Code quality

- KISS: no over-abstraction, no anticipation of future needs, code written for a human maintainer.
- Comments only for traps or external constraints that the code cannot express.
- Code style: PHP-CS-Fixer based on `@Symfony`, aligned with the owner's PhpStorm scheme (Ctrl+Alt+L and the CI must
  agree). Disable any `@Symfony` rule PhpStorm does not apply (e.g., Yoda style). The scheme's non-default settings:
    - no spaces around the concatenation operator (`'a'.$b`);
    - trailing comma in multiline arrays;
    - `elseif` as one word;
    - one blank line before `return`;
    - braces always required (`if`, `for`, `while`, `do`);
    - long parameter lists, argument lists and arrays: chopped down one item per line, with the opening and closing
      parentheses/brackets on their own line;
    - methods call chains wrapped only when too long;
    - `true`, `false`, `null` in lowercase, camelCase variables.
- `.editorconfig` at the root: LF, UTF-8, trim trailing whitespace, final newline, four spaces (2 for Twig/HTML).
- Static analysis: PHPStan, level to define at setup (as high as the code allows without noise).
- Tests: PHPUnit, separate test database. Few but meaningful tests: see the testing rules in `CLAUDE.md`.
- CI (GitHub Actions) on every PR: code style, PHPStan, PHPUnit, Twig/YAML/container lint.

## 7. Project management

### 7.1 GitHub

- Public repository on the owner's personal account, default branch `main`.
- **Project** (board): Backlog, Ready, In progress, In review, Done.
- **Milestones**: one per version (see 7.3).
- **Labels**: `feature`, `bug`, `design`, `tech`, `docs`, `accessibility`.
- One issue per feature, written by the project manager with acceptance criteria.
- Branches `feature/<issue>-<slug>` or `bugfix/<issue>-<slug>`, one PR per issue, `Closes #N` in the PR.
- Conventional Commits, atomic commits, merged with a merge commit (atomic history preserved).
- Every PR is reviewed and merged by the owner. CI must pass.

### 7.2 Team and workflow

| Role            | Responsibility                                                      |
|-----------------|---------------------------------------------------------------------|
| Project manager | Talks to the client, writes issues, plans, coordinates, plans tests |
| UX/UI designer  | HTML mockups (clickable prototypes) validated by the client         |
| Front-end dev   | Twig, components, Stimulus, CSS                                     |
| Back-end dev    | Entities, admin, security, terminal logic, PDF                      |
| QA / reviewer   | Writes tests, reviews other roles' code before the client review    |

Per feature: issue → mockup if UI → client validation → implementation → QA review → PR → client review → merge.

### 7.3 Milestones

**M0 — Setup**

- Symfony skeleton, Docker Compose, `.gitignore` for private data.
- Code style, PHPStan, PHPUnit, CI.
- README of the repository (what it is, how to run it).

**M1 — MVP**

- Mockups of the main layout (desktop and mobile).
- Data model, fixtures, admin with login.
- Layout: tree, tabs, editor, status bar, navigation with URLs.
- File rendering in Code/Split/Preview modes, syntax highlighting.
- Terminal with the V1 commands.
- PDF generation and download.
- Mobile layout, accessibility, and SEO pass.

**M2 — Release**

- Hosting choice, deployment, domain, HTTPS.
- Real data entered through the admin.
- Symfony 8.2 upgrade (if released).

**M3 — Achievements**

- See 4.1.

## 8. Decisions and open questions

Decided:

- Content in French first, English later (see 4.2).
- No photo, neither on the website nor in the PDF.

Open:

1. Hosting provider and domain name (needed for M2 only).
2. Additional sections beyond experience, education, skills, projects, languages, interests: proposals welcome along
   the way.
