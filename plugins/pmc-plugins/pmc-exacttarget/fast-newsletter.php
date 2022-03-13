<?php
/* TODO: Rename files to be more descriptive */
/*
Description: Prepare data for adding/editing fast newsletter aka breaking news alerts
*/
if ( !current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}

$sailthru_item = null;
$sailthru_edit_type = null;
$sailthru_edit_name;

$sailthru_types = sailthru_get_fast_newsletter();
$sailthru_types = ( is_array( $sailthru_types ) && ! empty( $sailthru_types ) ) ? $sailthru_types : [];

require( __DIR__ . '/php/vars.php' );

$mmcnws_nonce_key = "_mmcnws_addeditbna_nonce"; //nonce key for this page

$mmcnws_nonce = wp_create_nonce( $mmcnws_nonce_key ); //nonce

$notices = [];

if ( isset( $_GET['edit_type'] ) ) {
	$sailthru_edit_name = sanitize_text_field( $_GET['edit_type'] );
	$sailthru_item = $sailthru_types[$sailthru_edit_name];
	$sailthru_edit_type = '&edit_type='.$sailthru_edit_name;
}

if ( !empty( $_POST ) && !empty( $_POST[$mmcnws_nonce_key] ) && wp_verify_nonce( $_POST[$mmcnws_nonce_key], $mmcnws_nonce_key ) !== false ) {
	$sailthru_errors = array();
	if ( empty( $_POST['name'] ) ) {
		$sailthru_errors[] = "Alert must have a name";
	}
	if ( empty( $_POST['subject'] ) ) {
		$sailthru_errors[] = "Alert must have a subject";
	}
	if ( empty( $_POST['template'] ) ) {
		$sailthru_errors[] = "Alert must have a template";
	}

	if ( empty( $sailthru_errors ) ) {
		$newsletter_type = array(
			'template' => sanitize_text_field( trim( $_POST['template'] ) ),
			'subject' => wp_kses_data( $_POST['subject'] ),
			'post_tag_name' => sanitize_text_field( $_POST['post_tag_name'] ),
			'dataextension' => sanitize_text_field( $_POST['dataextension'])
		);

		$name                     = \PMC::filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
		$email_name               = \PMC::filter_input( INPUT_POST, 'email_name', FILTER_SANITIZE_STRING );
		$content_builder          = \PMC::filter_input( INPUT_POST, 'content_builder', FILTER_SANITIZE_STRING );
		$content_builder_template = \PMC::filter_input( INPUT_POST, 'content_builder_template', FILTER_SANITIZE_STRING );
		$old_newsletter_name      = \PMC::filter_input( INPUT_POST, 'old_name', FILTER_SANITIZE_STRING );

		// Mark whether the config belongs to Content Builder or Classic Content.
		$newsletter_type['content_builder'] = ( ! empty( $content_builder ) && 'yes' === strtolower( $content_builder ) ) ? 'yes' : 'no';

		if ( empty( $email_name ) ) {
			$email_name = $name;
		}

		$send_definition = \PMC::filter_input( INPUT_POST, 'pmc_newsletter_alert_senddefinition', FILTER_SANITIZE_STRING );
		if ( ! empty( $send_definition ) ) {
			$newsletter_type['pmc_newsletter_alert_senddefinition'] = $send_definition;
		}

		if ( 'yes' === $newsletter_type['content_builder'] ) {

			$newsletter_type['template'] = $content_builder_template;

			$et_template   = Exact_Target::get_template_from_content_builder_by_id( $newsletter_type['template'] );
			$template_html = ( $et_template->status && ! empty( $et_template->results->content ) ) ? $et_template->results->content : '';

			if ( empty( $template_html ) ) {

				$sailthru_errors[] = 'Not able to get template HTML ' . $et_template->message;

			} else {

				$et_email = Exact_Target::upsert_email_to_content_builder( $email_name, $newsletter_type['subject'], $template_html ); // phpcs:ignore

				if ( false === $et_email->status || empty( $et_email->results->id ) ) {

					$sailthru_errors[] = 'Email creation failed. Please Try Again, Error: ' . $et_email->message;

				} else {

					$newsletter_type['email_id']        = $et_email->results->id; // Save the email id.
					$newsletter_type['email_name']      = $email_name;
					$newsletter_type['content_builder'] = 'yes';                  // Mark that newsletter belongs to content builder.

					if ( ! empty( $old_newsletter_name ) ) {
						unset( $sailthru_types[ $old_newsletter_name ] );
					}

					$sailthru_types[ $name ] = $newsletter_type;
					sailthru_save_fast_newsletter( $sailthru_types );
				}
			}

		} else {

			$template_obj  = Exact_Target::get_templates( $newsletter_type['template'] );
			$template_html = ( ! empty( $template_obj->LayoutHTML ) ) ? $template_obj->LayoutHTML : ''; // phpcs:ignore

			if ( empty( $template_html ) ) {

				$sailthru_errors[] = 'Not able to get template HTML';

			} else {

				$et_email = Exact_Target::upsert_email( $email_name, $newsletter_type['subject'], $template_html ); // phpcs:ignore

				if ( 'OK' !== $et_email[0]->StatusCode ) {
					$sailthru_errors[] = 'Email creation failed. Please Try Again';
				}

				$newsletter_type['email_name']      = $email_name;
				$newsletter_type['content_builder'] = 'no'; // Mark that newsletter belongs to Classic Content.

				if ( ! empty( $old_newsletter_name ) ) {
					unset( $sailthru_types[ $old_newsletter_name ] );
				}

				$sailthru_types[ $name ] = $newsletter_type;
				sailthru_save_fast_newsletter( $sailthru_types );
			}
		}

		if ( empty( $sailthru_errors ) ) {
			$sailthru_types     = sailthru_get_fast_newsletter();
			$sailthru_item      = $sailthru_types[ $name ];
			$sailthru_edit_name = $name;
			$notices[]          = 'Breaking News Letter saved successfully';
		}
	}
}

$sailthru_emails           = Exact_Target::get_email();
$sailthru_dataextension    = \PMC\Exacttarget\Cache::get_instance()->get_data_extensions();
$et_sendclassification     = \PMC\Exacttarget\Cache::get_instance()->get_sendclassifications();
$sailthru_templates        = \PMC\Exacttarget\Cache::get_instance()->get_templates();
$content_builder_templates = \PMC\Exacttarget\Cache::get_instance()->get_templates_from_content_builder();

require( __DIR__ . '/views/fast-newsletter-tpl.php' );
