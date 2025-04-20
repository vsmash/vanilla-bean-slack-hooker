<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Checkbox
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_checkbox' ) ) {

	class Exopite_Simple_Options_Framework_Field_checkbox extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {

			parent::__construct( $field, $value, $unique, $config );

		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);
			$label = ( isset( $this->field['label'] ) ) ? $this->field['label'] : '';
			$style = ( isset( $this->field['style'] ) ) ? $this->field['style'] : '';

			switch ( $style ) {
				case 'fancy':
					echo '<label class="checkbox">';
					echo '<input type="checkbox" class="checkbox__input" name="' . esc_attr($this->element_name()) . '" value="yes"' . wp_kses($this->element_attributes() . checked( $this->element_value(), 'yes', false ), $this->allowedTags) . '>';
					echo '<div class="checkbox__checkmark"></div>';
					echo esc_html($label);
					echo '</label>';
					break;

				default:
					echo '<label><input type="checkbox" name="' . esc_attr($this->element_name()) . '" value="yes"' . esc_attr($this->element_class()) . wp_kses($this->element_attributes() . checked( $this->element_value(), 'yes', false ), $this->allowedTags) . '/> ' . esc_html($label) . '</label>';
					break;
			}


			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
