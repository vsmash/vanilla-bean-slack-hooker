<?php if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Number
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_map' ) ) {

    class Exopite_Simple_Options_Framework_Field_map extends Exopite_Simple_Options_Framework_Fields {

        public function __construct( $field, $value = '', $unique = '', $config = array() ) {
            parent::__construct( $field, $value, $unique, $config );
        }

        public function output() {

            echo $this->element_before();


            $attr = array();
            $attrs = ( ! empty( $attr ) ) ? ' ' . trim( implode( ' ', $attr ) ) : '';

            echo $this->element_prepend();

            echo '<div class="exopitemap-control"><input type="hidden" name="' . $this->element_name() . '" value="' . $this->element_value() . '"' . $this->element_class() . $this->element_attributes() . $attrs . '/>';
            echo '<div class="canvas exopitemap" style="width:300px; height:300px;" id="exopitemap'.$this->field['id'].'"></div></div>';
            echo $this->element_append();



            echo $this->element_after();

        }
        public static function enqueue( $args ) {

            $resources = array(
                array(
                    'name'       => 'exopite-sof-map-loader',
                    'fn'         => 'loader-googlemap.min.js',
                    'type'       => 'script',
                    'dependency' => array( 'jquery' ),
                    'version'    => '',
                    'attr'       => true,
                ),
            );

            parent::do_enqueue( $resources, $args );

        }

    }

}
