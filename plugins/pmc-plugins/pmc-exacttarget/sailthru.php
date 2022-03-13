<?php
/*
  Plugin Name: Sailthru
  Plugin URI: http://sailthru.com/
  Description: Schedule recurring newsletters and quick alerts with content from wordpress posts
  Version: 1.1
  Author: Will Farrell, PMC
  Author URI: http://sailthru.com
 */

use PMC\Exacttarget\Backup;
use PMC\Exacttarget\Config;
use PMC\Exacttarget\Cache;
use PMC\Exacttarget\RSS;
use PMC\Exacttarget\WP_REST_API;
use PMC\Exacttarget\Bna_Scheduled_Alert;

//load needed files .
require_once( __DIR__ . '/classes/class-exact-target.php' );
require_once( __DIR__ . '/classes/class-pmc-newsletter.php' );
require_once(__DIR__ . '/sailthru-config.php');
require_once ( __DIR__ . '/php/functions.php' );
require_once( __DIR__ . '/php/shared-functions.php' );
require_once( __DIR__ . '/classes/class-sailthru-blast-repeat.php' );
require_once( __DIR__ . '/classes/class-rest-request.php' );
require_once( __DIR__ . '/classes/class-rest-error.php' );
require_once( __DIR__ . '/classes/class-rest-support.php' );
require_once( __DIR__ . '/classes/class-folder-rest.php' );
require_once( __DIR__ . '/classes/class-content-block-rest.php' );
require_once( __DIR__ . '/classes/class-templates-rest.php' );
require_once( __DIR__ . '/classes/class-email-rest.php' );
require_once( __DIR__ . '/classes/class-bna-scheduled-alert.php' );

Bna_Scheduled_Alert::get_instance();

// We need to register the post type to prevent doing it wrong errors that are depending on the post type if code below exit
add_action( 'init', 'sailthru_create_post_type' );

// @TODO: to be remove
remove_action( 'post_tag_edit_form_fields', 'pmc_tvline_add_tag_custom_field', 10, 2 ); // remove the action because it will exist on tvline once tvline is cleaned up then this code needs to be removed.
remove_action( 'edited_post_tag', 'pmc_tvline_save_tag_custom_field', 10, 2 ); // remove the action because it will exist on tvline once tvline is cleaned up then this code needs to be removed.

// add needed actions
add_action( 'admin_enqueue_scripts', 'sailthru_add_admin_scripts' );
add_action( 'wp_ajax_sailthru_test_send_repeat', 'sailthru_test_send_repeat' );
add_action( 'wp_ajax_exacttarget_pause_newsletter', 'exacttarget_pause_newsletter' );
add_action( 'wp_ajax_sailthru_send_repeat_now', 'sailthru_send_repeat_now' );
add_action( 'wp_ajax_sailthru_load_recurring_campaigns', 'sailthru_load_recurring_campaigns' );
add_action( 'wp_ajax_sailthru-tag-autocomplete-get-list', 'sailthru_send_tag_autocomplete_list' );
add_action( 'after_setup_theme', 'sailthru_create_image_size' );

function setup_et_admin_hooks() {
	if ( \Exact_Target::is_active() ) {
		Backup::get_instance();
		add_action( 'admin_menu', 'sailthru_menu_init' );
		add_action( 'admin_print_scripts-post.php', 'sailthru_add_admin_scripts_onpost' );
		add_action( 'admin_print_scripts-post-new.php', 'sailthru_add_admin_scripts_onpost' );
		add_action( 'add_meta_boxes', 'sailthru_post_options' );
		add_action( 'add_meta_boxes', 'sailthru_newsletter_featured_post_module', 1 );
		add_action( 'save_post', 'sailthru_publish_to_sailthru' );
		add_action( 'future_to_publish', 'sailthru_breaking_news_scheduled_post' );
	}
}
add_action( 'init', 'setup_et_admin_hooks' );

/**
 * Add cheezcap options for 'Custom Subject' for Breaking News Newsletter.
 *
 * @ticket CDWE-136
 */
add_filter( 'pmc_global_cheezcap_options',  'sailthru_pmc_global_cheezcap_options' );

// @TODO: Remove action and related code, doesn't appears to be using any more.  Leave as is for the time being
add_action( 'post_tag_edit_form_fields', 'pmc_add_tag_custom_field', 10, 2 );
add_action( 'edited_post_tag', 'pmc_save_tag_custom_field', 10, 2 );

if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'sailthru_recurring_newsletter' || $_GET['page'] == 'sailthru_newsletter_settings' ) ) {
	add_action( 'admin_print_scripts', 'sailthru_custom_uploader_scripts' );
	add_action( 'admin_print_styles', 'sailthru_custom_uploader_styles' );
}

// @TODO: to be refactor and remove
add_filter( 'init', 'sailthru_add_feed' );
\PMC\Exacttarget\Rss::get_instance();

WP_REST_API\Manager::get_instance();

add_filter( 'the_content', 'sailthru_add_page_links' );
add_filter( 'widget_text', 'sailthru_add_page_links' );

//remove shortcode
remove_shortcode( 'sailthru_signup' ); // remove the shortcode because it will exist on tvline once tvline is cleaned up then this code needs to be removed.
//add shortcode
add_shortcode( 'sailthru_signup', 'pmc_render_newsletter_signup' );

function sailthru_menu_init() {

	if ( \Exact_Target::is_active() ) {
		add_submenu_page( 'sailthru_setup', 'Recurring Newsletters', 'Recurring Newsletters', 'publish_posts', 'sailthru_recurring_newsletters', function() {
			require __DIR__ . '/recurring-newsletters.php';
		} );
		add_submenu_page( 'sailthru_recurring_newsletters', 'Recurring Newsletter', 'Recurring Newsletter', 'publish_posts', 'sailthru_recurring_newsletter', function() {
			require __DIR__ . '/recurring-newsletter.php';
		} );
		add_submenu_page( 'sailthru_setup', 'Breaking News Alerts', 'Breaking News Alerts', 'publish_posts', 'sailthru_fast_newsletters', function() {
			require __DIR__ . '/fast-newsletters.php';
		} );
		add_submenu_page( 'sailthru_fast_newsletters', 'Breaking News Alert', 'Breaking News Alert', 'publish_posts', 'sailthru_fast_newsletter', function() {
			require __DIR__ . '/fast-newsletter.php';
		} );
		add_submenu_page( 'sailthru_setup', 'Newsletter Settings', 'Newsletter Settings', 'publish_posts', 'sailthru_newsletter_settings', function() {
			require __DIR__ . '/newsletter-settings.php';
		} );
		add_submenu_page( 'sailthru_setup', 'Newsletter Statuses', 'Newsletter Statuses', 'publish_posts', 'sailthru_newsletter_statuses', function() {
			require __DIR__ . '/newsletter-statuses.php';
		} );

	}
}

function send_ajax_response( $response = array() ) {
	$response = ( ! is_array( $response ) ) ? array( $response ) : $response;

	header("Content-Type: application/json");
	echo json_encode( $response );		//we want json
	unset( $response );	//clean up
	die();	//wp_die() is not good if you're sending json content
}

function sailthru_send_tag_autocomplete_list() {
	if( ! is_admin() || ! current_user_can( 'publish_posts' ) ) {
		return false;
	}

	if( ! isset( $_REQUEST['search_on'] ) || empty( $_REQUEST['search_on'] ) || ! check_ajax_referer( 'sailthru_t_ac_nonce', '_sailthru_t_ac_nonce', false ) ) {
		send_ajax_response();
	}

	$search_on = sanitize_text_field( $_REQUEST['search_on'] );

	$current_tags = array();

	if( isset( $_REQUEST['current_tags'] ) && ! empty( $_REQUEST['current_tags'] ) ) {
		$current_tags = array_filter( array_unique( array_map( 'intval', explode( ',', $_REQUEST['current_tags'] ) ) ) );
	}

	$post_tags = get_tags( array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => false,
		'exclude' => implode( ',', $current_tags ),
		'hierarchical' => true,
		'search' => $search_on,
		'number' => 5,
	) );

	if( empty( $post_tags ) ) {
		send_ajax_response();
	}

	$response = array();

	foreach( $post_tags as $tag ) {
		$response[] = array(
			'label' => $tag->name,
			'value' => $tag->term_id
		);
	}

	send_ajax_response( $response );
}

function sailthru_test_send_repeat() {
	if ( !current_user_can( 'publish_posts' ) ) {
			die( 'Access Denied' );
		}
	$mmcnws_nonce_key = "_mmcnws_recurring_nonce";
	if ( ! empty( $_POST[ $mmcnws_nonce_key ] ) && wp_verify_nonce( $_POST[ $mmcnws_nonce_key ],
			$mmcnws_nonce_key ) !== false
	) {
		if ( isset( $_POST['email_list_id'] ) && isset( $_POST['blast_repeat_id'] ) ) {

			$list_id = sanitize_text_field( $_POST['email_list_id'] );

			$dbrepeat = Sailthru_Blast_Repeat::load_from_db( $_POST['blast_repeat_id'] );

			/**
			 * Prefix subject with [Test] to mark the emails for easy recognition
			 *
			 * @ticket PPT-5250
			 * @since 2015-07-31 Amit Gupta
			 */
			$dbrepeat['is_test_email'] = true;

			if ( ! empty( $dbrepeat['content_builder'] ) && 'yes' === $dbrepeat['content_builder'] ) {

				$et_email = Exact_Target::get_email_from_content_builder( sanitize_text_field( $dbrepeat['email_id'] ) );

				if ( true !== $et_email->status ) {
					echo wp_json_encode( array( 'error' => 'Failed to retrieve newsletter from Exact Target. Please try again. Error: ' . $et_email->message ) );
					wp_die();
				}

			} else {

				$et_email = Exact_Target::get_email( sanitize_text_field( $dbrepeat['email_id'] ), 'ID', 'object' );

				if ( empty( $et_email ) ) {
					echo wp_json_encode( array( 'error' => 'Failed to retrieve newsletter from Exact Target. Please try again.' ) );
					wp_die();
				}
			}

			// No easy way to test this
			// @codeCoverageIgnoreStart
			// If the newsletter belongs to Content Builder then use content builder methods.
			if ( ! empty( $dbrepeat['content_builder'] ) && 'yes' === $dbrepeat['content_builder'] ) {

				$result = PMC_Newsletter::send_test_newsletter_content_builder( $dbrepeat, $list_id );

			} else {

				$result = PMC_Newsletter::send_test_newsletter( $dbrepeat, $list_id );
			}
			// @codeCoverageIgnoreEnd

			// @TODO: need to re-factor codes
			// @codeCoverageIgnoreStart
			if ( isset( $result['error'] ) ) {
				$result = [
					'error' => wp_json_encode( $result ),
				];
			}

			wp_send_json( $result );
			// @codeCoverageIgnoreEnd

		}
	}
	die;
}

