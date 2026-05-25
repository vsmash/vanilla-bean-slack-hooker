<?php
/**
 * Slack Hooker — Error Monitor
 *
 * Captures PHP errors (by level), throttles repeats, and routes an alert through
 * Slack Hooker's existing delivery pipeline (Vanilla_Bean_Slack_Hooker::send_data_message).
 * Ported from the standalone Vanilla Bean - Error Mailer plugin (epic VBSLACK-6: #7 capture, #8 routing).
 * Configured via the "Alert Options" settings tab (#9).
 *
 * Fail-safe by design: the handlers guard against re-entrancy, swallow their own
 * throwables, never echo, and always let PHP's normal error handling continue — so a
 * problem in here can never take the site down or recurse.
 *
 * @package Vanilla_Bean_Slack_Hooker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Slack_Hooker_Error_Monitor {

    /** Re-entrancy guard: an error raised while building/sending an alert must not recurse. */
    private static $handling = false;

    /** Per-request cache of resolved Alert Options. */
    private static $opts = null;

    /** Transient key prefix for throttle/dedup state. */
    const THROTTLE_PREFIX = 'slackhooker_alert_';

    /**
     * Register the handlers. Cheap + unconditional: the enabled/level gates are applied
     * lazily inside the handler (when options are reliably available), so registration
     * timing during bootstrap doesn't matter.
     */
    public static function init() {
        set_error_handler( array( __CLASS__, 'handle_error' ) );
        register_shutdown_function( array( __CLASS__, 'handle_shutdown' ) );
    }

    /**
     * Resolve Alert Options from the Exopite store, with sensible defaults so the
     * feature is useful with zero configuration. No import of legacy errormailer options.
     *
     * @return array
     */
    private static function options() {
        if ( self::$opts !== null ) {
            return self::$opts;
        }

        $o = array(
            'enabled'         => true,
            'levels'          => array( 'fatal' => true, 'parse' => true, 'warning' => false, 'notice' => false, 'deprecated' => false ),
            'exclude'         => array(),                  // errno ints to skip
            'exemptions'      => array(),                  // "file" or "file,line" entries
            'subject'         => '{sitename}: {errortype}',
            'throttle'        => 60,                        // seconds; collapse identical repeats
            'endpoints'       => false,
            'endpointOptions' => 'defaults',
        );

        if ( function_exists( 'get_exopite_sof_option' ) ) {
            $all = get_exopite_sof_option( 'vanilla-bean-slack-hooker' );
            $tab = ( is_array( $all ) && isset( $all['notifications']['alert_options']['tabs'] ) && is_array( $all['notifications']['alert_options']['tabs'] ) )
                ? $all['notifications']['alert_options']['tabs']
                : array();

            if ( ! empty( $tab ) ) {
                if ( isset( $tab['alert_options_onoff'] ) ) {
                    $o['enabled'] = ( 'yes' === $tab['alert_options_onoff'] );
                }
                // One on/off switcher per level (level_fatal, level_parse, …). Absent key keeps the default.
                foreach ( array( 'fatal', 'parse', 'warning', 'notice', 'deprecated' ) as $lvl ) {
                    if ( isset( $tab[ 'level_' . $lvl ] ) ) {
                        $o['levels'][ $lvl ] = ( 'yes' === $tab[ 'level_' . $lvl ] );
                    }
                }
                if ( ! empty( $tab['excludetypes'] ) ) {
                    $o['exclude'] = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $tab['excludetypes'] ) ) ) );
                }
                if ( ! empty( $tab['exemptions'] ) ) {
                    $o['exemptions'] = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $tab['exemptions'] ) ) );
                }
                if ( isset( $tab['subject'] ) && '' !== $tab['subject'] ) {
                    $o['subject'] = $tab['subject'];
                }
                if ( isset( $tab['throttle'] ) && '' !== $tab['throttle'] ) {
                    $o['throttle'] = max( 0, (int) $tab['throttle'] );
                }
                if ( ! empty( $tab['endpoints'] ) ) {
                    $o['endpoints'] = $tab['endpoints'];
                }
                if ( ! empty( $tab['endpointOptions'] ) ) {
                    $o['endpointOptions'] = $tab['endpointOptions'];
                }
            }
        }

        self::$opts = $o;
        return $o;
    }

    /** set_error_handler callback — non-fatal runtime errors. Returns false so PHP's handler still runs. */
    public static function handle_error( $errno, $errstr, $errfile = '', $errline = 0 ) {
        self::process( $errno, $errstr, $errfile, $errline );
        return false;
    }

    /** Shutdown callback — catches fatals/parse errors that set_error_handler cannot. */
    public static function handle_shutdown() {
        $e = error_get_last();
        if ( $e && in_array( (int) $e['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
            self::process( $e['type'], $e['message'], $e['file'], $e['line'] );
        }
    }

    /** Map a PHP error level to a severity bucket used for gating + colour. */
    private static function severity( $errno ) {
        switch ( (int) $errno ) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                return 'fatal';
            case E_PARSE:
                return 'parse';
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return 'warning';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'notice';
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'deprecated';
            default:
                return 'fatal'; // unknown → treat as significant, gated by the 'fatal' toggle
        }
    }

    /** The gauntlet: gate by enabled/level/exclude/exemption/throttle, then dispatch. Never throws. */
    private static function process( $errno, $errstr, $errfile, $errline ) {
        if ( self::$handling ) {
            return;
        }
        // Respect error_reporting / the @ suppression operator.
        if ( 0 === ( error_reporting() & $errno ) ) {
            return;
        }

        self::$handling = true;
        try {
            $o = self::options();
            if ( empty( $o['enabled'] ) ) {
                return;
            }

            $sev = self::severity( $errno );
            if ( empty( $o['levels'][ $sev ] ) ) {
                return;
            }
            if ( in_array( (int) $errno, $o['exclude'], true ) ) {
                return;
            }
            if ( self::is_exempt( $errfile, $errline, $errstr, $o['exemptions'] ) ) {
                return;
            }

            $key = md5( $errno . '|' . $errstr . '|' . $errfile . '|' . $errline );
            if ( self::is_throttled( $key, (int) $o['throttle'] ) ) {
                return;
            }

            self::dispatch( $errno, $errstr, $errfile, $errline, $sev, $o );
        } catch ( \Throwable $t ) {
            // Never let the monitor break the request.
        } finally {
            self::$handling = false;
        }
    }

    /**
     * Exemptions: each entry is "fileFragment" or "fileFragment,line". Matches when the
     * fragment matches the error file (endsWith) or starts the message, and (if given) the line.
     */
    private static function is_exempt( $errfile, $errline, $errstr, $exemptions ) {
        foreach ( (array) $exemptions as $entry ) {
            $row  = array_map( 'trim', explode( ',', $entry ) );
            $frag = $row[0];
            if ( '' === $frag ) {
                continue;
            }
            $file_match = ( $errfile === $frag ) || ( '' !== $errfile && '' !== $frag && substr( $errfile, -strlen( $frag ) ) === $frag );
            $msg_match  = ( 0 === strpos( (string) $errstr, $frag ) );
            if ( $file_match || $msg_match ) {
                if ( isset( $row[1] ) && is_numeric( $row[1] ) ) {
                    if ( (int) $row[1] === (int) $errline ) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Throttle/dedup with a transient. First occurrence of an identical error sends;
     * repeats within the window are suppressed (count incremented for context). Uses a
     * transient rather than errormailer's per-error update_option (one write, auto-expiring).
     */
    private static function is_throttled( $key, $window ) {
        if ( $window <= 0 ) {
            return false;
        }
        $tkey  = self::THROTTLE_PREFIX . $key;
        $state = get_transient( $tkey );
        if ( false === $state ) {
            set_transient( $tkey, array( 'count' => 1 ), $window );
            return false; // first one through
        }
        $state['count'] = ( isset( $state['count'] ) ? (int) $state['count'] : 1 ) + 1;
        set_transient( $tkey, $state, $window );
        return true; // suppress repeat
    }

    /** Build the alert and route it through Slack Hooker's delivery pipeline. */
    private static function dispatch( $errno, $errstr, $errfile, $errline, $sev, $o ) {
        if ( ! class_exists( 'Vanilla_Bean_Slack_Hooker' ) ) {
            return;
        }

        $labels = array(
            'fatal'      => 'Fatal Error',
            'parse'      => 'Parse Error',
            'warning'    => 'Warning',
            'notice'     => 'Notice',
            'deprecated' => 'Deprecation',
        );
        $colours = array(
            'fatal'      => '#cc0000',
            'parse'      => '#cc0000',
            'warning'    => '#e8a33d',
            'notice'     => '#3aa3e3',
            'deprecated' => '#888888',
        );
        $label  = isset( $labels[ $sev ] ) ? $labels[ $sev ] : 'Error';
        $colour = isset( $colours[ $sev ] ) ? $colours[ $sev ] : '#cc0000';

        // Title from the subject template (errormailer-compatible tokens).
        $title = self::render_subject( $o['subject'], $label, $errno, $errfile, $errline );

        // Fields. Keep the message bounded; do NOT dump a backtrace with arguments
        // (those can carry secrets) — a compact file:line frame list only.
        $message = self::clean( $errstr, 1500 );
        $data    = array(
            'Level'   => $label . ' [' . (int) $errno . ']',
            'Where'   => self::clean( $errfile, 300 ) . ':' . (int) $errline,
            'PHP'     => PHP_VERSION,
            'Site'    => get_option( 'blogname' ),
        );

        $options = array(
            'color' => $colour,
            'text'  => $message,
        );

        try {
            Vanilla_Bean_Slack_Hooker::send_data_message(
                $title,
                $data,
                $options,
                ! empty( $o['endpoints'] ) ? $o['endpoints'] : false,
                ! empty( $o['endpointOptions'] ) ? $o['endpointOptions'] : 'defaults'
            );
        } catch ( \Throwable $t ) {
            // delivery failures must stay silent
        }
    }

    /** Resolve subject template tokens (mirrors errormailer's tokens). */
    private static function render_subject( $tpl, $label, $errno, $errfile, $errline ) {
        $repl = array(
            '{errortype}'   => $label,
            '{errornumber}' => (int) $errno,
            '{errorline}'   => (int) $errline,
            '{errorpage}'   => basename( (string) $errfile ),
            '{siteurl}'     => (string) get_option( 'siteurl' ),
            '{sitename}'    => (string) get_option( 'blogname' ),
        );
        return strtr( (string) $tpl, $repl );
    }

    /** Strip tags + control chars and bound length, for safe inclusion in an alert payload. */
    private static function clean( $value, $max ) {
        $value = wp_strip_all_tags( (string) $value );
        $value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value );
        // Truncate on a character boundary so we never split a multibyte char (which would
        // make json_encode fail and silently drop the alert).
        if ( function_exists( 'mb_substr' ) ) {
            if ( mb_strlen( $value ) > $max ) {
                $value = mb_substr( $value, 0, $max ) . '…';
            }
        } elseif ( strlen( $value ) > $max ) {
            $value = substr( $value, 0, $max ) . '…';
        }
        return $value;
    }
}
