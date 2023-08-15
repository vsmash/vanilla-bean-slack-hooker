<?php


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
class Slack_Hooker_Message
{

    private $apiargs;

    public $payload;

    public $endpoints;

    private $options;

    public $endpointOptions;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->options = get_exopite_sof_option('vanilla-bean-slack-hooker');
        $this->apiargs = array(
            'timeout' => 45,
            'blocking' => true,
            'headers' => array(),
            'cookies' => array()
        );
    }

    // push it out
    public function sendit()
    {
        if(!$this->payload){
            return false;
        }

        $this->processEndpoints();
        if(!is_array($this->endpoints) || count($this->endpoints)<1){return false;}
        // look for username in the payload or use the default
        if((!isset($this->payload['username']))&&isset($this->options['default_username'])){
            $this->payload['username'] = $this->options['default_username'];
        }
        // look for emoji in the payload or use the default
        if((!isset($this->payload['icon_emoji']))&&isset($this->options['default_icon'])){
            $this->payload['icon_emoji']=$this->options['default_icon'];
        }
        $output = array();
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint["channel"]) {
                $this->payload['channel'] = '#' . $endpoint['channel'];
            }
            // check for slack app
            if($endpoint["alert"]){
                $this->payload['text']='<!'.$endpoint["alert"].'>'.$this->payload['text'];
            }
            if($endpoint["icon"]){
                $this->payload['icon_emoji']=$endpoint["icon"];
            }
            elseif($endpoint["emoji"]){
                $this->payload['icon_emoji']=$endpoint["emoji"];
            }
            if(str_starts_with($endpoint["url"],'https://hooks.slack.com/services/')){
                $this->payload['text']=$this->payload['username'].': '.$this->payload['text'];
            }
            $this->apiargs['body'] = array('payload' => json_encode($this->payload));
            if($this->canSend($endpoint['url'])){
                // if $endpoint['url'] is an email address, send it to the email address
                $isemail =filter_var($endpoint['url'], FILTER_VALIDATE_EMAIL);

                if($isemail){
                    $output[] = wp_mail($endpoint['url'], $this->payload['username'], $this->payload['text']);
                    continue;
                }
                if($this->options['omit_cron']=='yes') {
                    $output[] = wp_remote_post($endpoint['url'], $this->apiargs);
                }else{
                    $this->cronSend($endpoint['url'], $this->apiargs);
                }
            }
        }
        return $output;
    }

    // function to cron shedule remote post
    public function cronSend($url, $payload)
    {
        wp_schedule_single_event(time(), 'vbeanschedule', array($url, $payload));
    }


    public static function remotePost($url, $args)
    {
        $response = wp_remote_post($url, $args);
        return wp_remote_retrieve_body($response);
    }
    // sorts out the endpoints into an array with separated channels
    private function processEndpoints()
    {
        $defaults = $this->options['defaultendpoints'];
        $eps = $this->endpoints;
        // if endpointOptions is not set or endpoint options = 'default' $eps=defaults
        if (!isset($this->endpointOptions) || $this->endpointOptions == 'default' || $this->endpointOptions == 'defaults') {
            $eps = $defaults;
        } elseif($this->endpointOptions=='add'&&is_array($eps)&&is_array($defaults)) {
            $eps = array_merge($eps, $defaults);
        }

        if(!is_array($eps)){return false;}
        $formattedEndpoints = array();

       foreach ($eps as $endpoint) {
            $ep = explode("#", $endpoint['defaultendpoints_text_1']);
            $epx = array(
                'url' => $ep[0],
                'channel' => false,
                'alert' => false,
                'emoji' => false
            );
            if(count($ep)>1){
                $epx['channel'] = $ep[1];
                if(count($ep)>2){
                    $epx['alert'] = $ep[2];
                    if(count($ep)>3){
                        if(!str_starts_with($ep[3] ,'http') ){
                            $epx['emoji'] = ':'.str_replace(':','',$ep[3]).':';
                        }
                        $epx['emoji'] = $ep[3];
                    }
                }
            }
            if(!empty($this->payload['channel'])){
                $epx['channel'] = $this->payload['channel'];
            }
            $formattedEndpoints[]=$epx;
        }
        error_log("\033[0;53m");
        error_log(print_r($formattedEndpoints,true));
        error_log("\033[0m");
        $this->endpoints = $formattedEndpoints;
    }

    // validate against url and maximum uses per x seconds
    private function canSend($url)
    {
        $found = false;
        $maxper = (int)$this->options['limit_frequency']['sendsper'];
        $perseconds = (int)$this->options['limit_frequency']['perseconds'];
        $thishit = time();
        $limit = $this->options['limit_frequency']['limitfrequency'];
//        /return true;
        $msg = 'Maximum '.$maxper.' per '.$perseconds.' seconds'.PHP_EOL;
        if ($limit == 'no') {
            return true;
        }
        $lastoption = get_option('vbean_slack_hooker_lastmessage');
        if (empty($lastoption)) {
            $lasts = array(array('url' => $url, 'lasthit' => $thishit, 'total' => 1));
            $save = serialize($lasts);
            update_option('vbean_slack_hooker_lastmessage', $save);
            return true;
        } else {
            $lasts = maybe_unserialize($lastoption);
            for ($i = 0; $i < count($lasts); $i++) {
                if ($lasts[$i]['url'] == $url) { // we have a record
                    $found = true;
                    $lasttime = (int)$lasts[$i]['lasthit'];
                    $total = (int)$lasts[$i]['total'];
                    $seconds = $thishit - $lasttime;
                    $msg = $thishit.' - '.$lasttime. ' = '.$seconds.PHP_EOL;

                    if ($seconds > $perseconds) { // we can reset and send
                       // $msg.= $seconds.' is more than '.$perseconds.' so we reset'.PHP_EOL;
                        $lasts[$i]['total'] = 1;
                        $lasts[$i]['lasthit'] = $thishit;
                        $save = serialize($lasts);
                        update_option('vbean_slack_hooker_lastmessage', $save);
                        return true;
                    } else {
                        //$msg.= $seconds.' is less than '.$perseconds.' so we continue'.PHP_EOL;
                        if($total>=$maxper){
                            //$msg.= 'Total '.$total.' is >= maxper '.$maxper.' '.$perseconds.' seconds so we CANNOT POST';
                            return false;
                        }else{
                            //$msg.= 'Total '.$total.' is < maxper '.$maxper.' so we post and add to total';
                            $lasts[$i]['total'] = $total + 1;
                            $save = serialize($lasts);
                            update_option('vbean_slack_hooker_lastmessage', $save);
                            return true;
                        }
                    }
                }
            }
            if (!$found) {
                $thislast = array('url' => $url, 'lasthit' => $thishit, 'total' => 1);
                array_push($lasts, $thislast);
                $save = serialize($lasts);
                update_option('vbean_slack_hooker_lastmessage', $save);
                return true;
            }
        }
        return false;
    }

    public static function send_test_notification($msg){
        return  Vanilla_Bean_Slack_Hooker::notification_send($msg);
    }

}

