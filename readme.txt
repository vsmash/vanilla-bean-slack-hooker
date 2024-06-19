=== Plugin Name ===
Contributors: vsmash
Donate link: http://www.velvary.com.au/vanilla-beans/wordpress/slack-hooker/
Tags: slack, webhook, message, api, alerts
Requires at least: 3.4
Tested up to: 6.5
PHP Tested up to: 8.1
Stable tag: 5.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Notify one or more slack, mattermost or other endpoints with configured webhooks for
Wordpress events. Notifications for plugin installs and updates, page views, error messages, signups, woocommerce
or anything you like with fully customizable shortcodes, hooks
and filter functions.

== Description ==
*Automated notifications from your Wordpress * to your Slack / Mattermost application channels and webhooks.

There is no limit to the ways you may integrate this plugin.

It supports all other Vanilla Bean plugins and is available for under-the-bonnet
use as well as shortcode access.

= Some shortcode examples: =

`[slackhooker text="Here is a message from my wordpress"]`

or

`[slackhooker text="Here is a message" channel="general" username="My Wordpress"]`

or

`[slackhooker text="Here is a message" channel="https://myorg.slack.com/services/xxoxlsodsfsfsdfds#channelname" username="My Wordpress"]`

Messages are queued last in the PHP process to insure your other page processing is not interrupted, but you can override:

`[slackhooker text="Here is a message from my wordpress." sendnow="true"]`

Multiple or custom Webhooks supported

`[slackhooker text="Here is a message to multiple webhooks" alsoto="https://hooks.slack.com/services/T03TDTbUF/BBG9SQV6X/abcdefghij,https://hooks.slack.com/services/T03TDTbUF/BBG9SQV6X/abcxxxxxxx"]`


= Programmatic Examples =
`if(function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')){
    \VanillaBeans\SlackHooker\vbean_slackhooker('Here is my notification');
 }`

`if(function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')){
    \VanillaBeans\SlackHooker\vbean_slackhooker('Here is my notification','mychannel','Username',':specific_icon:');
 }`

`if(function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')){
    \VanillaBeans\SlackHooker\vbean_slacknotify(array('text'=>'Here is my notification','icon'=>':open_mouth:'));
 }`

There  are defaults for all except the message. You can specify:
`*   *text* - required. This is your message
*   *endpoints* - override defaults with a comma separated list of endpooints with optional channel and alert eg: endpoints='https://endpoint.com#channel#here"
*   *channel* - defaults to 'general' or your admin default specified channel - depracated
*   *username* - the identity you would like displayed as the sender in the channel
*   *icon* - any of the available emoji icons in the slack system
*   *sendnow* - true or false. Defaults to false. True means that the message would be executed at the time it is called rather than at the end of page load.



= Contact =
We can be found at:
*   (http://www.velvary.com.au/contact-us)
*   Twitter: @VelvaryVanilla
*   E: info@ or support@ velvary.com.au


== Installation ==
Unzip into your plugins directory, install directly from
wordpress plugins site.


== Screenshots ==
1. Example of wordpress monitoring
2. Admin instruction page
3. Another example of wordpress monitoring
4. Screenshot of default options
5. Screenshot of Notification choices

== Changelog ==
= 5.3.1 =
- Tested with wordpress 6.5 and php 8.2

= 5.2.14 =
- Bugfix - fixed issue with default channel reference having typo resulting in plugin upgrades notificaitons not working

= 5.2.11 =
- Bugfix - fixed issue with default channel reference having typo resulting in plugin upgrades notificaitons not working

= 5.2.7 =
- Fixed versioning inconsistency

= 5.2.0 =
- Added support for email notifications

= 5.1.7 =
- Admin menu bugfix

= 5.1.6 =
- tested with wordpress 6.2

= 5.1.3 =
- fixed double up in get_url() string replace


= 5.1.2 =
- fixed default value for fields
- fixed missing admin menu item

= 5.1.1 =
- fixed path error for mce plugin

= 5.1.0 =
- Cron messages made optional for when cron not supported


Another major overhaul. Previous settings not carried forward.
= 5.0.0 =
- Notification overrides can now be set for each endpoint
- Endpoints can now have alerts such as <!here> or <!channel> or <!everyone> added to them by adding #here or #channel or #everyone to the end of the endpoint url
- Improved admin layout
- Tested on PHP8.1
- Tested on Wordpress 6.1


Major overhaul. Previous settings not carried forward.
= 4.0.5 =
- bugfix naming conflict in javascript
- bugfix path for css

= 4.0.3 =
- bugfix for mattermost
- bugfix to textdomain

= 4.0.0 =
- MAJOR OVERHAUL Rewrite
- More alerts added for posts
- Customisable endpoints for all alerts

= 3.30 =
- added support for endpoints in channels

= 3.22 =
- bugfix for post status change

= 3.21 =
- tested against wordpress 6.1 and php 8.1

= 3.10 =
- added site url to notificaitons
- added post/page published notifications

= 3.05 =
- added code for system plugin updater

= 3.04 =
- Broken Image fix

= 3.03 =
- Cosmetic fix

= 3.02 =
- Added woocommerce sales hook

= 3.00 =
- Added compatibility with Mattermost

= PREVIOUS VERSIONS REMOVED =
- Added current site to default user

= 2.60 =
- Added current user to plugin alerts

= 2.55 =
- Added toggle option for help

= 2.47 =
- Enabled inheritance of <v2.46 channel default for alerts

= 2.46 =
- Enabled channel selection for default alerts

= 2.45 =
- Fixed test msg typo

= 2.44 =
- Added default notification options for when plugins are installed, updated etc
- Tested against WP 5.3
- Tested against Corona virus

= 2.20 =
- Changed Curl to http-api

= 2.14 =
- Changed @channel syntax to work with latest api

= 2.13 =
- Fixed bug of slack plugin validation updating incorrectly
- added programmatic examples to settings page

= 2.12 =
Updated Icon

= 2.11 =
Added missing menu icon

= 2.10 =
Tested against php7.1 and wordpress 4.7

= 2.0 =
* Added repetition prevention functionality
* Added support for multiple slack webhooks

= 1.50 =
Fixed issue with specified icon not displaying in notice

= 1.42 =
Set delayed send to flag slack as working

= 1.41 =
Moved slack message to immediate cron job so that it does not delay page load

= 1.24 =
Tested against wp 4.5

= 1.23 =
Updated 404 test

= 1.22 =
Updated common functions

= 1.21 =
Added support for default icon

= 1.2 =
Added default comment alert

= 1.1 =
Fix broken plugin download link

= 1.01 =
Shortcode in admin documentation bugfix

= 1.0 =
Initial release


= 2.44 =
Added alerts for when plugins are added/deleted/updated

= 1.5 =
Moved messaging to cron so that page load is not affected

= 1.2 =
Added functionality to notify channel of new comments
