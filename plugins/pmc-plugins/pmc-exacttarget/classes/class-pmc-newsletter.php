<?php
// phpcs:disable WordPress.NamingConventions --- We're using third party library
use PMC\Exacttarget\Rest_Request;
use PMC\Exacttarget\Rest_Error;

class PMC_Newsletter {

	public static function token_replace( $template, $data ) {
		foreach ( $data as $key => $value ) {
			$template = str_replace( '%%' . $key . '%%', \PMC::ascii_to_utf8( $value ), $template );
			$template = str_replace( '%%' . $key . '_esc_attr%%', esc_attr( $value ), $template );
			$template = str_replace( '%%' . $key . '_esc_html%%', esc_html( $value ), $template );
		}

		return $template;
	}

	public static function sanitize_template( $template ) {

		$extra_allowed_html = array(
			'html'    => array( 'xmlns' => array() ),
			'DOCTYPE' => array( 'html' => array() ),
			'style'   => array( 'type' => array() ),
			'head'    => array(),
			'meta'    => array(
				'http-equiv' => array(),
				'charset'    => array(),
				'content'    => array(),
			),
		);

		$allowedposttags = wp_kses_allowed_html( 'post' );
		$allowed_html    = array_merge( $allowedposttags, $extra_allowed_html );
		$template        = wp_kses( $template, $allowed_html );

		return stripslashes( $template );
	}

	public static function send_fast_newsletter( $newsletter, $data ) {

		$use_content_builder = ( ! empty( $newsletter['content_builder'] ) && 'yes' === $newsletter['content_builder'] );

		if ( $use_content_builder ) {

			$et_template   = Exact_Target::get_template_from_content_builder_by_id( $newsletter['template'] );
			$template_html = ( $et_template->status && ! empty( $et_template->results->content ) ) ? $et_template->results->content : '';

		} else {

			$template_obj  = Exact_Target::get_templates( $newsletter['template'] );
			$template_html = ( ! empty( $template_obj->LayoutHTML ) ) ? $template_obj->LayoutHTML : '';
		}

		if ( empty( $template_html ) ) {
			return array( 'error' => 'empty repeat data to send' );
		} else {
			$newsletter['template'] = self::token_replace( $template_html, $data['vars'] );

			//TODO: check the send definition of the feed first before grabing the global send definition.
			if ( isset( $newsletter['pmc_newsletter_alert_senddefinition'] ) && 0 !== $newsletter['pmc_newsletter_alert_senddefinition'] ) {
				$send_classification = $newsletter['pmc_newsletter_alert_senddefinition'];
			} else {
				$send_classification = self::get_send_classification_key( 'alert' );
			}

			if ( $use_content_builder ) {
				$et_send = Exact_Target::send_fast_newsletter_from_content_builder( $newsletter, $newsletter['dataextension'], $send_classification );
			} else {
				$et_send = Exact_Target::send_fast_newsletter( $newsletter, $newsletter['dataextension'], $send_classification );
			}

			return $et_send;
		}

	}

	/**
	 * Sends Content Builder newsletter email.
	 *
	 * @param array $repeat_data Newsletter configuration
	 *
	 * @return array
	 */
	public static function send_recurring_newsletter_content_builder( $repeat_data ) {

		$email = self::update_emails_html_content_builder( $repeat_data );

		if (
			$email->status
			&& ! empty( $email->results )
			&& ! empty( $email->results->legacyData )
			&& ! empty( $email->results->legacyData->legacyId )
		) {

			// Sending the email requires legacy id.
			$email_id = $email->results->legacyData->legacyId;

		} else {

			return array( 'error' => $email->message );
		}

		// check the classification for the feed first before taking the global one.
		if ( isset( $repeat_data['pmc_newsletter_senddefinition'] ) && 0 !== $repeat_data['pmc_newsletter_senddefinition'] ) {
			$send_classification = $repeat_data['pmc_newsletter_senddefinition'];
		} else {
			$send_classification = self::get_send_classification_key( 'newsletter' );
		}

		$et_send = Exact_Target::send_recurring_newsletter( $email_id, $repeat_data['dataextension'], $send_classification );

		if ( empty( $et_send['error'] ) ) {

			//remove the featured post when a blast send pulls this feed
			Sailthru_Blast_Repeat::save_featured_post( 0, $repeat_data['repeat_id'] );

			//Trigger success hook and pass Newsletter config to it
			$newsletter_args = Sailthru_Blast_Repeat::load_from_db( $repeat_data['repeat_id'] );

			if ( ! empty( $newsletter_args['query'] ) ) {
				do_action( 'pmc-exacttarget-recurring-newsletter-send-success', $newsletter_args['query'] );
			}

		}

		return $et_send;
	}

