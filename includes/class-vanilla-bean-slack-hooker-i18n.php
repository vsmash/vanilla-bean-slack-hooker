<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'vanilla-bean-slack-hooker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
