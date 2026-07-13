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
            // Each endpoint gets its own copy. The mutations below are per-endpoint
            // (channel, alert ping, icon, Slack username prefix); sharing one payload
            // let them accumulate, so a second endpoint inherited the first one's
            // channel and a doubled "User: User: " prefix.
            $payload = $this->payload;

            if ($endpoint["channel"]) {
                $payload['channel'] = '#' . $endpoint['channel'];
            }
            // check for slack app
            if($endpoint["alert"] && isset($payload['text'])){
                $payload['text']='<!'.$endpoint["alert"].'>'.$payload['text'];
            }
            if(isset($endpoint["icon"]) && $endpoint["icon"]){
                $payload['icon_emoji']=$endpoint["icon"];
            }
            elseif(isset($endpoint["emoji"]) && $endpoint["emoji"]){
                $payload['icon_emoji']=$endpoint["emoji"];
            }
            if(str_starts_with($endpoint["url"],'https://hooks.slack.com/services/') && isset($payload['text'])){
                $payload['text']=$payload['username'].': '.$payload['text'];
            }
            // if $endpoint['url'] is an email address, send it to the email address
            $isemail = filter_var($endpoint['url'], FILTER_VALIDATE_EMAIL);

            $args = false;
            if($isemail){
                $body = $this->messageText($payload);
            }else{
                $args = $this->transportArgs($endpoint['url'], $payload);
                $body = $args;
            }

            // Nothing renderable or encodable. Skip BEFORE canSend(), which charges a
            // rate-limit slot: spending one on a message we cannot send would refuse a
            // later valid message, which is the very failure #17 is about.
            if($body === false){
                $this->logUnsendable($endpoint['url']);
                $output[] = false;
                continue;
            }

            if($this->canSend($endpoint['url'])){
                if($isemail){
                    $output[] = wp_mail($endpoint['url'], $payload['username'], $body);
                    continue;
                }
                if($this->options['omit_cron']=='yes') {
                    $output[] = wp_remote_post($endpoint['url'], $args);
                }else{
                    $this->cronSend($endpoint['url'], $args);
                }
            }
        }
        return $output;
    }

    // which webhook family an endpoint belongs to
    public function endpointType($url)
    {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));

        if ($host === 'chat.googleapis.com') {
            return 'google_chat';
        }
        // Teams incoming webhooks are Power Automate Workflows (Logic Apps hosts).
        // The legacy Office 365 connectors (*.webhook.office.com) were disabled in
        // May 2026; they are matched here only so an old URL fails at Microsoft
        // with a real error rather than silently posting Slack JSON.
        if ($this->hostMatches($host, 'logic.azure.com') || $this->hostMatches($host, 'webhook.office.com')) {
            return 'teams';
        }

        return 'slack';
    }

    private function hostMatches($host, $suffix)
    {
        return $host === $suffix || str_ends_with($host, '.' . $suffix);
    }

    // request args for this endpoint's webhook family, or false if the payload cannot
    // be encoded at all — in which case the caller must skip rather than post an empty
    // body, which every platform answers with a 400 and the notification is lost.
    private function transportArgs($url, $payload)
    {
        $args = $this->apiargs;

        switch ($this->endpointType($url)) {
            case 'google_chat':
                // Google Chat needs a raw JSON body; it has no channel/username/icon
                // concept, so those Slack fields are dropped rather than forged.
                $args['headers']['Content-Type'] = 'application/json; charset=UTF-8';
                $text = $this->messageText($payload);
                // Guard the false: stripSlackAlerts(false) would coerce to '' and post
                // a perfectly encodable empty message, so the skip would never fire.
                $body = ($text === false)
                    ? false
                    : $this->jsonBody(array('text' => $this->stripSlackAlerts($text)));
                break;

            case 'teams':
                $args['headers']['Content-Type'] = 'application/json';
                $card = $this->teamsMessage($payload);
                $body = ($card === false) ? false : $this->jsonBody($card);
                break;

            default:
                // Slack / Mattermost: legacy form-encoded payload field.
                $json = $this->jsonBody($payload);
                $body = ($json === false) ? false : array('payload' => $json);
        }

        if ($body === false) {
            return false;
        }
        $args['body'] = $body;

        return $args;
    }

    // #17 was a notification lost without a trace. Skipping is better than posting an
    // empty body, but say so, or we have only moved the silence. Logs the host, never
    // the URL — a webhook URL is a credential.
    private function logUnsendable($url)
    {
        // Only ever name an email endpoint in full. A webhook URL is a credential, so if
        // the host will not parse we say nothing rather than log the secret.
        $host  = wp_parse_url($url, PHP_URL_HOST);
        $label = filter_var($url, FILTER_VALIDATE_EMAIL) ? $url : ($host ? $host : 'an endpoint');

        // json_last_error() is only meaningful if the last encode actually failed —
        // do not blame JSON when the payload simply had nothing to render.
        $reason = ( json_last_error() !== JSON_ERROR_NONE )
            ? json_last_error_msg()
            : 'nothing renderable in the payload';

        error_log(sprintf(
            'Vanilla Bean Slack Hooker: could not build a notification for %s (%s). Message not sent.',
            $label,
            $reason
        ));
    }

    // JSON_INVALID_UTF8_SUBSTITUTE: a PHP error message or a WooCommerce field pasted
    // from Word can carry invalid UTF-8, and a plain encode returns false. Substituting
    // the bad bytes keeps the message deliverable instead of silently dropping it.
    // Returns false only for what substitution cannot rescue (depth, recursion, NAN).
    private function jsonBody($data)
    {
        return wp_json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    }

    // Every structured notification (WooCommerce, post status, plugin changes, error
    // alerts) sets only 'attachments' — see notification_send(). Split them into prose
    // lines and name/value facts, so each platform can lay them out its own way.
    private function attachmentParts($payload)
    {
        $parts = array('lines' => array(), 'facts' => array());

        if (empty($payload['attachments']) || !is_array($payload['attachments'])) {
            return $parts;
        }
        foreach ($payload['attachments'] as $attachment) {
            if (!is_array($attachment)) {
                continue;
            }
            $flat = $this->flattenAttachment($attachment);
            $parts['lines'] = array_merge($parts['lines'], $flat['lines']);
            $parts['facts'] = array_merge($parts['facts'], $flat['facts']);
        }

        return $parts;
    }

    // the human-readable message for platforms with no attachment concept
    private function messageText($payload)
    {
        if (isset($payload['text']) && $payload['text'] !== '') {
            return $payload['text'];
        }

        $parts = $this->attachmentParts($payload);
        $lines = $parts['lines'];
        foreach ($parts['facts'] as $fact) {
            $lines[] = $fact['name'] . ': ' . $fact['value'];
        }
        if ($lines) {
            return implode("\n", $lines);
        }

        return $this->jsonBody($payload);
    }

    // a Slack attachment split into prose + facts; color/footer/ts are chrome, not content
    private function flattenAttachment($attachment)
    {
        $lines = array();
        $facts = array();

        if (!empty($attachment['pretext'])) {
            $lines[] = $attachment['pretext'];
        }
        // author_name is content, not chrome: for a post it is the post's author, who
        // is not the acting user named in the pretext; for a comment it carries the
        // commenter's email. Dropping it loses information.
        if (!empty($attachment['author_name'])) {
            $lines[] = !empty($attachment['author_link'])
                ? '<' . $attachment['author_link'] . '|' . $attachment['author_name'] . '>'
                : $attachment['author_name'];
        }
        if (!empty($attachment['title'])) {
            $lines[] = !empty($attachment['title_link'])
                ? '<' . $attachment['title_link'] . '|' . $attachment['title'] . '>'
                : $attachment['title'];
        }
        if (!empty($attachment['text'])) {
            $lines[] = $attachment['text'];
        }
        if (!empty($attachment['fields']) && is_array($attachment['fields'])) {
            foreach ($attachment['fields'] as $field) {
                // build_data_message() copies caller data straight into fields, so a
                // value can be an array; concatenating it would warn and print "Array".
                if (isset($field['title'], $field['value'])
                    && is_scalar($field['title']) && is_scalar($field['value'])) {
                    $facts[] = array('name' => $field['title'], 'value' => $field['value']);
                }
            }
        }

        return array('lines' => $lines, 'facts' => $facts);
    }

    // <!channel> / <!here> only mean something to Slack; elsewhere they render literally
    private function stripSlackAlerts($text)
    {
        return trim(preg_replace('/<!([a-z]+)(\|[^>]*)?>/i', '', $text));
    }

    // Slack link syntax <url|label> -> markdown, which an Adaptive Card TextBlock renders
    private function slackLinksToMarkdown($text)
    {
        $text = preg_replace('/<(https?:\/\/[^>|]+)\|([^>]+)>/', '[$2]($1)', $text);
        return preg_replace('/<(https?:\/\/[^>|]+)>/', '$1', $text);
    }

    // Teams: an Adaptive Card in the envelope a Power Automate Workflows webhook expects.
    // Adaptive Card, not the legacy MessageCard: it is what Microsoft documents for
    // Workflows, it is the only format their own designer will render, and MessageCard
    // is deprecated for new integrations.
    private function teamsMessage($payload)
    {
        $title = isset($payload['username']) && $payload['username']
            ? $payload['username']
            : get_bloginfo('name');

        $parts = $this->attachmentParts($payload);
        if ($parts['lines'] || $parts['facts']) {
            $lines = $parts['lines'];
        } else {
            $text = $this->messageText($payload);
            if ($text === false) {
                // Nothing renderable and nothing encodable — let the caller skip.
                return false;
            }
            $lines = array($text);
        }

        // One TextBlock per line, rather than one block of newline-joined Markdown —
        // in Markdown a single newline is a soft break and would collapse to a space.
        $body = array(array(
            'type'   => 'TextBlock',
            'text'   => $title,
            'weight' => 'Bolder',
            'size'   => 'Medium',
            'wrap'   => true,
        ));
        foreach ($lines as $line) {
            $line = $this->slackLinksToMarkdown($this->stripSlackAlerts($line));
            if ($line !== '') {
                $body[] = array('type' => 'TextBlock', 'text' => $line, 'wrap' => true);
            }
        }
        if ($parts['facts']) {
            // An Adaptive Card FactSet keys on title/value; our facts carry name/value.
            $facts = array();
            foreach ($parts['facts'] as $fact) {
                $facts[] = array('title' => $fact['name'], 'value' => $fact['value']);
            }
            $body[] = array('type' => 'FactSet', 'facts' => $facts);
        }

        return array(
            'type'        => 'message',
            'attachments' => array(array(
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content'     => array(
                    'type'    => 'AdaptiveCard',
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'version' => '1.4',
                    'body'    => $body,
                ),
            )),
        );
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

