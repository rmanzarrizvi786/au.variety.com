<?php

/**
 * Class which adds an AJAX button field to a Cheezcap Group
 *
 * For a usage example see pmc-wwd-2015/plugins/config/cheezcap.php:106
 *
 * This CAP field adds a simple AJAX button which can be hooked into
 * to control the functionality.
 */

// @codeCoverageIgnoreStart Ignoring coverage for this because this cannot be reasonably tested at present.
if ( ! class_exists( '\CheezCapOption', false ) ) {
	return;
}
// @codeCoverageIgnoreEnd

class PMC_CheezCapAjaxButton extends CheezCapOption {

	public $nonce = '';
	public $button_text = '';

	/**
	 * PMC_CheezCapAjaxButton constructor.
	 *
	 * @param string $_name
	 * @param string $_desc
	 * @param string $_id
	 * @param string $_button_text
	 * @param string $_std
	 * @param bool   $_validation_cb
	 */
	public function __construct( $_name, $_desc, $_id, $_button_text = '', $_std = '', $_validation_cb = false ) {

		// Create the field through the parent class
		parent::__construct( $_name, $_desc, $_id, $_std, $_validation_cb );

		$this->button_text = $_button_text;

		// Create a nonce to validate our AJAX request
		$this->nonce = wp_create_nonce( $this->id );

		/**
		 * 2016-11-07 Hau Vong
		 * IMPORTANT NOTE: This is a wrong way to add an enque script via cheezcap option constructor.
		 * This class may be create multiple time cause these action below to add multiple time!
		 * Fix via static variable to avoid multiple calls
		 */
		static $_action_fired = false;
		if ( ! $_action_fired ) {
			$_action_fired = true;
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 0 );
			add_action( 'wp_ajax_pmc_cheezcap_ajax_button', array( $this, 'ajax_callback' ), 10, 0 );
		}

	}

	/**
	 * Enqueue the scripts/styles for this AJAX button field
	 */
	public function enqueue_scripts() {

		// Enqueue Scripts
		wp_enqueue_script(
			'pmc-cheezcap-ajax-button',
			pmc_global_functions_url( '/js/pmc-cheezcap-ajax-button.js' ),
			array( 'jquery' ),
			false,
			true
		);

		// Localize some variables for our JavaScript
		wp_localize_script(
			'pmc-cheezcap-ajax-button',
			'pmc_cheezcap_ajax_button',
			array(
				'nonce'     => $this->nonce,
				'option_id' => $this->id,
			)
		);

		// Enqueue Styles
		wp_enqueue_style(
			'pmc-cheezcap-ajax-button',
			pmc_global_functions_url( '/css/pmc-cheezcap-ajax-button.css' ),
			array(),
			false,
			'all'
		);
	}

	/**
	 * Write out the HTML field which is displayed in the group in wp-admin
	 */
	public function write_html() { ?>
		<tr id="<?php echo esc_attr( $this->id ); ?>" class="pmc-cheezcap-ajax-button">
			<th scope="row" valign="top">
				<label for="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_html( $this->name ); ?></label>
				<br />
				<small><em><?php echo esc_html( $this->desc ); ?></em></small>
			</th>
			<td valign="top">
				<button id="cheezcap-ajax-button-<?php echo esc_attr( $this->id ); ?>" class="cheezcap-ajax-button"><?php echo esc_html( $this->button_text ); ?></button>
				<div class="waiting hideme"></div>
				<br /><br />
				<div class="output"><small></small></div>
			</td>
		</tr><?php
	}

	/**
	 * Clicking the ajax button fires this callback
	 *
	 * Any ajax functionality you need should be added in your theme/plugin
	 * and run on the filter below.
	 *
	 * For example, if you create a field with the id 'refresh-homepage-tout-cache',
	 * when the button is clicked and AJAX is fired, the following hook is executed:
	 * 'pmc_cheezcap_ajax_button_cap_refresh-homepage-tout-cache'
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function ajax_callback() {

		// Only proceed if this is an AJAX request
		if ( ! defined( 'DOING_AJAX' ) && ! DOING_AJAX )
			die();

		// Validate our nonce
		if ( ! check_ajax_referer( $this->id, 'nonce', false ) )
			wp_send_json_error( array( 'Invalid nonce' ) );

		// Send the JSON response, but allow it to be filtered
		// This mechanism is what let's themes/plugins hook in
		// and execute their own code for their AJAX button(s)
		wp_send_json( apply_filters( "pmc_cheezcap_ajax_button_{$this->id}", $response = array(
			'success'   => true,
			'error'     => false,
			'message'   => '',
			'option_id' => sanitize_title( $_POST['option_id'] ),
		) ) );
	}

	/**
	 * This CAP field does not save any values
	 *
	 * As such, no sanitize() callback is needed
	 *
	 * @param $value
	 */
	public function sanitize( $value ) {}

	/**
	 * The CAP field does not save any values
	 *
	 * @param $value
	 */
	public function save( $value ) {}

	/**
	 * This CAP field has no variables which need to be 'got'
	 */
	public function get() {}
}
