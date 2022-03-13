<?php
/*
 * Description: Prepare data for adding/editing recurring newsletter.
 *
 * @TODO: Rename files to be more descriptive
 *
 * @version 2018-02-09 brandoncamenisch - feature/PMCVIP-2977:
 * - Typecasting the post days var where an array is expected
 * - Adding docblock
*/
if ( !current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}

$mmcnws_nonce_key = "_mmcnws_recurring_nonce";
$mmcnws_nonce = wp_create_nonce( $mmcnws_nonce_key );

require_once( __DIR__ . '/classes/class-sailthru-blast-repeat.php' );

$sailthru_dataextension = \PMC\Exacttarget\Cache::get_instance()->get_data_extensions();
$et_sendclassification  = \PMC\Exacttarget\Cache::get_instance()->get_sendclassifications();

// Retrive template from Content Builder if it's enabled or use Clasic Content templates.

$content_builder_templates = \PMC\Exacttarget\Cache::get_instance()->get_templates_from_content_builder();
$sailthru_templates        = \PMC\Exacttarget\Cache::get_instance()->get_templates();

$notices         = [];
$sailthru_errors = array();
$sailthru_repeat = null;
$sailthru_postsquery = null;
if ( !empty( $_POST ) && !empty( $_POST[$mmcnws_nonce_key] ) && wp_verify_nonce( $_POST[$mmcnws_nonce_key], $mmcnws_nonce_key ) !== false ) {
	if ( empty( $_POST['subject'] ) ) {
		$sailthru_errors[] = "Missing required field: subject";
	}
	if ( empty( $_POST['name'] ) ) {
		$sailthru_errors[] = "Missing required field: name";
	}
	if ( empty( $sailthru_errors ) ) {
		$uniqid = uniqid();
		$sailthru_postsquery = $_POST['posts'];

		array_walk( $sailthru_postsquery, function( &$value, &$key ) {

			switch ( $key ) {
				case 'number_of_posts':
					$value = absint( $value );
					if ( $value > 49 )
						$value = 49;
					break;
				case 'story_source':
					if ( !in_array( $value, array( 'most_commented', 'most_popular', 'wp_most_popular' ) )
					)
						$value = '';
					break;
				case 'story_source_days':
					$value = absint( $value );
					if ( $value > 60 ) {
						$value = 60;
					}
					break;
				case 'filter_posts_by_cat':
				case 'filter_posts_by_tag':
				case 'filter_posts_by_zone':
				case 'require_featured':
				case 'auto_set_featured':
					if ( $value != 1 )
						$value = 0;
					break;

				case 'schedule_start_date':
					$date_arr = explode( '/', $value );
					if ( sailthru_isset_notempty( $date_arr ) && isset( $date_arr[2] ) ) {
						if ( !checkdate( $date_arr[0], $date_arr[1], $date_arr[2] ) )
							$value = "";
					}
					break;
				case 'filter_categories':
				case 'filter_tags':
						$value = array_map( 'absint', $value );
					break;
				case 'filter_zones':
					$value = array_map( 'sanitize_text_field', $value );
					break;
				case 'special_case':
						$value = sanitize_text_field( $value );
					break;

				default:
					break;
			}
		} );

		$sailthru_postsquery = apply_filters( 'pmc-exacttarget-recurring-newsletter-sanitize-post-query', $sailthru_postsquery );

		if ( isset( $_POST['hour'] ) ) {
			if ( intval( $_POST['hour'] ) < 1 || intval( $_POST['hour'] ) > 13 ) {
				$_POST['hour'] = '';
			}
		}



		if ( isset( $_POST['minute'] ) ) {
			if ( !in_array( $_POST['minute'], array( '00',
												   '15',
												   '30',
												   '45' ) )
			)
				$_POST['minute'] = '';
		}

		if ( ! empty( $_POST['ampm'] ) && in_array( $_POST['ampm'], array( 'AM', 'PM' ) ) ) {
			$ampm = $_POST['ampm'];
		}

		$_POST['days'] = array_filter( (array) $_POST['days'], 'sailthru_check_days' );

		$name = stripslashes( wp_kses_data( $_POST['name'] ) );

		$feed_url = '';
		if ( ! empty( $_POST['external_feed_url'] ) ) {
			$feed_url = esc_url_raw( $_POST['external_feed_url'] );
		}

		$img_size = '';
		if ( ! empty( $_POST['pmc_image_size'] ) ) {
			$img_size = sanitize_text_field( $_POST['pmc_image_size'] );
		}

		$img_type = '';
		if ( ! empty( $_POST['pmc_image_type'] ) ) {
			$img_type = trim( sanitize_text_field( $_POST['pmc_image_type'] ) );
		}

		$sailthru_repeat = array(
			'name' => $name,
			'template' => sanitize_text_field( $_POST['template'] ),
			'data_feed_url' => home_url() . '/?feed=sailthru&repeathash=' . $uniqid,
			'report_email' => 'asannad@pmc.com',
			'send_time' => sanitize_text_field( "$_POST[hour]:$_POST[minute] $ampm" ),
			'days' => $_POST['days'],
			'generate_time' => '0',
			'external_feed_url' => $feed_url,
			'dataextension' => sanitize_text_field( $_POST['dataextension']),
			'img_size' => sanitize_text_field( $img_size ),
			'img_type' => sanitize_text_field( $img_type )
		);

		$content_builder          = \PMC::filter_input( INPUT_POST, 'content_builder', FILTER_SANITIZE_STRING );
		$content_builder_template = \PMC::filter_input( INPUT_POST, 'content_builder_template', FILTER_SANITIZE_STRING );

		// Mark whether the config belongs to Content Builder or Classic Content.
		$sailthru_repeat['content_builder'] = ( ! empty( $content_builder ) && 'yes' === strtolower( $content_builder ) ) ? 'yes' : 'no';

		if( isset( $_POST['pmc_newsletter_recurr_senddefinition'] )  && $_POST['pmc_newsletter_recurr_senddefinition'] != "0" ){
			$sailthru_repeat['pmc_newsletter_senddefinition'] = sanitize_text_field( $_POST['pmc_newsletter_recurr_senddefinition'] );

		}

		if ( isset( $_POST['repeat_id'] ) && ctype_alnum( $_POST['repeat_id'] ) ) { // if this repeat already exists
			$sailthru_repeat['repeat_id']     = $_POST['repeat_id'];
			$et_repeat                        = Sailthru_Blast_Repeat::load_from_db( sanitize_text_field( $_POST['repeat_id'] ) );
			$sailthru_repeat['data_feed_url'] = home_url() . '/?feed=sailthru&repeathash=' . $et_repeat['feed_ref'];
		}

		if ( empty( $feed_url ) ) {
			$feed_url = $sailthru_repeat['data_feed_url'];
		}

		if ( 'yes' === $sailthru_repeat['content_builder'] ) {

			$sailthru_result             = [];
			$sailthru_repeat['template'] = $content_builder_template;

			// Prepare HTML for the email, this includes adding a call to Content Area so appropriate feed can be fetched by the email.
			$template_html = PMC_Newsletter::prepare_email_template_html_content_builder( $sailthru_repeat['template'], $name, $feed_url );

			// If HTML content was prepared then prepare the email.
			if ( ! empty( $template_html['html'] ) ) {

				$upsert_result = Exact_Target::upsert_email_to_content_builder( $name, '%%=v(@subject)=%%', $template_html['html'] );

				if ( ! empty( $upsert_result->status ) && true === $upsert_result->status && ! empty( $upsert_result->results->id ) ) {

					$sailthru_result                    = $sailthru_repeat;
					$sailthru_result['email_id']        = $upsert_result->results->id;
					$sailthru_result['content_builder'] = 'yes';

					// If this is new newsletter then assign an ID to it.
					if ( empty( $sailthru_repeat['repeat_id'] ) ) {
						$sailthru_result['repeat_id'] = $upsert_result->results->id;
					}

					if ( isset( $_POST['featured_post_id'] ) ) {
						$sailthru_result['featured_post_id'] = intval( $_POST['featured_post_id'] );
					}

					$repeat_subject_cb           = \PMC::filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_STRING );
					$repeat_default_thumb_url_cb = \PMC::filter_input( INPUT_POST, 'default_thumbnail_src', FILTER_SANITIZE_URL );

					$sailthru_repeat = Sailthru_Blast_Repeat::save_api_to_db( $sailthru_result, $sailthru_postsquery, $repeat_subject_cb, $repeat_default_thumb_url_cb );
					$notices[]       = 'Saved';

				} else {

					if ( empty( $sailthru_result['repeat_id'] ) ) {

						// If repeat_id is empty then it means this was a new newsletter being inserted but it failed.
						$sailthru_errors[] = 'Could not create email in ET! Error: ' . $upsert_result->message;
					} else {

						// If repeat_id exists then it means the newsletter already existed and we were trying to upate it but it failed.
						$sailthru_errors[] = 'Could not update email in ET! Error: ' . $upsert_result->message;
					}
				}
			} else {
				$sailthru_errors[] = $template_html['error'];
			}
		} else {
			$template_html = PMC_Newsletter::prepare_email_template_html( $sailthru_repeat['template'], $name, $feed_url );

			if ( ! empty( $template_html['error'] ) ) {
				$sailthru_errors[] = $template_html['error'];
			} else {
				$sailthru_result = Exact_Target::upsert_email( $name, '%%=v(@subject)=%%', $template_html );
			}

			if ( is_array( $sailthru_result ) && ! empty( $sailthru_result[0]->StatusCode ) && 'OK' === $sailthru_result[0]->StatusCode ) {

				$email = Exact_Target::get_email( $name );

				foreach ( $email as $email_id_cc => $email_name ) {

					if ( $name === $email_name ) {

						$sailthru_result             = $sailthru_repeat;
						$sailthru_result['email_id'] = $email_id_cc;

						if ( empty( $sailthru_repeat['repeat_id'] ) ) {
							$sailthru_result['repeat_id'] = $email_id_cc;
						}

						break;
					}
				}

				if ( ! empty( $sailthru_result['repeat_id'] ) ) {

					$cc_featured_post_id = \PMC::filter_input( INPUT_POST, 'featured_post_id', FILTER_SANITIZE_STRING );

					if ( isset( $cc_featured_post_id ) ) {
						$sailthru_result['featured_post_id'] = intval( $cc_featured_post_id );
					}

					$repeat_subject_cc           = \PMC::filter_input( INPUT_POST, 'subject', FILTER_SANITIZE_STRING );
					$repeat_default_thumb_url_cc = \PMC::filter_input( INPUT_POST, 'default_thumbnail_src', FILTER_SANITIZE_URL );

					$sailthru_repeat = Sailthru_Blast_Repeat::save_api_to_db( $sailthru_result, $sailthru_postsquery, $repeat_subject_cc, $repeat_default_thumb_url_cc );
					$notices[]       = 'Saved';

				} else {
					$sailthru_errors[] = 'Repeat ID returned is invalid';
				}
			} else {
				$sailthru_errors[] = $sailthru_result['errormsg'];
			}
		}
	}
}

