<?php
/**
 * File contains class for Variety Subscriptions.
 *
 * Functions for the discount forms (and eventually other things) related to VY's subcriptions.
 *
 * CDWE-580 -- Copied from pmc-variety-2014 theme
 *
 * @since 2017-09-09
 *
 * @package pmc-variety-2017
 */
class VarietySubscriptionsHelper {

	/**
	 * Email address.
	 *
	 * @var string
	 */
	private static $internal_email = 'aholisky@pmc.com';

	/**
	 * Sends a copy of the form to the hard coded email address
	 *
	 * @param int    $form_id     The ID of the form. All form elements must begin with the id. Example: "vcs".
	 * @param string $email_title Title of email.
	 *
	 * @return bool If mail sent successfully return true, otherwise false.
	 */
	public static function send_internal_email( $form_id, $email_title ) {

		$sanitized_values_to_email = array();
		$email_body = '';

		// Loop through the $_POST array, if an element beings with $form_id, include it in the email
		// Also sanity check to only include max of 500 characters.
		foreach ( $_POST as $post_name => $post_val ) { // input var ok.

			if ( substr( $post_name, 0, 3 ) === $form_id ) {
				$sanitized_values_to_email[ sanitize_text_field( substr( $post_name, 3, 25 ) ) ] = substr( sanitize_text_field( $post_val ), 0, 500 );
			}
		}

		// Something has gone wrong if we don't have a sanitized value list. Stop now.
		if ( count( $sanitized_values_to_email ) === 0 ) {
			return false;
		}

		foreach ( $sanitized_values_to_email as $field => $value ) {

			if ( 'Submit' === $field ) {
				continue;
			}

			$email_body .= sprintf( '%s: %s', esc_html( $field ), esc_html( $value ) ) . "\r\n";
		}

		return wp_mail( self::$internal_email, $email_title, $email_body );
	}

	/**
	 * Set default email address.
	 *
	 * @param string $email_address Email.
	 */
	public static function set_internal_email( $email_address ) {

		self::$internal_email = $email_address;
	}
}