	public static function send_recurring_newsletter( $repeat_data ) {

		$email = self::update_emails_html( $repeat_data );

		if ( ! empty( $email['error'] ) ) {
			return $email;
		}
		// check the classification for the feed first before taking the global one.
		if ( isset( $repeat_data['pmc_newsletter_senddefinition'] ) && 0 !== $repeat_data['pmc_newsletter_senddefinition'] ) {
			$send_classification = $repeat_data['pmc_newsletter_senddefinition'];
		} else {
			$send_classification = self::get_send_classification_key( 'newsletter' );
		}

		$et_send = Exact_Target::send_recurring_newsletter( $repeat_data['email_id'], $repeat_data['dataextension'], $send_classification );

		if ( empty( $et_send['error'] ) ) {

			//remove the featured post when a blast send pulls this feed
			Sailthru_Blast_Repeat::save_featured_post( 0, $repeat_data['repeat_id'] );

			//Trigger success hook and pass Newsletter config to it
			$newsletter_args = Sailthru_Blast_Repeat::load_from_db( $repeat_data['repeat_id'] );

			if ( ! empty( $newsletter_args['query'] ) ) {
				do_action( 'pmc-exacttarget-recurring-newsletter-send-success', $newsletter_args['query'] );
			}

		}

		return $et_send;

	}

	public static function send_test_newsletter( $repeat_data, $email_list ) {

		$email = self::update_emails_html( $repeat_data );

		if ( ! empty( $email['error'] ) ) {
			return $email;
		}

		// check the classification for the feed first before taking the global one.
		if ( isset( $repeat_data['pmc_newsletter_senddefinition'] ) && 0 !== $repeat_data['pmc_newsletter_senddefinition'] ) {
			$send_classification = $repeat_data['pmc_newsletter_senddefinition'];
		} else {
			$send_classification = self::get_send_classification_key( 'newsletter' );
		}

		$et_send = Exact_Target::send_senddefinition_to_list( $repeat_data['email_id'], $email_list, $send_classification );

		return $et_send;

	}

	/**
	 * Sends a test newsletter using Content Builder.
	 *
	 * @param array  $repeat_data Newsletter configuration data.
	 * @param string $email_list  List ID to which email is supposed to be sent.
	 *
	 * @return array Of the format array( 'status' => object ) if test send was successful or of the format array( 'error' => 'message' ) if something went wrong.
	 */
	public static function send_test_newsletter_content_builder( $repeat_data, $email_list ) {

		if ( empty( $repeat_data ) || empty( $email_list ) ) {
			return array( 'error' => 'Missing newsletter configuration or list id' );
		}

		$email = self::update_emails_html_content_builder( $repeat_data );

		if ( ! $email->status ) {
			return array( 'error' => $email->message );
		}

		if (
			! empty( $email->results )
			&& ! empty( $email->results->legacyData )
			&& ! empty( $email->results->legacyData->legacyId )
		) {
			$email_id = $email->results->legacyData->legacyId;
		}

		// check the classification for the feed first before taking the global one.
		if ( isset( $repeat_data['pmc_newsletter_senddefinition'] ) && 0 !== $repeat_data['pmc_newsletter_senddefinition'] ) {
			$send_classification = $repeat_data['pmc_newsletter_senddefinition'];
		} else {
			$send_classification = self::get_send_classification_key( 'newsletter' );
		}

		$et_send = Exact_Target::send_senddefinition_to_list( $email_id, $email_list, $send_classification );

		return $et_send;
	}

	public static function get_taxonomy_string( $terms ) {
		if ( empty( $terms ) ) {
			return;
		}
		$term_names = wp_list_pluck( $terms, 'name' );

		return sanitize_text_field( implode( ',', $term_names ) );
	}

