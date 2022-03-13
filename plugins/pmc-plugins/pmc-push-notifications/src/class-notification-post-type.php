<?php
/**
 * This file contains the Notifications Post Type class.
 *
 * @package PMC_Push_Notifications
 */

namespace PMC\Push_Notifications;

use Fieldmanager_Autocomplete;
use Fieldmanager_Checkbox;
use Fieldmanager_Datasource_Post;
use Fieldmanager_Datepicker;
use Fieldmanager_Group;
use Fieldmanager_Radios;
use Fieldmanager_TextArea;
use Fieldmanager_TextField;
use PMC\Global_Functions\Traits\Singleton;
use WP_Post;

/**
 * Register the post type.
 */
class Notification_Post_Type {

	use Singleton;

	/**
	 * Post type constant.
	 */
	const POST_TYPE = 'pmc-notification';

	/**
	 * Push message ID meta constant.
	 */
	const PUSH_MESSAGE_ID_META = 'push_message_id';

	/**
	 * Initialize things
	 */
	public function __construct() {
		$this->register_post_type();

		// User roles/capabilities.
		add_filter( 'user_has_cap', [ $this, 'user_has_cap' ], 11, 4 );

		// Meta fields and validation.
		add_action( 'fm_post_' . self::POST_TYPE, [ $this, 'add_fields' ] );
		add_action( 'wp_insert_post_data', [ $this, 'validate_confirmation' ], 10, 2 );
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_head', [ $this, 'disable_publish_button' ] );

		// Mobile Push Integration.
		add_action( 'transition_post_status', [ $this, 'delete_push_notification_on_draft' ], 10, 3 );
	}

