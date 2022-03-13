<?php
/**
 * Support Exacttarget newsletters for Digital Daily issues.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily\Integrations;

use function PMC\Digital_Daily\get_latest;
use PMC\Digital_Daily\Cheezcap;
use PMC\Digital_Daily\Full_View;
use PMC\Digital_Daily\Table_Of_Contents;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Touts\Tout;

/**
 * Class Exacttarget.
 */
class Exacttarget {
	use Singleton;

	/**
	 * Digital Daily issue ID.
	 *
	 * @var int|null
	 */
	protected ?int $_dd_id = null;

	/**
	 * Digital Daily's reformatted Table of Contents.
	 *
	 * @var array
	 */
	protected array $_toc = [];

	/**
	 * Image size for thumbnail overrides.
	 *
	 * @var string
	 */
	protected string $_image_size = 'full';

	/**
	 * Thumbnail width and height.
	 *
	 * @var array
	 */
	protected array $_thumbnail_args;

	/**
	 * Exacttarget constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks(): void {
		add_filter(
			'sailthru_process_recurring_post',
			[ $this, 'process_post' ],
			10,
			4
		);
	}

	/**
	 * Hook into post processing to set overrides from Table of Contents meta.
	 *
	 * @param array $post          Post data.
	 * @param mixed $original_post Unused. Post object or array of post data.
	 * @param array $settings      Newsletter settings.
	 * @param mixed $image_size    Newsletter's configured image size.
	 * @return array
	 */
	public function process_post(
		array $post,
		$original_post,
		array $settings,
		$image_size
	): array {
		if ( Cheezcap::get( 'newsletter' ) !== $settings['feed_ref'] ) {
			return $post;
		}

		$this->_set_data( is_string( $image_size ) ? $image_size : null );

		$this->_override_post_data( $post );

		return $post;
	}

	/**
	 * Cache data needed to override post data.
	 *
	 * @param string|null $image_size Named thumbnail image size, if specified.
	 * @return void
	 */
	protected function _set_data( ?string $image_size ): void {
		if ( ! empty( $this->_dd_id ) ) {
			return;
		}

		$this->_dd_id = get_latest();
		$this->_get_toc();

		if ( ! empty( $image_size ) ) {
			$this->_image_size = $image_size;
		}

		$this->_thumbnail_args = [
			'w' => (int) get_option( 'mmcnewsletter_feature_image_width' ),
			'h' => (int) get_option( 'mmcnewsletter_feature_image_height' ),
		];
	}

	/**
	 * Retrieve Table of Contents meta and reformat for easier access.
	 */
	protected function _get_toc(): void {
		if ( empty( $this->_dd_id ) ) {
			return;
		}

		$toc = get_post_meta(
			$this->_dd_id,
			Table_Of_Contents::META_KEY,
			true
		);

		if ( empty( $toc ) ) {
			return;
		}

		foreach ( $toc as $entry ) {
			$this->_toc[ $entry['ID'] ] = $entry;
		}
	}

	/**
	 * Set post data from overrides set in issue's blocks.
	 *
	 * @param array $post Post data.
	 * @return void
	 */
	public function _override_post_data( array &$post ): void {
		$id = $post['ID'];

		// Cover image is added as a tout, and should use the tout's settings.
		if ( Tout::POST_TYPE_NAME === get_post_type( $id ) ) {
			return;
		}

		$post['permalink'] = Full_View::get_post_permalink(
			$this->_dd_id,
			$id
		);

		if ( ! isset( $this->_toc[ $id ] ) ) {
			return;
		}

		if ( isset( $this->_toc[ $id ]['title'] ) ) {
			$post['title'] = $this->_toc[ $id ]['title'];
		}

		if ( isset( $this->_toc[ $id ]['excerpt'] ) ) {
			$post['excerpt'] = sailthru_fix_html_encoding(
				$this->_toc[ $id ]['excerpt']
			);
		}

		if ( isset( $this->_toc[ $id ]['content'] ) ) {
			$post['content'] = $this->_toc[ $id ]['content'];
		}

		if ( isset( $this->_toc[ $id ]['featured_image'] ) ) {
			$post['thumb'] = create_mmcnewsletter_feature_image(
				wp_get_attachment_image_url(
					$this->_toc[ $id ]['featured_image'],
					$this->_image_size
				),
				$this->_thumbnail_args['w'],
				$this->_thumbnail_args['h']
			);
		}
	}
}