	/**
	 * This function returns subject which is to be used for the newsletter.
	 * It adds [TEST] prefix if email blast is flagged as test run.
	 *
	 * @ticket PPT-5250
	 * @since 2015-08-20 Amit Gupta
	 */
	public static function get_email_subject( array $email_data ) {
		$subject = '%%=v(@subject)=%%';

		if ( isset( $email_data['is_test_email'] ) && true === $email_data['is_test_email'] ) {
			$subject = '[TEST]: ' . $subject;
		}

		return $subject;
	}

	/**
	 * Updates the email in Content Builder which corresponds to selected Newsletter with proper data such as call to Content Block with appropriate feed url.
	 *
	 * @param array $repeat_data Newsletter configuration.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function update_emails_html_content_builder( $repeat_data ) {

		if ( empty( $repeat_data ) ) {
			return new PMC\Exacttarget\Rest_Error( 'Empty repeat data to send' );
		}

		$feed_url = self::get_feed_url( $repeat_data );

		$template = self::prepare_email_template_html_content_builder( $repeat_data['template'], $repeat_data['name'], $feed_url );

		if ( ! empty( $template['html'] ) ) {
			$et_email = Exact_Target::upsert_email_to_content_builder( $repeat_data['name'], static::get_email_subject( $repeat_data ), $template['html'] );

			if ( false === $et_email->status || empty( $et_email->results ) || empty( $et_email->results->id ) ) {

				return new Rest_Error( 'Email creation failed. Please Try Again, Message: ' . $et_email->message );
			}

			return $et_email;

		} else {

			return new Rest_Error( $template['error'] );
		}
	}

	/**
	 * Gets the sailthru RSS feed URL from the Newsletter config.
	 *
	 * @param array $repeat_data Newsletter configuration.
	 *
	 * @return string rss feed URL.
	 */
	public static function get_feed_url( array $repeat_data ) : string {
		if ( ! empty( $repeat_data['external_feed_url'] ) ) {
			$feed_url = $repeat_data['external_feed_url'];
		} else {
			$feed_url = home_url() . '/?feed=sailthru&repeathash=' . $repeat_data['feed_ref'];
		}

		return $feed_url;
	}

	public static function update_emails_html( $repeat_data ) {

		if ( empty( $repeat_data ) ) {
			return array( 'error' => 'empty repeat data to send' );
		}

		$feed_url = self::get_feed_url( $repeat_data );

		// If the flag is set in the query to disable sending of empty newsletters
		if ( $repeat_data['query']['disable_empty_send'] ) {
			$rss       = PMC\Exacttarget\RSS::get_instance();
			$feed_data = $rss->get_data( $repeat_data['feed_ref'] );

			if ( empty( $feed_data['posts'] ) ) {
				return array( 'error' => 'RSS feed is empty.' );
			}

		}

		$template_html = self::prepare_email_template_html( $repeat_data['template'], $repeat_data['name'], $feed_url );

		if ( ! empty( $template_html['error'] ) ) {
			return $template_html;
		} else {
			$et_email = Exact_Target::upsert_email( $repeat_data['name'], static::get_email_subject( $repeat_data ), $template_html );

			if ( 'OK' !== $et_email[0]->StatusCode ) {
				array( 'error' => 'Email creation failed. Please Try Again' );
			}

			return $et_email;
		}
	}

	public static function prepare_email_template_html( $template_id, $content_name, $feed_url ) {
		if ( empty( $template_id ) || empty( $content_name ) || empty( $feed_url ) ) {
			return array( 'error' => 'template_d, content_name or feed_url empty' );
		}

		$et_content_area = Exact_Target::upsert_xml_source( $content_name, $feed_url );
		if ( 'OK' !== $et_content_area[0]->StatusCode ) {
			return array( 'error' => 'Not able to create content area' );
		}

		$template_obj = Exact_Target::get_templates( $template_id );

		if ( empty( $template_obj->LayoutHTML ) ) {
			return array( 'error' => 'Not able to get template HTML' );
		}

		$template_html = str_replace(
			'##pmc-auto-insert-feed-url##',
			'Set @xml = ContentAreaByName("my contents\\' . $et_content_area[0]->Object->Name . '")',
			$template_obj->LayoutHTML
		);

		return $template_html;
	}