function exacttarget_pause_newsletter() {
	if ( ! current_user_can( 'publish_posts' ) ) {
		die( 'Access Denied' );
	}
	$mmcnws_nonce_key = "_mmcnws_recurring_nonce";

	if ( ! empty( $_POST[ $mmcnws_nonce_key ] ) && wp_verify_nonce( $_POST[ $mmcnws_nonce_key ],
			$mmcnws_nonce_key ) !== false
	) {

		if ( isset( $_POST['state'] ) && isset( $_POST['blast_repeat_id'] ) ) {

			$state = sanitize_text_field( $_POST['state'] );

			$repeat_id = sanitize_text_field( $_POST['blast_repeat_id'] );

			$dbrepeat = Sailthru_Blast_Repeat::load_from_db( $repeat_id );

			if ( in_array( $state, array( 'play', 'pause' ) ) ) {
				$dbrepeat['state'] = $state;

				Sailthru_Blast_Repeat::save_to_db( $dbrepeat );

				echo json_encode( array( 'ok' => 'success', 'error' => false ) );
			} else {
				echo json_encode( array( 'error' => 'wrong state' ) );
			}
		}
	}
	die;
}

function sailthru_load_recurring_campaigns() {
	if ( !current_user_can( 'publish_posts' ) ) {
		die( 'Access Denied' );
	}

	if ( ! Exact_Target::is_active() ) {
		return;
	}

	$repeats = Sailthru_Blast_Repeat::get_repeats();

	$fastnewsletters = sailthru_get_fast_newsletter();

	$data_extensions = Cache::get_instance()->get_data_extensions();

	$subscribers = array();

	foreach ( $data_extensions as $id => $name ) {
		$subscribers[ $id ] = sanitize_text_field( $name );
	}

	foreach ( (array) $fastnewsletters as $name => $fastnewsletter ) {

		// Don't process disabled BNA.
		if ( ! empty( $fastnewsletter['newsletter_status'] && 'disabled' === $fastnewsletter['newsletter_status'] ) ) {
			unset( $fastnewsletters[ $name ] );
			continue;
		}

		if ( ! empty( $fastnewsletter['dataextension'] ) ) {
			$fastnewsletters[ $name ]['subs'] = $subscribers[ stripslashes( $fastnewsletter['dataextension'] ) ];
		}
	}

	foreach ( (array) $repeats as $i => $repeat ) {
		$db_repeat = Sailthru_Blast_Repeat::load_from_db( $repeat['repeat_id'] );

		if ( isset( $db_repeat['featured_post_id'] ) && intval( $db_repeat['featured_post_id'] ) === intval( $_GET['post_id'] ) && intval( $db_repeat['featured_post_id'] ) !== 0 ) {
			$repeats[$i]['selected'] = true;
		} else {
			$repeats[$i]['selected'] = false;
		}
		unset( $repeats[$i]['template']);
	}

	echo json_encode( array(
						   'repeats' => $repeats,
						   'fastnewsletters' => $fastnewsletters,
					  ) );
	wp_die();
}

function sailthru_send_repeat_now() {

	$mmcnws_nonce_key = "_mmcnws_recurring_nonce";

	if ( !current_user_can( 'publish_posts' ) ) {
		wp_die( 'Access Denied' );
	}

	if( !empty( $_POST[$mmcnws_nonce_key] ) && wp_verify_nonce( $_POST[$mmcnws_nonce_key], $mmcnws_nonce_key ) !== false ){

		if ( isset( $_POST['repeat_id'] ) && ctype_alnum( $_POST['repeat_id'] ) ) {
			$dbrepeat = Sailthru_Blast_Repeat::load_from_db( sanitize_text_field( $_POST['repeat_id'] ) );
		}

		if ( empty( $dbrepeat['email_id'] ) ) {
			echo json_encode( array( 'error' => 'Invalid repeat ID. Please try again.' ) );
			wp_die();
		}

		if ( ! empty( $dbrepeat['content_builder'] ) && 'yes' === $dbrepeat['content_builder'] ) {

			$et_email = Exact_Target::get_email_from_content_builder( sanitize_text_field( $dbrepeat['email_id'] ) );

			if ( true !== $et_email->status ) {
				echo wp_json_encode( array( 'error' => 'Failed to retrieve newsletter from Exact Target. Please try again.' ) );
				wp_die();
			}

		} else {

			$et_email = Exact_Target::get_email( sanitize_text_field( $dbrepeat['email_id'] ), 'ID', 'object' );

			if ( empty( $et_email ) ) {
				echo wp_json_encode( array( 'error' => 'Failed to retrieve newsletter from Exact Target. Please try again.' ) );
				// This line is covered but pipeline seems to say otherwise.
				wp_die(); // @codeCoverageIgnore
			}
		}

		if ( !empty( $dbrepeat ) ) {

			if ( isset( $dbrepeat['last_send_date'] ) && $dbrepeat['last_send_date'] === date( 'Ymd' ) && empty( $_POST['confirm'] ) ) {
				echo json_encode( array( 'confirm' => 'This newsletter has already been sent today, would you like to send it again?' ) );
				// This line is covered but pipeline seems to say otherwise.
				wp_die(); // @codeCoverageIgnore
			}

			if ( isset( $dbrepeat['state'] ) && 'pause' == $dbrepeat['state'] ) {
				echo json_encode( array( 'error' => 'This newsletter is paused, start & send it again?' ) );
				// This line is covered but pipeline seems to say otherwise.
				wp_die(); // @codeCoverageIgnore
			}

			// If newsletter belongs to Content Builder side then use Content Builder methods.
			if ( ! empty( $dbrepeat['content_builder'] ) && 'yes' === $dbrepeat['content_builder'] ) {

				$result = PMC_Newsletter::send_recurring_newsletter_content_builder( $dbrepeat );

			} else {

				$result = PMC_Newsletter::send_recurring_newsletter( $dbrepeat );
			}

			if ( ! empty( $result['error'] ) ) {
				$error_msg = wp_json_encode( $result );
				echo wp_json_encode( array( 'error' => $error_msg ) );
				// This line is covered but pipeline seems to say otherwise.
				wp_die(); // @codeCoverageIgnore
			}

			$dbrepeat['last_send_date'] = date( 'Ymd' );

			Sailthru_Blast_Repeat::save_to_db( $dbrepeat );

			echo json_encode( array( 'ok' => 'success' ) );
		}
	}
	// This line is covered but pipeline seems to say otherwise.
	wp_die(); // @codeCoverageIgnore
}

function sailthru_add_page_links( $content ) {

	if ( preg_match( '/\[sailthru .+\]/i', $content, $matches ) ) {

		$result = wp_cache_get( 'sailthru_page_links' );
		// Check if the cache has it
		if ( false === $result ) {
			$result = Exact_Target::get_object()->apiGet( 'page', array() );
			if ( !isset( $result['error'] ) ) {
				wp_cache_set( 'sailthru_page_links', $result );
			}
		}

		$pages = array();
		foreach ( $result as $type ) {
			foreach ( $type as $page ) {
				$pages[$page['name']] = $page;
			}
		}
		if ( empty( $pages ) ) {
			return $content;
		}

		$replacements = array();
		foreach ( $matches as $i => $match ) {
			$trimmed = mb_substr( $match, 1, -1 );
			$parts = explode( '|', $trimmed ); //should be in the form of [sailthru page_name | Link text to display]
			if ( count( $parts ) < 2 ) {
				unset( $matches[$i] );
				continue;
			}
			$s = explode( ' ', ( $parts[0] ) );
			$page_name = $s[1];
			$link_text = trim( $parts[1] );
			unset( $parts[0], $parts[1] );
			if ( !isset( $pages[$page_name]['url'] ) || !$link_text ) {
				unset( $matches[$i] );
				continue;
			}
			$url = $pages[$page_name]['url'];
			$link = '<a href="' . esc_url( 'http://' . $url ) . '">' . esc_html( $link_text ) . '</a>';
			$replacements[] = $link;
		}
		$content = str_replace( $matches, $replacements, $content );
	}
	return $content;
}


function sailthru_add_admin_scripts_onpost(){
	$js_version = '6.0';
	wp_register_script( 'load_newsletter_info', plugins_url( 'pmc-exacttarget/js/load_newsletter_info.js', dirname( __FILE__ ) ), array( 'jquery' ), $js_version );
	wp_enqueue_script( 'load_newsletter_info' );

	wp_register_script( 'exacttarget_preventDoubleClick', plugins_url( 'pmc-exacttarget/js/preventDoubleClick.js', dirname( __FILE__ ) ), array( 'jquery' ), $js_version );
	wp_enqueue_script( 'exacttarget_preventDoubleClick' );
}

