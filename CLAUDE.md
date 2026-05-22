# Vanilla Bean Slack Hooker — Claude context

## What this is

A WordPress plugin that pushes site events to Slack, Mattermost, Discord, or any
webhook-compatible endpoint, with an email fallback. It hooks WordPress and
WooCommerce events (post status changes, comments, registrations, plugin
installs/updates, sales) and exposes a shortcode (`[slackhooker]`) plus a
namespaced programmatic API (`\VanillaBeans\SlackHooker\…`). Messages are composed
as rich attachments and delivered through a non-blocking queue with an optional
send-now path. It is distributed on the WordPress.org plugin directory and
developed on GitHub. Built on the WordPress Plugin Boilerplate (WPPB) layout with
the bundled Exopite Simple Options framework for its settings screen.

## Repo map

| Path | What it is |
|------|------------|
| `vanilla-bean-slack-hooker.php` | Bootstrap: plugin header, constants (`SLACKHOOKER_*`), activation/deactivation registration, kicks off the core class. |
| `includes/class-vanilla-bean-slack-hooker.php` | Core orchestrator — defines the loader and registers admin + public hooks. |
| `includes/class-vanilla-bean-slack-hooker-loader.php` | Hook registrar; also handles plugin-update notification de-duplication. |
| `includes/class-slack-hooker-message.php` | Message/attachment composition — the webhook payload shape. |
| `includes/notifier.php` | Namespaced `VanillaBeans\SlackHooker` functions + legacy wrappers — the public/programmatic API and shortcode entry. |
| `includes/legacy.php` | Large backwards-compat surface. Touch with care; preserve existing function signatures. |
| `includes/class-vanilla-bean-slack-hooker-{i18n,activator,deactivator}.php` | i18n + lifecycle. |
| `admin/class-vanilla-bean-slack-hooker-admin.php` | The largest file. Settings screen wiring (via Exopite) + the WP/WooCommerce event → notification subscriptions. |
| `admin/{css,js,partials}/` | Admin assets and the settings display partial. |
| `public/` | Public-facing class + tiny css/js + shortcode display partial. |
| `exopite-simple-options/` | **Vendored** third-party settings framework. Do not edit (see Shared conventions). |
| `languages/` | Translation files (`.pot`/`.po`/`.mo`), text domain `vanilla-bean-slack-hooker`. |
| `assets/` | WordPress.org listing assets (icon, banner). |
| `uninstall.php` | Removes plugin data on uninstall. |
| `readme.txt` | WordPress.org readme (Stable tag, sections). Source of truth for the WP.org listing. |
| `README.md` | GitHub-facing readme. |
| `CHANGELOG.md` / `.CHANGELOG_internal.md` | MAIASS-managed changelogs (public + internal). |
| `VERSION` | MAIASS **primary** version source. |
| `.env.maiass`, `.env.maiass.local` | MAIASS configuration. |

## Shared conventions

- **WPPB structure.** Code is split admin / public / shared-core under `includes/`.
  Hooks are registered through the loader, not scattered `add_action` calls — follow
  that pattern when adding behaviour.
- **Do not edit `exopite-simple-options/`.** It is a vendored library with its own
  `sanitize-class.php`. If it genuinely must be patched, say so explicitly and treat
  it as a vendored-lib patch (documented, minimal); prefer working around it.
- **Security baseline (this plugin handles external HTTP + admin input + email).**
  Sanitise all input, escape all output, verify nonces on form/AJAX handlers, gate
  admin actions with `current_user_can`. Webhook URLs are user-supplied → treat the
  outbound request as an SSRF surface and never echo secrets/URLs into logs or
  notifications. Never commit real webhook URLs or tokens (note `.env`, `maiass.log`).
- **i18n.** All user-facing strings use the `vanilla-bean-slack-hooker` text domain.
- **Backwards compatibility.** `includes/legacy.php` and the `notifier.php` legacy
  wrappers exist because real sites call these functions. Don't break public function
  signatures or the shortcode contract without a deprecation path.
