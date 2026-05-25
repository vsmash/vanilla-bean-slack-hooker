<?php if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Radio
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_compass' ) ) {

    class Exopite_Simple_Options_Framework_Field_compass extends Exopite_Simple_Options_Framework_Fields {

        public function __construct( $field, $value = '', $unique = '', $config = array() ) {
            parent::__construct( $field, $value, $unique, $config );
        }

        public function output() {
            $val='';
            $classes = ( isset( $this->field['class'] ) ) ? implode( ' ', explode( ' ', $this->field['class'] ) ) : '';
            $options = array(
                // translators: Compass direction abbreviation for North
                'n'  => __( 'N', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for North-Northeast
                'nne'  => __( 'NNE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for Northeast
                'ne'  => __( 'NE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for East-Northeast
                'ene'  => __( 'ENE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for East
                'e'  => __( 'E', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for East-Southeast
                'ese'  => __( 'ESE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for Southeast
                'se'  => __( 'SE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for South-Southeast
                'sse'  => __( 'SSE', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for South
                's'  => __( 'S', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for South-Southwest
                'ssw'  => __( 'SSW', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for Southwest
                'sw'  => __( 'SW', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for West-Southwest
                'wsw'  => __( 'WSW', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for West
                'w'  => __( 'W', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for West-Northwest
                'wnw'  => __( 'WNW', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for Northwest
                'nw'  => __( 'NW', 'exopite-simple-options' ),
                // translators: Compass direction abbreviation for North-Northwest
                'nnw'  => __( 'NNW', 'exopite-simple-options' ),
            );
            echo $this->element_before();

                $options = ( is_array( $options ) ) ? $options : array_filter( $this->element_data( $options ) );
                $style   = ( isset( $this->field['style'] ) ) ? $this->field['style'] : '';

                switch ( $style ) {
                    case 'fancy':
                        echo '<div class="compass-control"><div class="compass">';
                        break;
                    default:
                        echo '<ul' . $this->element_class() . '>';
                        break;
                }
                    foreach ( $options as $key => $value ) {

                        switch ( $style ) {
                            case 'fancy':
                                echo '<input type="radio" class="compassdir" name="' . $this->element_name() . '" value="' . $key . '"' . $this->element_attributes( $key ) . $this->checked( $this->element_value(), $key ) . '>';

                                break;

                            default:
                                echo '<li><label><input type="radio" name="' . $this->element_name() . '" value="' . $key . '"' . $this->element_attributes( $key ) . $this->checked( $this->element_value(), $key ) . '/> ' . $value . '</label></li>';
                                if($this->checked( $this->element_value(), $key )){
                                    $val=$value;
                                }
                                break;
                        }

                    }

                switch ( $style ) {
                    case 'fancy':
                        echo ' <div class="selected-direction">'.$this->value.'</div></div></div>';
                        break;
                    default:
                        echo '</ul>';
                        break;
                }



            echo $this->element_after();

        }
        public static function enqueue( $args ) {

            $resources = array(
                array(
                    'name'       => 'exopite-sof-compass-loader',
                    'fn'         => 'loader-compass.min.js',
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
