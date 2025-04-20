<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Switcher
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_switcher' ) ) {

	class Exopite_Simple_Options_Framework_Field_switcher extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);
			$label = ( isset( $this->field['label'] ) ) ? '<div class="exopite-sof-text-desc">' . esc_html($this->field['label']) . '</div>' : '';

			$classes = ( isset( $this->field['class'] ) ) ? implode( ' ', explode( ' ', $this->field['class'] ) ) : '';

			echo '<label class="checkbox">';
			echo '<input name="' . esc_attr($this->element_name()) . '" value="yes" class="checkbox__input ' . esc_attr($classes) . '" type="checkbox"' . wp_kses($this->element_attributes() . checked( $this->element_value(), 'yes', false ), $this->allowedTags) . '>';
			echo '<div class="checkbox__switch"></div>';
			echo '</label>' . wp_kses($label, $this->allowedTags);
			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