function sailthru_add_admin_scripts( $page ) {
	$prefix = 'sailthru_page_';
	$css_version = '3.6.1';
	$js_version = '5.8.1';

	switch ( $page ) {
		case $prefix . 'sailthru_recurring_newsletters':
		case $prefix . 'sailthru_fast_newsletters':
			/* register the jQuery JEditable plugin */
			wp_register_script( 'jquery_jeditable_js', plugins_url( 'pmc-exacttarget/js/jquery.jeditable.js', dirname( __FILE__ ) ), array( 'jquery' ), $js_version );
			wp_enqueue_script( 'jquery_jeditable_js' );
			/* register our inline edit JS */
			wp_register_script( 'mmc_nws_inline_edit_js', plugins_url( 'pmc-exacttarget/js/mmc_newsletter_inline_edit.js', dirname( __FILE__ ) ), array( 'jquery',
				'jquery_jeditable_js',
				'mmc_newsletter_js' ), $js_version );
			wp_enqueue_script( 'mmc_nws_inline_edit_js' );
			break;

		case $prefix . 'sailthru_recurring_newsletter':
			wp_enqueue_style( 'MMC_newsletter_datepicker_style', plugins_url( 'pmc-exacttarget/js/datepick/jquery.datepick.css', dirname( __FILE__ ) ), false, $css_version, 'all' );
			wp_enqueue_script( 'MMC_newsletter_datepicker', plugins_url( 'pmc-exacttarget/js/datepick/jquery.datepick.min.js', dirname( __FILE__ ) ), array( 'jquery' ), $js_version, false );
			break;

		case 'admin_page_sailthru_recurring_newsletter':
			//load scripts bundled in WordPress
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );

			//load jquery-ui css from Google CDN, WordPress doesn't have it bundled
			wp_enqueue_style( 'sailthru-jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css', array() );

			//load our css and js
			wp_enqueue_style( 'sailthru-tag-autocomplete-css', plugins_url( 'css/sailthru-tag-autocomplete.css', __FILE__ ), array( 'sailthru-jquery-ui-theme-smoothness' ) );
			wp_enqueue_script( 'sailthru-tag-autocomplete-js', plugins_url( 'js/sailthru-tag-autocomplete.js', __FILE__ ), array( 'jquery-ui-autocomplete' ) );

			wp_localize_script( 'sailthru-tag-autocomplete-js', 'sailthru_admin_t_ac', array(
				'nonce' => wp_create_nonce( 'sailthru_t_ac_nonce' )
			) );

			wp_enqueue_script( 'pmc_fast_newsletter_js', plugins_url( 'js/content_builder_newsletters.js', __FILE__ ), array( 'jquery' ) );

			break;

		case 'admin_page_sailthru_fast_newsletter':

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'pmc_fast_newsletter_js', plugins_url( 'js/content_builder_newsletters.js', __FILE__ ), array( 'jquery' ) );

			break;
	}

	//Loads for any sailthrough page
	if ( $page !== 'post.php' && $page !== 'post-new.php' && strpos( $page, $prefix . 'sailthru_' ) === 0 ) {
		wp_enqueue_style( 'MMC_newsletter_admin_css', plugins_url( 'pmc-exacttarget/css/mmc_newsletter.css', dirname( __FILE__ ) ), false, $css_version, 'all' );
	}

	//Loads for add edit post and any sailthrough page
	if ( $page == 'post.php' || $page == 'post-new.php' || strpos( $page, $prefix . 'sailthru_' ) === 0 || strpos( $page, 'exacttarget_' ) === 0 ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'MMC_newsletter_postModule_css', plugins_url( 'pmc-exacttarget/css/mmc_newsletter_postModule.css', dirname( __FILE__ ) ), false, $css_version, 'all' );

		/* register & load mmc_newsletter.js */
		wp_register_script( 'mmc_newsletter_js', plugins_url( 'pmc-exacttarget/js/mmc_newsletter.js', dirname( __FILE__ ) ), array( 'jquery',
			'media-upload',
			'thickbox' ), $js_version );
		wp_enqueue_script( 'mmc_newsletter_js' );
		/* register the shared functions JS */
		wp_register_script( 'mmcnws_shared_js', plugins_url( 'pmc-exacttarget/js/shared_functions.js', dirname( __FILE__ ) ), array( 'jquery' ), $js_version );
		wp_enqueue_script( 'mmcnws_shared_js' );
	}
}

function sailthru_custom_uploader_scripts() {
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_register_script( 'mmc_upload', plugins_url( 'pmc-exacttarget/js/custom_image_uploader.js', dirname( __FILE__ ) ), array( 'jquery',
																														  'media-upload',
																														  'thickbox' ), '5.8' );
	wp_enqueue_script( 'mmc_upload' );
}

function sailthru_custom_uploader_styles() {
	wp_enqueue_style( 'thickbox' );
}


function sailthru_addfeed_activate() {
	sailthru_add_feed();
	//flush_rewrite_rules(); Add option in setting page to do so
}


function sailthru_create_image_size() {
	if ( get_option( 'mmcnewsletter_thumb_width' ) && get_option( 'mmcnewsletter_thumb_height' )
	) {
		add_image_size( 'mmc_newsletter_thumb', get_option( 'mmcnewsletter_thumb_width' ), get_option( 'mmcnewsletter_thumb_height' ), true );
	}

	if ( get_option( 'mmcnewsletter_feature_image_width' ) && get_option( 'mmcnewsletter_feature_image_height' )  ) {
		add_image_size( 'mmc_newsletter_featured', get_option( 'mmcnewsletter_feature_image_width' ), get_option( 'mmcnewsletter_feature_image_height' ), true );
	}
}
// Register the feed

function sailthru_add_feed() {
	add_feed( 'sailthru', 'sailthru_addfeed_do_feed' );
}

