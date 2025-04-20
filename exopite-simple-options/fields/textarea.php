<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Textarea
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_textarea' ) ) {

	class Exopite_Simple_Options_Framework_Field_textarea extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses($this->element_before(), $this->allowedTags);
			echo '<textarea name="' . esc_attr($this->element_name()) . '"' . wp_kses($this->element_class(), $this->allowedTags) . wp_kses($this->element_attributes(), $this->allowedTags) . '>' . esc_textarea($this->element_value()) . '</textarea>';
			echo wp_kses($this->element_after(), $this->allowedTags);

		}


	}

}
