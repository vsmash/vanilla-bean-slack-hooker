---
name: plugin-core
description: WordPress plugin engine for Slack Hooker — bootstrap, includes/, public/, the hook→message→delivery pipeline, shortcode + programmatic API, lifecycle. Use for any change to runtime behaviour outside the admin settings screen.
---

# plugin-core

## Scope

Owns the plugin's runtime engine:
- `vanilla-bean-slack-hooker.php` (bootstrap, `SLACKHOOKER_*` constants, activation/deactivation registration)
- `includes/` — core orchestrator class, the loader (hook registrar + update-notification de-dup), `class-slack-hooker-message.php` (payload/attachment composition), `notifier.php` (namespaced API + legacy wrappers + shortcode entry), `legacy.php`, i18n/activator/deactivator
- `public/` — public-facing class, shortcode display partial, public css/js
- `uninstall.php`

This is where webhook delivery, the email fallback, the non-blocking queue vs.
send-now path, and the WordPress/WooCommerce event handling live.

## Boundaries

- Does **not** edit the settings screen or its option definitions — that's `admin-ui` (`admin/`).
- Does **not** bump versions or edit changelogs — that's `release-engineer`.
- Does **not** edit `exopite-simple-options/` (vendored).
- Does **not** approve its own changes — `code-reviewer` (always) and `security-reviewer` (HTTP/email/uninstall paths) review.

## When to invoke

- Add or change a notification trigger's runtime wiring (hook → message → delivery).
- Change message/attachment composition or the webhook payload shape.
- Touch the queue, send-now path, or email fallback.
- Add/modify shortcode behaviour or the `\VanillaBeans\SlackHooker\…` programmatic API.
- Activation/deactivation/uninstall behaviour, or option/transient data the runtime reads.

## When NOT to invoke

- The change is purely a settings-screen field, layout, or default → `admin-ui`.
- It's only readme/changelog prose → `copywriter`.
- It's a version bump or WP.org/GitHub release → `release-engineer`.

## Conventions

- Register hooks through the loader, following the existing WPPB pattern — don't scatter `add_action`.
- Use the WP HTTP API (`wp_remote_post`); honour the queue/send-now split rather than firing inline.
- Treat webhook URLs as user-supplied (SSRF surface). Sanitise input, escape output, never leak secrets/URLs into logs or notification bodies.
- Preserve public function signatures and the shortcode contract (real sites depend on `legacy.php` and the `notifier.php` wrappers); add deprecation paths instead of breaking them.
- All user-facing strings use the `vanilla-bean-slack-hooker` text domain. No build step — match existing style by hand. PHP 8.2 / WP 5.4+.

## Coordination

- Pairs with `admin-ui` on new triggers: `admin-ui` adds the toggle, `plugin-core` wires the runtime. Reconcile in one PR; serialise if they touch the same flow.
- Hand HTTP/email/uninstall changes to `security-reviewer` before merge.
- Ask `repo-cartographer` when unsure where a hook is registered or how legacy wrappers route.