/*
 * Render the feed
*/
function sailthru_addfeed_do_feed( $in ) {
	header_remove();
	// disable caching as we don't want stale newsletter to be sent.
	// Also we remove featured post from the feed once newsletter is sent, caching might
	// keep the featured posts and send out stale newsletter. This feed is going to be
	// accessed by sailthru only once or twice a day max
	if ( !isset( $_GET['repeathash'] ) || !ctype_alnum( $_GET['repeathash'] ) ) {
		header( 'HTTP/1.0 400 Bad Request' );
		die;
	}

	// Moved originally uncovered legacy code into rss.php to get_data() for easier testing in a future ticket.
	// @codeCoverageIgnoreStart

	// Load the feed CPT
	// Ignoring Nonce Verification as legacy method until refactor.
	$repeat_hash = sanitize_text_field( $_GET['repeathash'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$rss         = PMC\Exacttarget\RSS::get_instance();
	$rss->render( $rss->get_data( $repeat_hash ), true );
	// @codeCoverageIgnoreEnd

}

function sailthru_get_post_authors( $current_post ){
	global $post;

	$post = $current_post;

	$coauthor = coauthors( null,  null,  null, null, false );

	wp_reset_postdata();

	return $coauthor;
}

function sailthru_fix_html_encoding( $text ) {
	// apply html encoding
	$text = htmlentities2( $text );
	// sanitize data with esc_html so json_encode won't throw error if string contains invalid utf8 characters
	$text = esc_html( $text );
	// convert selected entities back to character string
	$text = str_replace( array('&lt;','&gt;','&quot;','&#039;'), array('<','>','"','\''), $text );
	return $text;
}

function sailthru_newsletter_process_posts( &$post, $featured = false, $repeat ) {
	if ( sailthru_isset_notempty( $post ) ) {

		$author = sailthru_get_post_authors( $post );

		$featured_deck = get_post_meta( $post->ID, 'override_post_excerpt', true );
		$seo_description = get_post_meta( $post->ID, 'mt_seo_description', true );

		if ( empty( $featured_deck ) ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$featured_deck = $post->post_excerpt;
			} elseif ( ! empty( $seo_description ) ) {
				$featured_deck = $seo_description;
			}
		}

		$text = !empty( $featured_deck ) ? $featured_deck : sailthru_get_excerpt( $post->post_content, '<em>' );

		$primary_editorial = array();
		$editorial         = array();
		if ( taxonomy_exists( 'editorial' ) ) {

			if ( function_exists( 'variety_editorial_get_primary' ) ) {
				$p_editorial = variety_editorial_get_primary( $post );

				if ( !empty( $p_editorial ) && !is_wp_error( $p_editorial ) ) {

					$primary_editorial = array(
						'name' => isset( $p_editorial->name ) ? $p_editorial->name : "",
						'link' => isset( $p_editorial->link ) ? $p_editorial->link : ""
					);
				}

				$all_editorial = get_the_terms( $post->ID, 'editorial' );

				if ( !empty( $all_editorial ) )
					foreach ( $all_editorial as $edit ) {
						$editorial[] = array(
							'name' => isset( $edit->name ) ? $edit->name : "",
							'link' => get_term_link( $edit, 'editorial' )
						);
					}
			}
		}

		$post_categories = wp_get_post_categories( $post->ID );
		$cats            = array();

		if ( !empty( $post_categories ) )
			foreach ( $post_categories as $c ) {
				$cat    = get_category( $c );
				$cats[] = array( 'name' => $cat->name,
								 'link' => get_category_link( $cat->cat_ID ) );
			}

		$original_post = $post;

		if ( ! empty( $repeat['img_size'] ) ) {
			$img_size = $repeat['img_size'];
		} else {
			$img_size = 'large';
		}

		if ( ! empty( $repeat['img_type'] ) ) {
			$img_type = $repeat['img_type'];
		} else {
			$img_type = 'featured';
		}

		$post = array(
			'title'             => $post->post_title,
			'excerpt'           => sailthru_fix_html_encoding( $text ),
			'permalink'         => get_permalink( $post->ID ),
			'author'            => $author,
			'thumb'             => sailthru_get_featured_image( $post, $img_size, $img_type ),
			'categories'        => $cats,
			'primary_editorial' => $primary_editorial,
			'editorials'        => $editorial,
			'ID'                => $post->ID
		);

		// Entire method needs coverage; adding it is not in scope for Digital Daily project.
		$post = apply_filters( 'sailthru_process_recurring_post', $post, $original_post, $repeat, $img_size ); // @codeCoverageIgnore

	}
}

function sailthru_get_excerpt( $content, $allowed_tags = '' ) {

	if ( empty( $content ) ) {
		return '';
	}

	$text = ( ! empty( $allowed_tags ) ) ? strip_tags( $content, $allowed_tags ) : strip_tags( $content );

        //@ticket PPT-3595, @since 2014-11-13 Archana Mandhare
        //Allow text inside pmc_film_review_snippet shortcode for the newsletter
        if( has_shortcode( $text , 'pmc_film_review_snippet') ) {
            if ( 1 === preg_match('@\[pmc_film_review_snippet(.*?)\](.*?)\[/pmc_film_review_snippet\]@', $text, $matches ) ) {
                $text = str_replace( $matches[0], '#REVIEWSNIPPET#', $text );
                $text = strip_shortcodes( $text );
                $text = str_replace( '#REVIEWSNIPPET#', $matches[0], $text );
                $text = do_shortcode( $text );
            }
        }
        else {
            $text = strip_shortcodes( $text );
        }


	$text = str_replace( ']]>', ']]&gt;', $text );
	$text = str_replace( '/caption]', '', $text );
	$text = trim( $text );

	if ( empty ( $text ) ) {
		return '';
	}

	// extract 1st & 2nd paragraph
	$text = implode( " ", array_slice( array_filter( array_map( "trim", explode( "\n", $text ) ) ), 0, 2 ) );

	if ( mb_strlen( $text ) > 450 ) {
		$text = mb_substr( $text, 0, 450 );

		// fix issue with multibyte character get cut off
		// use esc_html to detect invalid character
		while ( !empty( $text) && '' === esc_html( $text ) )  {
			$text = mb_substr( $text, 0, -1 );
		}

		$text .= '...';
	}

	if ( ! empty( $allowed_tags ) ) {
		$text = balanceTags( $text, true );

		return PMC::untexturize( $text, 'html' );
	}

	return PMC::untexturize( $text );
}

function sailthru_get_featured_image( $post, $size = "", $type = "featured" ) {

	if ( class_exists( 'MultiPostThumbnails' ) && ! empty( $type ) && 'featured' != $type ) {
		$image_url = MultiPostThumbnails::get_post_thumbnail_url( get_post_type( $post->ID ), $type, $post->ID, $size );
		if ( ! empty( $image_url ) ) {
			return $image_url;
		}
	}

	$image_url = "";

	$image_id = get_post_thumbnail_id( $post->ID );

	$image = wp_get_attachment_image_src( $image_id, $size );

	if ( ! empty( $image[0] ) ) {
		$image_url = $image[0];
	}

	return $image_url;
}

function sailthru_get_post_thumb( $post, $width = null, $height = null ) {
	/*@since 2011-12-15 Amit Sannad
* for sailthru fastnewsletter $post is not post variable
* but is $_POST. So changing the code to adjust that.
*/
	if ( isset( $post->ID ) && intval( $post->ID ) > 0 ) {
		$post_id = $post->ID;
		$post_content = $post->post_content;
	} else if ( isset( $post['ID'] ) && intval( $post['ID'] ) > 0 ) {
		$post_id = $post['ID'];
		$post_content = $post['post_content'];
	} else if ( isset( $post['post_ID'] ) && intval( $post['post_ID'] ) > 0 ) {
		$post_id = $post['post_ID'];
		$post_content = $post['post_content'];
	}

	if ( !isset( $post_id ) || intval( $post_id ) < 1 )
		return '';

	$thumb = get_post_meta( $post_id, '__mmc_nws_preset_thumb', true );

	if ( empty( $thumb ) ) {
		/*
* Oh bugger, there's no custom thumb! Unsetting it so the default thumb can be loaded up.
*/
		unset( $thumb );
	}

	if ( empty( $thumb ) ) {
		//check for a specific thumbnail to be set through wordpress
		$thumb = apply_filters( 'mmc_newsletter_url_filter', get_the_post_thumbnail( $post_id, 'mmc_newsletter_thumb', array( 'class' => 'thumbnail' ) ) );

	}

	if ( empty( $thumb ) ) {

		$src = false;

		//check for all image attachments on the post, and grab the latest image if available
		$images = get_children( 'post_type=attachment&post_mime_type=image&post_parent=' . $post_id );

		if ( count( $images ) > 0 ) {
			$image = array_slice( $images, 0, 1 ); //grab the first image
			$image = $image[0];
			$src = mmc_newsletter_get_attachment_thumb( $image->ID );
		}

		if ( empty( $src ) ) {

			$img_id = mmc_newsletter_get_first_image_id( $post_content, $image_src );
			if ( $img_id ) {
				$src = mmc_newsletter_get_attachment_thumb( $img_id );
			} else {
				if ( $image_src ) {
					$image_attr = @getimagesize( $image_src );
					$src[] = $image_src;
					$src[] = $image_attr[0];
					$src[] = $image_attr[1];
				}
			}
		}

		if ( !empty( $src ) ) {
			$mmcnewsletter_thumb_width = $width ? $width : intval( get_option( 'mmcnewsletter_thumb_width' ) );
			$mmcnewsletter_thumb_height = $height ? $height : intval( get_option( 'mmcnewsletter_thumb_height' ) );
			if ( intval( $src[2] ) > 0 ) {
				if ( $mmcnewsletter_thumb_height > 0 ) {
					$thumb_style = ( ( $mmcnewsletter_thumb_width / $mmcnewsletter_thumb_height ) > ( $src[1] / $src[2] ) ) ? "max-height:{$mmcnewsletter_thumb_height}px;" : "width:{$mmcnewsletter_thumb_width}px;";
				} else {
					$thumb_style = ( ( 100 / 87 ) > ( $src[1] / $src[2] ) ) ? 'max-height:87px;' : 'width:100px;';
				}
			} else {
				$thumb_style = 'width:100px;';
			}
			$thumb = $src[0]; //just need img src
		}
	} elseif ( strpos( $thumb, '<img' ) !== false ) {

		//lets extract attributes from img tag
		$img = mmc_newsletter_get_img_attr( $thumb, array( 'src',
														 'height',
														 'width' ) );
		$thumb = $img['src']; //this is where most images come from
		if ( !empty( $img['width'] ) && !empty( $img['height'] ) ) {
			//$thumb_style = ((100/87) > ($$img['height']/$img['width'])) ? "height:100px;" : "width:87px;";
			$thumb_style = "width:{$img['width']}px;";
		}
		$width = get_option( 'mmcnewsletter_thumb_width' );
		$height = get_option( 'mmcnewsletter_thumb_height' );
		if ( (isset($img['width'])&& $img['width'] > $width ) || ( isset($img['height'] ) && $img['height'] > $height ) ) {
			$thumb = create_mmcnewsletter_feature_image( $thumb, $width, $height );
		}

	}
	//use default thumb image if that is set
	if ( empty( $thumb ) && isset( $meta['default_thumbnail_src'] ) && $meta['default_thumbnail_src'] != '' ) {
		//$thumb = "<img src='".apply_filters('mmc_newsletter_url_filter',$meta['default_thumbnail_src'])."' class='thumbnail' width='123' height='87' />";
		$thumb = apply_filters( 'mmc_newsletter_url_filter', $meta['default_thumbnail_src'] ); //just need img src
	}
	//no images or defaults found, don't display an image
	if ( empty( $thumb ) ) {
		$thumb = '';
	}
	return $thumb;
}

function sailthru_newsletter_get_most_popular( $num_of_articles, $query ) {

	$days                  = intval( $query['story_source_days'] );
	$num_of_articles       = min( 100, $num_of_articles );
	$top_articles          = array();
	$num_of_articles_fetch = intval( $num_of_articles ) * 2;

	if ( 'wp_most_popular' === $query['story_source'] ) {
		$vip_top_articles = PMC_Newsletter::get_vip_mostpopular( array(
			'limit'    => $num_of_articles_fetch,
			'duration' => $days
		) );
	} else {
		$vip_top_articles = wpcom_vip_top_posts_array( $days, $num_of_articles_fetch ); // Top 25 posts for past 1 day
	}

	$count                 = 0;

	foreach ( $vip_top_articles as $top_post ) {

		$post_type = get_post_type( $top_post['post_id'] );

		if ( ! is_supported_sailthru_post_type( $post_type ) ) {
			continue; //not a supported post type, move to next iteration
		}

		//type is post, we'll take it
		$top_articles[] = $top_post;
		$count ++;

		if ( $count >= $num_of_articles ) {
			break; //got all posts that we need, bail the loop
		}
	}

	return $top_articles;
}

function sailthru_where_filter( $where ) {

	if ( ! empty( $GLOBALS['pmc_newsletter_post_days'] ) ) {
		$mmc_newsletter_post_days = intval( $GLOBALS['pmc_newsletter_post_days'] );
		$where .= " AND post_date > '" . date( 'Y-m-d', strtotime( '-' . $mmc_newsletter_post_days . ' days' ) ) . "' ";
	}

	return $where;
}

function sailthru_orderby_filter( $ordeby ) {
	global $wpdb;
	$ordeby .= ", $wpdb->posts.post_date DESC";
	return $ordeby;
}

////////////////////////////////////////////////////////////////
//Initialize Plugin features such as admin pages and modules
////////////////////////////////////////////////////////////////


function sailthru_post_options() {
    //add box to add/edit post page where user can choose to send mass email on publish
    $supported_post_types = Config::get_instance()->get( 'supported_post_types' );
    if( !empty( $supported_post_types ) && is_array( $supported_post_types )){
        foreach ($supported_post_types as $supported_post_type ){
            if($supported_post_type !== 'page')
                add_meta_box( 'breaking_news', 'Breaking News Exacttarget', 'sailthru_fastnewsletter_box', $supported_post_type, 'advanced', 'high', NULL );
        }
    }else{
        add_meta_box( 'breaking_news', 'Breaking News Exacttarget', 'sailthru_fastnewsletter_box', 'post', 'advanced', 'high', NULL );
    }

}

function sailthru_newsletter_featured_post_module() {
    $supported_post_types = Config::get_instance()->get( 'supported_post_types' );
    if( !empty( $supported_post_types ) && is_array( $supported_post_types )){
        foreach ($supported_post_types as $supported_post_type ){
            if($supported_post_type !== 'page')
                add_meta_box( 'sailthru_newsletter_featured_post_module', 'Newsletter Featured Post', function() {
                }, $supported_post_type, 'advanced', 'high' );
        }
    }else{
        add_meta_box( 'sailthru_newsletter_featured_post_module', 'Newsletter Featured Post', function() {
        }, 'post', 'advanced', 'high' );
    }

}

function sailthru_fastnewsletter_box( $post, $args ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'mmc_breaking_news_nonce' );


	$post_is_draft = false;
	if ( strtolower( $post->post_status ) == 'draft' ) {
		$post_is_draft = true;

		//get the alert names which were selected by author on last save
		$selected_alerts = get_post_meta( $post->ID, '_sailthru_selected_alerts', true );
	}

	if ( empty( $selected_alerts ) || ! is_array( $selected_alerts ) ) {
		$selected_alerts = array();
	}

	$alert_types = sailthru_get_fast_newsletter();
	echo '<div id="pmc_sailthru_timegmt" data-date_time="' . esc_attr( get_option('gmt_offset') ) . '"></div>';

	$is_breaking_news_subject_enable = sailthru_is_breaking_news_subject_enabled();

	if ( $is_breaking_news_subject_enable ) {

		$custom_subject_line = get_post_meta( $post->ID, '_sailthru_alert_subject', true );
		$custom_subject_line = ( ! empty( $custom_subject_line ) ) ? sanitize_text_field( wp_unslash( $custom_subject_line ) ) : '';

		echo PMC::render_template( __DIR__ . '/templates/sailthru-fastnewsletter-metabox.php', array(
			'custom_subject_line' => $custom_subject_line,
		) );

	}

	if ( $alert_types ) {
		echo 'Select the breaking news alerts for which you want to send an email blast. <br /><br />';
		// get mmc_newsletter_list_counts
		// $list_counts = get_option('mmc_newsletter_list_counts');
		foreach ( $alert_types as $name => $alert ) {

			// Don't display newsletter if it's disabled.
			if ( ! empty( $alert['newsletter_status'] ) && 'disabled' === $alert['newsletter_status'] ) {
				continue;
			}

			$name = stripslashes( esc_html( $name ) );

			// Ignoring tests temporarily, will be adding after launch.
			$content_builder_label = ( ! empty( $alert['content_builder'] ) && 'yes' === $alert['content_builder'] ) ? ' (CB)' : ''; // @codeCoverageIgnore

			$is_checked = false;

			/*
			 * If current alert is in the saved list and this is a draft
			 * then alert should be selected
			 */
			if ( $post_is_draft === true && in_array( $name, $selected_alerts ) ) {
				$is_checked = true;
			}

			echo '<div class="mmc_breakingnews_selector"';

			if( sailthru_isset_notempty( $alert['post_tag_name'] ) ){
				echo ' style="display:none;" id="';
				$post_tag_name = stripcslashes( str_replace( "'", "", PMC::sanitize_title( $alert['post_tag_name'] ) ) );
				echo 'pmc_tag_'.esc_attr( $post_tag_name ).'"';
			}

			echo '>';
			echo '<div class="mmc_breakingnews_alert_checkbox">';
			echo "<input type=\"checkbox\" id=\"fastnewsletter-$name\" name=\"fastnewsletters[]\" value=\"" . esc_attr( $name ) . "\" class='fast_breaking_news_meta_field' " . checked( true, $is_checked, false ) . "/> &nbsp;";
			// Ignoring tests temporarily, will be adding after launch.
			echo '<label for="fastnewsletter-' . esc_attr( $name ) . '">' . esc_html( stripslashes( $name . $content_builder_label ) ) . '</label>'; // @codeCoverageIgnore
			echo '</div>';
			echo '</div>';

			unset( $is_checked );
		}

		// Some times editors want to send alerts with changes again. We have a lock that multiple alerts needs to be spaced out 5 mins apart. This setting is to override that.
		?>
		<br/><br/>
		<div class="mmc_breakingnews_alert_checkbox">
			<input type="checkbox" value="override" name="pmc-et-override-send"/>
			<label for="pmc-et-override-send">Special Event Coverage Override<br/><i>Check this box if you want to send breaking news within five minutes of previous send</i></label>
		</div>
		<?php

		//get the alert log
		$alert_log = get_post_meta( $post->ID, '_sailthru_alert_blast_log', true );

		if ( ! empty( $alert_log ) && is_array( $alert_log ) ) {
			$timezone = sailthru_get_site_timezone();

			echo '<br /><br />';
			echo '<strong>Alert Blast Log:</strong><br />';

			krsort( $alert_log );

			$log_list = '';

			foreach ( $alert_log as $timestamp => $log ) {
				$alert_user = get_userdata( $log['user'] );

				if ( ! $alert_user ) {
					continue;
				}

				$log_list .= '<li>';

				$log_list .= '<strong>' . esc_html( PMC_TimeMachine::create( $timezone )->from_time( 'U', $timestamp )->format_as( 'Y-m-d H:i' ) ) . '</strong> -';
				$log_list .= ' Sent by <strong><em>' . esc_html( $alert_user->user_nicename ) . '</em></strong>';

				//run esc_html() on each item in array before concatenating them in string
				$log_list .= ' to <em>' . implode( '</em>, <em>', array_map( 'esc_html', $log['lists'] ) ) . '</em>';
				$log_list .= '</li>';

				unset( $alert_user );
			}

			if ( ! empty( $log_list ) ) {
				echo '<ul>';
				echo $log_list;
				echo '</ul>';
			}

			unset( $log_list, $timezone );
		}

		// temp_bna - Add send buton for hubs
		if ( 'publish' === $post->post_status && 'pmc-hub' === $post->post_type ) {
			echo sprintf(
				'<br><button class="js-SendBna components-button is-primary">%s</button>',
				esc_html( __( 'Send Breaking News Alert', 'pmc-exacttarget' ) )
			);

			echo sprintf(
				'<p class="js-SendBna-help-text" style="font-style:italic;">%s</p>',
				esc_html( __( 'Only click this button after the content of this page is saved and published otherwise out of date content will be included in the alert.', 'pmc-exacttarget' ) )
			);
		}

		unset( $alert_log );
	} else {
		echo 'No breaking news alerts available';
	}
}

