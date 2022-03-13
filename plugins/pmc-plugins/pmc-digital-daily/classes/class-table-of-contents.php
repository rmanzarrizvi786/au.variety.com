<?php
/**
 * Digital Daily Table of Contents feature.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Global_Functions\Utility\Post;
use PMC_Primary_Taxonomy;
use WP_Post;
use WP_Term;

/**
 * Class Table_Of_Contents.
 */
class Table_Of_Contents {
	use Singleton;

	/**
	 * Meta key holding parsed contents for ToC.
	 */
	public const META_KEY = '_pmc_digital_daily_toc';

	/**
	 * Table_of_Contents constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action(
			'save_post_' . POST_TYPE,
			[ $this, 'generate' ],
			10,
			2
		);
	}

	/**
	 * Generate Table of Contents when post is updated.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function generate( int $post_id, WP_Post $post ): void {
		$post_data = (
			new Table_of_Contents\Parser( $post->post_content )
		)->get();

		if ( empty( $post_data ) ) {
			delete_post_meta( $post->ID, static::META_KEY );
		} else {
			update_post_meta( $post->ID, static::META_KEY, $post_data );
		}
	}

	/**
	 * Retrieve a post's Table of Contents.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	public function get( int $post_id ): ?array {
		$meta = get_post_meta( $post_id, static::META_KEY, true );

		if ( ! is_array( $meta ) ) {
			return null;
		}

		$meta = array_filter( $meta, [ $this, '_filter_unavailable_item' ] );

		return $this->_process_digital_daily_post_layout_data( $meta );
	}

	/**
	 * Remove unpublished items if user cannot preview them.
	 *
	 * @param array $item Table of Contents entry.
	 * @return bool
	 */
	protected function _filter_unavailable_item( array $item ): bool {
		if ( empty( $item['ID'] ) ) {
			return false;
		}

		return apply_filters(
			'pmc_digital_daily_table_of_contents_can_include_item',
			Post::is_accessible_by_current_user( $item['ID'] ),
			$item
		);
	}

	/**
	 * Process the post data into a usable array for menus and sidebars.
	 *
	 * @param array $post_data Post data to process.
	 * @return array|null
	 * */
	private function _process_digital_daily_post_layout_data(
		array $post_data
	): ?array {
		if ( empty( $post_data ) ) {
			return null;
		}

		$header_items = [];
		$top_stories  = array_slice( $post_data, 0, 3 );
		$post_data    = array_slice( $post_data, 3 );

		$primary_taxonomy = apply_filters(
			'pmc_digital_daily_toc_taxonomy',
			'category'
		);

		$header_items['top-stories'] = [
			'parent_category' => (object) [
				'name' => __( 'Top Stories', 'pmc-digital-daily' ),
			],
			'children_items'  => [],
		];

		foreach ( $top_stories as $datum ) {
			$header_items['top-stories']['children_items'][] =
				$this->_populate_child_item( $datum );
		}

		foreach ( $post_data as $datum ) {
			$category = PMC_Primary_Taxonomy::get_instance()
				->get_primary_taxonomy(
					$datum['ID'],
					$primary_taxonomy
				);

			if ( ! $category instanceof WP_Term ) {
				continue;
			}

			if ( ! isset( $header_items[ $category->slug ] ) ) {
				$header_items[ $category->slug ] = [
					'parent_category' => $category,
					'children_items'  => [],
				];
			}

			$header_items[ $category->slug ]['children_items'][] =
				$this->_populate_child_item( $datum );
		}

		return empty( $header_items ) ? null : $header_items;
	}

	/**
	 * Populate a menu item with required post data.
	 *
	 * @param array $post_data Post data.
	 * @return array
	 */
	protected function _populate_child_item( array $post_data ): array {
		$child_item = [];

		$child_item['title']   = $post_data['title']
			?? get_the_title( $post_data['ID'] );
		$child_item['excerpt'] = $post_data['excerpt']
			?? get_the_excerpt( $post_data['ID'] );

		$child_item['featured_image'] = get_the_post_thumbnail_url(
			$post_data['ID']
		);

		Full_View::add_permalink_filters();
		$child_item['permalink'] = get_permalink( $post_data['ID'] );
		Full_View::remove_permalink_filters();

		$child_item['analytics_permalink'] = get_permalink( $post_data['ID'] );

		return $child_item;
	}
}
