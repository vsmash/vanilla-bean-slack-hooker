<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Hidden
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_hidden' ) ) {

	class Exopite_Simple_Options_Framework_Field_hidden extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);
			echo '<input type="' . esc_attr($this->element_type()) . '" name="' . esc_attr($this->element_name()) . '" value="' . esc_attr($this->element_value()) . '"' . wp_kses($this->element_class() . $this->element_attributes(), $this->allowedTags) . '/>';
			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
