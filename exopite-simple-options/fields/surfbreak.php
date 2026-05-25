<?php if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Number
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_surfbreak' ) ) {

    class Exopite_Simple_Options_Framework_Field_surfbreak extends Exopite_Simple_Options_Framework_Fields {

        public function __construct( $field, $value = '', $unique = '', $config = array() ) {
            parent::__construct( $field, $value, $unique, $config );

        }

        public function output() {

            echo $this->element_before();


            $attr = array();
            $attrs = ( ! empty( $attr ) ) ? ' ' . trim( implode( ' ', $attr ) ) : '';

            // Get map center from field config or use default
            $map_center = isset($this->field['map_center']) ? $this->field['map_center'] : null;
            $map_center_json = $map_center ? json_encode($map_center) : 'null';
            error_log('map_center');
            error_log(print_r($map_center, true));

            echo $this->element_prepend();

            echo "<div class='exopitesurfbreak-control' data-map-center='" . esc_attr($map_center_json) . "'><input type='hidden' name='" . $this->element_name() . "' value='". $this->element_value() . "'" . $this->element_class() . $this->element_attributes() . $attrs . "/><div class='map-controls'><button class='button button-bar fittobounds'>Fit</button><button class='button button-bar undochange'>Undo</button>

  <input type='text' class='map-search-box' placeholder='Search for a location' style='margin-bottom: 10px; width: 100%; padding: 8px;' />
                    <div class='mapnotice'></div>
                  
</div>";
            echo '<div class="canvas exopitesurfbreak" style="width:100%; height:400px;" id="exopitesurfbreak'.$this->field['id'].'"></div></div>';
            echo $this->element_append();



            echo $this->element_after();

        }
        public static function enqueue( $args ) {

            $resources = array(
                array(
                    'name'       => 'exopite-sof-surfbreak-loader',
                    'fn'         => 'loader-surfbreak.min.js',
                    'type'       => 'script',
                    'dependency' => array( 'jquery','google-maps' ),
                    'version'    => '',
                    'attr'       => true,
                ),
            );

            parent::do_enqueue( $resources, $args );

        }

        public static function validate( $post_id ) {
            // if revision or autosave, don't validate
            if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
                return;
            }

            // if the post type is not 'break', don't validate
            if ( get_post_type( $post_id ) != 'break' ) {
                return;
            }

            // if the post status is not 'publish', don't validate
            if ( get_post_status( $post_id ) != 'publish' ) {
                return;
            }


            // get the exopite metabox for this field
            $surfinfo = get_post_meta( $post_id, 'localknowledge-meta');
            // error log it
            error_log('surfinfo'.$post_id);
            error_log(print_r($surfinfo, true));

            if(isset($surfinfo[0]['breakinfo'])){
               return;
            }

            // change post to draft
            remove_action('save_post', array('Exopite_Simple_Options_Framework_Field_surfbreak', 'validate'));
            wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));

            add_action('save_post', array('Exopite_Simple_Options_Framework_Field_surfbreak', 'validate'));
            // send admin notice
            $msg = 'Post status changed to draft';
            $msg = apply_filters('exopite_sof_admin_notice', $msg, 'error');
            add_action('admin_notices', function() use ($msg) {
                echo '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>';
            });
        }


    }

}
// run the static function on save_post
add_action('save_post', array('Exopite_Simple_Options_Framework_Field_surfbreak', 'validate'));