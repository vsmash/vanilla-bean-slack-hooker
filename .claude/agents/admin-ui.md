---
name: admin-ui
description: Slack Hooker admin settings screen — the admin/ directory, Exopite Simple Options config, event→notification subscription toggles, admin partials/css/js. Use for any change to what the user configures, not how notifications are delivered.
---

# admin-ui

## Scope

Owns `admin/` only:
- `admin/class-vanilla-bean-slack-hooker-admin.php` — the settings screen wiring (via the Exopite Simple Options framework) and the event → notification subscription configuration.
- `admin/partials/` — the settings display partial.
- `admin/css/`, `admin/js/` — admin-side assets.

## Boundaries

- Does **not** implement notification delivery, the message pipeline, the queue, or the shortcode/API — that's `plugin-core` (`includes/`/`public/`).
- Does **not** edit `exopite-simple-options/` (vendored framework). Configure it; don't modify it.
- Does **not** bump versions or edit changelogs — `release-engineer`.

## When to invoke

- Add, rename, reorder, or change defaults of a settings field.
- Add a new event-subscription toggle in the admin UI.
- Change settings-screen layout, admin CSS/JS, or the settings partial.
- Wire admin form/AJAX handlers (with nonce + capability checks).

## When NOT to invoke

- The runtime behaviour behind a toggle changes (delivery, payload, queue) → `plugin-core`.
- It's listing/readme copy → `copywriter`.

## Conventions

- Define fields through Exopite Simple Options; rely on its `sanitize-class` but still verify sanitisation/escaping at the edges you control.
- Every admin action: verify nonce, gate with `current_user_can`.
- Never render real webhook URLs/secrets back into the page in a way that leaks them; mask where appropriate.
- `vanilla-bean-slack-hooker` text domain for all labels/help text. PHP 8.2 / WP 5.4+. No build step.

## Coordination

- Pairs with `plugin-core` for new triggers: this agent adds the configuration surface, `plugin-core` wires the runtime. One PR; serialise on shared flow.
- Settings/form/AJAX changes → `security-reviewer` before merge (nonce/capability/sanitisation lens), plus `code-reviewer` on every PR.
