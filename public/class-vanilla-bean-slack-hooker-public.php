<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.velvary.com.au
 * @since      1.0.0
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Vanilla_Bean_Slack_Hooker
 * @subpackage Vanilla_Bean_Slack_Hooker/public
 * @author     Velvary <info@velvary.com.au>
 */
class Vanilla_Bean_Slack_Hooker_Public {

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

    private $options;


    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
        //delete_option($plugin_name);
        $this->options = get_exopite_sof_option('vanilla-bean-slack-hooker');
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vanilla-bean-slack-hooker-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vanilla-bean-slack-hooker-public.js', array( 'jquery' ), $this->version, false );

	}

    // notification of post status change
    public function post_statuschanged($new_status, $old_status, $post){
        // justify sending the msg
        if(!isset($this->options['notifications']['post_status_change']['tabs'])){
            return false;
        }
        $postStatusChange = $this->options['notifications']['post_status_change']['tabs'];

        if( !isset($postStatusChange['post_status'][$new_status]) || $postStatusChange['post_status'][$new_status]!='yes'){
            return;
        }
        if($new_status == $old_status || ($post->post_type != 'post' && $post->post_type != 'page')){return;}
        // check for overrides in endpoints
        $endpoints = $postStatusChange['endpoints']??false;
        $endpointOptions = $postStatusChange['endpointOptions']??'default';

        $status = array();
        $status['draft'] = $postStatusChange['post_status']['draft_colour'] ?? '#6b6046';
        $status['pending'] = $postStatusChange['post_status']['pending_colour'] ?? '#00e2cc';
        $status['publish'] = $postStatusChange['post_status']['publish_colour'] ?? '#FFFF00';

        $message = self::build_post_status_message($status[$new_status],$old_status.' to '.$new_status, $post);
        return Vanilla_Bean_Slack_Hooker::notification_send($message,$endpoints,$endpointOptions);
    }

    // notification of comment inserted
    public function comment_inserted($comment_id, $comment_object)
    {
        // validate and justify

        if(!isset($this->options['notifications']['comments']['tabs'])){
            return false;
        }
        $commentsChange = $this->options['notifications']['comments']['tabs'];

        if(!isset($commentsChange['comment_status']['inserted'])||$commentsChange['comment_status']['inserted']!='yes'){
            return false;
        }
        $colour='#c0c0c0';
        if(isset($commentsChange['comment_status']['inserted_colour'])) {
            $colour = $commentsChange['comment_status']['inserted_colour'];
        }
        if(!$comment_object){
            return false;
        }

        // build message
        $post = get_post($comment_object->comment_post_ID);
        if(!$post || ($post->post_type!='post' && $post->post_type!='page')){
            return;
        }
        $author = $comment_object->comment_author;
        if (!empty($comment_object->comment_author_email)) {
            $author .= ' <' . $comment_object->comment_author_email . '> ';
        }
        $message = array(
            "color" => $colour,
            "pretext" => "Comment added by " . $comment_object->comment_author . " on " . get_site_url() . "",
            "author_name" => $author,
            "author_link" => '',
            "title" => $post->post_title,
            "title_link" => get_permalink($post->ID),
            "text" => $comment_object->comment_content,
            "footer" => SLACKHOOKER_FOOTERTEXT,
            "footer_icon" => SLACKHOOKER_LOGO,
            "ts" => time()
        );
        if (!empty($comment_object->comment_author_email)) {
            $message['author_link'] = 'mailto:'.$comment_object->comment_author_email;
        }
        $endpoints = $commentsChange['endpoints']??false;
        $endpointOptions = $commentsChange['endpointOptions']??'default';


        return Vanilla_Bean_Slack_Hooker::notification_send($message,$endpoints,$endpointOptions);

    }

    // notification of woocommerce sale

    public function woocommerce_payment_complete($order_id){


        if(!isset($this->options['notifications']['woo_commerce']['tabs'])){
            return false;
        }
        $woo = $this->options['notifications']['woo_commerce']['tabs'];

        if(!isset($woo['hook']['payment_completed'])||$woo['hook']['payment_completed']!='yes'){
            return false;
        }
        $colour='#bfff00';
        if(isset($woo['hook']['payment_completed_colour'])) {
            $colour = $woo['hook']['payment_completed_colour'];
        }


        // build message
        $order = wc_get_order($order_id);
        //$user = $order->get_user();
        $orderurl = admin_url('post.php?post=' . absint($order_id) . '&action=edit');
        //if( $user ){
        // 			do something with the user
        //}
        $message = array(
            "color" => $colour,
            "pretext" => "Successful purchase at " . get_site_url() . "!",
            //"		author_name" => '',
            //"author_link" => '',
            "title" => html_entity_decode(get_woocommerce_currency_symbol()) . $order->get_total(),
            "title_link" => $orderurl,
            // "text" => $order->get_formatted_shipping_address(),
            "footer" => SLACKHOOKER_FOOTERTEXT,
            "footer_icon" => SLACKHOOKER_LOGO,
            "ts" => time()
        );

        $endpoints = $woo['endpoints']??false;
        $endpointOptions = $woo['endpointOptions']??'default';

        Vanilla_Bean_Slack_Hooker::notification_send($message,$endpoints,$endpointOptions);
        return true;
    }

    // build post status attachment message
    public static function build_post_status_message($color,$status,$post){
        $current_user = wp_get_current_user();
        $username = empty($current_user) ? 'System' : $current_user->display_name;
        $author = get_user_by('id', $post->post_author);
        $message = array(
            "color" => $color,
            "pretext" => "Post status changed from ".$status." by " . $username . " on " . get_site_url() . "",
            "author_name" => $author->display_name,
            "title" => $post->post_title,
            "title_link" => get_permalink($post->ID),
            "text" => $post->post_excerpt,
            "footer" => SLACKHOOKER_FOOTERTEXT,
            "footer_icon" => SLACKHOOKER_LOGO,
            "ts" => time()
        );
        return $message;
    }



}
