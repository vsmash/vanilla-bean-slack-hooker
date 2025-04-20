<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Tap List
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_tap_list' ) ) {

	class Exopite_Simple_Options_Framework_Field_tap_list extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			$input_type = ( ! empty( $this->field['radio'] ) ) ? 'radio' : 'checkbox';
			$input_attr = ( $input_type == 'checkbox' ) ? '[]' : '';

			echo wp_kses($this->element_before(), $this->allowedTags);

			if ( isset( $this->field['options'] ) ) {

				echo '<ul class="list">';

				$options = $this->field['options'];
				foreach ( $options as $key => $value ) {

					echo '<li class="list__item list__item--tappable">';
					echo '<div class="list__item__left">';

					if ( $input_type == 'radio' ) {
						# code...
					}

					switch ( $input_type ) {
						case 'radio':
							echo '<label class="radio-button">';
							echo '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($this->element_name( $input_attr )) . '" class="radio-button__input" id="' . esc_attr($this->field['id'] . '-' . sanitize_title( $value )) . '" value="' . esc_attr($key) . '"' . wp_kses($this->element_attributes( $key ) . $this->checked( $this->element_value(), $key ), $this->allowedTags) . '/>';
							echo '<div class="radio-button__checkmark"></div>';
							echo '</label>';
							break;

						case 'checkbox':
							echo '<label class="checkbox checkbox--noborder">';
							echo '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($this->element_name( $input_attr )) . '" class="checkbox__input checkbox--noborder__input" id="' . esc_attr($this->field['id'] . '-' . sanitize_title( $value )) . '" value="' . esc_attr($key) . '"' . wp_kses($this->element_attributes( $key ) . $this->checked( $this->element_value(), $key ), $this->allowedTags) . '>';
							echo '<div class="checkbox__checkmark checkbox--noborder checkbox--noborder__checkmark"></div>';
							echo '</label>';
							break;
					}


					echo '</div>';
					echo '<label for="' . esc_attr($this->field['id'] . '-' . sanitize_title( $value )) . '" class="list__item__center">';
					echo esc_html($value);
					echo '</label>';
					echo '</li>';

				}

				echo '</ul>';
			}

			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
