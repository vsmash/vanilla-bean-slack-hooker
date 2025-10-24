<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/includes
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array(
            //['hook'=>'admin_menu', 'component'=>'create_menu', 'priority'=>0],
        );
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

         // Standard hook for admin updates (this works fine)
         add_action('upgrader_process_complete', function($upgrader, $hook_extra) {
             \VanillaBeans\SlackHooker\plugin_upgrader($upgrader, $hook_extra);
             
             // Mark these plugins as already notified to prevent duplicate notifications from version detection
             if (isset($hook_extra['plugin'])) {
                 $notified = get_option('_slackhooker_already_notified', array());
                 $notified[$hook_extra['plugin']] = time();
                 update_option('_slackhooker_already_notified', $notified);
             } elseif (isset($hook_extra['plugins'])) {
                 $notified = get_option('_slackhooker_already_notified', array());
                 foreach ($hook_extra['plugins'] as $plugin) {
                     $notified[$plugin] = time();
                 }
                 update_option('_slackhooker_already_notified', $notified);
             }
         }, 10, 2);
         
         // For WP-CLI and auto-updates: Detect version changes on every load
         add_action('plugins_loaded', function() {
             $current_plugins = get_plugins();
             $stored_plugins = get_option('_slackhooker_plugin_versions', array());
             
             // Store the current update context (WP-CLI, cron, etc.)
             $update_context = null;
             if (defined('WP_CLI') && WP_CLI) {
                 // Get the configured CLI username
                 $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
                 $update_context = isset($options['cli_username']) ? $options['cli_username'] : 'WP-CLI';
                 update_option('_slackhooker_update_context', $update_context);
             } elseif (defined('DOING_CRON') && DOING_CRON) {
                 // Get the configured CLI username (used for auto-updates too)
                 $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
                 $update_context = isset($options['cli_username']) ? $options['cli_username'] : 'Auto-update';
                 update_option('_slackhooker_update_context', $update_context);
             }
             
             // First time - just store current state
             if (empty($stored_plugins)) {
                 update_option('_slackhooker_plugin_versions', $current_plugins);
                 return;
             }
             
             // Compare to detect updates that happened
             $updated_plugins = array();
             foreach ($current_plugins as $plugin_file => $plugin_data) {
                 if (isset($stored_plugins[$plugin_file])) {
                     $old_version = $stored_plugins[$plugin_file]['Version'];
                     $new_version = $plugin_data['Version'];
                     
                     if (version_compare($new_version, $old_version, '>')) {
                         $updated_plugins[$plugin_file] = $plugin_data;
                     }
                 } elseif (!isset($stored_plugins[$plugin_file])) {
                     // New plugin installed
                     $updated_plugins[$plugin_file] = $plugin_data;
                 }
             }
             
             // Update stored versions for next check
             update_option('_slackhooker_plugin_versions', $current_plugins);
             
             // Send notifications for updated plugins
             if (!empty($updated_plugins)) {
                 $stored_context = get_option('_slackhooker_update_context', null);
                 
                 // If no context was stored, use the configured CLI username
                 if (!$stored_context) {
                     $options = get_exopite_sof_option('vanilla-bean-slack-hooker');
                     $stored_context = isset($options['cli_username']) ? $options['cli_username'] : 'Auto-update';
                 }
                 
                 // Get list of already notified plugins
                 $already_notified = get_option('_slackhooker_already_notified', array());
                 $current_time = time();
                 
                 foreach ($updated_plugins as $plugin_file => $plugin_data) {
                     // Skip if this plugin was already notified in the last 60 seconds
                     if (isset($already_notified[$plugin_file]) && ($current_time - $already_notified[$plugin_file]) < 60) {
                         continue;
                     }
                     
                     $hook_extra = array(
                         'type' => 'plugin',
                         'action' => 'update',
                         'plugin' => $plugin_file,
                         'context' => $stored_context
                     );
                     
                     \VanillaBeans\SlackHooker\plugin_upgrader(null, $hook_extra);
                 }
                 
                 // Clean up old entries (older than 5 minutes)
                 foreach ($already_notified as $plugin => $timestamp) {
                     if ($current_time - $timestamp > 300) {
                         unset($already_notified[$plugin]);
                     }
                 }
                 update_option('_slackhooker_already_notified', $already_notified);
                 
                 // Clear the context after use
                 delete_option('_slackhooker_update_context');
             }
         }, 0); // Priority 0 to run as early as possible
	}

}