- **Local dev.** The plugin is symlinked into a WordPress sandbox; the canonical repo
  is this directory. There is **no build step** (plain PHP/CSS/JS) and no PHPCS config
  checked in — match the existing code style by hand.
- **PHP / WP target.** Requires WP 5.4+, tested to 6.8; PHP 8.2.

## Data & external integration

- **Settings** persist in `wp_options` via Exopite Simple Options — there are no
  custom DB tables (verify before assuming one is needed). Queue state and the
  "already-notified plugins" list are stored as options/transients.
- **Outbound:** HTTP POST to webhook endpoints (Slack/Mattermost/Discord/custom) and
  email fallback. Use the WP HTTP API (`wp_remote_post`), respect the queue/send-now
  split in the message pipeline rather than firing inline.
- **Inbound triggers:** WordPress core events + WooCommerce hooks + the `[slackhooker]`
  shortcode + the programmatic API in `notifier.php`.

## Git workflow

### Tickets

This plugin tracks work in **GitHub Issues** on `vsmash/vanilla-bean-slack-hooker`.
Use the human-facing prefix **`VBSLACK-<N>`** where `<N>` is the GitHub issue number
(so `VBSLACK-42` ⇔ issue `#42`). The prefix must appear in the **branch name**,
**commit subject**, and **PR title**. The PR **body** must include `Closes #<N>` so
GitHub auto-closes the issue on merge.

> slack-hooker deliberately uses **GitHub Issues** as a trial of GitHub for the team.
> The rest of the Vanilla Bean family tracks in **Jira**: pdf-validator → project
> `TMPDES`, themelogin and meta-maid → project `VEL`. Keep this one on GitHub unless told
> otherwise.

### Branches

- **`develop`** is the trunk. All non-hotfix work integrates here.
- **`master`** is promote-only / production (the released state). No direct commits.

Non-hotfix work — branch from `develop`:
- `feature/VBSLACK-<N>_short_desc`
- `task/VBSLACK-<N>_short_desc`
- `bug/VBSLACK-<N>_short_desc`

