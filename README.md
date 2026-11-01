# resume-ide

[![CI](https://github.com/lbonnet-gda/resume-ide/actions/workflows/ci.yaml/badge.svg?branch=main)](https://github.com/lbonnet-gda/resume-ide/actions/workflows/ci.yaml?query=branch%3Amain)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)](https://phpstan.org)
[![License](https://img.shields.io/github/license/lbonnet-gda/resume-ide)](LICENSE)

A résumé website styled as an IDE inspired by PhpStorm: the résumé is browsed as a project, with a file tree, an editor
with tabs and syntax highlighting, a terminal that runs a few shell-like commands, and a button to download a classic
PDF version. The public repository is part of the deliverable: it is meant to show a clean, modern Symfony codebase.

## Status

Work in progress. The setup milestone (M0: Symfony skeleton, Docker, code style, static analysis, tests, CI) is done;
the features come with the MVP milestone (M1). For now the application only serves the Symfony welcome page.

Progress is tracked on the public [roadmap](https://github.com/users/lbonnet-gda/projects/1).

## Stack

- PHP 8.4, Symfony 8.1, served by FrankenPHP in Docker Compose.
- Doctrine ORM with SQLite: the database is a single git-ignored file, no database server.
- PHP-CS-Fixer, PHPStan (level `max`) and PHPUnit, run by GitHub Actions.
- Planned for M1: Twig Components, Symfony UX Turbo, Live Component and Stimulus, AssetMapper (no Node.js, no build
  step), EasyAdmin, PDF generation with Gotenberg.

Pages are meant to be server-rendered and usable without JavaScript. Each file of the fake project is generated from
the database. Details and reasons for each choice: [specifications](docs/specifications.md).

## Run it locally

Prerequisites: Docker with Compose v2.

```bash
git clone https://github.com/lbonnet-gda/resume-ide.git
cd resume-ide
docker compose up --build
```

The first start installs the Composer dependencies, which takes a moment. Every start syncs them with `composer.lock`,
so a restart is enough after a `git pull`. Each start also runs the database migrations, which create the SQLite
database in `var/` on the first run. Then open https://localhost:8443 (plain HTTP on
http://localhost:8000 redirects to it).

To fill the database with the fake "Jane Doe" résumé, in a second terminal (this empties the database first):

```bash
docker compose exec php bin/console doctrine:fixtures:load
```

The stack runs in the foreground, so use a second terminal for other commands, or add `-d` to start it in the
background. Stop it with `Ctrl+C` or `docker compose down`.

- **Ports**: override them with `HTTP_PORT` and `HTTPS_PORT`, e.g.
  `HTTP_PORT=8001 HTTPS_PORT=8444 docker compose up --build`.
- **Host user**: the container runs as UID/GID 1000 so that the files it writes in the project belong to you. If your
  user has other IDs (`id -u`, `id -g`), run `env UID=$(id -u) GID=$(id -g) docker compose up --build` instead, each
  time you build. The `env` prefix matters: in Bash, `UID` is read-only, so `UID=$(id -u) docker compose …` would
  keep 1000 (Bash only prints a warning).
- **HTTPS certificate**: Caddy signs it with its own local certificate authority, so browsers warn about it. To trust
  it, copy the authority's root certificate out of the container, then import it in your OS or browser trust store (as a
  trusted root certificate authority):

  ```bash
  docker compose cp php:/data/caddy/pki/authorities/local/root.crt ~/resume-ide-root.crt
  ```

## Quality checks

With the stack running:

```bash
docker compose exec php composer cs-check   # code style (dry run, shows the diff)
docker compose exec php composer cs-fix     # fix the code style
docker compose exec php composer phpstan    # static analysis
docker compose exec php composer test       # PHPUnit
```

The CI runs these checks, plus YAML and service container lints and a check of the database schema and fixtures, on
every pull request and on `main`. The `main` branch is protected: changes go through a pull request with a passing CI.

## How it is built

The project is built with a team of [Claude Code](https://claude.com/product/claude-code) agents: a project manager,
a UX/UI designer, a front-end developer, a back-end developer, and a QA/reviewer. Their rules are in
[CLAUDE.md](CLAUDE.md) and [.claude/agents/](.claude/agents). The owner acts as the client: they make the decisions and
review and merge every pull request.

## License

[MIT](LICENSE)