/**
 * Save handler for Classic Editor metabox.
 *
 * @param int $post_id Post ID.
 */
function sailthru_publish_to_sailthru( $post_id = 0 ) {
	global $pagenow;

	if ( ! $post_id ) {
		// Legacy, untestable code.
		return; // @codeCoverageIgnore
	}

	if (
		! isset( $_POST['mmc_breaking_news_nonce'] )
		|| ! wp_verify_nonce(
			\PMC::filter_input(
				INPUT_POST,
				'mmc_breaking_news_nonce'
			),
			plugin_basename( __FILE__ )
		)
	) {
		return;
	}

	$post_status = get_post_status( $post_id );

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	// Don't proceed if post status is other than publish or future.
	if (
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! in_array(
			// Sniff is confused.
			// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
			$post_status,
			[
				'publish',
				'future',
			],
			true
		)
	) {
		return;
	}

	// Check permissions & bail if the post type is not on the list of supported post types changed 10-03 Adaeze Esiobu
	// Legacy, untestable code.
	// @codeCoverageIgnoreStart
	if ( ! is_supported_sailthru_post_type( get_post_type( $post_id ) ) ) {
		return;
	} elseif ( 'page' === get_post_type( $post_id ) ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	// @codeCoverageIgnoreEnd

	sailthru_process_featured_posts(
		$post_id,
		// Sanitized in the function for backcompat.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$_POST['st_recurring_newsletters'] ?? []
	);


	//check to be sure this is the the edit post page, not quick edit, edit from email, or any other crazy wordpress ways to edit a post
	if ( 'post.php' !== $pagenow ) {
		return;
	}

	// Legacy, untestable code.
	// @codeCoverageIgnoreStart
	$subject = isset( $_POST['fastnewsletters-subject'] )
		? sanitize_text_field(
			wp_unslash(
				$_POST['fastnewsletters-subject']
			)
		)
		: null;
	// @codeCoverageIgnoreEnd

	sailthru_process_bnas(
		$post_id,
		// Sanitized in the function for backcompat.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$_POST['fastnewsletters'] ?? [],
		$subject,
		isset( $_POST['pmc-et-override-send'] ) && 'override' === $_POST['pmc-et-override-send'],
		isset( $_GET['breakingNewsBlast'] ),
		false
	);

	//when a post contains image, save_post hook is called twice.
	//Removing action to prevent this so that email is not sent more than once
	remove_action( 'save_post', 'sailthru_publish_to_sailthru' );
}

/**
 * Process selections for newsletters' featured posts.
 *
 * @param int   $post_id               ID of featured post.
 * @param array $recurring_newsletters Newsletters chosen to feature the post.
 */
function sailthru_process_featured_posts(
	int $post_id,
	array $recurring_newsletters
): void {
	// Argument is strictly typed.
	// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
	$recurring_newsletters = array_map( 'intval', $recurring_newsletters );

	$repeat_array = array();
	foreach ( (array) Sailthru_Blast_Repeat::get_repeats() as $repeat ) {

		if ( ! empty( $repeat['repeat_id'] ) ) {

			// Legacy, untestable code.
			// @codeCoverageIgnoreStart
			// Argument is strictly typed.
			// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
			if ( in_array( (int) $repeat['repeat_id'], $recurring_newsletters, true ) ) {
				if ( 0 !== $post_id ) {
					// @codeCoverageIgnoreEnd
					Sailthru_Blast_Repeat::save_featured_post( $post_id, $repeat['repeat_id'] );
				}
			} else {

				$repeat_array[] = $repeat['repeat_id'];

			}

		}
	}

	if ( ! empty( $repeat_array ) ) {
		Sailthru_Blast_Repeat::remove_featured_post( $post_id, $repeat_array );
	}
}

/**
 * Process a post's Breaking News Alerts.
 *
 * $_POST data is process only for requests from `sailthru_publish_to_sailthru`,
 * which performs nonce verification.
 * phpcs:disable WordPress.Security.NonceVerification.Missing
 *
 * @param int         $post_id          Post ID.
 * @param array       $lists            Lists to send to.
 * @param string|null $subject_override Custom subject.
 * @param bool        $override_lock    For special event coverage, allow alerts
 *                                      every 30 seconds, rather than every five
 *                                      minutes.
 * @param bool        $is_not_duplicate Legacy metabox protection against
 *                                      duplicate sends.
 * @param bool        $prefer_wp_data   Retrieve data using WP APIs rather than
 *                                      from raw $_POST input, such as with
 *                                      Gutenberg.
 */
function sailthru_process_bnas(
	int $post_id,
	array $lists,
	?string $subject_override = null,
	bool $override_lock = false,
	bool $is_not_duplicate = true,
	bool $prefer_wp_data = true
): void {
	global $wpdb;

	$post_status = get_post_status( $post_id );
	// Legacy code.
	// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
	$log_meta_key = sprintf( 'sailthru_breaking_news_alert_logs_%s', current_time( 'timestamp' ) );
	$current_time = current_time( 'mysql' );

	//delete the list of selected alerts, no matter what post status is
	delete_post_meta( $post_id, '_sailthru_selected_alerts' );

	/**
	 * If post is a draft then save the selection (if any) of alerts
	 */
	if ( 'draft' === $post_status && ! empty( $lists ) ) {
		$selected_alert_lists = array_unique(
			array_filter(
				array_map(
					'sanitize_text_field',
					array_map(
						// Sniff is confused.
						// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
						'trim',
						$lists
					)
				)
			)
		);

		if ( ! empty( $selected_alert_lists ) ) {
			add_post_meta( $post_id, '_sailthru_selected_alerts', $selected_alert_lists, true );
		}

		unset( $selected_alert_lists );
	}

	$is_breaking_news_subject_enable = sailthru_is_breaking_news_subject_enabled();

	if ( $is_breaking_news_subject_enable ) {

		/**
		 * Manage custom breaking newsletter subject line.
		 * Note : we are not going to save only for draft as 'Selecte Alrets'
		 * Because we will need 'subject' for next time as well.
		 */
		if ( ! empty( $subject_override ) ) {
			update_post_meta( $post_id, '_sailthru_alert_subject', $subject_override );
		} else {
			delete_post_meta( $post_id, '_sailthru_alert_subject' );
		}

	}

	if (
		wp_is_post_revision( $post_id ) //if the id is for a revision do nothing
		|| ! in_array( strtolower( $post_status ), [ 'future', 'publish' ], true ) //Lets make sure no matter what, a draft should never send an email blast
		|| 'page' === get_post_type( $post_id ) // is the current post a page? if so dont send
		|| ( ! empty( get_post_field( 'post_password', $post_id ) ) ) //Are we sending notifications for password protected posts? NO
	) {

		add_post_meta( $post_id, $log_meta_key, sprintf( '%s : Post_status(in $_POST) = %s', $current_time, $post_status ) );

		return;
	}

	//OK, we made it through all the default checks.
	//Now lets check all the active alert types

	$alert_lists             = array();
	$user_id_for_future_post = 0;

	$fastnewsletters_array = sailthru_get_fast_newsletter();
	$scheduled_array       = array();
	foreach ( $lists as $fastnewsletter_name ) {

		if ( isset( $fastnewsletters_array[ $fastnewsletter_name ] ) ) {
			$fastnewsletter = $fastnewsletters_array[ $fastnewsletter_name ];
		} else { //the checked fastnewsletter option doesn't exist
			return;
		}

		// Don't process the disabled BNA, this scenario is unlikely to happen.
		// Ignoring tests temporarily, will be adding after launch. Exact_Target::get_email_from_content_builder() is already unit tested.
		// @codeCoverageIgnoreStart
		if ( ! empty( $fastnewsletter['newsletter_status'] ) && 'disabled' === $fastnewsletter['newsletter_status'] ) {
			continue;
		}
		// @codeCoverageIgnoreEnd

		if ( ! $is_not_duplicate ) {
			return;
		}
		// Code to prevent same breaking news to be sent twice in 30 seconds interval
			// Logic: 	1 Insert a post meta value for the postID and obtain a lock. (timestamp + unique lock id)
			//			2 Select back the number of attempted locks on the postID
			//			3.1 If only your lock is returned, you are good to go
			//			3.2 If more than one returned.
			//			3.2.1 If other meta_entries older than 1 minute. Delete them. (lock time out safety mechanism)
			//			3.3.2 Deadlock is broken by lower meta_id
			//			4 Send the alert
			//

		$my_time = microtime( true ); // Allows lock expiry

		$my_lock_id = uniqid( '', true ); // Uniquely identifies lock requests

		$i_have_lock = false;

		$invalid_lock_ids = array(); // collect invalid lockids for deletion

		$log = array(
			'title' => wp_kses_data( get_post_field( 'post_title', $post_id ) ),
			'alert' => wp_kses_data( $fastnewsletter_name ),
			// Legacy code.
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			'time'  => date( 'r', $my_time ),
		);
		if ( isset( $fastnewsletter['list'] ) ) {
			$log['alert list'] = wp_kses_data( stripslashes( $fastnewsletter['list'] ) );
		} elseif ( isset( $fastnewsletter['dataextension'] ) ) {
			$log['alert dataextension'] = wp_kses_data( stripslashes( $fastnewsletter['dataextension'] ) );
		}

		$alert_name = sanitize_title_with_dashes( $fastnewsletter_name );

		if ( 'future' !== $post_status ) {
			add_post_meta( $post_id, '_MMGDupeAlertLock_' . $alert_name, $my_time . ' --- ' . $my_lock_id ); // @codingStandardsIgnoreLine
		}

		// Method is only called from admin context.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, post_id, meta_key, meta_value
FROM $wpdb->postmeta WHERE post_id = %d AND
meta_key = %s ORDER BY meta_id",
				$post_id,
				'_MMGDupeAlertLock_' . $alert_name
			)
		);

		$is_scheduled_post = false;

		if ( empty( $result ) && 'future' === $post_status ) {
			$is_scheduled_post = true;
		}

		$lock_duration = 300; //5 mins by default

		//Override lock duration to 30 secs if checkbox is checked
		if ( $override_lock ) {
			$lock_duration = 30;
		}

		foreach ( $result as $entry ) {

			if ( "$my_time --- $my_lock_id" === $entry->meta_value && 1 === $wpdb->num_rows ) {

				$i_have_lock = true;

				break; // We have lock!
			}

			$tmp_entry_time = explode( ' --- ', $entry->meta_value );

			$entry_time = $tmp_entry_time[0];

			// Legacy, untestable code.
			$entry_lock_id = $tmp_entry_time[1]; // @codeCoverageIgnore

			// Check if this lock was taken more than 300 seconds before me. If so, lock has expired, if not, respect it.

			// Legacy, untestable code.
			if ( $entry_time > ( $my_time - $lock_duration ) && $entry_lock_id !== $my_lock_id ) { // @codeCoverageIgnore

				// you cannot lock.

				$i_have_lock = false;

				break;
				// Legacy, untestable code.
			} elseif ( $entry_lock_id === $my_lock_id ) { // @codeCoverageIgnore

				// I can get lock

				$i_have_lock = true;

				break;
			} else {
				// Legacy, untestable code.
				$invalid_lock_ids[] = $entry->meta_id; // @codeCoverageIgnore
			}
		}

		// Yay! I have lock!
		if ( true === $i_have_lock || true === $is_scheduled_post ) {

			//send blast
			$template = stripslashes( $fastnewsletter['template'] );

			/**
			 * If custom subject is available in post then use it, instead of Post title.
			 *
			 * Note : If 'Custom Subject' Option enable in Theme option then only
			 * replace Custom Subject other wise use 'Post title'
			 * Ticket : CDWE-136
			 */
			if ( $is_breaking_news_subject_enable ) {
				$clean_subject = ! empty( $subject_override ) ? $subject_override : get_post_field( 'post_title', $post_id );

				/**
				 * If custom subject line is provided in post,
				 * Then pass it to 'Exact_Target::send_fast_newsletter()',
				 * so it can understand wheather it is custom subject line or default one.
				 */
				if ( ! empty( $subject_override ) ) {
					$fastnewsletter['custom_subject'] = true;
				}
			} else {
				$clean_subject = get_post_field( 'post_title', $post_id );
			}

			$clean_subject   = str_replace( [ '&amp;', ' & ' ], [ '&', ' and ' ], $clean_subject );
			$clean_subject   = sailthru_htmlentities_to_strings( $clean_subject );
			$subject         = str_replace( '[title]', stripslashes( $clean_subject ), stripslashes( $fastnewsletter['subject'] ) );
			$seo_description = get_post_meta( $post_id, 'mt_seo_description', true );

			if ( $prefer_wp_data ) {
				$override_post_excerpt = get_post_meta( $post_id, 'override_post_excerpt', true );
			} elseif ( isset( $_POST['override_post_excerpt'] ) ) {
				// Legacy, untestable code.
				$override_post_excerpt = PMC::filter_input( INPUT_POST, 'override_post_excerpt' ); // @codeCoverageIgnore
			} else {
				$override_post_excerpt = '';
			}

			if ( $prefer_wp_data ) {
				$post_excerpt = get_post_field( 'post_excerpt', $post_id );
			} elseif ( isset( $_POST['excerpt'] ) ) {
				// Legacy, untestable code.
				$post_excerpt = PMC::filter_input( INPUT_POST, 'excerpt' ); // @codeCoverageIgnore
			} else {
				$post_excerpt = '';
			}

			if ( $prefer_wp_data ) {
				$post_content = get_post_field( 'post_content', $post_id );
			} elseif ( isset( $_POST['post_content'] ) ) {
				// Legacy, untestable code.
				$post_content = PMC::filter_input( INPUT_POST, 'post_content' ); // @codeCoverageIgnore
			} else {
				$post_content = '';
			}

			if ( ! empty( $override_post_excerpt ) ) {
				$excerpt = $override_post_excerpt;
			} elseif ( ! empty( $post_excerpt ) ) {
				$excerpt = $post_excerpt;
			} elseif ( ! empty( $seo_description ) ) {
				$excerpt = wp_kses_post( $seo_description );
			} else {
				$excerpt = ( ! empty( $post_content ) ? $post_content : '' );
			}
			$excerpt = sailthru_get_excerpt( $excerpt );

			$author = '';

			if ( $prefer_wp_data ) {
				$author = \PMC::get_post_authors_list( $post_id, 'all', 'display_name' ) ?? '';
				$author = str_replace( ',', ', ', $author );
			} elseif ( isset( $_POST['coauthors-main'] ) ) {
				$author = sanitize_text_field( wp_unslash( $_POST['coauthors-main'] ) );
			}

			if (
				empty( $author )
				&& ! empty( $_POST['features']['byline_tab']['authors'] )
				&& is_array( $_POST['features']['byline_tab']['authors'] )
			) {
				// Legacy, untestable code
				// @codeCoverageIgnoreStart
				$authors = array_map(
					// Sniff is confused.
					// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
					'sanitize_text_field',
					$_POST['features']['byline_tab']['authors']
				);
				// @codeCoverageIgnoreEnd

				// Legacy, untestable code.
				$byline = []; // @codeCoverageIgnore

				foreach ( $authors as $slug ) {
					$co_author = $GLOBALS['coauthors_plus']->get_coauthor_by( 'user_nicename', $slug );
					$byline[]  = sanitize_text_field( $co_author->display_name );
				}
				$author = implode( ', ', $byline );
			}

			// Determine the post tags
			$post_tag_ids = [];
			$tag_names    = [];
			$tags         = '';

			// Fetch tags entered in the default WP 'Tags' meta box
			// The is_array() check exists to silence warnings when a string is passed.
			// Exploding the string might be the correct approach, but that would change functionality whereas the is_array() check simply silences a warning.
			if ( $prefer_wp_data ) {
				$post_tag_ids = wp_get_object_terms(
					$post_id,
					'post_tag',
					[
						'fields' => 'ids',
					]
				);
			} elseif ( ! empty( $_POST['tax_input']['post_tag'] ) && is_array( $_POST['tax_input']['post_tag'] ) ) {
				// Parameter is verified above.
				// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
				$post_tag_ids = array_map( 'absint', $_POST['tax_input']['post_tag'] );
			}

			// Fetch tags entered through the Fieldmanager 'Relationships' meta box.
			if (
				empty( $post_tag_ids )
				&& ! empty( $_POST['relationships']['post_tag']['terms'] )
				&& is_array( $_POST['relationships']['post_tag']['terms'] )
			) {
				// Legacy, untestable code.
				// @codeCoverageIgnoreStart
				$post_tag_ids = array_map(
					// Sniff is confused.
					// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
					'absint',
					$_POST['relationships']['post_tag']['terms']
				);
				// @codeCoverageIgnoreEnd
			}

			// Loop through the array of tag ids and
			// build a comma-delimited string of the tag names
			// e.g. "tag 1, Another Tag, this is my taggg"
			if ( ! empty( $post_tag_ids ) ) {
				foreach ( $post_tag_ids as $tag_id ) {
					if ( ! empty( $tag_id ) ) {
						// Legacy, untestable code.
						$post_tag    = get_term_by( 'id', $tag_id, 'post_tag' ); // @codeCoverageIgnore
						$tag_names[] = sanitize_text_field( $post_tag->name );
					}
				}

				if ( ! empty( $tag_names ) ) {
					$tags = implode( ', ', $tag_names );
				}
			}

			$thumb_data = (
				isset( $_POST['ID'], $_POST['post_content'] )
				|| isset( $_POST['post_ID'], $_POST['post_content'] )
			)
				// Legacy, untestable code.
				? $_POST // @codeCoverageIgnore
				: get_post( $post_id );

			$options = array(
				'subject' => wp_strip_all_tags( $subject ),
				'vars'    => array(
					'content'   => get_the_content( null, false, $post_id ),
					'title'     => get_post_field( 'post_title', $post_id ),
					'thumb'     => sailthru_get_post_thumb( $thumb_data ),
					'permalink' => get_permalink( $post_id ),
					'excerpt'   => str_replace( '"', '&#34;', stripslashes( $excerpt ) ),
					'author'    => esc_html( $author ),
					'tags'      => esc_html( $tags ),
				),
			);

			$options = apply_filters( 'sailthru_options', $options, get_post( $post_id ) );

			if ( 'future' === $post_status ) {
				$fastnewsletter['subject']  = $options['subject'];
				$fastnewsletter['template'] = wp_unslash( $template );
				$fastnewsletter['name']     = $fastnewsletter_name;

				$scheduled_array[] = $fastnewsletter;

				//Store data in post meta, so that we can send breaking news alerts when scheduled post
				//is published.
				update_post_meta( $post_id, '_sailthru_breaking_news_post_data', $options );
				update_post_meta( $post_id, '_sailthru_breaking_news_meta_data', $scheduled_array );

				$log['scheduled'] = 'Breaking news scheduled for this future post';

				$user_id_for_future_post = get_current_user_id();
			} else {
				//Post is already published, so no need to have these post meta's set for
				// scheduled breaking news.
				delete_post_meta( $post_id, '_sailthru_breaking_news_post_data' );
				delete_post_meta( $post_id, '_sailthru_breaking_news_meta_data' );

				$fastnewsletter['subject']  = $options['subject'];
				$fastnewsletter['template'] = wp_unslash( $template );
				$result                     = PMC_Newsletter::send_fast_newsletter( $fastnewsletter, $options );
			}

			if ( ! empty( $result['error'] ) ) {
				$log['error'] = wp_json_encode( $result );
			} else {
				$log['blast_id'] = wp_json_encode( $result );

				if ( 'future' !== $post_status ) {
					//blast went out alright, log it
					$alert_lists[] = $fastnewsletter_name;
				}
			}

			// release all invalid locks!
			if ( ! empty( $invalid_lock_ids ) ) {
				// Legacy, untestable code.
				$invalid_lock_str = implode( ',', $invalid_lock_ids ); // @codeCoverageIgnore

				//$alert_name is sanitized sanitize_title_with_dashes
				// Legacy, untestable code.
				// @codeCoverageIgnoreStart
				// Only called in admin context.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->query(
					// Cannot prepare IN clause.
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->prepare(
						"DELETE FROM {$wpdb->postmeta}
						WHERE post_id = %d
							AND meta_key = %s
							AND meta_id in ({$invalid_lock_str})",
						$post_id,
						'_MMGDupeAlertLock_' . $alert_name
					)
					// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					// @codeCoverageIgnoreEnd
				);
			}
		} else {
			$log['error'] = 'Tried to send but the blast was locked';
		}

		update_post_meta( $post_id, "fastnewsletter_log_{$fastnewsletter_name}_{$my_time}", wp_json_encode( $log ) );
	}

	//If none of the breaking news is selected then no one wants to schedule breaking news alert.
	//Therefore delete the post meta's.
	if ( empty( $lists ) ) {
		delete_post_meta( $post_id, '_sailthru_breaking_news_post_data' );
		delete_post_meta( $post_id, '_sailthru_breaking_news_meta_data' );
	}

	//post is scheduled for future, save user ID
	if ( $user_id_for_future_post > 0 ) {
		update_post_meta( $post_id, '_sailthru_alert_log_userid', $user_id_for_future_post );
	}

	//save breaking news alert log
	sailthru_save_alert_log( $post_id, get_current_user_id(), $alert_lists );

	// phpcs:enable WordPress.Security.NonceVerification.Missing
}


