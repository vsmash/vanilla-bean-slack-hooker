## 5.6.14
14 July 2026

- chore: dependabot config for the options-framework submodule
	chore: add dependabot config for the options-framework submodule
	  - feat: track the options-framework submodule pin, dependabot opens a PR when it falls behind, which is otherwise invisible because submodules are pinned by SHA
	  - feat: also keep the github actions themselves current
	  - note: this plugin has no npm or composer manifests, so the submodule is the only dependency there is to track
	  - note: options-framework is private, so dependabot needs read access granted to it or this silently does nothing
- chore: bump options-framework submodule to v1.0.1 (c1b7837)
	  - fix: pick up the removal of unconditional debug logging from the framework core class, which wrote the plugin url to error_log on every call
	  - note: verified live, the Slack Hooker settings screen renders 77 fields with zero PHP errors and no framework asset 4xx

## 5.6.13
13 July 2026

- scope the test-send handler to our own options panel
	fix: scope the test-send handler to our own options panel (#4)
	  - fix: exopite_sof_do_save_options is a global hook and fires for every Exopite panel and metabox on the site, not just ours
	  - fix: check the unique the hook passes, so another plugins save no longer reaches into our options array
	  - fix: this is why saving Local Knowledge warned Undefined array key send_test on every save
	  - fix: worse than a warning, a foreign panel carrying send_test=yes actually fired our test notification, verified live
	  - fix: guard textarea_testcontent too, it was dereferenced unchecked
- invalid UTF-8 no longer silently drops a notification
	fix: do not blame JSON in the skip log when the payload simply had nothing to render
	  - fix: only report a JSON error when json_last_error actually reports one
	  - fix: never print an unparseable endpoint verbatim, a webhook URL is a credential and only an email address is safe to name in full
- fix: address the code review findings on the invalid UTF-8 guard
	  - fix: skip an unsendable message before canSend(), it was charging a rate-limit slot and then discarding the message, so a later valid alert got refused
	  - fix: propagate the encode failure through Google Chat and Teams, stripSlackAlerts(false) coerced to an empty string and posted an encodable empty message, so the skip never fired
	  - fix: log a skipped notification instead of dropping it silently, which is the exact failure this ticket is about
	  - note: the log records the endpoint host only, never the URL, a webhook URL is a credential
- fix: stop invalid UTF-8 silently dropping a notification (#17)
	  - fix: substitute invalid UTF-8 rather than encoding to false and posting an empty payload to Slack
	  - fix: a PHP error message or a WooCommerce field pasted from Word could carry bad bytes, and the alert was lost with no trace
	  - fix: skip the endpoint entirely when nothing can be encoded, instead of posting an empty body for a guaranteed 400
	  - fix: apply the same guard to Google Chat and Teams, which returned an empty string on encode failure
	  - note: the Slack body stays byte-identical for any payload that was already valid
	  - chore: bump options-framework submodule to db4978a for the image field fix (options-framework#8)
- docs: remove Microsoft Teams from the public listing until it is proven
	  - docs: the Teams code ships and works, it is simply not advertised, so nobody is promised an integration we have never seen deliver
	  - docs: keep Google Chat public, its contract is a single documented JSON field rather than a nested card envelope
	  - docs: add the missing 5.6.11 changelog entry, the readme section had fallen behind the stable tag again
	  - note: CHANGELOG.md keeps the full Teams detail and is excluded from the wordpress.org ship
- docs: flag Google Chat and Teams as new and invite bug reports
	  - docs: the payload and rendering are verified against Microsofts renderer, but no live delivery has been observed yet
	  - docs: ask users to report a non-arriving message with the webhook host, so we hear about it rather than assume it works
- send Teams an Adaptive Card, not the legacy MessageCard
	feat: send Teams an Adaptive Card instead of the legacy MessageCard
	  - feat: wrap the card in the envelope a Power Automate Workflows webhook expects (#16)
	  - feat: map attachment fields to an Adaptive Card FactSet, keyed title/value
	  - fix: one TextBlock per line, so Markdown soft breaks can no longer collapse the layout
	  - note: MessageCard is deprecated for new integrations and Microsofts own designer refuses to render it
	  - note: verified by pasting the plugins generated card into the Actionable Message Designer, it renders

## 5.6.10
12 July 2026

- Google Chat + Microsoft Teams webhook support, compat headers, changelog catch-up
	fix: address the code review findings on the Teams and Google Chat transports
	  - fix: map attachment fields to MessageCard facts, the layout Microsoft documents for name/value data
	  - fix: join Teams prose with a blank line, a single newline is a Markdown soft break and collapsed to a space
	  - fix: keep author_name, which carries the post author and the commenter email and was being dropped
	  - fix: guard non-scalar field values, they raised an Array to string warning on the email path too
- fix: render structured notifications properly on Google Chat and Teams
	  - fix: flatten Slack attachments to text, they arrived as a raw JSON dump (#15, #16)
	  - fix: every structured message (WooCommerce, post status, plugin changes, error alerts) sets attachments and no text, so the fallback fired for all of them
	  - fix: give each endpoint its own payload copy, mutations were accumulating across endpoints
	  - fix: a second endpoint no longer inherits the first ones channel or @here ping
	  - fix: Google Chat no longer inherits the Slack username prefix
	  - fix: email endpoints now get readable text instead of a JSON dump, a pre-existing bug
	  - fix: substitute invalid UTF-8 rather than encoding to false and posting an empty body
- SOF: bump options-framework submodule to b089e95
	- pick up the HTML attribute escaping fix (options-framework#7)
	- hardening only: this plugin passes static esc_html__ field config, not user input

## 5.6.8
7 July 2026

- Version bump only, no code changes (SVN deploy retry after the submodule migration)

## 5.6.7
7 July 2026

- Version bump only, no code changes (SVN deploy retry after the submodule migration)

## 5.6.6
7 July 2026

- Version bump only, no code changes (SVN deploy retry after the submodule migration)

## 5.6.5
6 July 2026

- SOF: adopt shared options-framework submodule (single source of truth)
	SOF: move Exopite to shared options-framework submodule (single source of truth)
	  - add options-framework submodule of vsmash/options-framework
	  - repoint both framework require_once lines to the submodule
	  - remove vendored exopite-simple-options (548 files)
	  - verified live on vanillabeans: settings form renders, framework assets 200, no fatal
	  - SVN deploy hardened in shared tooling (submodule init + fail-closed assertion + auto svn-rm of stale copy)

## 5.6.4
26 May 2026

- Updated Node.js version in CI workflow 
- Introduced PHP error alerts, routing through Slack with a new "PHP Error Alerts" tab 
	- Updated settings tab name to "PHP Error Alerts" 
	- Clarified defaults for PHP Error Alerts options 
- Updated alert options tab title to 'PHP Error Alerts' 
- Improved error monitoring configuration, including handling of exemption entries 
	- Enhanced handling of exemption entries as a repeater group 
	- Ensured exemptions are checked as an array before processing 
	- Streamlined extraction of exemption text entries 
- Implemented error monitoring for Slack notifications with new alert options configuration 
	- Updated comments for clarity and structure 
	- Ensured fail-safe mechanisms in error handling

## 5.6.3
25 May 2026

- Removed redundant symlink to vanilla-bean-slack-hooker.
- Updated README with new release information
	Confirmed compatibility with WordPress 7.0 and PHP 8.3
	Updated changelog to reflect version change from 5.5.14 to 5.6.2
- Updated secondary file versioning format in environment variable
	Fixed escaping of newline characters in MAIASS_VERSION_SECONDARY_FILES
- Updated secondary files versioning format in .env.maiass

## 5.5.18
25 May 2026

- Updated secondary files versioning format.
	Fixed newline formatting in MAIASS_VERSION_SECONDARY_FILES.
- Updated secondary files versioning in configuration  
	Adjusted version format for secondary files.
- Updated versioning in configuration file  
	Improved format for secondary files in .env.maiass and included changelog in MAIASS_VERSION_SECONDARY_FILES.
- Updated the readme changelog for end users
	Added a 5.5.14 readme.txt changelog entry for WordPress 7.0 and PHP 8.3 compatibility.
- Declared WordPress 7.0 compatibility
	Updated the readme.txt "Tested up to" section from 6.8 to 7.0 after confirming functional compatibility through a static audit.
- Added ticket repo-tagging rule to the workflow
	Every ticket now carries its plugin/repo name.
- Implemented automatic version bump on PR merge
	Included setup instructions and workflow notes in YAML file
- Improved WordPress compatibility for versions 6.7 and 8.2 by fixing the i18n textdomain and syncing Exopite to the local knowledge source of truth.
	Documented the Exopite source of truth and the rule against in-place fixes.
		- Bundled exopite-simple-options is a shared, self-maintained framework; the source of truth is localknowledge for legacy and velvary/options-framework for the future.
		- Clarified the need to document fixes in the source of truth instead of making in-place patches.
- Removed the admin constructor translation that occurred before initialization.
		- Fixed the issue by eliminating the eager buildEndpointOverrides call from the constructor, which triggered an esc_html__ notice before initialization.
		- Dropped the write-only $endpointoverrides property; consumers now rebuild it during the initialization process.
- Added an intent-first block to the security-reviewer agent.
		- Required verification of the threat model prior to rating severity to avoid assumptions.
- Corrected commit message guidance for the changelog.
		- Updated the format to use a plain subject followed by type body bullets, correcting the previous error in the structure.
		- Specified that commits should not end with Co-Authored-By.

## 5.5.12
23 May 2026

- Added detailed documentation for new agents
	- Introduced `code-reviewer` agent for independent reviews on PRs
	- Added `copywriter` agent for managing WordPress.org listing text
	- Implemented `plugin-core` agent for runtime behavior management
	- Created `release-engineer` agent for version management and releases
	- Specified scopes, boundaries, and usage for each agent

## 5.5.10
25 October 2025

- Refactored Vanilla Bean Slack Hooker Loader
	- refactor: streamlined plugin update hook
	- refactor: removed duplicate update notifications
	- refactor: simplified version detection and notification process
	- refactor: cleaned up context determination for non-admin updates
- Removed unused config and enhanced plugin update notifications
	- refactor: removed unused MAIASS_CHANGELOG config from .env.maiass
	- feat: added logic to prevent duplicate notifications on plugin update in class-vanilla-bean-slack-hooker-loader.php
	- feat: implemented cleanup of old entries from the notified plugins list
	- delete: removed reference to vanilla-bean-slack-hooker in root directory
- Enhancements and fixes in Slack Hooker
	- feat: added CLI/Auto-update User option in admin settings
	- fix: added checks for isset conditions to prevent undefined errors
	- fix: removed error logging in Slack_Hooker_Message
	- feat: implemented detection of plugin updates during WP-CLI and auto-updates
	- feat: enhanced update context to differentiate between WP-CLI and auto-updates
	- feat: implemented plugin updater notifier for detected updates
	- fix: adjusted email body composition to handle missing payload['text'] scenario

## 5.5.3
03 October 2025

- Updated Vanilla Bean Slack Hooker
	- feat: changed file reference in MAIASS_VERSION_SECONDARY_FILES of .env.maiass
	- feat: added new CONTRIBUTORS.md and README.md
	- docs: populated README.md with comprehensive plugin description
- Removed vanilla-bean-slack-hooker reference
	- rm: deleted reference to vanilla-bean-slack-hooker
- Enhanced MAIASS and SlackHooker features
	- feat: added MAIASS configuration file
	- feat: added new notifier functions in SlackHooker
	- fix: updated .gitignore to ignore .env.maiass.local
	- fix: renamed CHANGELOG_verbose.md to CHANGELOG_internal.md
	- feat: created symlink for vanilla-bean-slack-hooker

## 5.5.0
21 April 2025

- Fix to overly zealous escaping

## 5.4.6
21 April 2025

- Security updates

## 5.3.5
21 April 2025
