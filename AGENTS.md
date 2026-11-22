# AGENTS.md

This is a Symfony application. `composer.json` gives the versions of Symfony and
PHP. `symfony.lock` lists the recipes that ran. Check both before you assume
that a package is installed.

## Installing packages

Before writing infrastructure code or adding a third-party library, check
whether a Symfony component does the job: Lock, Cache, HttpClient, Mailer,
Messenger, Scheduler, RateLimiter, Workflow... Usually there is one, and
`composer require` installs it.

## Configuration

- Since Symfony 8.2, each component is configured at its own root key:
  `messenger:`, `cache:`, `mailer:`, `validation:`... not under `framework:` as
  in older versions.
- `config/reference.php` describes every option of the installed bundles, and
  `config/schema.json` is their JSON schema, which editors use and `lint:yaml`
  checks when `opis/json-schema` is installed. Both are regenerated when the
  container is rebuilt in debug mode. Read them instead of guessing an option.
- Prefer attributes to configuration files: `#[Route]`, `#[IsGranted]`,
  `#[AsCommand]`, `#[AsEventListener]`, `#[AsMessageHandler]`, `#[Autowire]`,
  `#[Target]`... Services are autowired and autoconfigured, so a service
  definition in YAML is the last resort.
- Map request data with `#[MapRequestPayload]` and `#[MapQueryString]` on the
  arguments of an action. These attributes deserialize and validate the data,
  unlike `json_decode()`.
- `.env` is committed and contains default values only. Secrets go in
  `.env.local`, which is ignored by git, or in the vault
  (`bin/console secrets:set`).

## This project

MemorialAtlas turns gravestone photos into proposed records that a person reviews.
The photo is the evidence; every AI result is a proposal, stored with its model and
input hash. README.md describes the pipeline and the setup.

- `src/Mcp/MemorialTools.php`: the read-only tools. The MCP server
  (`config/packages/mcp.yaml`) and the `memorial_chat` agent
  (`config/packages/ai.yaml`) share these methods. Change a tool in one place.
- `config/packages/ai.yaml`: platforms, the chat agent, the pgvector store and
  the vectorizer. `config/prompts/memorial-chat.md` is the agent's prompt.
- `src/Workflow/`, `src/MessageHandler/`: item and image processing. Messages go
  to the Doctrine `processing` transport.
- `src/Decision/`: Jev (TypeSafe) questions and routing thresholds.
- `src/Search/`: Elasticsearch documents for items and images.
- `src/Controller/Admin/`: EasyAdmin. `/admin` requires `ROLE_ADMIN`; the only
  user is `admin`, with the hash in `ADMIN_PASSWORD_HASH`.
- `bin/install.sh`: the fresh-install path in README.md. Keep it and the README
  Quickstart in step.

Rules for this domain:

- Never present a proposed reading as a verified fact. Keep uncertainty.
- An AI-cleaned or straightened image is for display only. OCR and extraction
  read the original or an OpenCV-corrected frame.
- Camera GPS is the camera position, not a grave location.
- Commands and tests must not call paid providers unless the user asks.

## Running the app

- `bash bin/install.sh` starts PostgreSQL (5544), Elasticsearch (19200) and
  Imagor (8088) with Docker Compose, loads the public demo and starts
  `symfony server:start -d`. `bin/stop` stops them.
- The committed `.env` points at a shared PostgreSQL on port 5434;
  `.env.local` overrides it. Service-ignore labels keep the Symfony CLI from
  overriding `DATABASE_URL`.
- `symfony console app:processing:status` lists pending photos and missing keys.
  `symfony console messenger:consume processing -vv` processes them and calls paid
  providers.
- There's no need to clear the cache in dev: the kernel rebuilds what changed.
- When something fails, read `var/log/dev.log` and the profiler (`/_profiler`
  lists the last requests) before changing code.
- Makers (`bin/console make:*`) ask for missing arguments, and a
  non-interactive shell then waits forever. Pass all the arguments, with
  `--no-interaction`.

## Tests

Tests are standalone PHP scripts in `tests/`, not PHPUnit. Each one boots the
kernel and exits non-zero on failure. Run one with `symfony php tests/NAME.php`.
None of them call paid AI providers. README.md lists the main ones under
Validation. Also run:

```bash
symfony console lint:container
symfony console lint:twig templates
symfony console doctrine:schema:validate
```

## Writing

These rules apply to comments, commit messages, docs and anything written for
the team:

- Write plain, factual English, because many readers aren't native speakers.
  Write one idea per sentence and use verbs in their everyday sense. Repeat the
  noun instead of using a pronoun that makes the reader look back.
- State facts, not how good they are. Avoid words like "robust", "seamless",
  "powerful" or "simply", exclamation marks and emoji.
- Start with what changes for the reader, then explain why. Don't repeat what
  the code or the diff already shows.
- Use commas, colons and parentheses, not em-dashes.
- A commit message says what changed in one line, plus a sentence or two when
  the reason isn't obvious.

## Code

- Write a comment only for what the code can't say, such as a reason, a
  reference to a spec or a non-obvious constraint. Don't narrate the code.
- Don't set an option to its default value. `config/reference.php` shows the
  defaults.
- Fix a bug by writing a test that reproduces it first.
- Keep the diff to what the task needs: no unrelated renames, reformatting or
  refactoring.

## When a package gets in the way

These rules apply to bugs, missing features, slow code paths and APIs that make
the app write boilerplate, in Symfony or in any other package:

- Before you write a workaround, check that the problem is real. Reproduce it in
  a few lines, compare the installed version with the latest release, and
  search the issues and pull requests of the project.
- For a performance issue, measure it first, with the profiler or a benchmark,
  and keep the numbers.
- Keep the workaround small. Link the workaround to the upstream issue or pull
  request in a comment, so that it can be removed after the fix is released.
- Offer to report it upstream. Draft the issue with the reproduction or the
  numbers, or a pull request with a test when the fix is clear. Ask before
  posting anything, since it is published under the name of the person you
  work for.
- Report security issues privately, as the security policy of the project says,
  never in a public issue. For Symfony, see
  https://symfony.com/doc/current/contributing/code/security.html.

## Look it up

Symfony's APIs change between versions, and what you remember may be outdated.
The application can tell what exists:

- `bin/console debug:autowiring <keyword>`: the services you can type-hint;
- `bin/console debug:autoconfiguration <keyword>`: the attributes and interfaces
  that make a class a command, a listener, a voter, a message handler...;
- `bin/console debug:config <root key>`: the configuration as resolved;
- `bin/console debug:router`, `debug:container --env-vars`, `debug:messenger`,
  `debug:event-dispatcher`...;
- `bin/console lint:container`, `lint:yaml config/`, `lint:twig templates/`: to
  catch errors before running;
- `vendor/symfony/<package>/CHANGELOG.md`: what each version added, deprecated
  or removed. Read the sections of the recent versions before you use a
  component;
- the source and the docblocks in `vendor/`;
- https://symfony.com/doc/current/, switched to the version of `composer.json`
  when it differs.
