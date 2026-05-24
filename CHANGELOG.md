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
