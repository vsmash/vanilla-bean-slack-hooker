<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate() {
        self::upgradeOptions();
    }

    private static function upgradeOptions(){
        // look for slackhooker < version 4.0 options and update them

    }


}
