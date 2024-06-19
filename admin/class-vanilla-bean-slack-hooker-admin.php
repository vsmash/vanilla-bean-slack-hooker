<?php

// require once the legacy.php file
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/legacy.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/admin
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

    private $options;

    private $endpointoverrides;
    private $tabcount=0;

	public function __construct( $plugin_name, $version ) {
        $this->options = get_exopite_sof_option($plugin_name);
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	    $this->endpointoverrides = $this->buildEndpointOverrides();
    }


    private function buildEndpointOverrides(){
        $or =
            array(
            array(
                    'id'      => 'endpointOptions',
                    'type'    => 'radio',
                    'title'   => 'Endpoints',
                    'description' => 'Specified endpoints can be added to or replace parent defaults.<br />Note: If there are no endpoints specified, parent defaults will be used.',
                    'options' => array(
                        'defaults'   => 'Use defaults',
                        'override'    => 'Use these endpoints instead of global defaults',
                        'add'    => 'Use these endpoints in addition to global defaults',
                    ),
                    'default' => 'defaults',     // optional
                    'style'    => 'fancy', // optional
                    'layout'    => 'horizontal', // optional
                ),
            array(
            'type'    => 'group',
            'id'      => 'endpoints',
            'title'   => esc_html__( 'Specify additional/replacement endpoints here', 'vmsh' ),
//            'help'       => esc_html__('Leave empty to use default endpoint(s).','vanilla-bean-slack-hooker').'<br />&nbsp;<br />'.esc_html__('Format can be endpoint or channel or both','vanilla-bean-slack-hooker').'<br />'.esc_html__('eg:','vanilla-bean-slack-hooker').'<br />'.esc_html__('https://yourendpoint','vanilla-bean-slack-hooker').'<br />'.esc_html__('yourchannel (uses default enpoint)','vanilla-bean-slack-hooker').'<br />'.esc_html__('https://yourendpoint#yourchannel','vanilla-bean-slack-hooker'),
            'options' => array(
                'repeater'          => true,
                'accordion'         => false,
                'button_title'      => esc_html__( 'Add new', 'vanilla-bean-slack-hooker' ),
                'group_title'       => esc_html__( 'Override endpoints', 'vanilla-bean-slack-hooker' ),
                'limit'             => 5,
                'sortable'          => true,
                'mode'              => 'compact', // only repeater
            ),
            'fields'  => array(

                array(
                    'id'      => 'defaultendpoints_text_1',
                    'type'    => 'text',
                    'attributes' => array(
                        // mark this field az title, on type this will change group item title
                        'data-title' => 'Endpoint',
                        'placeholder' => esc_html__( 'https://endpoint#channel#here or email address', 'vanilla-bean-slack-hooker' ),

                    ),

                ),


            ),
        ));
        return $or;
    }

    private function tabWrapper($array, $title, $tabtitle, $tabid, $closed=true){
        $or = $this->buildEndpointOverrides();
        $this->tabcount+=1;
        $tabs = array(
            'options' => array(
                'icon'   => 'fa fa-star',
                'title'  => $title,
                'closed' => $closed,  // optional, this accordion item is open in page load
            ),
            'fields' => array(
                // post status change
                array(
                    'type'    => 'fieldset',
                    'id'      => $tabid,
                    //'title'   => esc_html__( 'Post Status', 'vanilla-bean-slack-hooker' ),
                    //'description' => 'Notify when posts and pages are published',
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields'  => array(
                        array(
                            'id'          => 'tabs',
                            'type'        => 'tab',
                            'options'     => array(
                                'equal_width' => true,        // optional, true or false
                            ),
                            'tabs'        => array(

                                array(
                                    'icon'   => 'fa fa-star', // optional
                                    'title'  => $tabtitle, $this->plugin_name,
                                    'fields' => array(


                                        // statuses

                                    ),
                                ),

                                // optional
                                array(
                                    'title'  => esc_html__( 'Endpoints', $this->plugin_name ),
                                    'icon'   => 'fa fa-star', // optional
                                    'fields' => array(

                                        // override endpoints
                                        $this->buildEndpointOverrides()[0],
                                        $this->buildEndpointOverrides()[1],
                                    ),
                                ),

                                // other tabs...

                            ),
                        ),


                    ),
                ),

            ),
        );
        foreach($array as $key => $value){
            $tabs['fields'][0]['fields'][0]['tabs'][0]['fields'][] = $value;
        }
        return $tabs;
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vanilla_Bean_Slack_Hooker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vanilla_Bean_Slack_Hooker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vanilla-bean-slack-hooker-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vanilla_Bean_Slack_Hooker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vanilla_Bean_Slack_Hooker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vanilla-bean-slack-hooker-admin.js', array( 'jquery' ), $this->version, false );

	}




    public function vbean_menu(){
        if (empty($GLOBALS['admin_page_hooks']['vanillabeans-settings'])) {

            $menu = add_menu_page(
                'Vanilla Beans',
                'Vanilla Beans',
                'manage_options',
                'vanillabeans-settings', //slug
                '\VanillaBeans\LiveSettings',
                SLACKHOOKER_DIR_URL.'/vicon.png',
                4
            );
        }

    }


