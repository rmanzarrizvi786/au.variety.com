<?php
/**
 * Meta for Digital Daily features.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Global_Functions\WP_REST_API\Utilities\Sanitization;

/**
 * Class Meta.
 */
class Meta {
	use Singleton;

	/**
	 * Meta key indicating that an issue shows an ad atop its cover image.
	 */
	protected const KEY_ISSUE_HAS_COVER_AD =
		'_pmc_digital_daily_issue_has_cover_ad';

	/**
	 * Meta key identifying print version of a Digital Daily issue.
	 */
	protected const KEY_PRINT_PDF = '_pmc_digital_daily_print_support_pdf_id';

	/**
	 * Meta key identifying header image for a Special Edition.
	 */
	protected const KEY_SPECIAL_EDITION_HEADER_IMAGE =
		'_pmc_digital_daily_special_edition_header_image_id';

	/**
	 * Check if an issue displays an ad atop its cover image.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function issue_has_cover_ad( int $post_id ): bool {
		if ( ! Assets::get_instance()->boomerang_is_available() ) {
			return false;
		}

		return (bool) get_post_meta(
			$post_id,
			static::KEY_ISSUE_HAS_COVER_AD,
			true
		);
	}

	/**
	 * Retrieve attachment ID for issue's print PDF.
	 *
	 * @param int $post_id Post ID.
	 * @return int|null
	 */
	public static function get_print_pdf_id( int $post_id ): ?int {
		return static::_get_id( $post_id, static::KEY_PRINT_PDF );
	}

	/**
	 * Retrieve ID of a Special Edition's header image.
	 *
	 * @param int $post_id Post ID.
	 * @return int|null
	 */
	public static function get_special_edition_header_image_id(
		int $post_id
	): ?int {
		return static::_get_id(
			$post_id,
			static::KEY_SPECIAL_EDITION_HEADER_IMAGE
		);
	}

	/**
	 * Retrieve a given key's value from the specified post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return int|null
	 */
	protected static function _get_id( int $post_id, string $key ): ?int {
		$id = get_post_meta( $post_id, $key, true );

		return empty( $id ) ? null : (int) $id;
	}

	/**
	 * Meta constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Run after post types are registered.
		add_action( 'init', [ $this, 'register' ], 20 );

		add_filter(
			'is_protected_meta',
			[ $this, 'allow_rest_edits' ],
			10,
			2
		);
	}

	/**
	 * Register meta.
	 */
	public function register(): void {
		$meta = [
			static::KEY_ISSUE_HAS_COVER_AD           => [
				'default'           => false,
				'object_subtype'    => POST_TYPE,
				'sanitize_callback' => [ Sanitization::class, 'boolean' ],
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'boolean',
			],
			static::KEY_PRINT_PDF                    => [
				'default'           => 0,
				'object_subtype'    => POST_TYPE,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'integer',
			],
			static::KEY_SPECIAL_EDITION_HEADER_IMAGE => [
				'default'           => 0,
				'object_subtype'    => POST_TYPE,
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'integer',
			],
		];

		foreach ( $meta as $key => $args ) {
			register_meta( 'post', $key, $args );
		}
	}

	/**
	 * Allow meta keys prefixed with and underscore to be edited via the REST
	 * API.
	 *
	 * @param bool   $protected Is meta key deemed protected.
	 * @param string $key       Meta key.
	 * @return bool
	 */
	public function allow_rest_edits( bool $protected, string $key ): bool {
		$keys = [
			static::KEY_ISSUE_HAS_COVER_AD,
			static::KEY_PRINT_PDF,
			static::KEY_SPECIAL_EDITION_HEADER_IMAGE,
			Table_Of_Contents::META_KEY,
		];

		// Variable is defined as an array and never overwritten.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		if ( in_array( $key, $keys, true ) ) {
			return false;
		}

		return $protected;
	}
}
