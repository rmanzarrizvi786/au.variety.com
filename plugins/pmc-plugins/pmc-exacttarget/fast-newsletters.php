<?php
/* TODO: Rename files to be more descriptive */
/*
Description: Prepare data for listing fast newsletters aka breaking news alerts on the site has.
*/
### Check Whether User Can Manage Posts
if ( !current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}

use PMC\Global_Functions\Nonce;

$nonce           = Nonce::get_instance( basename( __FILE__ ) );
$sailthru_types  = sailthru_get_fast_newsletter();
$action_name     = strtolower( PMC::filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) );
$newsletter_name = PMC::filter_input( INPUT_GET, 'newsletter_name', FILTER_SANITIZE_STRING );
$notices         = [];

if ( ! empty( $action_name ) && ! empty( $newsletter_name ) && $nonce->verify() ) {

	// If the newsletter exists then process the action request.
	if ( ! empty( $sailthru_types[ $newsletter_name ] ) ) {

		if ( 'delete' === $action_name ) {

			$newsletter_to_delete = $sailthru_types[ $newsletter_name ];
			
			if ( ! empty( $newsletter_to_delete['content_builder'] ) && 'yes' === $newsletter_to_delete['content_builder'] ) {
				$email_id                        = ! empty( $newsletter_to_delete['email_id'] ) ? $newsletter_to_delete['email_id'] : false;
				$email_not_found_on_exact_target = false;

				if ( $email_id ) {
					$email                           = Exact_Target::get_email_from_content_builder( $email_id );
					$email_not_found_on_exact_target = empty( $email->status );
				}

				// Newsletter belongs to Content Builder
				// If the email is not found on exact target, let's remove it from the database.
				if ( $email_id && ( $email_not_found_on_exact_target || Exact_Target::delete_email_from_content_builder( $email_id ) ) ) {
					unset( $sailthru_types[ $newsletter_name ] );
					sailthru_save_fast_newsletter( $sailthru_types );
				}
			} elseif ( empty( $newsletter_to_delete['content_builder'] ) || 'yes' !== $newsletter_to_delete['content_builder'] ) {
				$email = Exact_Target::get_email( $newsletter_name );

				// Newsletter belongs to Classic Content
				// If the email is not found on exact target, let's remove it from the database.
				if ( empty( $email ) || Exact_Target::delete_email( $newsletter_name ) ) {
					unset( $sailthru_types[ $newsletter_name ] );
					sailthru_save_fast_newsletter( $sailthru_types );
				}
			}

		} elseif ( 'disable' === $action_name ) {

			$sailthru_types[ $newsletter_name ]['newsletter_status'] = 'disabled';
			sailthru_save_fast_newsletter( $sailthru_types );

		} elseif ( 'enable' === $action_name ) {

			$sailthru_types[ $newsletter_name ]['newsletter_status'] = 'enabled';
			sailthru_save_fast_newsletter( $sailthru_types );
		}
	}
}

require( __DIR__ . '/views/fast-newsletters-tpl.php' );