	/**
	 * Prepares template content for an email.
	 * Upserts proper feed url to ET Content Block and prepares HTML Content to call the Content Block to fetch feed.
	 *
	 * @param string|int $template_id  Template ID.
	 * @param string     $content_name Name for corresponding Content Block.
	 * @param string     $feed_url     Feed URL
	 *
	 * @return array Returns an array with 'html' key if content was prepared otherwise an array with 'error' key if something went wrong.
	 */
	public static function prepare_email_template_html_content_builder( $template_id, $content_name, $feed_url ) {

		if ( empty( $template_id ) || empty( $content_name ) || empty( $feed_url ) ) {
			return array( 'error' => 'template_id, content_name or feed_url empty' );
		}

		$content_block = Exact_Target::upsert_xml_source_to_content_builder( $content_name, $feed_url );
		if ( false === $content_block->status || empty( $content_block->results ) || empty( $content_block->results->id ) ) {
			return array( 'error' => 'Not able to create content block in ExactTarget, Error: ' . $content_block->message );
		}

		$block       = $content_block->results;
		$et_template = Exact_Target::get_template_from_content_builder_by_id( $template_id );

		if ( empty( $et_template->results->content ) ) {
			return array( 'error' => 'Not able to get template HTML ' . $et_template->message );
		}

		// ##pmc-auto-insert-feed-url## is added to each ET Template, this is to make the template dynamic as per requirements of the email. The comented text is replaced with a call to proper Content Block.
		$template_html = str_replace( '##pmc-auto-insert-feed-url##', 'Set @xml = ContentBlockByName("my contents\\' . $block->name . '")', $et_template->results->content );

		return array( 'html' => $template_html );
	}

	public static function get_send_classification_key( $type = 'newsletter' ) {

		if ( 'newsletter' === $type ) {
			$send_classification = pmc_get_option( 'pmc_newsletter_senddefinition', 'exacttarget' );

		} elseif ( 'alert' === $type ) {
			$send_classification = pmc_get_option( 'pmc_alert_senddefinition', 'exacttarget' );
		}

		if ( empty( $send_classification ) ) {
			$send_classification = 'PMC'; //Default Value
		}

		return $send_classification;

	}

	/**
	 * @since 2016-11-30 Get mostpopular posts for duration args ( $duration ), where $duration = max of 90
	 *        user VIPs stats function
	 *
	 * @param array $args
	 *
	 * @return array|bool|mixed|void
	 */
	public static function get_vip_mostpopular( $args = array() ) {

		if ( ! function_exists( 'stats_get_daily_history' ) ) {
			return;
		}

		$defaults = array(
			'cachelife'    => 300,
			'includepages' => false,
			'limit'        => 10,
			'duration'     => 7,
		);
		$args     = wp_parse_args( $args, $defaults );

		global $wpdb;

		if ( ( $args['limit'] >= 100 ) || ( -1 === $args['limit'] ) ) {
			$args['limit'] = 100;
		}

		if ( 0 === $args['limit'] ) {
			$args['limit'] = 10;
		}

		$mostpopular_duration = $args['duration'];

		$cacheid = md5( "pmc_exacttarget|{$args['limit']}|{$args['duration']}|{$args['includepages']}|{$args['cachelife']}" );

		$filtered_post_ids = wp_cache_get( $cacheid, 'output' );

		if ( empty( $filtered_post_ids ) ) {

			$top_posts = array();

			$top_posts = array_shift( stats_get_daily_history( false, $wpdb->blogid, 'postviews', 'post_id', false, $mostpopular_duration, '', 100, true ) );

			if ( ! empty( $top_posts ) ) {

				foreach ( $top_posts as $id => $views ) {
					$post = get_post( $id );

					if ( empty( $post ) ) {
						$post = get_page( $id );
					}

					if ( 'publish' === $post->post_status ) {
						if ( ( 'post' === $post->post_type ) || ( $args['includepages'] ) ) {
							$filtered_post_ids[] = array(
								'post_id' => $id,
								'views'   => $views,
							);
						}
					}
				}
			}

			if ( $args['limit'] >= 0 ) {
				$filtered_post_ids = array_slice( $filtered_post_ids, 0, $args['limit'] );
			}

			wp_cache_add( $cacheid, $filtered_post_ids, 'output', $args['cachelife'] );
		}

		return $filtered_post_ids;

	}
}


//EOF
