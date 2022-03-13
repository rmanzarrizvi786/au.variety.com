<?php
/* TODO: Rename files to be more descriptive */
/*
Description: Global settings page for recurring news letter. Sets the featured image and image size etc for newsletters. Also sets default thumbnail in case the post does not have image.
*/
### Check Whether User Can Manage Posts
if ( !current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}
$et_sendclassification = \PMC\Exacttarget\Cache::get_instance()->get_sendclassifications();

require( 'php/vars.php' );
$mmcnws_nonce_key = $mmcnws_nonce_keys['nwsltr_settings']; //nonce key for this page
$mmcnws_nonce = wp_create_nonce( $mmcnws_nonce_key ); //nonce

$sailthru_success = false;
$sailthru_errors = array();
//form was submitted
if ( !empty( $_POST['_mmcnws_settings_nonce'] ) && wp_verify_nonce( $_POST['_mmcnws_settings_nonce'], $mmcnws_nonce_key ) !== false ) {
	$sailthru_item = $_POST;
	$valid_chars = "/^[0-9]+$/";

	if ( !$sailthru_item['mmcnewsletter_thumb_width'] ) {
		$sailthru_errors[] = 'Newsletter thumbnail width is required';
	} else if ( !$valid = preg_match( $valid_chars, $sailthru_item['mmcnewsletter_thumb_width'] ) ) {
		$sailthru_errors[] = "Invalid newsletter thumbnail width";
	}

	if ( !$sailthru_item['mmcnewsletter_thumb_height'] ) {
		$sailthru_errors[] = 'Newsletter thumbnail height is required';
	} else if ( !$valid = preg_match( $valid_chars, $sailthru_item['mmcnewsletter_thumb_height'] ) ) {
		$sailthru_errors[] = "Invalid newsletter thumbnail height";
	}

	if ( empty( $sailthru_item['mmcnewsletter_thumb_src'] ) ) {
		$sailthru_errors[] = 'Newsletter thumbnail source is required';
	} elseif ( !array_key_exists( $sailthru_item['mmcnewsletter_thumb_src'], $mmcnws_thumb_src ) ) {
		$sailthru_errors[] = "Invalid newsletter thumbnail source";
	}

	if ( !$sailthru_item['mmcnewsletter_feature_image_width'] ) {
		$sailthru_errors[] = 'Newsletter featured post image width is required';
	} else if ( !$valid = preg_match( $valid_chars, $sailthru_item['mmcnewsletter_feature_image_width'] ) ) {
		$sailthru_errors[] = "Invalid newsletter featured post image width";
	}

	if ( !$sailthru_item['mmcnewsletter_feature_image_height'] ) {
		$sailthru_errors[] = 'Newsletter featured post image height is required';
	} else if ( !$valid = preg_match( $valid_chars, $sailthru_item['mmcnewsletter_feature_image_height'] ) ) {
		$sailthru_errors[] = "Invalid newsletter featured post image height";
	}
	if ( !$sailthru_errors ) {
		update_option( 'mmcnewsletter_thumb_width', $sailthru_item['mmcnewsletter_thumb_width'] );
		update_option( 'mmcnewsletter_thumb_height', $sailthru_item['mmcnewsletter_thumb_height'] );
		update_option( 'mmcnewsletter_thumb_src', $sailthru_item['mmcnewsletter_thumb_src'] );

		update_option( 'mmcnewsletter_feature_image_width', $sailthru_item['mmcnewsletter_feature_image_width'] );
		update_option( 'mmcnewsletter_feature_image_height', $sailthru_item['mmcnewsletter_feature_image_height'] );

		$sailthru_success = "Settings updated successfully";
	}
	pmc_update_option( 'pmc_newsletter_senddefinition', sanitize_text_field( $sailthru_item['pmc_newsletter_senddefinition'] ), 'exacttarget' );
	pmc_update_option( 'pmc_alert_senddefinition', sanitize_text_field( $sailthru_item['pmc_alert_senddefinition'] ), 'exacttarget' );
	pmc_update_option( 'pmc_newsletter_api_token', sanitize_text_field( $sailthru_item['pmc_newsletter_api_token'] ), 'exacttarget' );

	if ( $sailthru_item['global_default_image'] ) {
		update_option( 'global_default_image', esc_url( $sailthru_item['global_default_image'] ) );
	}
} else {
	$sailthru_item['mmcnewsletter_thumb_width'] = absint( get_option( 'mmcnewsletter_thumb_width' ) );
	$sailthru_item['mmcnewsletter_thumb_height'] = absint( get_option( 'mmcnewsletter_thumb_height' ) );
	$sailthru_item['mmcnewsletter_thumb_src'] = esc_url( get_option( 'mmcnewsletter_thumb_src' ) );
	$sailthru_item['mmcnewsletter_thumb_src'] = ( !$sailthru_item['mmcnewsletter_thumb_src'] ) ? "" : $sailthru_item['mmcnewsletter_thumb_src'];
	$sailthru_item['mmcnewsletter_feature_image_width'] = absint( get_option( 'mmcnewsletter_feature_image_width' ) );
	$sailthru_item['mmcnewsletter_feature_image_height'] = absint( get_option( 'mmcnewsletter_feature_image_height' ) );

	$sailthru_item['pmc_newsletter_senddefinition'] = sanitize_text_field( pmc_get_option( 'pmc_newsletter_senddefinition',
		'exacttarget' ) );
	$sailthru_item['pmc_alert_senddefinition']      = sanitize_text_field( pmc_get_option( 'pmc_alert_senddefinition',
		'exacttarget' ) );

	$sailthru_item['pmc_newsletter_api_token']      = sanitize_text_field( pmc_get_option( 'pmc_newsletter_api_token', 'exacttarget' ) );

	$sailthru_item['global_default_image'] = esc_url( get_option( 'global_default_image' ) );
}

