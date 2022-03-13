<?php
/**
 * The textarea class.
 *
 * @author Amit Gupta <http://amitgupta.in/>
 */

namespace iG\Metabox;

use iG\Metabox\Helper as iG_Metabox_Helper;

class Textarea extends Field {

	/**
	 * Field initialization stuff
	 *
	 * @return void
	 */
	protected function _sub_init() {
		$this->set_render_callback( array( $this, '_render_input_field' ) );
	}

	/**
	 * Set columns of the field.
	 *
	 * @param int $size
	 * @return iG\Metabox\Field
	 */
	public function set_width( $size ) {
		if ( empty( $size ) || ! is_numeric( $size ) ) {
			throw new \ErrorException( 'Textarea width needs to be a number' );
		}

		$this->_field['cols'] = intval( $size );

		return $this;
	}

	/**
	 * Set rows of the field.
	 *
	 * @param int $size
	 * @return iG\Metabox\Field
	 */
	public function set_height( $size ) {
		if ( empty( $size ) || ! is_numeric( $size ) ) {
			throw new \ErrorException( 'Textarea height needs to be a number' );
		}

		$this->_field['rows'] = intval( $size );

		return $this;
	}

	/**
	 * Set maxlength of the field.
	 *
	 * @param int $maxlength
	 * @return iG\Metabox\Field
	 */
	public function set_maxlength( $maxlength ) {
		if ( empty( $maxlength ) || ! is_numeric( $maxlength ) ) {
			throw new \ErrorException( 'Textarea maxlength needs to be a number' );
		}

		$this->_field['maxlength'] = intval( $maxlength );

		return $this;
	}

	/**
	 * Set placeholder text for the field
	 *
	 * @param string $placeholder
	 * @return iG\Metabox\Field
	 */
	public function set_placeholder( $placeholder ) {
		if ( empty( $placeholder ) || ! is_string( $placeholder ) ) {
			throw new \ErrorException( 'Textarea placeholder needs to be a string' );
		}

		$this->_field['placeholder'] = $placeholder;

		return $this;
	}

	/**
	 * Set wrapping for the field
	 *
	 * @param string $type
	 * @return iG\Metabox\Field
	 */
	public function set_wrap( $type ) {
		if ( empty( $type ) || ! is_string( $type ) ) {
			throw new \ErrorException( 'Textarea wrap type needs to be a string' );
		}

		$this->_field['wrap'] = $type;

		return $this;
	}

	/**
	 * This method renders the UI for the field
	 *
	 * @param int $post_id ID of the post for which field is being created
	 * @return string Field UI markup
	 */
	protected function _render_input_field( $post_id = 0 ) {
		$post_id = intval( $post_id );

		if ( empty( $this->_field['value'] ) && $post_id > 0 ) {
			$this->_field['value'] = $this->get_data( $post_id );
		}

		if ( empty( $this->_field['value'] ) && ! is_null( $this->_default_value ) ) {
			$this->_field['value'] = $this->_default_value;
		}

		$label = '';
		$description = '';
		$status = '';
		$value = '';

		if ( ! empty( $this->_field['value'] ) ) {
			$value = $this->_field['value'];
		}

		unset( $this->_field['value'] );

		if ( ! empty( $this->_field['label'] ) ) {
			$label = $this->_field['label'];
		}

		unset( $this->_field['label'] );

		if ( ! empty( $this->_field['description'] ) ) {
			$description = $this->_field['description'];
		}

		unset( $this->_field['description'] );

		if ( $this->_field['required'] === true ) {
			$status = ' required';
		} elseif ( $this->_field['readonly'] === true ) {
			$status = ' readonly';
		} elseif ( $this->_field['disabled'] === true ) {
			$status = ' disabled';
		}

		unset( $this->_field['required'], $this->_field['readonly'], $this->_field['disabled'], $this->_field['type'] );

		//allow template file override
		$template = apply_filters(
						sprintf( 'ig-cmf-template-%s', $this->_get_class_name( true ) ),
						IG_CUSTOM_METABOXES_ROOT . '/templates/field-ui/textarea.php'
					);

		return iG_Metabox_Helper::render_template( $template, array(
			'attributes'   => $this->_field,
			'label'        => $label,
			'description'  => $description,
			'status'       => $status,
			'field_value'  => $value,
		) );
	}

}	//end of class



//EOF