	/**
	 * Create custom post type.
	 */
	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'        => array(
					'name'               => esc_html__( 'Push Notifications', 'pmc-push-notifications' ),
					'singular_name'      => esc_html__( 'Push Notification', 'pmc-push-notifications' ),
					'add_new'            => esc_html__( 'Add New Push Notification', 'pmc-push-notifications' ),
					'add_new_item'       => esc_html__( 'Add New Push Notification', 'pmc-push-notifications' ),
					'edit'               => esc_html__( 'Edit Push Notification', 'pmc-push-notifications' ),
					'edit_item'          => esc_html__( 'Edit Push Notification', 'pmc-push-notifications' ),
					'new_item'           => esc_html__( 'New Push Notification', 'pmc-push-notifications' ),
					'view'               => esc_html__( 'View Push Notification', 'pmc-push-notifications' ),
					'view_item'          => esc_html__( 'View Push Notification', 'pmc-push-notifications' ),
					'search_items'       => esc_html__( 'Search Push Notifications', 'pmc-push-notifications' ),
					'not_found'          => esc_html__( 'No Push Notification found', 'pmc-push-notifications' ),
					'not_found_in_trash' => esc_html__( 'No Push Notification found in Trash', 'pmc-push-notifications' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'menu_position' => 2,
				'supports'      => [
					'title',
					'author',
				],
				'menu_icon'     => 'dashicons-external',
				'has_archive'   => false,
				'map_meta_cap'  => true,
			]
		);
	}

	/**
	 * Only allow specific user roles to edit/publish Notifications.
	 *
	 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name and boolean values
	 *                          represent whether the user has that capability.
	 * @param string[] $caps    Required primitive capabilities for the requested capability.
	 * @param array    $args    {
	 *                          Arguments that accompany the requested capability check.
	 *
	 * @param \WP_User $user    The user object.
	 *
	 * @return array
	 */
	public function user_has_cap(
		array $allcaps,
		array $caps,
		array $args,
		\WP_User $user
	): array {

		// Only apply to Notifications.
		if ( empty( $args[2] ) || self::POST_TYPE !== get_post_type( $args[2] ) ) {
			return $allcaps;
		}

		// Deny for any user other than admins or editorial managers.
		if ( empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return $allcaps;
		}

		if ( empty( array_intersect( [ 'pmc-editorial-manager', 'administrator' ], $user->roles ) ) ) {
			unset( $allcaps[ $caps[0] ] );
		}

		return $allcaps;
	}

	/**
	 * Register the fields for the Notifications.
	 *
	 * Ignoring because we don't need to retest Fieldmanager.
	 *
	 * @codeCoverageIgnore Ignoring because it was tested already.
	 */
	public function add_fields() {

		// Disable alignment requirement because it makes these much harder to read.
		// phpcs:disable
		$current_timezone = get_option( 'timezone_string' );

		$fm_notification_settings = new Fieldmanager_Group(
			[
				'name'     => 'notification_settings',
				'children' => [
					'message_type' => new Fieldmanager_Radios(
						[
							'name'          => 'message_type',
							'label'         => 'Message Type',
							'options'       => [
								'custom' => 'Custom',
								'article'   => 'Article ( Uses article title and excerpt )',
							],
							'default_value' => 'custom',
						]
					),
					'title'     => new Fieldmanager_Textfield(
						[
							'name'        => 'title',
							'label'       => __( 'Push Notification Title', 'pmc-push-notifications' ),
							'required'   => true,
							'display_if' => [
								'src'   => 'message_type',
								'value' => 'custom',
							],
						]
					),
					'subtitle'     => new Fieldmanager_Textfield(
						[
							'name'        => 'subtitle',
							'label'       => __( 'Subtitle (iOS Only)', 'pmc-push-notifications' ),
							'required'   => false,
							'display_if' => [
								'src'   => 'message_type',
								'value' => 'custom',
							],
						]
					),
					'message' => new Fieldmanager_TextArea(
						__( 'Message', 'pmc-push-notifications' ),
						[
							'attributes' => [
								'cols'      => 50,
								'rows'      => 10,
								'maxlength' => 175,
							],
							'required'   => true,
							'display_if' => [
								'src'   => 'message_type',
								'value' => 'custom',
							],
						]
					),
					'attached_post' => new Fieldmanager_Autocomplete(
						__( 'Attached Post For Notification', 'pmc-push-notifications' ),
						[
							'limit'              => 1,
							'extra_elements'     => 0,
							'datasource'         => new Fieldmanager_Datasource_Post(
								[
									'query_args' => [
										'post_type'   => [ 'post', 'pmc-gallery', 'pmc_list','pmc_top_video' ],
										'post_status' => 'publish',
									],
								]
							),
						]
					),
					'when_to_deliver' => new Fieldmanager_Radios(
						[
							'name'    => 'when_to_deliver',
							'label'   => 'When to Deliver',
							'options' => [
								'immediately' => 'Immediately',
								'schedule'    => 'Schedule',
							],
							'default_value' => 'immediately',
						]
					),
					'scheduled_time' => new Fieldmanager_Datepicker(
						[
							'name'             => 'scheduled_time',
							'label'            => 'Scheduled Time. ( Site timezone ' . $current_timezone . ' )',
							'use_time'         => true,
							'store_local_time' => true,
							'display_if'       => [
								'src'   => 'when_to_deliver',
								'value' => 'schedule',
							],
						]
					),
					'deliver_immediately_confirmation' => new Fieldmanager_Checkbox(
						[
							'name'        => 'deliver_immediately_confirmation',
							'label'       => 'I understand this will send immediately upon publishing',
							'description' => 'Please check this box before publishing.',
							'display_if'  => [
								'src'   => 'when_to_deliver',
								'value' => 'immediately',
							],
							'required'   => true,
						]
					),
					'deliver_scheduled_confirmation' => new Fieldmanager_Checkbox(
						[
							'name'        => 'deliver_scheduled_confirmation',
							'label'       => 'I understand this will schedule for the selected date upon publishing',
							'description' => 'Please check this box before publishing.',
							'display_if'  => [
								'src'   => 'when_to_deliver',
								'value' => 'schedule',
							],
							'required'   => true,
						]
					),
				],
			]
		);

		// phpcs:enable

		$fm_notification_settings->add_meta_box(
			__( 'Notification Settings', 'pmc-push-notifications' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Avoid the user from publishing a notification without
	 * the required field being properly checked.
	 */
	public function disable_publish_button() {
		global $post;

		if ( empty( $post ) ) {
			return;
		}

		// Avoid loading it in other post types.
		if ( ! empty( $post->post_type ) && self::POST_TYPE !== $post->post_type ) {
			return;
		}

		$post_status = get_post_status();
		$post_disable = 'false';

		$message_id            = get_post_meta( $post->ID, 'push_message_id', true );
		$notification_settings = get_post_meta( $post->ID, 'notification_settings', true );

		if ( isset( $notification_settings['scheduled_time'] ) && 'schedule' === $notification_settings['when_to_deliver'] ) {
			$right_now = getdate();
			if ( $right_now[0] >= $notification_settings['scheduled_time'] ) {
				$post_disable = 'true';
			}
		}

		if ( ! empty( $message_id ) && 'immediately' === $notification_settings['when_to_deliver'] ) {
			$post_disable = 'true';
		}

		?>
		<style>
			div#visibility.misc-pub-section.misc-pub-visibility {
				display: none
			}
			input#fm-notification_settings-0-scheduled_time-0 {
				width: 7.5em;
			}
			input.fm-element.fm-datepicker-time{
				width: 2.5em;
			}
			<?php
			if ( 'true' === $post_disable ) {
				?>
				.edit-post-status{
					display: none;
				}
				<?php
			}
		?>
		</style>

		<script type="text/javascript">
					document.addEventListener('DOMContentLoaded', () => {
						document.getElementById('publish').disabled = true;
						setInterval(
							() => {
								const post_disable = <?php echo wp_json_encode( $post_disable ); ?>;
								// Disable the button.

								// Check for the immediate field.
								const WhenImmediateField      = document.getElementById('fm-notification_settings-0-when_to_deliver-0-immediately');
								const DeliverImmediatelyField = document.getElementById('fm-notification_settings-0-deliver_immediately_confirmation-0');
								const immediatelyFieldActive  = WhenImmediateField.checked && DeliverImmediatelyField.checked;

								// Check for the Schedule field.
								const WhenScheduledField    = document.getElementById('fm-notification_settings-0-when_to_deliver-0-schedule');
								const DeliverscheduledField = document.getElementById('fm-notification_settings-0-deliver_scheduled_confirmation-0');
								const DateField             = document.getElementById('fm-notification_settings-0-scheduled_time-0');
								const scheduledFieldActive  = WhenScheduledField.checked && DeliverscheduledField.checked && '' !== DateField.value;

								// Confirm that any of the required field(s) is/are checked.
								if ( (scheduledFieldActive === true || immediatelyFieldActive === true) && 'false' === post_disable ) {
									// Reenable the button.
									document.getElementById('publish').disabled = false;
								}
							},
							1000
						);
					});

					jQuery( document ).ready( function( $ ) {
						var message_textarea = $( 'textarea#fm-notification_settings-0-message-0' );
						var max = 175;

						message_textarea.parent().append( '<span class="countdown" style="display: block;"></span>' );
						updateCountdownAll();
						message_textarea.on( 'input', updateCountdown );

						function updateCountdownAll() {
							message_textarea.each( function() {
								updateCountdown( this );
							} );
						}

						function updateCountdown( e ) {
							var currentElement;
							if ( e.target ) {
								currentElement = e.target;
							} else {
								currentElement = e;
							}

							var currentLength = $( currentElement ).val().length;
							var remaining = max - currentLength;
							$( currentElement ).nextAll( '.countdown:first' ).text( remaining + ' characters remaining.' );
						}
					} );

					jQuery( document ).ready( function( $ ) {
						var post_status = <?php echo wp_json_encode( $post_status ); ?>;
						var post_disable = <?php echo wp_json_encode( $post_disable ); ?>;
						if ( 'publish' === post_status || 'true' === post_disable ) {
							$( '#fm-notification_settings-0-title-0' ).prop( 'readonly', true );
							$( '#fm-notification_settings-0-attached_post-0' ).prop( 'readonly', true );
							$( '#fm-notification_settings-0-message-0' ).prop( 'readonly', true );
							$( '#fm-notification_settings-0-when_to_deliver-0-immediately' ).attr( 'disabled', 'disabled' );
							$( '#fm-notification_settings-0-when_to_deliver-0-schedule' ).attr( 'disabled', 'disabled' );
							$( '#fm-notification_settings-0-message_type-0-custom' ).attr( 'disabled', 'disabled' );
							$( '#fm-notification_settings-0-message_type-0-article' ).attr( 'disabled', 'disabled' );
							$( '#fm-notification_settings-0-deliver_immediately_confirmation-0' ).prop( 'readonly', true );

							$( '#fm-notification_settings-0-subject-0' ).prop( 'readonly', true );
							$( '#fm-notification_settings-0-subtitle-0' ).prop( 'readonly', true );
							$( 'input[name^="notification_settings[scheduled_time]"]' ).prop( 'readonly', true );
							$( 'select[name^="notification_settings[scheduled_time]"]' ).attr( 'disabled', 'disabled' );
						}
					} );
		</script>
		<?php
	}

	/**
	 * Prevent posts from being published if required fields were filled.
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array
	 */
	public function validate_confirmation( array $data, array $postarr ): array {

		// Return early if we're not trying to publish.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'publish' !== $data['post_status'] || self::POST_TYPE !== $data['post_type'] ) {
			return $data;
		}

		$settings = $postarr['notification_settings'] ?? '';

		if (
			empty( $settings['when_to_deliver'] ) ||
			( 'immediately' === $settings['when_to_deliver'] && empty( $settings['deliver_immediately_confirmation'] ) ) ||
			( 'schedule' === $settings['when_to_deliver'] && empty( $settings['deliver_scheduled_confirmation'] ) )
		) {
			$data['post_status'] = 'draft';
			add_filter( 'redirect_post_location', [ $this, 'add_confirmation_message' ], 99 );
		}

		if ( 'custom' === $settings['message_type'] && ( empty( $settings['message'] ) || empty( $settings['title'] ) ) ) {
			$data['post_status'] = 'draft';
			add_filter( 'redirect_post_location', [ $this, 'add_empty_post_content_message' ], 99 );
		}

		if ( 'article' === $settings['message_type'] && empty( $settings['attached_post'] ) ) {
			$data['post_status'] = 'draft';
			add_filter( 'redirect_post_location', [ $this, 'add_empty_article_message' ], 99 );
		}

		if ( 'publish' === $data['post_status'] ) {
			$response = $this->create_push_notification_on_publish( $postarr );
			if ( ! empty( $response ) ) {
				$data['post_status'] = 'draft';
				if ( 'Schedule Notifications may not be scheduled in the past.' === $response ) {
					add_filter( 'redirect_post_location', [ $this, 'date_is_in_past' ], 99 );
				} else {
					add_filter( 'redirect_post_location', [ $this, 'something_went_wrong' ], 99 );
				}
			}
		}

		return $data;
	}

	/**
	 * Delete push notification on draft transition.
	 *
	 * @param string  $new_status New Status.
	 * @param string  $old_status Old Status.
	 * @param WP_Post $post       Post object.
	 */
	public function delete_push_notification_on_draft( string $new_status, string $old_status, WP_Post $post ) {

		if ( self::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( ( 'draft' !== $new_status || 'trash' !== $new_status ) && 'publish' !== $old_status ) {
			return;
		}

		$message_id = get_post_meta( $post->ID, self::PUSH_MESSAGE_ID_META, true );
		if ( empty( $message_id ) ) {
			return;
		}

		// Try to delete push message.
		$response = $this->api()->delete_push_message( $message_id );

		// Delete push message ID from post meta.
		if ( true === wp_validate_boolean( $response ) ) {
			delete_post_meta( $post->ID, self::PUSH_MESSAGE_ID_META, $message_id );
		}
	}

	/**
	 * Create push notification.
	 *
	 * @param array $postarr Post Data.
	 * @return string
	 */
	protected function create_push_notification_on_publish( array $postarr ): string {
		$post_id       = absint( $postarr['post_ID'] );
		$push_settings = get_option( 'push_notifications_settings', [] );
		$attached_post = '';

		if ( ! empty( $postarr['notification_settings']['attached_post'] ) ) {
			$attached_post = get_post( $postarr['notification_settings']['attached_post'] );

			if ( $attached_post instanceof WP_Post ) {
				if ( 'pmc_gallery' === $attached_post->post_type ) {
					$app_url = $push_settings['app_url'] . 'article/' . $attached_post->ID . '/' . $attached_post->post_type;
				} elseif ( 'pmc_top_video' === $attached_post->post_type ) {
					$app_url = $push_settings['app_url'] . 'video/' . $attached_post->ID;
				} else {
					$app_url = $push_settings['app_url'] . 'article/' . $attached_post->ID;
				}
			}
		} else {
			$app_url = $push_settings['app_url'];
		}

		// Getting those fields directly.
		if ( 'custom' === $postarr['notification_settings']['message_type'] ) {
			$title    = html_entity_decode( $postarr['notification_settings']['title'] );
			$subtitle = html_entity_decode( $postarr['notification_settings']['subtitle'] );
			$message  = html_entity_decode( $postarr['notification_settings']['message'] );
		} else {
			if ( $attached_post instanceof WP_Post ) {
				$title    = html_entity_decode( $attached_post->post_title );
				$subtitle = '';

				$message = substr( $attached_post->post_excerpt, 0, 175 );

				if ( empty( $message ) ) {
					$message = substr( $attached_post->post_content, 0, 175 );
				}

				$message = html_entity_decode( $message );
			}
		}

		$segments   = explode( ',', $push_settings['included_segments'] );
		$value      = $postarr['notification_settings']['scheduled_time'];
		$send_after = '';

		if ( 'schedule' === $postarr['notification_settings']['when_to_deliver'] ) {
			$time_to_parse = sanitize_text_field( $value['date'] );
			if ( isset( $value['hour'] ) && is_numeric( $value['hour'] ) ) {
				$hour           = intval( $value['hour'] );
				$minute         = ( isset( $value['minute'] ) && is_numeric( $value['minute'] ) ) ? intval( $value['minute'] ) : 0;
				$time_to_parse .= ' ' . $hour;
				$time_to_parse .= ':' . str_pad( $minute, 2, '0', STR_PAD_LEFT );
				$time_to_parse .= ' ' . sanitize_text_field( $value['ampm'] );
			}

			$offset     = (float) get_option( 'gmt_offset' );
			$hours      = (int) $offset;
			$minutes    = ( $offset - $hours );
			$sign       = ( $offset < 0 ) ? '-' : '+';
			$abs_hour   = abs( $hours );
			$abs_mins   = abs( $minutes * 60 );
			$tz_offset  = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
			$send_after = ! empty( $postarr['notification_settings']['scheduled_time'] ) ? gmdate( 'M d Y H:i:s \G\M\T' . $tz_offset, strtotime( $time_to_parse ) ) : '';
		}

		$data = [
			'app_id'            => $push_settings['app_id'],
			'app_url'           => $app_url,
			'included_segments' => $segments,
			'headings'          => [ 'en' => $title ],
			'subtitle'          => [ 'en' => $subtitle ],
			'contents'          => [ 'en' => $message ],
			'send_after'        => $send_after,
		];

		// Create push message.
		$response = $this->api()->create_push_message( $data );

		// No ID, so something went wrong.
		if ( empty( $response->id ) ) {
			if ( isset( $response->errors[0] ) ) {
				return $response->errors[0];
			}
			return 'Something went wrong';
		}

		// Save push message ID.
		add_post_meta( $post_id, self::PUSH_MESSAGE_ID_META, $response->id, true );

		// Return nothing.
		return '';
	}

	/**
	 * Adds a query arg to trigger the notification about needing to confirm that the
	 * notification will be published.
	 *
	 * @param string $location The destination URL.
	 * @return string
	 */
	public function something_went_wrong( string $location ): string {
		remove_filter( 'redirect_post_location', [ $this, 'something_went_wrong' ], 99 );
		return add_query_arg(
			[ 'something_went_wrong' => true ],
			$location
		);
	}

	/**
	 * Adds a query arg to trigger the notification about needing to confirm that the
	 * notification will be published.
	 *
	 * @param string $location The destination URL.
	 * @return string
	 */
	public function date_is_in_past( string $location ): string {
		remove_filter( 'redirect_post_location', [ $this, 'date_is_in_past' ], 99 );
		return add_query_arg(
			[ 'date_is_in_past' => true ],
			$location
		);
	}

	/**
	 * Adds a query arg to trigger the notification about needing to confirm that the
	 * notification will be published.
	 *
	 * @param string $location The destination URL.
	 * @return string
	 */
	public function add_confirmation_message( string $location ): string {
		remove_filter( 'redirect_post_location', [ $this, 'add_confirmation_message' ], 99 );
		return add_query_arg(
			[ 'needs_publish_confirmation' => true ],
			$location
		);
	}

	/**
	 * Adds a query arg to trigger the notification about needing to add content.
	 *
	 * @param string $location The destination URL.
	 * @return string
	 */
	public function add_empty_post_content_message( string $location ): string {
		remove_filter( 'redirect_post_location', [ $this, 'add_empty_post_content_message' ], 99 );
		return add_query_arg(
			[ 'needs_content' => true ],
			$location
		);
	}

	/**
	 * Adds a query arg to trigger the notification about needing to add content.
	 *
	 * @param string $location The destination URL.
	 * @return string
	 */
	public function add_empty_article_message( string $location ): string {
		remove_filter( 'redirect_post_location', [ $this, 'add_empty_article_message' ], 99 );
		return add_query_arg(
			[ 'needs_article' => true ],
			$location
		);
	}

	/**
	 * Filters the post updated messages.
	 *
	 * This doesn't actually filter the Messages, but we need a place to
	 * hook in to prevent the "Post published." message from showing if
	 * we have prevented the post status from being changed to "publish".
	 *
	 * @param array[] $messages Post updated messages.
	 *
	 * @return array
	 */
	public function post_updated_messages( array $messages ): array {

		// phpcs:disable
		if (
			isset( $_GET['needs_publish_confirmation'] ) ||
			isset( $_GET['needs_content'] ) ||
			isset( $_GET['add_empty_article_message'] ) ||
			isset( $_GET['date_is_in_past'] ) ||
			isset( $_GET['something_went_wrong'] )
		) {
			unset( $_GET['message'] );
		}
		// phpcs:enable

		return $messages;
	}

	/**
	 * Display the admin notice if the confirmation checkbox was not selected.
	 */
	public function admin_notices() {
		$current_screen = get_current_screen();

		if ( self::POST_TYPE !== $current_screen->post_type || 'edit' === $current_screen->base ) {
			return null;
		}

		$post_id = get_the_ID();

		$message_id            = get_post_meta( $post_id, 'push_message_id', true );
		$notification_settings = get_post_meta( $post_id, 'notification_settings', true );
		$notification_sent     = false;

		if ( isset( $notification_settings['scheduled_time'] ) && 'schedule' === $notification_settings['when_to_deliver'] ) {
			$right_now = getdate();

			if ( $notification_settings['scheduled_time'] >= $right_now[0] ) {
				?>
				<div class="notice notice-success">
					<p style="font-weight: bold"><?php esc_html_e( 'This notification has been scheduled, it still be changed or updated until the scheduled time. You must draft it first.', 'pmc-push-notifications' ); ?></p>
				</div>
				<?php
			} elseif ( $right_now[0] >= $notification_settings['scheduled_time'] ) {
				$notification_sent = true;
			}
		}

		if ( ! empty( $message_id ) && 'immediately' === $notification_settings['when_to_deliver'] ) {
			$notification_sent = true;
		}

		if ( $notification_sent ) {
			?>
			<div class="notice notice-success">
				<p style="font-weight: bold"><?php esc_html_e( 'This notification has been sent, it cannot be changed or updated.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}

		// phpcs:ignore
		if ( isset( $_GET['needs_publish_confirmation'] ) ) {
			?>
			<div class="notice notice-error">
				<p style="font-weight: bold"><?php esc_html_e( 'Please confirm that the notification will be sent before publishing.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}

		// phpcs:ignore
		if ( isset( $_GET['needs_content'] ) ) {
			?>
			<div class="notice notice-error">
				<p style="font-weight: bold"><?php esc_html_e( 'Please fill out the Title and Message before publishing.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}

		// phpcs:ignore
		if ( isset( $_GET['needs_article'] ) ) {
			?>
			<div class="notice notice-error">
				<p style="font-weight: bold"><?php esc_html_e( 'Please select article for notification.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}

		// phpcs:ignore
		if ( isset( $_GET['date_is_in_past'] ) ) {
			?>
			<div class="notice notice-error">
				<p style="font-weight: bold"><?php esc_html_e( 'Schedule Notifications may not be scheduled in the past.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_GET['something_went_wrong'] ) ) {
			?>
			<div class="notice notice-error">
				<p style="font-weight: bold"><?php esc_html_e( 'Something went wrong with the request. Try again.', 'pmc-push-notifications' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Get Mobile Push API object.
	 *
	 * @return Mobile_Push_API
	 */
	protected function api() {
		// Getting token and url from the pmc notifications settings.
		$push_settings = get_option( 'push_notifications_settings', [] );

		$rest_api_key = \defined( 'PMC_PUSH_NOTIFICATION_REST_API_KEY' )
			? PMC_PUSH_NOTIFICATION_REST_API_KEY
			: $push_settings['rest_api_key'] ?? '';

		$app_id = \defined( 'PMC_PUSH_NOTIFICATION_APP_ID' )
			? PMC_PUSH_NOTIFICATION_APP_ID
			: $push_settings['app_id'] ?? '';

		return new Mobile_Push_API(
			[
				'rest_api_key' => $rest_api_key,
				'app_id'       => $app_id,
			]
		);
	}
}
