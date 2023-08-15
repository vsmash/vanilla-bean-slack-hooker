<?php

require_once (SLACKHOOKER_DIR.'exopite-simple-options/exopite-simple-options-framework-class.php');
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Vanilla_Bean_Slack_Hooker_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    protected $plugin_url;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SLACKHOOKER_VERSION' ) ) {
			$this->version = SLACKHOOKER_VERSION;
		} else {
			$this->version = '4.0.0';
		}
		$this->plugin_name = 'vanilla-bean-slack-hooker';
        $this->plugin_url = '';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Vanilla_Bean_Slack_Hooker_Loader. Orchestrates the hooks of the plugin.
	 * - Vanilla_Bean_Slack_Hooker_i18n. Defines internationalization functionality.
	 * - Vanilla_Bean_Slack_Hooker_Admin. Defines all hooks for the admin area.
	 * - Vanilla_Bean_Slack_Hooker_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        /** vanilla bean legacy **/
        require_once plugin_dir_path( dirname(__FILE__)).'includes/legacy.php';

        /** options **/
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'exopite-simple-options/exopite-simple-options-framework-class.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-vanilla-bean-slack-hooker-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-vanilla-bean-slack-hooker-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-vanilla-bean-slack-hooker-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-vanilla-bean-slack-hooker-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-slack-hooker-message.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/notifier.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/legacy.php';

		$this->loader = new Vanilla_Bean_Slack_Hooker_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Vanilla_Bean_Slack_Hooker_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Vanilla_Bean_Slack_Hooker_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Vanilla_Bean_Slack_Hooker_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'vbean_menu', 1 );
        $this->loader->add_action( 'init', $plugin_admin, 'admin_menu', 999 );
        $this->loader->add_action('exopite_sof_do_save_options', $plugin_admin,'send_test_notification', 20);
        $this->loader->add_action( 'activated_plugin', $plugin_admin, 'plugin_activated', 999 );
        $this->loader->add_action( 'deactivated_plugin', $plugin_admin, 'plugin_deactivated', 999 );
        $this->loader->add_action( 'delete_plugin', $plugin_admin, 'plugin_deleted', 999 );
        //$this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'upgrader_process_complete', 999, 2 );
    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Vanilla_Bean_Slack_Hooker_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'transition_post_status', $plugin_public, 'post_statuschanged', 999, 3 );
        $this->loader->add_action( 'wp_insert_comment', $plugin_public, 'comment_inserted', 999, 2 );
        $this->loader->add_action( 'woocommerce_payment_complete', $plugin_public, 'woocommerce_payment_complete' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Vanilla_Bean_Slack_Hooker_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


    public static function debuglog($message, $shouldNotDie = true)
    {
        error_log(print_r($message, true));
        if ($shouldNotDie) {
            exit;
        }
    }

    public static function notification_send($message, $endpoints = false, $endpointOptions='defaults')
    {

        $msg = new Slack_Hooker_Message();
        $msg->endpoints=$endpoints;
        $msg->endpointOptions = $endpointOptions;
        $msg->payload = array(
        );
        if (is_array($message)) {
            $msg->payload['attachments'] = array($message);
        } else {
            $msg->payload['text'] = $message;
        }
        $result = $msg->sendit();
        return($result);
    }

    public static function custom_notify($atts){
        $a = shortcode_atts(array(
            'postto' => false,
            'alsoto' => false,
            'text' => false,
            'channel' => false,
            'username' => false,
            'icon' => false,
            'sendnow' => false
        ), $atts);
        $slack = self::custom_send($a['text'], $a['channel'], $a['username'], $a['icon'], $a['sendnow'],$a['postto'], $a['alsoto']);
        if (is_wp_error($slack)) {
            // 			bad url or something
        } elseif (!empty($slack['response'])) {

            if ($slack['body'] == 'ok') {
                // 				all good
            } else {
                // 				error defined by slack
            }
        } else {
            // 			failed - no response
        }
    }

    public static function custom_send($message, $channel = false, $username = false, $icon = false, $now = false, $urls = false, $extraurls = false){
        $endpoints = false;
        // concatenate $urls and $extraurls with $channel
        if (!empty($urls)) {
            if (!empty($extraurls)) {
                $urls = implode(',', $urls, $extraurls);
            }
            $endpoints = explode($urls);
            if (!empty($channel)) {
                $channel = str_replace("#", "", $channel);
                foreach ($endpoints as $endpoint) {
                    if (!strpos("#", $endpoint)) {
                        $endpoint .= "#" . $channel;
                    }
                }
            }
        }


        $msg = new Slack_Hooker_Message();
        //$msg->endpoints = $endpoints;
        $msg->payload = array(
            "username" => $username??false,
            'icon_emoji' => $icon ? str_replace(':', '', $icon . '') : false,
        );
        if (is_array($message)) {
            $msg->payload['attachments'] = array($message);
        } else {
            $msg->payload['text'] = $message;
        }
        return $msg->sendit();
    }




}
