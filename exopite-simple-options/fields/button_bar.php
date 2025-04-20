<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Button Bar
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_button_bar' ) ) {

	class Exopite_Simple_Options_Framework_Field_button_bar extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);

			if ( isset( $this->field['options'] ) ) {

				$options = $this->field['options'];
				$options = ( is_array( $options ) ) ? $options : array_filter( $this->element_data( $options ) );

				if ( ! empty( $options ) ) {

					echo '<div class="button-bar">';
					foreach ( $options as $key => $value ) {
						echo '<div class="button-bar__item">';
						echo '<input type="radio" name="' . esc_attr($this->element_name()) . '" value="' . esc_attr($key) . '"' . wp_kses($this->element_attributes( $key ) . $this->checked( $this->element_value(), $key ), $this->allowedTags) . '>';
						echo '<button class="button-bar__button">' . esc_html($value) . '</button>';
						echo '</div>';

					}
					echo '</div>';
				}

			}

			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
