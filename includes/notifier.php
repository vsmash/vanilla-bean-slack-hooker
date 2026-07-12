<?php

namespace VanillaBeans\SlackHooker;

use Slack_Hooker_Message;
use Vanilla_Bean_Slack_Hooker_Admin;

// <editor-fold defaultstate="collapsed" desc="Legacy Wrappers">

// Legacy wrapper for backwards compatibility
if (!function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')) {
    function vbean_slackhooker($message, $channels = null, $username = null, $icon = null, $now = false, $postto = null, $alsoto = null)
    {
        return \Vanilla_Bean_Slack_Hooker::custom_send($message, $channels, $username, $icon, $now, $postto, $alsoto);
    }
}

// Legacy Slacknow wrapper for backwards compatibility
if (!function_exists('\VanillaBeans\SlackHooker\vbean_slacknow')) {
    function vbean_slacknow($message, $channel = null, $username = null, $icon = null, $now = false, $urls = null, $extraurls = null)
    {
        return \Vanilla_Bean_Slack_Hooker::custom_send($message, $channel, $username, $icon, $now, $urls, $extraurls);
    }
}

// legacy shortcode hook
if (!function_exists('\VanillaBeans\SlackHooker\vbean_slacknotify')) {
    function vbean_slacknotify($atts)
    {
        \Vanilla_Bean_Slack_Hooker::custom_notify($atts);
    }
}

add_action('vbeanschedule', '\VanillaBeans\SlackHooker\vbean_schedulesend', 1, 2);

// legacy shortcode hook
if (!function_exists('\VanillaBeans\SlackHooker\vbean_schedulesend')) {
    function vbean_schedulesend($url, $payload)
    {
        \Slack_Hooker_Message::remotePost($url,$payload);
    }
}



// </editor-fold>

add_shortcode('slackhooker', '\VanillaBeans\SlackHooker\vbean_slacknotify');




// <editor-fold defaultstate="collapsed" desc="2022">

function notification_send($message, $endpoints = null)
{
    $msg = new Slack_Hooker_Message();
    $msg->endpoints=$endpoints;
    $msg->payload = array(
    );
    if (is_array($message)) {
        $msg->payload['attachments'] = array($message);
    } else {
        $msg->payload['text'] = $message;
    }
    return($msg->sendit());
}

// build plugin attachment message
if (!function_exists('\VanillaBeans\SlackHooker\build_attachment_message')) {
    function build_attachment_message($color, $status, $plugin, $context = null)
    {
        $current_user = wp_get_current_user();
        // is $current_user an object?
        $username = empty($current_user) ? 'System' : $current_user->display_name??'System';
        
        // Check for stored context first (from version detection)
        if ($context) {
            $username = $context;
        }
        // Otherwise check if WP-CLI is currently running
        elseif (defined('WP_CLI') && WP_CLI) {
            $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
            $username = isset($options['cli_username']) ? $options['cli_username'] : 'WP-CLI';
        }
        // Check for auto-updates
        elseif (defined('DOING_CRON') && DOING_CRON) {
            $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
            $username = isset($options['cli_username']) ? $options['cli_username'] : 'Auto-update';
        }
        
        $message = array(
            "color" => $color,
            "pretext" => "Plugin " . $status . " on " . get_site_url() . " by " . $username,
            "author_name" => $plugin["AuthorName"],
            "author_link" => $plugin["AuthorURI"],
            "title" => html_entity_decode($plugin["Name"]),
            "title_link" => $plugin["PluginURI"],
            "text" => html_entity_decode($plugin["Description"]),
            // 			"thumb_url" => "http://placekitten.com/g/200/200",
            "footer" => "Slackhooker by Velvary",
            "footer_icon" => "https://iili.io/F1uU8P.png",
            "ts" => time()
        );
        return $message;
    }
}

// notification of plugin install or update
// This does not work from within Vanilla_Bean_Slack_Hooker_Admin even as a static function
// The args do not arrive correctly so it is here.
// The hook is specified directly in the loader
if (!function_exists('\VanillaBeans\SlackHooker\plugin_upgrader')) {
    function plugin_upgrader($upgrader, $hook_extra)
    {
        if (!isset($hook_extra['type'], $hook_extra['action']) || $hook_extra['type'] != 'plugin' || ($hook_extra['action'] != 'install'&& $hook_extra['action'] != 'update')) {
            return false;
        }

        $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
        if(!isset($options['notifications']['plugin_change']['tabs'])){
            return false;
        }
        $obj = $options['notifications']['plugin_change']['tabs'];

        if($obj['plugin_change_onoff']!='yes'){
            return false;
        }

        $colour = $obj['colours'][$hook_extra['action']];

        // Extract plugin path(s) from hook_extra
        // Handle both single plugin updates and bulk updates
        $plugins = array();
        if (isset($hook_extra['plugin'])) {
            // Single plugin update
            $plugins[] = $hook_extra['plugin'];
        } elseif (isset($hook_extra['plugins'])) {
            // Bulk plugin update
            $plugins = $hook_extra['plugins'];
        } else {
            return false;
        }

        // Process each plugin (usually just one)
        foreach ($plugins as $plugin_path) {
            $plugin = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path, false);

            // Skip if plugin data couldn't be retrieved
            if (empty($plugin['Name'])) {
                continue;
            }
            
            // Pass context if available
            $context = isset($hook_extra['context']) ? $hook_extra['context'] : null;
            $message = build_attachment_message($colour, $hook_extra['action'], $plugin, $context);
            
            $endpoints = $obj['endpoints']??false;
            $endpointOptions = $obj['endpointOptions']??'default';

            \Vanilla_Bean_Slack_Hooker::notification_send($message, $endpoints, $endpointOptions);
        }

        return true;
    }
}

// </editor-fold>

/**
 * Send a formatted message with data fields
 *
 * @param string $title The main title of the message
 * @param array $data Associative array of name => value pairs
 * @param array $options Optional settings: color, pretext, footer, etc.
 * @param mixed $endpoints Custom endpoints
 * @param string $endpointOptions Endpoint options
 * @return mixed Send result
 */
if (!function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
    function vbean_send_data($title, $data = array(), $options = array(), $endpoints = false, $endpointOptions = 'defaults') {
        return \Vanilla_Bean_Slack_Hooker::send_data_message($title, $data, $options, $endpoints, $endpointOptions);
    }
}

/**
 * Build a formatted attachment message with data fields
 *
 * @param string $title The main title of the message
 * @param array $data Associative array of name => value pairs
 * @param array $options Optional settings: color, pretext, footer, etc.
 * @return array Formatted attachment array
 */
if (!function_exists('\VanillaBeans\SlackHooker\vbean_build_data_message')) {
    function vbean_build_data_message($title, $data = array(), $options = array()) {
        return \Vanilla_Bean_Slack_Hooker::build_data_message($title, $data, $options);
    }
}
