<?php
/* TODO: Rename files to be more descriptive */
/*
Description: Prepare data for listing recurring newsletters on the site has. Also has option to send the test news letter and send it now.
*/
if ( !current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}
$mmcnws_nonce_key = '_mmcnws_recurring_nonce';
$mmcnws_nonce     = wp_create_nonce( $mmcnws_nonce_key );
$sailthru_repeats = Sailthru_Blast_Repeat::get_custom_post();

if ( isset( $_GET['delete'] ) && ctype_alnum( $_GET['delete'] ) && !empty( $_GET[$mmcnws_nonce_key] ) && wp_verify_nonce( $_GET[$mmcnws_nonce_key], $mmcnws_nonce_key ) !== false ) {

	$newsletter_id        = sanitize_text_field( $_GET['delete'] );
	$newsletter_to_delete = ( isset( $sailthru_repeats[ $newsletter_id ] ) ) ? $sailthru_repeats[ $newsletter_id ] : [];

	if ( ! empty( $newsletter_to_delete ) ) {

		if ( ! empty( $newsletter_to_delete['content_builder'] ) && 'yes' === $newsletter_to_delete['content_builder'] ) {
			$email                           = Exact_Target::get_email_from_content_builder( $newsletter_id );
			$email_not_found_on_exact_target = empty( $email->status );

			if ( $email_not_found_on_exact_target || Exact_Target::delete_email_from_content_builder( $newsletter_id ) ) {
				if ( Sailthru_Blast_Repeat::remove_from_db( $newsletter_id ) ) {
					unset( $sailthru_repeats[ $newsletter_id ] );
				}
			}
		} else {
			$email = Exact_Target::get_email( $newsletter_id, 'ID' );

			// If the email is not found on exact target, let's remove it from the database.
			if ( empty( $email ) || Exact_Target::delete_email( $newsletter_id, 'ID' ) ) {
				if ( Sailthru_Blast_Repeat::remove_from_db( $newsletter_id ) ) {
					unset( $sailthru_repeats[ $newsletter_id ] );
				}
			}
		}
	}

}

$sailthru_user_email       = '';
$sailthru_templates        = \PMC\Exacttarget\Cache::get_instance()->get_templates();
$content_builder_templates = \PMC\Exacttarget\Cache::get_instance()->get_templates_from_content_builder();

require( __DIR__ . '/views/recurring-newsletters-tpl.php' );
