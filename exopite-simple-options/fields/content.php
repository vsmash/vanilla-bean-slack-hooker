<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Content
 *
 */
if ( ! class_exists( 'Exopite_Simple_Options_Framework_Field_content' ) ) {

	class Exopite_Simple_Options_Framework_Field_content extends Exopite_Simple_Options_Framework_Fields {

		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function output() {

			$content = ( isset( $this->field['content'] ) ) ? $this->field['content'] : '';

			if ( isset( $this->field['callback'] ) ) {

				$callback = $this->field['callback'];
				if ( is_callable( $callback['function'] ) ) {

					$args    = ( isset( $callback['args'] ) ) ? $callback['args'] : '';
					$content = call_user_func( $callback['function'], $args );

				}
			}

			echo wp_kses($this->element_before(), $this->allowedTags);
			echo '<div' . wp_kses($this->element_class() . $this->element_attributes(), $this->allowedTags) . '>' . wp_kses($content, $this->allowedTags) . '</div>';
			echo wp_kses($this->element_after(), $this->allowedTags);

		}

	}

}
