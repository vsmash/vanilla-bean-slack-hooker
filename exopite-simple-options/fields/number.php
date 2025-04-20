<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Number
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_number' ) ) {

	class Exopite_Simple_Options_Framework_Field_number extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			echo wp_kses( $this->element_before(), $this->allowedTags );

			$unit = ( isset( $this->field['unit'] ) ) ? '<em>' . esc_html( $this->field['unit'] ) . '</em>' : '';

			$attr = array();
			if ( isset( $this->field['min'] ) ) {
				$attr[] = 'min="' . esc_attr( $this->field['min'] ) . '"';
			}
			if ( isset( $this->field['max'] ) ) {
				$attr[] = 'max="' . esc_attr( $this->field['max'] ) . '"';
			}
			if ( isset( $this->field['step'] ) ) {
				$attr[] = 'step="' . esc_attr( $this->field['step'] ) . '"';
			}
			$attrs = ( ! empty( $attr ) ) ? ' ' . trim( implode( ' ', $attr ) ) : '';

			echo wp_kses( $this->element_prepend(), $this->allowedTags );

			echo '<input type="number" name="' . esc_attr( $this->element_name() ) . '" value="' . esc_attr( $this->element_value() ) . '"' . wp_kses( $this->element_class()  . $this->element_attributes()  . $attrs, $this->allowedTags) . '/>';

			echo wp_kses( $this->element_append(), $this->allowedTags );

			echo wp_kses( $unit, $this->allowedTags );

			echo wp_kses( $this->element_after(), $this->allowedTags );

		}

	}

}
