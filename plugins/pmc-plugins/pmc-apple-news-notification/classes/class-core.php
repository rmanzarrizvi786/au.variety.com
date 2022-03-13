<?php
/**
 * Class for apple news notification core.
 *
 * @since   2021-02-23
 *
 * @package pmc-apple-news-notification
 */

namespace PMC\Apple_News_Notification;

use Admin_Apple_Settings;

use \PMC\Global_Functions\Traits\Singleton;

class Core {

	use Singleton;

	/**
	 * Allowed post types where metabox can be showsn.
	 */
	protected $allowed_post_types = [ 'post', 'pmc_featured', 'pmc-gallery' ];

	/**
	 * Apple news settings.
	 *
	 * @var \Apple_Exporter\Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'apple_news_settings_sections', [ $this, 'add_extra_settings' ], 1, 1 );
		add_filter( 'apple_news_post_args', [ $this, 'override_post_args' ] );
		add_filter( 'apple_news_column_title', [ $this, 'add_notification_button' ], 10, 3 );

		/**
		 * Actions
		 */
		add_action( 'do_meta_boxes', [ $this, 'add_apple_news_meta_box' ] );
		add_action( 'apple_news_after_index_table', [ $this, 'add_notification_thickbox' ] );
		add_action( 'wp_ajax_send_notification_request', [ $this, 'send_notification_request' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook_name Hook name.
	 */
	public function enqueue_scripts( $hook_name ) {
		$post_type = get_post_type();

		$is_allowed_post_page   = ( 'post.php' === $hook_name && in_array( $post_type, (array) $this->allowed_post_types, true ) );
		$is_apple_articles_list = ( 'toplevel_page_apple_news_index' === $hook_name );

		if ( ! $is_allowed_post_page && ! $is_apple_articles_list ) {
			return;
		}

		add_thickbox();

		wp_enqueue_style( 'anf-admin-css', PMC_APPLE_NEWS_NOTIFICATION_URL . 'css/admin/admin.css', [], PMC_APPLE_NEWS_NOTIFICATION_VERSION );
		wp_enqueue_script( 'anf-admin-script', PMC_APPLE_NEWS_NOTIFICATION_URL . 'js/notifications-admin.js', [ 'jquery' ], PMC_APPLE_NEWS_NOTIFICATION_VERSION );

		wp_localize_script(
			'anf-admin-script',
			'anfVars',
			[
				'nonce' => wp_create_nonce( 'apple_news_send_notification' ),
			]
		);
	}

	/**
	 * Ran as a filter, this hooks into the function generating the list view of articles in admin
	 * and adds the "Send Notification" button to the article if the article is on Apple News.
	 * See Apple News > Articles in admin.
	 *
	 * @param string   $html    The HTML string of the buttons.
	 * @param \WP_Post $item    The current post.
	 * @param array    $actions The current posts's available actions as an array.
	 *
	 * @return string The modified HTML string.
	 */
	public function add_notification_button( string $html, \WP_Post $item, array $actions ): string {

		$apple_news_api_id = get_post_meta( $item->ID, 'apple_news_api_id', true );

		if ( empty( $apple_news_api_id ) ) {
			return $html;
		}

		$notification_link = sprintf(
			"<a title='%s' href='#' class='thickbox notification-thickbox-link' data-anf-post-title='%s' data-post-id='%d'>%s</a>",
			esc_attr__( 'Send a notification', 'pmc-apple-news-notification' ),
			esc_attr( $item->post_title ),
			absint( $item->ID ),
			esc_html__( 'Send a Notification', 'pmc-apple-news-notification' )
		);

		/**
		 * Needs to be done because alleyinteractive/apple-news applies its own kind of WP list styling, and if i just append
		 * the button to the array then the styling will break
		 */
		$actions = array_slice( $actions, 0, 1 ) + [ 'blast' => $notification_link ] + array_slice( $actions, 1 );

		$actions_list = '';

		if ( ! empty( $actions ) && is_array( $actions ) ) {
			foreach ( $actions as $name => $value ) {
				$actions_list .= sprintf( "<span class='%s'>%s</span> | ", $name, $value );
			}
		}

		return sprintf(
			'%1$s <span>(id:%2$s)</span> <div class="row-actions">%3$s</div>',
			$item->post_title,
			absint( $item->ID ),
			rtrim( $actions_list, '| ' )
		);
	}

	/**
	 * Renders the WP thickbox for sending the notifications, as well as append the JS and styling for it.
	 */
	public function add_notification_thickbox() {

		\PMC::render_template(
			PMC_APPLE_NEWS_NOTIFICATION_DIR . '/templates/notification-thickbox.php',
			[],
			true
		);
	}

	/**
	 * Takes the user-entered notification body and the WP post ID, signs the request, and sends it to the Apple
	 * News API.
	 *
	 * If ANF accepted the request, this will return a JSON array of the number of notifications sent today, and how
	 * many are left available to send.
	 *
	 * If ANF rejected it, this will return a JSON failure with the error that ANF sent back.
	 */
	public function send_notification_request() {

		$nonce   = \PMC::filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
		$post_id = \PMC::filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$body    = \PMC::filter_input( INPUT_POST, 'body', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'apple_news_send_notification' ) || ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'error' => 'Invalid Nonce or permission issue' ] );
		}