if ( !empty( $_POST['_mmcnws_listcache_nonce'] ) && wp_verify_nonce( $_POST['_mmcnws_listcache_nonce'], $mmcnws_nonce_key ) !== false ) {
	mmc_newsletter_update_listgroups();
	$sailthru_success = "List groups updated successfully";
}

function pmc_newsletter_toggle( &$switch ) {
	if ( is_bool( $switch ) ) {
		$switch = ( $switch === true ) ? false : true;
	} elseif ( is_numeric( $switch ) ) {
		$switch = ( intval( $switch ) === 1 ) ? 0 : 1;
	} else {
		$switch = ( strtolower( $switch ) == "yes" ) ? "no" : "yes";
	}
}

function pmc_newsletter_create_options( $options, $selected = '', $opt_groups = array() ) {
	if ( empty( $options ) || !is_array( $options ) ) {
		return;
	}
	if ( !empty( $opt_groups ) && !is_array( $opt_groups ) ) {
		return;
	}
	$txt_options = "";
	$blnCloseGroup = false;
	$txtEndKey = "";
	$intCount = 1;
	foreach ( $options as $key => $option ) {
		if ( array_key_exists( $key, $opt_groups ) ) {
			$txt_options .= "<optgroup label=\"" . $opt_groups[$key]['name'] . "\">\n";
			pmc_newsletter_toggle( $blnCloseGroup );
			$txtEndKey = $opt_groups[$key]['end'];
		}
		$txt_options .= "<option value=\"{$key}\"" . selected( $key, $selected, false) . ">{$option}</option>\n";
		if ( $key == $txtEndKey && $blnCloseGroup === true ) {
			$txt_options .= "</optgroup>\n";
			pmc_newsletter_toggle( $blnCloseGroup );
		}
		$intCount++;
	}
	if ( $blnCloseGroup === true ) {
		$txt_options .= "</optgroup>\n";
	}
	print( $txt_options );
}

require( __DIR__ . '/views/newsletter-settings-tpl.php' );

