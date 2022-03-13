<?php
/**
 * Class to post published post info to Slack webhook
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-01-31
 */

namespace PMC\Webhooks\Services;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;
use \WP_Post;


class Slack {

	use Singleton;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'transition_post_status', [ $this, 'send_payload' ], 10, 3 );

	}

	/**
	 * Method which constructs the payload array and returns it
	 *
	 * @param string   $new_status
	 * @param string   $old_status
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	protected function _get_payload( string $new_status, string $old_status, WP_Post $post ) : array {

		$payload = [];

		if ( 'publish' !== $new_status ) {
			return $payload;
		}

		$webhook_url = apply_filters( 'pmc_webhooks_services_slack_webhook_url', '' );
		$webhook_url = filter_var( $webhook_url, FILTER_SANITIZE_URL );

		if ( empty( $webhook_url ) ) {
			return $payload;
		}

		$payload = [
			'post_id'       => $post->ID,
			'post_title'    => $post->post_title,
			'post_author'   => get_the_author_meta( 'display_name', $post->post_author ),
			'post_edit_url' => get_edit_post_link( $post->ID ),
			'post_url'      => get_permalink( $post->ID ),
			'webhook_url'   => $webhook_url,
		];

		$payload['subject'] = sprintf(
			// translators: The placeholder is replaced by the display name of post author
			__( 'New post from %s ', 'pmc-webhooks' ),
			$payload['post_author']
		);

		$payload['body'] = sprintf(
			// translators: The placeholders are replaced by the post title and a URL to edit them
			__( 'Title: %1$s, link: %2$s ', 'pmc-webhooks' ),
			$payload['post_title'],
			$payload['post_edit_url']
		);

		$payload = apply_filters( 'pmc_webhooks_services_slack_payload', $payload );
		$payload = ( ! is_array( $payload ) ) ? [] : $payload;

		return $payload;

	}

	/**
	 * Method to send the payload to webhook.
	 * This is hooked to 'transition_post_status' action and will send payload when a post is published.
	 *
	 * @param string   $new_status
	 * @param string   $old_status
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	public function send_payload( string $new_status, string $old_status, WP_Post $post ) : bool {

		if ( ! PMC::is_production() ) {
			return false;
		}

		$payload = $this->_get_payload( $new_status, $old_status, $post );

		if ( empty( $payload ) ) {
			return false;
		}

		$payload_to_send = [
			'body'     => wp_json_encode(
				[
					'text'        => $payload['subject'],
					'attachments' => [
						[
							'fallback' => $payload['body'],
							'color'    => '#bada55',
							'fields'   => [
								[
									'title' => $payload['post_title'],
									'value' => sprintf(
										// translators: The placeholder is replaced by the URL to edit post
										__( "View link: %1\$s\nEdit link: %2\$s", 'pmc-webhooks' ),
										$payload['post_url'],
										$payload['post_edit_url']
									),
									'short' => false,
								],
							],
						],
					],
				]
			),
			'blocking' => false,
		];

		$payload_to_send = apply_filters( 'pmc_webhooks_services_slack_payload_to_send', $payload_to_send );
		$payload_to_send = ( ! is_array( $payload_to_send ) ) ? [] : $payload_to_send;

		$result = wp_remote_post( $payload['webhook_url'], $payload_to_send );

		return ( is_wp_error( $result ) ? false : true );

	}

}  // end class

//EOF
