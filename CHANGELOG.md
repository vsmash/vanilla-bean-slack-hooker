## 5.5.7
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