function sailthru_create_post_type() {

	register_post_type(
		'sailthru_fast',
		array(
			 'label'  => __('PMC  Fast Sailthru Newsletters', 'pmc-sailthru' ),
			 'public'  => false,
			 'rewrite' => false,
		)
	);

	register_post_type(
		'sailthru_recurring',
		array(
			 'label'  => __('PMC Recurring Sailthru Newsletters', 'pmc-sailthru' ),
			 'public'  => false,
			 'rewrite' => false,
		)
	);
}

function sailthru_save_fast_newsletter( $post_data  ) {

	$post_present = get_posts( array( 'post_type' => 'sailthru_fast',
									'numberposts' => 1 )
	);
	if ( isset( $post_data ) && !empty( $post_data ) )
		$post_data = addslashes( serialize( $post_data ) );
	else
		$post_data = '';
	$data = array(
		'post_name' => '',
		'post_type' => 'sailthru_fast',
		'post_title' => "Sailthru Fast News Letter",
		'post_content' => $post_data,
		'post_status' => 'publish',
		'post_date' => current_time( 'mysql' )
	);
	remove_action( 'save_post', 'sailthru_publish_to_sailthru' );
	if ( isset( $post_present[0] ) && !empty( $post_present[0] ) && isset( $post_present[0]->ID ) && $post_present[0]->ID > 0 ) {
		$data['ID'] = $post_present[0]->ID;
		wp_update_post( $data );


	} else {
		 wp_insert_post( $data );
	}

	add_action( 'save_post', 'sailthru_publish_to_sailthru' );
}

