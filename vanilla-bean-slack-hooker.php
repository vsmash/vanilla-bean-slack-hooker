<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.velvary.com.au
 * @since             1.0.0
 * @package           Vanilla Bean Slack Hooker
 *
 * @wordpress-plugin
 * Plugin Name:       Vanilla Bean Slack Hooker
 * Plugin URI:        https://www.velvary.com.au
 * Description:       Integrate webhooks into your site for notifications via Slack, Mattermost or others
 * Version:        5.3.1
 * Author:            Velvary
 * Author URI:        https://www.velvary.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vanilla-bean-slack-hooker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



const SLACKHOOKER_VERSION = '5.3.1';
const SLACKHOOKER_LOGO = 'https://iili.io/F1uU8P.png';
const SLACKHOOKER_LINK = 'https://www.velvary.com.au/wordpress_plugins/slackhooker';
const SLACKHOOKER_FOOTERTEXT = 'Slackhooker by Velvary';
const SLACKHOOKER_PLUGIN_SLUG = 'vanilla-bean-slack-hooker';
define('SLACKHOOKER_DIR_URL', plugin_dir_url(__FILE__));
define('SLACKHOOKER_DIR',plugin_dir_path(__FILE__));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vanilla-bean-slack-hooker-activator.php
 */
function activate_vanilla_bean_slack_hooker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vanilla-bean-slack-hooker-activator.php';
	Vanilla_Bean_Slack_Hooker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vanilla-bean-slack-hooker-deactivator.php
 */
function deactivate_vanilla_bean_slack_hooker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vanilla-bean-slack-hooker-deactivator.php';
	Vanilla_Bean_Slack_Hooker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vanilla_bean_slack_hooker' );
register_deactivation_hook( __FILE__, 'deactivate_vanilla_bean_slack_hooker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vanilla-bean-slack-hooker.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vanilla_bean_slack_hooker() {
	$plugin = new Vanilla_Bean_Slack_Hooker();
	$plugin->run();

}


run_vanilla_bean_slack_hooker();
