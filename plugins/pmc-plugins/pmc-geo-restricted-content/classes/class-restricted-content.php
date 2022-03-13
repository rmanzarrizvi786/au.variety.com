<?php

namespace PMC\Geo_Restricted_Content;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API as Post_Options_API;

class Restricted_Content {

	use Singleton;

	const OPTION_NAME  = 'restrict-post-from-uk';
	const OPTION_LABEL = 'Restrict post from UK';
	const CACHE_GROUP  = 'geo_restrict_content';
	const CACHE_LIFE   = HOUR_IN_SECONDS * 12;

	/**
	 * Set up hooks
	 *
	 * @since 2019-12-30 Jignesh Nakrani ROP-764
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'action_init' ] );
		add_action( 'save_post', [ $this, 'action_post_save_clear_cache' ], 10, 3 );
		add_action( 'template_redirect', [ $this, 'action_restrict_post_content' ] );

	}

	/**
	 * Add Post Options to allow Editors flag a post to play it's featured video in the river.
	 */
	public function action_init() {

		$post_options_api = Post_Options_API::get_instance();

		$post_options_api->register_global_options( [ self::OPTION_NAME => [ 'label' => self::OPTION_LABEL ] ] );
	}

	/**
	 * Do not load the post if it's restricted/blocked for UK.
	 */
	public function action_restrict_post_content() {

		$post = get_post();

		/*
		 * Check if post should be restrict/block for UK or Not.
		 */
		if (
			! empty( $post )
			&& is_single()
			&& ! is_preview()
			&& 'gb' === strtolower( pmc_geo_get_user_location() )
			&& $this->_is_post_restricted( $post )
		) {

			wp_die(
				'Access to this content has been restricted in your region.',
				'Error 451: Unavailable For Legal Reasons',
				'451'
			);

		}

	}

	/**
	 * Function to check current post request should restrict/block on UK region of not.
	 *
	 * @param \WP_Post $post The post in question.
	 * @param bool     $refresh_cache Whether to refresh the cache or not.
	 *
	 * @return bool returns TRUE if current post is BLocked/restricted for UK and request is coming from UK(gb) region else FALSE
	 */
	private function _is_post_restricted( $post, $refresh_cache = false ): bool {

		$key   = $post->ID . '_restricted-content';
		$cache = new \PMC_Cache( $key, self::CACHE_GROUP );

		$cache
			->expires_in( self::CACHE_LIFE )
			->updates_with(
				[ $this, 'is_post_restricted_uncached' ],
				[ $post ]
			);

		if ( $refresh_cache ) {
			$cache->invalidate();
		}

		return $cache->get();

	}

	/**
	 * Determine whether $post is restricted Post.
	 *
	 * @param \WP_Post $post The Post object.
	 *
	 * @return bool
	 */
	public function is_post_restricted_uncached( $post ) : bool {

		$cache_data = false;

		if ( is_object( $post ) ) {
			$cache_data = Post_Options_API::get_instance()->post( $post->ID )->has_option( self::OPTION_NAME );
		}

		return $cache_data;
	}

	/**
	 * Fires on save_post_post to purge  cache.
	 *
	 * @param int      $post_id Post ID of post being saved.
	 * @param \WP_Post $post    Post Object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public function action_post_save_clear_cache( $post_id, $post, $update ) {

		// If post_id is empty, bail out.
		// If this is just a revision, bail out.
		// If $post is not object, bail out
		if ( ! empty( $post_id ) && ! wp_is_post_revision( $post_id ) && is_object( $post ) ) {

			$this->_is_post_restricted( $post, true );

		}
	}

}

//EOF