function sailthru_get_fast_newsletter() {
	// There's only 1 "sailthru_fast" post, used as a datastore
	$post_latest = get_posts( array( 'post_type' => 'sailthru_fast',
								   'numberposts' => 1 ) );
	if ( ! $post_latest || ! isset($post_latest[0]->post_content) ) {
		return null;
	}
	return \pmc_et_maybe_decode( $post_latest[0]->post_content );
}


/** Sailthru Sign up shortcodes */
/**
 * Add custom field in post tag edit page
 *
 * @param $tag
 */
function pmc_add_tag_custom_field( $tag ){
    $term_id = $tag->term_id;
    $term_sailthru_url = '';
    $taxonomy_term_sailthru = pmc_get_option( "pmc_post_tag_custom_field_sailthru" );

    if( isset( $taxonomy_term_sailthru ) && isset( $taxonomy_term_sailthru[$term_id] ) ){
        $term_sailthru_url = $taxonomy_term_sailthru[$term_id];
    }

    wp_nonce_field( __FILE__, 'pmc_tag_custom_field' );
    ?>

<tr class="form-field">
    <th scope="row" valign="top">
        <label for="Sailthru">Sailthru Signup Link</label>
    </th>
    <td>
        <input type="text" name="term_sailthru_url" id="term_sailthru_url" size="25" style="width:60%;" value="<?php echo esc_url($term_sailthru_url); ?>">
        <br />
        <span class="description">Sailthru Newsletter Signup Link per Tag</span>
    </td>
</tr>

<?php
}


/**
 * Save custom field in post tag edit page
 *
 * @param $term_id
 *
 * @return mixed
 */