Hotfix work:
- `hotfix/VBSLACK-<N>_short_desc` branches from `master`.
- Lands in **both** `master` (the fix) and `develop` (so it isn't lost). MAIASS's
  release flow normally drives `develop → release/* → master`; for a hotfix, open a PR
  into `master` and ensure it is merged back to `develop`.

Always `git fetch && git checkout develop && git pull` before branching.

### Commit + PR hygiene

- **Commit subject:** conventional-commit type + `VBSLACK-<N>` reference, e.g.
  `feat: queue retry on webhook 5xx (VBSLACK-42)`. Keep the `feat:` / `fix:` /
  `refactor:` / `chore:` prefix — MAIASS groups the changelog by it.
- **PR title:** `VBSLACK-<N>: short summary`.
- **PR body:** `Closes #<N>` + a one-line what/why.
- **Versioning is MAIASS's job, not a manual edit** — see Versioning & release.

### Merge gates

- No direct commits to `master`; `develop` is the integration target.
- **`code-reviewer` runs on every PR** before merge (user requirement).
- **`security-reviewer`** must pass on any change touching the webhook/HTTP path,
  admin settings/form handlers, email composition, or `uninstall.php`.
- The four version locations must agree before release (see below).

### Bundling and serialisation

- Same file → bundle into one PR, reference every issue (`Closes #41`, `Closes #42`).
- Logical dependency → block via issue link; don't parallelise.
- Check file overlap before dispatching parallel agents — `admin/` work and
  `includes/` work usually parallelise; anything spanning both serialises.
- Rebase against `develop` immediately before opening the PR.

### Force-push

- `--force-with-lease` only, only on your own `feature/`/`task/`/`bug/` branch.
- Never on `develop` or `master`.

## Versioning & release (MAIASS + WordPress.org)

Versioning is driven by **MAIASS** (`.env.maiass`). The **primary** source is the
`VERSION` file. MAIASS also updates these **secondary** locations on a bump — keep all
four in sync:

1. `VERSION` (primary)
2. `readme.txt` → `Stable tag:`
3. `vanilla-bean-slack-hooker.php` → `Version:` header
4. `vanilla-bean-slack-hooker.php` → `const SLACKHOOKER_VERSION = '{version}';`

> Observed at bootstrap: `VERSION`, the header, and `readme.txt` Stable tag all read
> `5.5.11`, but `const SLACKHOOKER_VERSION` reads `5.5.0`. This looks like a stale
> secondary-file match — **release-engineer should verify** the MAIASS secondary
> pattern updates the const, rather than assume it's correct.

Distribution: WordPress.org SVN (deploy scripts live one level up in the plugins
workspace: `svn_deploy.sh`, `create_archive.sh`, `svn-rsync-exclude.txt`) plus GitHub
releases. `.CHANGELOG_internal.md` is internal-only and excluded from the WP.org build.

## Agent team (`.claude/agents/`)

The main session is the orchestrator. Delegate to specialists via the Task tool.
Subagents run in fresh contexts — brief them with concrete file paths and line
numbers, never "based on what we discussed."

### Engineering (mutually exclusive scope, by directory)

| Agent | Owns |
|-------|------|
| `plugin-core` | Bootstrap (`vanilla-bean-slack-hooker.php`), `includes/`, `public/`, `uninstall.php` — the runtime engine: hooks, loader, message/queue pipeline, webhook + email delivery, shortcode + programmatic API, lifecycle, options/transient data. |
| `admin-ui` | `admin/` only — the settings screen (Exopite config), event → notification subscription wiring, admin partials/css/js. |

### Release

| Agent | Owns |
|-------|------|
| `release-engineer` | MAIASS version bumps (the four locations above), CHANGELOG, WordPress.org SVN packaging/deploy, GitHub releases. Read-mostly elsewhere; the version-sync authority. |

### Copy (WordPress.org listing only — no social distribution)

| Agent | Owns |
|-------|------|
| `copywriter` | Prose in `readme.txt` (Description, Installation, FAQ, screenshot captions, human-readable changelog), `README.md` marketing sections, and WP.org listing fields (short description, tags). Does **not** touch the `Stable tag` / version mechanics (that's `release-engineer`). |

### Cross-cutting (read-only)

| Agent | Owns |
|-------|------|
| `code-reviewer` | Independent review on **every PR** — correctness, WPPB/style consistency, i18n, backwards-compat with `legacy.php`, the four-way version sync. |
| `security-reviewer` | WordPress-plugin security lens: sanitisation, output escaping, nonces, capability checks, SSRF on user-supplied webhook URLs, secret/URL leakage into logs or notifications. |
| `repo-cartographer` | "Where is X?" / "How do A and B connect?" across the WPPB layout, the loader-driven hook graph, and `legacy.php`. |

### Coordination patterns

- **New notification trigger** (e.g. notify on a new WooCommerce event): `admin-ui`
  adds the subscription toggle/setting; `plugin-core` wires the hook → message
  pipeline. Serialise if they touch the same flow; otherwise parallelise and reconcile
  in one PR.
- **Webhook/HTTP, email, or `uninstall.php` changes** → always route through
  `security-reviewer` before merge, in addition to `code-reviewer`.
- **Any version bump or release** → `release-engineer` owns it; `copywriter` finalises
  the `readme.txt`/`CHANGELOG.md` prose first, then `release-engineer` cuts the build
  so the Stable tag and the four version locations land together.
- **Parallelise** `admin/` and `includes/`+`public/` work when there's no shared flow.
  **Serialise** anything spanning admin + core, or any same-file overlap.

### Discovery patterns

<!-- Append concrete lessons here as real issues/PRs land. Reference them by VBSLACK-<N> / #<N>. Start empty; do not fabricate. -->
