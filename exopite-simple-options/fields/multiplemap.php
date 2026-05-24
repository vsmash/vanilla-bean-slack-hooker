<?php if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Number
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_multiplemap' ) ) {

    class Exopite_Simple_Options_Framework_Field_multiplemap extends Exopite_Simple_Options_Framework_Fields {

        public function __construct( $field, $value = '', $unique = '', $config = array() ) {
            parent::__construct( $field, $value, $unique, $config );
        }

        public function output() {

            echo $this->element_before();


            $attr = array();
            $attrs = ( ! empty( $attr ) ) ? ' ' . trim( implode( ' ', $attr ) ) : '';

            echo $this->element_prepend();



            echo "<div class='wp-block is-layout-flex' style='display: flex; flex-wrap: wrap; align-items: flex-start; gap: 1rem;'>
        <div class='exopitemap-control'>
            <input type='hidden' name='" . $this->element_name() . "' value='" . $this->element_value() . "'" . $this->element_class() . $this->element_attributes() . $attrs . "/>
  <input type='text' class='map-search-box' placeholder='Search for a location' style='margin-bottom: 10px; width: 100%; padding: 8px;' />
                   <div class='mapnotice'></div>
                   <div class='canvas exopitemultiplemap' style='width: 50%; min-width: 400px; height: 400px;' id='exopitemultiplemap" . $this->field['id'] . "'></div>
            " . $this->element_append() . "
        </div>
        <div>" . $this->element_after() . "</div>
      </div>";




        }
        public static function enqueue( $args ) {

            $resources = array(
                array(
                    'name'       => 'exopite-sof-multiplemap-loader',
                    'fn'         => 'loader-googlemultiplemap.min.js',
                    'type'       => 'script',
                    'dependency' => array( 'jquery', 'google-maps' ),
                    'version'    => '',
                    'attr'       => true,
                ),
            );

            parent::do_enqueue( $resources, $args,
                array(
                'object_name' => 'myFieldData',
                'data' => array()
            )
            );


        }

    }

}
