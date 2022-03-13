<?php
/*
 * Helper Class to render object tags in feeds etc which does not allow
 * javascript (and hense swfobject.js) to render video embeds
 */
class PMC_Swfobjects {
	public $attributes = array();
	public $params = array();
	public $variables = array();
	public $path;
	public $width;
	public $height;
	public $id;
	public $default_alt_content = '<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>';

	function __construct( $path = '#', $width = 100, $height = 100, $alt_content = '', $id = '' ) {
		$this->path = esc_url( $path );
		$this->width = intval( $width );
		$this->height = intval( $height );
		if ( ! empty( $alt_content ) ) {
			$this->alt_content = wp_kses_data( $alt_content );
		} else {
			$this->alt_content = $this->default_alt_content;
		}

		if ( ! empty( $id ) ) {
			$this->id = ' id="' . sanitize_text_field( $id ) . '"';
		}
	}
	// set alt content
	function set_alt_content( $str ) {
		$this->alt_content = wp_kses_data( $str );
	}

	// set flash variables
	function set_variables( $arr ) {
		$this->variables = $arr;
	}

	// set object tag params
	function set_params( $arr ) {
		$this->params = $arr;
	}

	// set arrtibutes
	function set_attributes( $arr ) {
		$this->attributes = $arr;
	}

	// set id for object tag
	function set_id( $id ) {
		$this->id = sanitize_text_field( $id );
	}

	// prepare object tags to be rendered
	function get_content_to_render() {
		$attributes = '';
		$params = '';
		$variables = array();
		foreach ( $this->attributes as $key => $val ) {
			if ( $key !== 'id' ) {
				$attributes .= ' ' . sanitize_text_field( $key ) . '=' . '"' . esc_attr( $val ) . '"';
			}
		}
		foreach ( $this->params as $key => $val ) {
			$key = sanitize_text_field( $key );
			$val = sanitize_text_field( $val );
			$params .= "<param name='". esc_attr( $key ) . "' value='" . esc_attr( $val ) . "' />";
		}
		foreach ( $this->variables as $key => $val ) {
			$key = sanitize_text_field( $key );
			$val = sanitize_text_field( $val );
			$variables[] = $key . '=' . rawurlencode( $val );
		}
		if ( count( $variables ) ) {
			$params .= "<param name='flashvars' value='" . esc_attr( implode( '&', $variables ) ) . "' />";
		}
		$str = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='{$this->width}' height='{$this->height}'{$this->id}{$attributes}>";
		$str .= "<param name='movie' value='{$this->path}' />";
		$str .= $params;
		$str .= '<!--[if !IE]>-->';
		$str .= "<object type='application/x-shockwave-flash' data='{$this->path}' width='{$this->width}' height='{$this->height}'{$attributes}>";
		$str .= $params;
		$str .= '<!--<![endif]-->';
		$str .= wp_kses_data( $this->alt_content );
		$str .= '<!--[if !IE]>-->';
		$str .= '</object>';
		$str .= '<!--<![endif]-->';
		$str .= '</object>';
		return $str;
	}

	// render object tags prepared earlier
	function render() {
		echo $this->get_content_to_render(); // WPCS: XSS ok.
	}
}
//EOF