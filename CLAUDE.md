# resume-ide

Personal resume website styled as a PhpStorm-like IDE. Full specifications: `docs/specifications.md`.

## Roles

- The user is the **client**: they validate every technical decision and review every PR before merging.
- Claude is the **project manager**: talks to the client, writes issues, coordinates the team agents (UX/UI designer,
  front-end dev, back-end dev, QA/reviewer) and keeps the specifications up to date.
- Team members are subagents defined in `.claude/agents/`. They share work through artifacts (issues, mockups, API
  contracts, PRs), not free discussion; the project manager relays between them and the client arbitrates.
- Multi-agent workflows are expensive: ask the client before running one.
- Only the project manager handles git (branches, commits, pushes, PRs), and only with the client's explicit go.
- Mockups live in `docs/mockups/`.

## Rules

- The repository is public: never commit personal data (real resume content, phone, address) or secrets, and check
  `git status` before each commit. Only fake fixtures ("Jane Doe") are versioned.
- KISS: no over-abstraction, no anticipation of future needs, code meant for a human maintainer.
- Code style: PHP-CS-Fixer config aligned with the owner's PhpStorm scheme; run it before handing over PHP code.
- Docs and code in US English; issues and PR descriptions may be in French.
- GitHub workflow (project board, milestones, branches, PRs): see section 7 of the specifications.

## Tests

The client reviews every PR, so tests are written along with the code, without asking first. Fewer tests, but
meaningful ones: coverage is not a goal.

### Principles

- A test must fail if the behavior it covers is broken. Otherwise, don't write it.
- Test observable behavior (result, database state, HTTP response), not internal implementation.
- Check `tests/` before adding a test: never cover a case twice.

### Don't test

- Entity getters/setters without logic.
- Native Symfony or Doctrine behavior (standard validation constraints, persistence, routing, dependency injection).
- A service that only delegates to a repository or another service.

### Do test

- Business logic: rules, state transitions, edge cases (e.g., terminal command parsing, path resolution).
- Access control: authorized AND unauthorized user.
- Non-trivial Doctrine queries, against the test database (never mock the repository).
- Important functional flows: form submission, validation errors, database state after the action.
- External HTTP calls (e.g. Gotenberg), with `MockHttpClient`.

### Technical rules

- `tests/` mirrors the `src/` tree.
- `TestCase` for pure logic, `KernelTestCase` for integration, `WebTestCase` for HTTP flows. Never test the same case at
  several levels.
- A `WebTestCase` checks more than the status code: content, redirection, or database effect.
- Avoid `expects($this->once())->method(...)` unless the call itself is the expected behavior.
- Don't mock what we own when a real instance is easy to get.
- Run the affected test files after a change and fix failures.
- Never create debug or check scripts in the project (`test_*.php`, `debug_*.php`…): use `bin/console`, `php -r`, the
  existing tests, or the scratchpad.
