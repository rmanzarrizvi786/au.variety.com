<?php
/**
 * Override settings and add new options to offical web stories plugin.
 *
 * @package pmc-web-stories
 */

namespace PMC\Web_Stories;

use Google\Web_Stories\AMP\Integration\AMP_Story_Sanitizer;
use Google\Web_Stories\AMP\Story_Sanitizer;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Web_Stories
 */
class Web_Stories {

	use Singleton;

	const POST_TYPE = 'web-story';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup Hooks
	 */
	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'action_init' ] );
		add_filter( 'pmc-post-options-allowed-types', [ $this, 'filter_pmc_post_options_allowed_types' ] );
		add_filter( 'user_has_cap', [ $this, 'apply_web_stories_caps' ], 10, 1 );
		add_filter( 'rest_prepare_attachment', [ $this, 'force_https_attachment' ] );
		add_filter( 'web_stories_editor_settings', [ $this, 'update_user_endpoint' ] );
		add_action( 'pre_get_posts', [ $this, 'remove_google_attachments_meta_query' ], 11 ); // Adding after Google adds their query.
		add_action( 'web_stories_story_head', [ $this, 'remove_new_relic_for_web_stories' ] );
		// we need to override the sanitizers with a lower priority
		add_filter( 'web_stories_amp_sanitizers', [ $this, 'filter_amp_sanitizers' ], 999 );
		add_filter( 'amp_content_sanitizers', [ $this, 'filter_amp_sanitizers' ], 999 );

		// add this web-story to sitemaps
		add_filter( 'pmc_sitemaps_post_type_whitelist', [ $this, 'filter_sitemaps_post_types' ] );
		add_filter( 'msm_sitemap_entry_post_type', [ $this, 'filter_sitemaps_post_types' ] );

		add_filter( 'wp_insert_post_data', [ $this, 'filter_wp_insert_post_data' ], 9999, 2 );

	}

	public function action_init() {
		$this->enable_experimental_features();
		add_action( 'wp', [ $this, 'maybe_setup_hooks' ] );
	}

	public function maybe_setup_hooks() {
		if ( ! is_singular( 'web-story' ) ) {
			return;
		}

		add_filter( 'template_include', [ $this, 'maybe_override_template' ], PHP_INT_MAX );

		// @see Google\Web_Stories\Integrations::filter_amp_validation_error_sanitized
		// We want to accept all errors by default but we don't want to remove the invalid node
		// return true => accept error, remove node with errors
		// return false => reject error, do not remove node
		// return null => default, generate error info node
		add_filter( 'amp_validation_error_sanitized', '__return_false' );

		add_action( 'amp_server_timing_stop', [ $this, 'action_amp_server_timing_stop' ] );
		do_action( 'amp_server_timing_stop', 'amp_sanitizer' );

		add_filter( 'default_post_metadata', [ $this, 'filter_default_post_metadata' ], 10, 5 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_wp_get_attachment_image_src' ], 10, 3 );

	}

	public function action_amp_server_timing_stop( $event_name ) {
		if ( 'amp_sanitizer' === $event_name
			&& property_exists( \AMP_Validation_Manager::class, 'is_validate_request' )
			&& property_exists( \AMP_Validation_Manager::class, 'validation_results' )
			&& ! \AMP_Validation_Manager::$is_validate_request
			) {
			// clear all validation errors to prevent invalid AMP script generation
			// AMP generate this code when it detected errors: document.addEventListener( "DOMContentLoaded", function() { document.write = function( text ) { throw new Error( "[AMP-WP] Prevented document.write() call with: "  + text ); }; } );
			\AMP_Validation_Manager::$validation_results = [];
		}
	}

	/**
	 * Detect and implement fallback default web stories poster images
	 * @param Array $image        Array of image properties
	 * @param int $attachment_id  The attachment id
	 * @param string $size        The image size string
	 * @return array|mixed
	 */
	public function filter_wp_get_attachment_image_src( $image, $attachment_id, $size ) {
		if ( empty( $image ) && PHP_INT_MIN === $attachment_id ) {
			switch ( $size ) {
				case 'amp-story-poster-portrait':
				case 'web-stories-poster-portrait':
					$img_url = apply_filters( 'pmc_web_stories_poster_portrait', false );
					if ( ! empty( $img_url ) ) {
						$image = [
							$img_url,
							640,
							853,
							false,
						];
					}
					break;
				case 'amp-story-poster-landscape':
				case 'web-stories-poster-landscape':
					$img_url = apply_filters( 'pmc_web_stories_poster_landscape', false );
					if ( ! empty( $img_url ) ) {
						$image = [
							$img_url,
							853,
							640,
							false,
						];
					}
					break;
				case 'amp-story-poster-square':
				case 'web-stories-poster-square':
					$img_url = apply_filters( 'pmc_web_stories_poster_square', false );
					if ( ! empty( $img_url ) ) {
						$image = [
							$img_url,
							640,
							640,
							false,
						];
					}
					break;
			}
		}
		return $image;
	}

	/**
	 * We're using the 'default_post_metadata' filter to generate a fake _thumbnail_id value to inject the default poster image
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 * @param $meta_type
	 * @return int|mixed|string
	 */
	public function filter_default_post_metadata( $value, $object_id, $meta_key, $single, $meta_type ) {

		if ( empty( $value )
			&& $single
			&& 'web-story' === get_post_type( $object_id )
			&& '_thumbnail_id' === $meta_key
			&& 'post' === $meta_type ) {

			$value = (int) apply_filters( 'pmc_web_stories_poster_thumbnail_id', $value, get_post( $object_id ) );
			if ( 0 === $value ) {
				$value = PHP_INT_MIN;
			}

		}

		return $value;
	}

	/**
	 * We want to override the web-story template to our custom template
	 * @param $template
	 * @return mixed|string
	 */
	public function maybe_override_template( $template ) {
		if ( is_singular( 'web-story' ) && preg_match( '/single-web-story/', $template ) ) {
			$template = PMC_WEBSTORIES_DIR . '/templates/single-web-story.php';
		}
		return $template;
	}

	/**
	 *
	 * @param array $sanitizers Sanitizers.
	 * @return array Sanitizers.
	 */
	public function filter_amp_sanitizers( $sanitizers ) {
		$sanitizer_classes = [ Story_Sanitizer::class, AMP_Story_Sanitizer::class ];
		foreach ( $sanitizer_classes as $class ) {
			if ( isset( $sanitizers[ $class ] ) ) {
				// poster images cannot be empty, remove any empty value
				if ( isset( $sanitizers[ $class ]['poster_images'] ) ) {
					$sanitizers[ $class ]['poster_images'] = array_filter( $sanitizers[ $class ]['poster_images'] );
				}
			}
		}
		return $sanitizers;
	}

	/**
	 * WPCom will convert the script tags in the post_content to anchor tags before inserting in DB. Turn these back to script tags before rendering.
	 * Remove once all properties are on VIP Go
	 *
	 * @param WP_Post $post The post object
	 *
	 * @return WP_Post
	 */
	public function fix_post( \WP_Post $post ) : \WP_Post {
		$content = $post->post_content;

		$content = str_replace( '<a href="https://cdn.ampproject.org/v0.js">https://cdn.ampproject.org/v0.js</a>', '<script async="" src="https://cdn.ampproject.org/v0.js"></script>', $content ); // phpcs:ignore
		$content = str_replace( '<a href="https://cdn.ampproject.org/v0/amp-story-1.0.js">https://cdn.ampproject.org/v0/amp-story-1.0.js</a>', '<script async="" src="https://cdn.ampproject.org/v0/amp-story-1.0.js" custom-element="amp-story"></script>', $content ); // phpcs:ignore
		$content = str_replace( '<a href="https://cdn.ampproject.org/v0/amp-video-0.1.js">https://cdn.ampproject.org/v0/amp-video-0.1.js</a>', '<script async="" src="https://cdn.ampproject.org/v0/amp-video-0.1.js" custom-element="amp-video"></script>', $content ); // phpcs:ignore
		$content = str_replace( '<meta name="web-stories-replace-head-start" />', '<meta name="web-stories-replace-head-start"/>', $content ); // phpcs:ignore
		$content = str_replace( '<meta name="web-stories-replace-head-end" />', '<meta name="web-stories-replace-head-end"/>', $content ); // phpcs:ignore

		$thumbnail_id = (int) get_post_thumbnail_id( $post );

		if ( 0 !== $thumbnail_id ) {
			$poster_portrait = wp_get_attachment_image_url( $thumbnail_id, 'web-stories-poster-portrait' );
			if ( ! empty( $poster_portrait ) ) {
				$content = preg_replace_callback(
					'/<amp-story .*?poster-portrait-src=""/',
					function( $matches ) use ( $poster_portrait ) {
						return str_replace( 'poster-portrait-src=""', sprintf( 'poster-portrait-src="%s"', esc_attr( $poster_portrait ) ), $matches[0] );
					},
					$content
				);
			}
		}

		$post->post_content = $content;

		return $post;
	}

	public function remove_google_attachments_meta_query( &$query ) {
		$post_type = $query->get( 'post_type' );

		if ( ! in_array( 'any', (array) $post_type, true ) && ! in_array( 'attachment', (array) $post_type, true ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );

		$unset_meta_query = [
			[
				'key'     => 'web_stories_is_poster',
				'compare' => 'NOT EXISTS',
			],
		];

		$meta_query = array_diff( $meta_query, $unset_meta_query );

		$query->set( 'meta_query', $meta_query ); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
	}

	/**
	 * Force the guid fields to be https.
	 */
	public function force_https_attachment( $response ) {
		if ( $response->data['guid']['rendered'] ) {
			$response->data['guid']['rendered'] = set_url_scheme( $response->data['guid']['rendered'], 'https' );
		}
		if ( $response->data['guid']['raw'] ) {
			$response->data['guid']['raw'] = set_url_scheme( $response->data['guid']['raw'], 'https' );
		}
		return $response;
	}

	/**
	 * Enables the usefull experimental features by default.
	 */
	public function enable_experimental_features() {
		$expiremental_options = get_option( 'web_stories_experiments' );

		$expiremental_options['enableAnimation'] = true;

		update_option( 'web_stories_experiments', $expiremental_options );
	}

	/**
	 * Filter post types in post options module.
	 *
	 * @param array $post_types array of post types.
	 *
	 * @return array $post_types
	 */
	public function filter_pmc_post_options_allowed_types( $post_types ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = [];
		}

		$post_types[] = 'web-story';

		return $post_types;
	}

	/**
	 * Maps web story capabilities to post capabilities.
	 * Remove once all properties are on VIP Go
	 *
	 * @param array $caps array Capabilities of current user.
	 *
	 * @return array $caps
	 */
	public function apply_web_stories_caps( $caps ) {

		if ( ! empty( $caps['edit_posts'] ) ) {
			$caps['edit_web-story']   = true;
			$caps['edit_web-stories'] = true;
		}

		if ( ! empty( $caps['edit_published_posts'] ) ) {
			$caps['edit_published_web-stories'] = true;
		}

		if ( ! empty( $caps['read'] ) ) {
			$caps['read_web-story'] = true;
		}

		if ( ! empty( $caps['read_private_posts'] ) ) {
			$caps['read_private_web-stories'] = true;
		}

		if ( ! empty( $caps['delete_posts'] ) ) {
			$caps['delete_web-story']   = true;
			$caps['delete_web-stories'] = true;
		}

		if ( ! empty( $caps['edit_others_posts'] ) ) {
			$caps['edit_others_web-stories'] = true;
		}

		if ( ! empty( $caps['publish_posts'] ) ) {
			$caps['publish_web-stories'] = true;
		}

		if ( ! empty( $caps['edit_private_posts'] ) ) {
			$caps['edit_private_web-stories'] = true;
		}

		if ( ! empty( $caps['delete_private_posts'] ) ) {
			$caps['delete_private_web-stories'] = true;
		}

		if ( ! empty( $caps['delete_published_posts'] ) ) {
			$caps['delete_published_web-stories'] = true;
		}

		if ( ! empty( $caps['delete_others_posts'] ) ) {
			$caps['delete_others_web-stories'] = true;
		}

		return $caps;
	}

	/**
	 * Updates the user endpoint for editor.
	 *
	 * @param array $setttings An array of editor settings.
	 * @return void
	 */
	public function update_user_endpoint( $setttings ) {

		if ( ! defined( 'PMC_IS_VIP_GO_SITE' ) || true !== PMC_IS_VIP_GO_SITE ) {
			$setttings['config']['api']['users'] = '/pmc-web-stories/v1/users';
		}


		return $setttings;
	}

	/**
	 * Return a other list of valid post types for pmc sitemap
	 *
	 * @param array $post_types An array of post types.
	 *
	 * @return array
	 */
	public function filter_sitemaps_post_types( $post_types ) {

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = [];
		}

		$post_types = array_merge( $post_types, [ self::POST_TYPE ] );

		return array_values( array_unique( (array) $post_types ) );
	}

	/**
	 * Filter to workaround WebStories revert back to original_post_status because web stories
	 * use wp rest api to update the post; while custom meta data use traditional wp edit post update
	 * Both trigger one after another causing race condition where Rest API update post status = publish
	 * However, custom meta data update cause post status to revert back to draft (original post status read from db during race condition)
	 *
	 * To property fix this, one of these options might be applicable:
	 * 1. Patch WP core support post data submit detection and skip updating the post related info if no data related to post are submit
	 * 2. Patch web-stories to include post status update when custom meta data are submit
	 * 3. Disable all custom meta data on web-stories edit
	 *
	 * @param $data
	 * @param $postarr
	 * @return mixed
	 */
	public function filter_wp_insert_post_data( $data, $postarr ) {
		if (
			is_admin()
			&& version_compare( WEBSTORIES_VERSION, '1.5', '>=' )
			&& ! empty( $postarr['action'] ) && 'editpost' === $postarr['action']
			&& ! empty( $data['post_type'] ) && 'web-story' === $data['post_type']
			&& ! empty( $postarr['ID'] )
			&& ! empty( \PMC::filter_input( INPUT_GET, 'meta-box-loader' ) )
		) {
			unset( $data['post_status'] );
		}
		return $data;
	}

	/**
	 * Disable new relic monitoring on web stories
	 * @return void
	 */
	public function remove_new_relic_for_web_stories(): void {
		// Ensure PHP agent is available
		if ( is_singular( 'web-story' ) && extension_loaded( 'newrelic' ) ) {
			//  Injected on VIP, so can't test locally
			// @codeCoverageIgnoreStart
			newrelic_disable_autorum();
			// @codeCoverageIgnoreEnd
		}
	}

}
