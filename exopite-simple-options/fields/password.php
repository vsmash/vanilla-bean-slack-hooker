<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Password
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_password' ) ) {

	class Exopite_Simple_Options_Framework_Field_password extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array()) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);

			echo wp_kses($this->element_prepend(), $this->allowedTags);

			echo '<input type="' . esc_attr($this->element_type()) . '" name="' . esc_attr($this->element_name()) . '" value="' . esc_attr($this->element_value()) . '"' . wp_kses($this->element_class(), $this->allowedTags) . wp_kses($this->element_attributes(), $this->allowedTags) . '/>';;

			echo wp_kses($this->element_append(), $this->allowedTags);

			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