/**
     * Instantiate the admin menu.
     *
     * @since    4.0.0
     */



    public function admin_menu(){


        if(!isset($this->options['defaultendpoints'])||!is_array($this->options['defaultendpoints'])){
            $fields[] = array(
                'name'   => 'notices',
                'title'  => 'Notice',
                'icon'   => 'fa fa-exclamation-circle',
                'fields' => array(
                    array(
                        'type'    => 'notice',
                        'class'   => 'warning',
                        'content' => 'You currently do not have any default endpoints. This is okay if you specify an endpoint for each notification switched on.',
                    ),

                ),
            );

        }
        $is_submenu = true;
        $parent = 'vanillabeans-settings';
        // sub arrays
        $test =array(
            'options' => array(
                'icon'   => 'fa fa-star',
                'title'  => 'Plugin Changes',
                'description' => esc_html__( 'Notify when a plugin is activated, deactivated, updated or removed','vanilla-bean-slack-hooker')
            ),
            'fields' => array(

                // plugin status change
                array(
                    'type'    => 'fieldset',
                    'id'      => 'plugin_change',
                    //'title'   => esc_html__( 'Plugin Status Change', 'vanilla-bean-slack-hooker' ),
                    //'description' => 'Notify when a plugin is activated, deactivated, updated or removed',
                    //'description' => esc_html__( 'Notify when a plugin is activated, deactivated, updated or removed','vanilla-bean-slack-hooker'),
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields'  => array(
                        // override endpoints
                        $this->buildEndpointOverrides()[0],
                        $this->buildEndpointOverrides()[1],
                        array(
                            'id'      => 'plugin_change_onoff',
                            'type'    => 'switcher',
                            'title'   => esc_html__( 'Active', 'vanilla-bean-slack-hooker' ),
                            'default' => 'no',
                        ),
                        array(
                            'type'    => 'fieldset',
                            'id'      => 'colours',
                            'title'   => esc_html__( 'Status colours', 'vanilla-bean-slack-hooker' ),
                            'options' => array(
                                'cols' => 3,
                            ),
                            'fields'  => array(
                                array(
                                    'id'     => 'install',
                                    'type'   => 'color',
                                    'title'  => 'Installed', // optional
                                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                                    'default' => '#006600',            // optional
                                    'type'   => 'color_wp',
                                ),
                                array(
                                    'id'     => 'activated',
                                    'type'   => 'color',
                                    'title'  => 'Activated', // optional
                                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                                    'default' => '#00ff00',            // optional
                                    'type'   => 'color_wp',
                                    // 'default' => 'rgba(255,0,0,1)', // optional
                                ),
                                array(
                                    'id'     => 'update',
                                    'type'   => 'color',
                                    'title'  => 'Updated', // optional
                                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                                    'default' => '#00bb00',            // optional
                                    'type'   => 'color_wp',
                                    // 'default' => 'rgba(255,0,0,1)', // optional
                                ),
                                array(
                                    'id'     => 'deactivated',
                                    'type'   => 'color',
                                    'title'  => 'Deactivated', // optional
                                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                                    'default' => '#606060',            // optional
                                    'type'   => 'color_wp',
                                    // 'default' => 'rgba(255,0,0,1)', // optional
                                ),
                                array(
                                    'id'     => 'deleted',
                                    'type'   => 'color',
                                    'title'  => 'Deleted', // optional
                                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                                    'default' => '#ff0000',            // optional
                                    'type'   => 'color_wp',
                                    // 'default' => 'rgba(255,0,0,1)', // optional
                                ),
                            ),
                        ),

                    ),
                ),

            ),
        );

        $pluginchanges =array(
            array(
                'id'      => 'plugin_change_onoff',
                'type'    => 'switcher',
                'title'   => esc_html__( 'Active', 'vanilla-bean-slack-hooker' ),
                'default' => 'no',
            ),
            array(
                'type'    => 'fieldset',
                'id'      => 'colours',
                'title'   => esc_html__( 'Status colours', 'vanilla-bean-slack-hooker' ),
                'options' => array(
                    'cols' => 3,
                ),
                'fields'  => array(
                    array(
                        'id'     => 'install',
                        'type'   => 'color',
                        'title'  => 'Installed', // optional
                        // 'picker' => 'html5',            // optional, display native HTML5 color picker
                        'default' => '#006600',            // optional
                        'type'   => 'color_wp',
                    ),
                    array(
                        'id'     => 'activated',
                        'type'   => 'color',
                        'title'  => 'Activated', // optional
                        // 'picker' => 'html5',            // optional, display native HTML5 color picker
                        'default' => '#00ff00',            // optional
                        'type'   => 'color_wp',
                        // 'default' => 'rgba(255,0,0,1)', // optional
                    ),
                    array(
                        'id'     => 'update',
                        'type'   => 'color',
                        'title'  => 'Updated', // optional
                        // 'picker' => 'html5',            // optional, display native HTML5 color picker
                        'default' => '#00bb00',            // optional
                        'type'   => 'color_wp',
                        // 'default' => 'rgba(255,0,0,1)', // optional
                    ),
                    array(
                        'id'     => 'deactivated',
                        'type'   => 'color',
                        'title'  => 'Deactivated', // optional
                        // 'picker' => 'html5',            // optional, display native HTML5 color picker
                        'default' => '#606060',            // optional
                        'type'   => 'color_wp',
                        // 'default' => 'rgba(255,0,0,1)', // optional
                    ),
                    array(
                        'id'     => 'deleted',
                        'type'   => 'color',
                        'title'  => 'Deleted', // optional
                        // 'picker' => 'html5',            // optional, display native HTML5 color picker
                        'default' => '#ff0000',            // optional
                        'type'   => 'color_wp',
                        // 'default' => 'rgba(255,0,0,1)', // optional
                    ),
                ),
            ),
        );
        $woo = array(
            array(
            'type'    => 'fieldset',
            'id'      => 'hook',
            'title'   => esc_html__( 'Statuses:', 'vanilla-bean-slack-hooker' ),
            'options' => array(
                'cols' => 6,
            ),
            'fields'  => array(
                array(
                    'id'      => 'payment_completed',
                    'type'    => 'switcher',
                    'title'   => esc_html__( 'Sold', 'vanilla-bean-slack-hooker' ),
                    'default' => 'no',

                ),
                array(
                    'id'     => 'payment_completed_colour',
                    'type'   => 'color',
                    'title'  => 'Colour', // optional
                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                    'default' => '#bfff00',            // optional
                    'type'   => 'color_wp',
                ),
            ),
        ));
        $comments = array(array(
            'type'    => 'fieldset',
            'id'      => 'comment_status',
            'title'   => esc_html__( 'Status', 'vanilla-bean-slack-hooker' ),
            'options' => array(
                'cols' => 6,
            ),
            'fields'  => array(
                array(
                    'id'      => 'inserted',
                    'type'    => 'switcher',
                    'title'   => esc_html__( 'Posted', 'vanilla-bean-slack-hooker' ),
                    'default' => 'no',

                ),
                array(
                    'id'     => 'inserted_colour',
                    'type'   => 'color',
                    'title'  => 'Colour', // optional
                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                    'default' => '#c0c0c0',            // optional
                    'type'   => 'color_wp',
                ),
            ),
        ));
        $post_status = array(array(
            'type'    => 'fieldset',
            'id'      => 'post_status',
            'title'   => esc_html__( 'Status', 'vanilla-bean-slack-hooker' ),
            'options' => array(
                'cols' => 6,
            ),
            'fields'  => array(
                array(
                    'id'      => 'publish',
                    'type'    => 'switcher',
                    'title'   => esc_html__( 'Published', 'vanilla-bean-slack-hooker' ),
                    'default' => 'no',

                ),
                array(
                    'id'     => 'publish_colour',
                    'type'   => 'color',
                    'title'  => 'Colour', // optional
                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                    'default' => '#ffff00',            // optional
                    'type'   => 'color_wp',
                ),
                array(
                    'id'      => 'pending',
                    'type'    => 'switcher',
                    'title'   => esc_html__( 'Pending', 'vanilla-bean-slack-hooker' ),
                    'default' => 'no',

                ),
                array(
                    'id'     => 'pending_colour',
                    'type'   => 'color',
                    'title'  => 'Colour', // optional
                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                    'default' => '#999900',            // optional
                    'type'   => 'color_wp',
                ),
                array(
                    'id'      => 'draft',
                    'type'    => 'switcher',
                    'title'   => esc_html__( 'Draft', 'vanilla-bean-slack-hooker' ),
                    'default' => 'no',

                ),
                array(
                    'id'     => 'draft_colour',
                    'type'   => 'color',
                    'title'  => 'Colour', // optional
                    // 'picker' => 'html5',            // optional, display native HTML5 color picker
                    'default' => '#808080',            // optional
                    'type'   => 'color_wp',
                ),
            ),
        ));

