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
    function build_attachment_message($color, $status, $plugin)
    {
        $current_user = wp_get_current_user();
        // is $current_user an object?
        $username = empty($current_user) ? 'System' : $current_user->display_name??'System';
        // is the current user WP-CLI?
        if (defined('WP_CLI') && WP_CLI) {
            $username = 'WP-CLI';
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
    function plugin_upgrader($plugin, $upgrader)
    {
        if (!isset($upgrader['type'], $upgrader['action']) || $upgrader['type'] != 'plugin' || ($upgrader['action'] != 'install'&& $upgrader['action'] != 'update')) {
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

        $colour = $obj['colours'][$upgrader['action']];
        $current_user = wp_get_current_user();
        // is $current_user an object?
        $username = empty($current_user) ? 'System' : $current_user->display_name??'System';




        $plugin = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin->plugin_info(), false);

        $message = build_attachment_message($colour,$upgrader['action'],$plugin);

        error_log("\033[0;33m attachment message done \033[0m");
        // if $obj has no array key 'endpoints' assign false to $endpoints
        $endpoints = $obj['endpoints']??false;


        $endpointOptions = $obj['endpointOptions']??'default';


        return \Vanilla_Bean_Slack_Hooker::notification_send($message, $endpoints, $endpointOptions);
    }
}

// </editor-fold>

add_shortcode('slackhooker', '\VanillaBeans\SlackHooker\vbean_slacknotify');