function pmc_save_tag_custom_field( $term_id ){

    if ( !current_user_can( 'publish_posts' ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['pmc_tag_custom_field'], __FILE__ ) ) {
        return;
    }

    $option_name = 'pmc_post_tag_custom_field_sailthru';

    if( isset( $_POST['term_sailthru_url'] )  ){

        $taxonomy_term_sailthru = pmc_get_option( $option_name );

        if( !isset( $taxonomy_term_sailthru ) || !is_array( $taxonomy_term_sailthru ) ){
            $taxonomy_term_sailthru = array();
        }

        if( !empty( $_POST['term_sailthru_url'] ) ){
            $sailthru_url = esc_url_raw( $_POST['term_sailthru_url'] );

            $domain = parse_url( $sailthru_url, PHP_URL_HOST );

            if( 'cb.sailthru.com' != $domain ){
                return;
            }

            $taxonomy_term_sailthru[$term_id] = $sailthru_url;

        } else{

            unset( $taxonomy_term_sailthru[$term_id] );
        }

        pmc_update_option( $option_name, $taxonomy_term_sailthru );
    }
}


function pmc_render_newsletter_signup( $atts =array() ){
    if( is_single() ){

        extract( shortcode_atts( array(
            'sailthru_default_url' => 'https://cb.sailthru.com/join/1p5/tlsignup-article',
			'eventracking' => '',
        ), $atts ) );

        ob_start();
        ?>
    <div id="newsletter-sign-up">
        <?php


        $sailthru_url = $sailthru_default_url;

        $taxonomy_term_sailthru = pmc_get_option( "pmc_post_tag_custom_field_sailthru" );

        $tags = wp_get_post_tags( get_the_ID(), array( 'orderby' => 'count', 'order' => 'desc' ) );
        if ( $tags ) {
            if ( count($tags) > 1 )
                $tags = array_slice( $tags, 0, 1 );

            $tag_id = join( ', ', wp_list_pluck( $tags, 'term_id' ) );

            if( isset($taxonomy_term_sailthru) && is_array($taxonomy_term_sailthru) && isset( $taxonomy_term_sailthru[$tag_id] ) ){
                $sailthru_url = $taxonomy_term_sailthru[$tag_id];
            }
        }
        ?>
		<div id="pmc_sailthru_signup"><iframe src="<?php echo esc_url($sailthru_url);?>" style="width:100%;height:118px;" frameborder="0" allowTransparency="true"></iframe></div>
    </div>
    <?php

        $html = ob_get_clean();
        return $html;

    }
}


function sailthru_htmlentities_to_strings( $text ) {
	$find    = array( '&lt;',
					  '&gt;',
					  '&hellip;',
					  '&ldquo;',
					  '&rdquo;',
					  '&lsquo;',
					  '&rsquo;',
					  '&mdash;',
					  '&ndash;' );
	$replace = array( '<',
					  '>',
					  '...',
					  '"',
					  '"',
					  '',
					  '',
					  '--',
					  '--' );

	$text = str_replace( $find, $replace, $text );

	return $text;
}

/**
 * Transiton of post from future to publish.
 * Send breaking news alert if its set to.
 *
 * @param $post
 */
function sailthru_breaking_news_scheduled_post( $post ) {

	if ( !isset( $post ) || !isset( $post->ID ) ) {
		return;
	}

	$post_id = $post->ID;

	if ( empty( $post_id ) ) {
		return;
	}

	$options         = get_post_meta( $post_id, '_sailthru_breaking_news_post_data', true );
	$scheduled_array = get_post_meta( $post_id, '_sailthru_breaking_news_meta_data', true );

	/*
	 * No Breaking News is set, bail out.
	 */
	if ( empty( $options ) || empty( $scheduled_array ) ) {
		return;
	}

	$alert_lists = array();

	$attempted = false;
	foreach ( $scheduled_array as $fastnewsletter ) {
		$log = array( 'time'      => current_time( 'mysql' ),
					  'template'  => $fastnewsletter["template"],
					  'list'      => $fastnewsletter["dataextension"],
					  'post_data' => $options );

		// Don't process the disabled BNA, this scenario is unlikely to happen.
		// Ignoring tests temporarily, will be adding after launch. Exact_Target::get_email_from_content_builder() is already unit tested.
		// @codeCoverageIgnoreStart
		if ( ! empty( $fastnewsletter['newsletter_status'] ) && 'disabled' === $fastnewsletter['newsletter_status'] ) {
			continue;
		}
		// @codeCoverageIgnoreEnd

		$alert_name = sanitize_title_with_dashes( $fastnewsletter['name'] );

		$my_time    = microtime( true ); // Allows lock expiry

		$my_lock_ID = uniqid( '', true ); // Uniquely identifies lock requests

		add_post_meta( $post_id, '_MMGDupeAlertLock_' . $alert_name, $my_time . ' --- ' . $my_lock_ID );

		global $wpdb;

		//Copy the exact same code from publish_to_sailthru function
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, post_id, meta_key, meta_value
FROM $wpdb->postmeta WHERE post_id = %d AND
meta_key = %s ORDER BY meta_id", $post_id, '_MMGDupeAlertLock_' . $alert_name ) );

		foreach ( $result as $entry ) {

			if ( $entry->meta_value === "$my_time --- $my_lock_ID" && 1 === $wpdb->num_rows ) {

				$i_have_lock = true;

				break; // We have lock!
			}

			$tmp_entry_time = explode( ' --- ', $entry->meta_value );

			$entry_time = $tmp_entry_time[0];

			$entry_lock_ID = $tmp_entry_time[1];

			// Check if this lock was taken more than 300 seconds before me. If so, lock has expired, if not, respect it.

			if ( $entry_time > ( $my_time - 300 ) && $entry_lock_ID <> $my_lock_ID ) {

				// you cannot lock.

				$i_have_lock = false;

				break;
			} else if ( $entry_lock_ID === $my_lock_ID ) {

				// I can get lock

				$i_have_lock = true;

				break;
			} else {

				array_push( $invalid_lock_IDs, $entry->meta_id );
			}
		}

		// Yay! I have lock!

		if ( true === $i_have_lock ) {

			$result = PMC_Newsletter::send_fast_newsletter( $fastnewsletter, $options );

			if ( isset( $result['error'] ) ) {
				$log['error'] = $result['errormsg'];
			} else {
				$log['blast'] = $result;

				//blast went out alright, log it
				$alert_lists[] = $fastnewsletter['name'];
			}

		}else{
			$log['error'] = 'Tried to send but the blast was locked';
		}
		$log['type'] = 'scheduled';

		$now = time();
		$attempted = true;

		update_post_meta( $post_id, sanitize_text_field( "fastnewsletter_log_{$fastnewsletter['name']}_{$now}" ), json_encode( $log ) );
	}

	//save breaking news alert log
	$user_id = get_post_meta( $post_id, '_sailthru_alert_log_userid', true );
	delete_post_meta( $post_id, '_sailthru_alert_log_userid' );

	if ( ! empty( $alert_lists ) && ! empty( $user_id ) ) {
		sailthru_save_alert_log( $post_id, $user_id, $alert_lists );
	}

	unset( $user_id, $alert_lists );

	/**
	 * if Attempt has been made to send breaking news alert, delete post metas.
	 */
	if ( $attempted ) {
		delete_post_meta( $post_id, '_sailthru_breaking_news_post_data' );
		delete_post_meta( $post_id, '_sailthru_breaking_news_meta_data' );
	}
}

/**
 * Return timezone string or GMT Offset set in current site settings
 *
 * @since 2014-04-08 Amit Gupta
 */
function sailthru_get_site_timezone() {
	$timezone = get_option( 'timezone_string' );
	$timezone = ( empty( $timezone ) ) ? get_option( 'gmt_offset' ) : $timezone;

	return $timezone;
}

/**
 * This function saves the log each time breaking news alerts are sent.
 *
 * @since 2014-04-08 Amit Gupta
 */
function sailthru_save_alert_log( $post_id, $user_id, $lists = array() ) {
	$post_id = intval( $post_id );
	$user_id = intval( $user_id );
	$lists = array_filter( array_unique( $lists ) );

	if ( $post_id < 1 || $user_id < 1 || empty( $lists ) || ! is_array( $lists ) ) {
		return;
	}

	$timezone = sailthru_get_site_timezone();

	$alert_log = get_post_meta( $post_id, '_sailthru_alert_blast_log', true );

	if ( empty( $alert_log ) || ! is_array( $alert_log ) ) {
		$alert_log = array();
	}

	$now = PMC_TimeMachine::create( $timezone )->format_as( 'U' );

	$alert_log[ $now ] = array(
		'user' => $user_id,
		'lists' => $lists,
	);

	krsort( $alert_log );

	$log_length = (int) apply_filters( 'sailthru_alert_log_length', 5 );

	$alert_log = array_slice( $alert_log, 0, $log_length, true );

	update_post_meta( $post_id, '_sailthru_alert_blast_log', $alert_log );

	unset( $log_length, $now, $alert_log, $timezone );

	return true;
}

/**
 * Add 'Custom Subject Line' option in Theme Global Options.
 *
 * @ticket CDWE-136
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @param  array $cheezcap_options List of cheezcap options.
 *
 * @return array \CheezCapDropdownOption
 */
function sailthru_pmc_global_cheezcap_options( $cheezcap_options ) {

	$cheezcap_options[] = new CheezCapDropdownOption(
		__( 'Custom subject for Breaking news newsletter' ),
		__( 'Turn on custom subject for Breaking news newsletter' ),
		'pmc_exacttarget_breaking_news_subject',
		array( 'no', 'yes' ),
		0, // 1sts option => no by default
		array( 'No', 'Yes' )
	);

	return $cheezcap_options;
}

/**
 * To get 'Custom Subject' for breaking news is enable or not.
 *
 * @ticket CDWE-136
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @return bool true if 'Custom Subject' is enable for theme other wise false.
 */
function sailthru_is_breaking_news_subject_enabled() {
	return 'yes' === strtolower( PMC_Cheezcap::get_instance()->get_option( 'pmc_exacttarget_breaking_news_subject' ) );
}
//EOF