		if ( empty( $post_id ) ) {
			wp_send_json_error( [ 'error' => 'Invalid or no post id' ] );
		}

		$apple_settings = $this->fetch_apple_settings();
		$article_id     = get_post_meta( $post_id, 'apple_news_api_id', true );
		$date           = gmdate( 'Y-m-d\TH:i:s\Z' );
		$url            = sprintf( 'https://news-api.apple.com/articles/%s/notifications', $article_id );
		$content_type   = 'application/json';

		$body = wp_json_encode(
			[
				'data' => [
					'alertBody' => wp_strip_all_tags( \PMC::untexturize( stripslashes( $body ) ) ),
					'countries' => [ 'US' ],
				],
			]
		);

		$canonical_request = 'POST' . $url . $date . $content_type . $body;
		$secret_key        = base64_decode( $apple_settings->api_secret );
		$hash              = hash_hmac( 'sha256', $canonical_request, $secret_key, true );
		$signature         = base64_encode( $hash );
		$authorization     = sprintf( 'HHMAC; key=%s; signature=%s; date=%s', $apple_settings->api_key, $signature, $date );

		$headers = [
			'Content-Type'    => $content_type,
			'Accept'          => $content_type,
			'Accept-language' => 'en',
			'Authorization'   => $authorization,
		];

		$response = wp_safe_remote_post(
			$url,
			[
				'headers' => $headers,
				'body'    => $body,
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->errors );
		}

		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );

		if ( null !== $body && ! empty( $body->errors[0] ) ) {
			wp_send_json_error(
				[
					'error' => $body->errors[0]->code,
				]
			);
		}

		$daily = ( null !== $body && ! empty( $body->meta->quotas->daily ) ) ? $body->meta->quotas->daily : '';

		/**
		 * @todo With guzzel/guzzel client the response was `wp_send_json_success( $response['meta']['quotas']['daily'] );`
		 * Since I do not have a way of getting successful response with sandbox account, it needs to be checked when we can test is successfully.
		 */
		wp_send_json_success( $daily );

		// Coverage is ignored because wp_send_json_success will die() right before this line and coverage will not able to reach the next line.
	} // @codeCoverageIgnore

	/**
	 * Adds our extra settings to the plugin's settings page.
	 *
	 * @param array $settings The settings array.
	 *
	 * @return array The settings array, with settings added.
	 */
	public function add_extra_settings( array $settings ): array {
		$settings[] = new Admin_Apple_Spin_Settings();

		return $settings;
	}

	/**
	 * Creates the meta box.
	 *
	 * @param string $post_type The post type.
	 */
	public function add_apple_news_meta_box( string $post_type ) {

		if ( ! in_array( $post_type, (array) $this->allowed_post_types, true ) ) {
			return;
		}

		add_meta_box(
			'pgm-publish-to-apple-news-metabox',
			__( 'Apple News Notification', 'pmc-apple-news-notification' ),
			[ $this, 'render_apple_news_meta_box' ],
			null,
			'side',
			'high'
		);
	}

	/**
	 * Renders the meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function render_apple_news_meta_box( \WP_Post $post ) {

		$this->add_notification_thickbox();

		$args = [
			'is_sending_notifications_allowed' => $this->is_allowed_to_send_notification(),
			'anf_post_title'                   => $post->post_title,
			'post_id'                          => $post->ID,
			'apple_news_status'                => \Admin_Apple_News::get_post_status( $post->ID ),
		];

		\PMC::render_template(
			PMC_APPLE_NEWS_NOTIFICATION_DIR . '/templates/metabox.php',
			$args,
			true
		);
	}

	/**
	 * Fetch apple settings.
	 *
	 * @return \Apple_Exporter\Settings
	 */
	private function fetch_apple_settings() {

		if ( ! empty( $this->settings ) ) {
			return $this->settings;
		}

		$apple_new_settings = new Admin_Apple_Settings();
		$apple_settings     = $apple_new_settings->fetch_settings();
		$stored_settings    = get_option( 'apple_news_settings' );

		// Add extra settings added by this plugin.
		if ( ! empty( $stored_settings ) && is_array( $stored_settings ) ) {
			foreach ( $stored_settings as $key => $value ) {
				if ( ! isset( $apple_settings->$key ) || $apple_settings->$key !== $value ) {
					$apple_settings->$key = $value;
				}
			}
		}

		$this->settings = $apple_settings;

		return $apple_settings;
	}

	/**
	 * Get allow sending notification to apple news status.
	 *
	 * @return bool
	 */
	private function is_allowed_to_send_notification() {
		$apple_settings = $this->fetch_apple_settings();

		if ( ! isset( $apple_settings->allow_sending_notifications_to_apple_news ) ) {
			return false;
		}

		return ( 'no' !== $apple_settings->allow_sending_notifications_to_apple_news );
	}

	/**
	 * Overrides the Apple News plugin's arguments when POSTing Apple servers.
	 *
	 * @param array $args Agruments.
	 */
	public function override_post_args( array $args ): array {
		$args['timeout'] = 120; // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- See function description.

		return $args;
	}
}