if ( !$sailthru_repeat && isset( $_GET['id'] ) && ctype_alnum( $_GET['id'] ) ) {
	$sailthru_repeat = Sailthru_Blast_Repeat::load_from_db( sanitize_text_field( $_GET['id'] ) );
	$sailthru_postsquery = $sailthru_repeat['query'];

	if ( !empty( $sailthru_repeat['error'] ) ) {
		echo '<script>document.location="' . menu_page_url( "sailthru_recurring_newsletters", false ) . '</script>';
		exit;
	}
}

$sailthru_schedule_days = array(
	'Monday' => 'Mon',
	'Tuesday' => 'Tue',
	'Wednesday' => 'Wed',
	'Thursday' => 'Thu',
	'Friday' => 'Fri',
	'Saturday' => 'Sat',
	'Sunday' => 'Sun'
);

$sailthru_featured_post_id ='';

if( isset( $sailthru_dbrepeat['featured_post_id'] ) )
	$sailthru_featured_post_id = intval( $sailthru_dbrepeat['featured_post_id'] );
elseif( isset( $_POST['featured_post_id'] ) ){

	$sailthru_featured_post_id = intval( $_POST['featured_post_id'] ) ;

}

$sailthru_featured_post = get_post( $sailthru_featured_post_id );
if ( isset( $sailthru_featured_post ) ) {
	$sailthru_featured_post->image = sailthru_get_featured_image( $sailthru_featured_post );
	$sailthru_featured_post->excerpt = sailthru_get_excerpt( $sailthru_featured_post->post_content );
}

require( 'views/recurring-newsletter-tpl.php' );

//EOF
