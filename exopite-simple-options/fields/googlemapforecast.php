<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.

/**
 * Field: googlemapforecast
 */
if( ! class_exists( 'Exopite_Simple_Options_Framework_Field_googlemapforecast' ) ) {
    class Exopite_Simple_Options_Framework_Field_googlemapforecast extends Exopite_Simple_Options_Framework_Fields {

        public function __construct( $field, $value = '', $unique = '', $config = array() ) {
            parent::__construct( $field, $value, $unique, $config );
        }

        public function output() {
            $classes = ( isset( $this->field['class'] ) ) ? ' ' . $this->field['class'] : '';
            
            // Ensure we have a valid default value
            $value = $this->element_value();
            if (empty($value) && isset($this->field['default'])) {
                $value = $this->field['default'];
            }

            echo $this->element_before();
            echo '<input type="hidden" class="exopite-sof-field-googlemapforecast' . $classes . '" 
                data-field-id="' . esc_attr($this->field['id']) . '" 
                name="'. esc_attr($this->element_name()) .'" 
                value="'. esc_attr($value) .'" />';
            echo '<div class="google-map" style="width: 100%; height: 400px; margin-top: 10px;"></div>';
            echo $this->element_after();
        }

        public static function enqueue() {
            // Enqueue Google Maps API if not already enqueued
            if (!wp_script_is('google-maps', 'enqueued')) {
                $api_key = get_option('google_maps_api_key');
                if (!empty($api_key)) {
                    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=marker', array(), null, true);
                }
            }
        }
    }
}