//        if (empty($GLOBALS['admin_page_hooks'][$parent])) {
////            //create new top-level menu
//            $is_submenu = false;
//            $parent = 'admin.php?page='.$this->plugin_name;
//        }

        $config_submenu = array(
            'type'              => 'menu',                          // Required, menu or metabox
            'icon'              => plugin_dir_url( __DIR__ ).'vicon.png',
            'id'                => $this->plugin_name,              // Required, meta box id,
            // unique per page, to save:
            // get_option( id )
            'parent'            => $parent,                   // Required, sub page to your options page
            'submenu'           => $is_submenu,                            // Required for submenu
            'title'             => 'Slack Hooker',               // The name in the WordPress menu and the title of the Option page
            'option_title'      => 'Slack Hooker',               // The title of the Option page, this will override 'title'
            'menu_title'        => 'Slack Hooker',
            'capability'        => 'manage_options',                // The capability needed to view the page
            'plugin_basename'   =>  plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' ),
            // 'tabbed'            => false,                        // is tabbed or not
                                                            // Note: if only one section then
                                                            // Tabs are disabled.
            //'settings_link'     => array('https://www.velvary.com.au'),
            'settings-link'     =>  'vanilla-bean-slack-hooker',

            'multilang'         => false,                         // Disable mutilang support, default: true
            'position'          => 3,
            'description'       => 'fubarian',
        );


        // defaults
        $fields[] = array(
            'name'   => 'defaults',
            'title'  => 'Defaults',
            'icon'   => 'dashicons-admin-generic',
            'fields' => array(
                // endpoints
                array(
                    'type'    => 'group',
                    'id'      => 'defaultendpoints',
                    'title'   => esc_html__( 'Default Endpoints', 'vmsh' ),
                    'description' => '',
                    'after'       => 'eg: <b>https://endpoint, https://endpoint#channel, https://endpoint#channel#here,<br/>https://endpoint##here</b> <i>(no channel)</i><br />Channel is only needed for legacy slack apps and mattermost. You can use "here" or "channel" or "everyone" to use the alert option.<br />You may also choose to use an email address if you prefer it to slack or mattermost',
                    'options' => array(
                        'repeater'          => true,
                        'accordion'         => false,
                        'button_title'      => esc_html__( 'Add new', 'vanilla-bean-slack-hooker' ),
                        'group_title'       => esc_html__( 'Accordion Title', 'vanilla-bean-slack-hooker' ),
                        'limit'             => 5,
                        'sortable'          => true,
                        'mode'              => 'compact', // only repeater
                    ),
                    'fields'  => array(

                        array(
                            'id'      => 'defaultendpoints_text_1',
                            'type'    => 'text',
                            'attributes' => array(
                                // mark this field az title, on type this will change group item title
                                'data-title' => 'Endpoint',
                                'placeholder' => esc_html__( 'https://yourdomain.cloud.mattermost.com/hooks/xxx#channel or email address', 'vanilla-bean-slack-hooker' ),
                            ),
                        ),


                    ),
                ),
                // default username
                array(
                    'id'          => 'default_username',
                    'type'        => 'text',
                    'title'       => 'Default User',
                    //'before'      => 'Text Before',
                    //'after'       => 'Text After',
                    'class'       => 'text-class',
                    //'attributes'  => 'data-test="test"',
                    //'description' => 'Description',
                    //'default'     => 'Default Text',
                    //'help'        => 'Go get your google map api key',
                ),
                // default icon
                array(
                    'id'          => 'default_icon',
                    'type'        => 'text',
                    'title'       => 'Default icon',
                    //'before'      => 'Text Before',
                    //'after'       => 'Text After',
                    'class'       => 'text-class',
                    //'attributes'  => 'data-test="test"',
                    //'description' => 'Description',
                    //'default'     => 'Default Text',
                    //'help'        => 'Go get your google map api key',
                ),
                // limit frequency
                array(
                    'type'    => 'fieldset',
                    'id'      => 'limit_frequency',
                    'title'   => esc_html__( 'Limit Frequency', 'vanilla-bean-slack-hooker' ),
                    'options' => array(
                        'cols' => 3,
                    ),
                    'description' => 'Limit Send frequency to prevent spam',
                    'after'     => 'This is a safeguard against your site firing thousands of messages due to heavy traffic or if you are using it in conjunction with error reporting. It is highly recommended that you leave this switched on.',
                    'fields'    => array(
                        array(
                            'id'      => 'limitfrequency',
                            'type'    => 'switcher',
                            'title'   => esc_html__( 'Limit', 'vanilla-bean-slack-hooker' ),
                            'default' => 'yes',
                        ),
                        array(
                            'id'      => 'sendsper',
                            'type'    => 'number',
                            'title'   => 'To',
                            'default' => '1',                                     // optional
                            // 'unit'    => '$',                                      // optional
                            'after'   => 'Send(s)', // optional
                            'min'     => '1',                                      // optional
                            'max'     => '1000',                                     // optional
                            // 'step'    => '2',                                      // optional
                        ),
                        array(
                            'id'      => 'perseconds',
                            'type'    => 'number',
                            'title'   => 'Per',
                            'default' => '1',                                     // optional
                            // 'unit'    => '$',                                      // optional
                            'after'   => 'seconds', // optional
                            'min'     => '1',                                      // optional
                            'max'     => '30',                                     // optional
                            // 'step'    => '2',                                      // optional
                        ),

                    )
                ),
                                         array(
                                            'id'      => 'omit_cron',
                                            'type'    => 'switcher',
                                            'title'   => esc_html__( 'Do Not use Cron Schedule', 'vanilla-bean-slack-hooker' ),
                                            'default' => 'no',
                                            'after' => 'By default, messages are added to the cron schedule rather than interrupting the page load. This can be overridden here.'
                                        ),
            ),
        );

        // notifications
        $fields[] = array(
            'name'   => 'notifications',
            'title'  => 'Notifications',
            'icon'   => 'dashicons-megaphone',


            'fields' => array(
                array(
                    'id'          => 'notifications',
                    'type'        => 'accordion',
                    //'title'       => esc_html__( 'Accordion', 'plugin-name' ),
                    //'title'       => esc_html__( 'Accordion', 'plugin-name' ),
                    'options' => array(
                        'allow_all_open' => false,  // optional, minimum one is always open
                    ),
                    'sections'        => array(
                        // post status
                        $this->tabWrapper($post_status,'Post Status', 'Post Status Changes','post_status_change', false),
                        //$post_status,

                        // comments activity
                        $this->tabWrapper($comments,'Comments Activity', 'Status Changes','comments'),

                        // woo commerce
                        $this->tabWrapper($woo,'Woo Commerce', 'Status Changes','woo_commerce'),

                        // plugin changes
                        $this->tabWrapper($pluginchanges,'Plugin Changes', 'Status Alerts and Colours','plugin_change'),

                    ),
                ),
            ),
        );

        // test message
        $fields[] = array(
            'name'   => 'testnotifications',
            'title'  => 'Test Send',
            'icon'   => 'dashicons-performance',
            'fields' => array(

                        array(
                            'id'      => 'send_test',
                            'type'    => 'switcher',
                            'title'   => esc_html__( 'Active', 'vanilla-bean-slack-hooker' ),
                            'default' => 'no',
                            'after' => 'Send a test notification'
                        ),
                        array(
                            'id'          => 'textarea_testcontent',
                            'type'        => 'textarea',
                            'title'       => 'Test message content',
                            'attributes'    => array(
                            'placeholder' => 'This is a test message from '.get_site_url(),
                            'default'   =>  'This is a test message from '.get_site_url(),
                        ),

                ),

                    ),
        );

        $options_panel = new Exopite_Simple_Options_Framework( $config_submenu, $fields );
    }
    /**
     * Available fields:
     * - ACE field
     * - attached
     * - backup
     * - button
     * - botton_bar
     * - card
     * - checkbox
     * - color
     * - content
     * - date
     * - editor
     * - group
     * - hidden
     * - image
     * - image_select
     * - meta
     * - notice
     * - number
     * - password
     * - radio
     * - range
     * - select
     * - switcher
     * - tap_list
     * - text
     * - textarea
     * - upload
     * - video mp4/oembed
     *
     * Add your fields, eg.:
     */




    // send a test from admin
    public function send_test_notification($obj){
        if($obj['send_test']=='yes'){
             Vanilla_Bean_Slack_Hooker::notification_send($obj["textarea_testcontent"],false,'default');
        }
        return $obj;
    }

    /**
     *  Plugin notificiations
     *
     * */


    // plugin installed is in notifier.php
    // plugin upgraded is also in notifier.php
    // because we have not yet figured why this class doesn't pass the two args properly

    private function sendPluginMessage($colour,$status,$messageStatus, $plugin_file){
        if(!isset($this->options['notifications']['plugin_change']['tabs'])){return false;}
        $plug = $this->options['notifications']['plugin_change']['tabs'];
        if($plug['plugin_change_onoff']!='yes'){
            return false;
        }
        $colour = $this->get_colour($plug,$colour,$status);
        $message = $this->build_plugin_message($colour,$messageStatus, $plugin_file);
        $endpoints = $plug['endpoints'] ?? false;
        $endpointOptions = $plug['endpointOptions'] ?? 'defaults';
        $slack = Vanilla_Bean_Slack_Hooker::notification_send( $message, $endpoints, $endpointOptions );
    }


    // plugin activated
    public function plugin_activated($plugin_file){
        $this->sendPluginMessage('#00ff00','activated','Activated',$plugin_file);
    }

    // plugin deactivated
    public function plugin_deactivated($plugin_file){
        $this->sendPluginMessage('#a0a0a0','deactivated','Deactivated',$plugin_file);
    }

    // plugin deleted
    public function plugin_deleted($plugin_file){
        $this->sendPluginMessage('#ff0000','deleted','Deleted',$plugin_file);
    }

    // plugin installed or upgraded
    public function upgrader_process_complete($plugin_file, $upgrader){
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
        $name = get_plugin_data($upgrader['plugins'][0]);

        $colour = $obj['colours'][$upgrader['action']];

        $this->sendPluginMessage($colour,$upgrader['action'],$upgrader['action'], $name);
    }


    // sets attachment colour
    private function get_colour($plug,$default,$index){
        return $plug['colours'][$index] ?? $default;
    }

    // build plugin attachment message
    public static function build_plugin_message($color,$status,$plugin_file){
        $current_user = wp_get_current_user();
        $username = empty($current_user) ? 'System' : $current_user->display_name;
        // if $plugin_file is a string, then it's a plugin file name
        // if it's an array, then it's a plugin data array
        $plugin_name = is_array($plugin_file)? $plugin_file['Name'] : get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file.'', false)['Name'];

        if(is_array($plugin_file)){
            $plugin = $plugin_file;
        }else{
            $plugin = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file.'', false);
        }

        $message = array(
            "color" => $color,
            "pretext" => "Plugin ".$status." on " . get_site_url() . " by " . $username,
            "author_name" => $plugin["AuthorName"],
            "author_link" => $plugin["AuthorURI"],
            "title" => html_entity_decode($plugin["Name"]),
            "title_link" => $plugin["PluginURI"],
            "text" => html_entity_decode($plugin["Description"]),
            //  "thumb_url" => "http://placekitten.com/g/200/200",
            "footer" => SLACKHOOKER_FOOTERTEXT,
            "footer_icon" => SLACKHOOKER_LOGO,
            "ts" => time()
        );
        return $message;
    }



}
