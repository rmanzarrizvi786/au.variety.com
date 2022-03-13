<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\List_Object class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

use PMC\Gallery\Lists;
use PMC\Gallery\Lists_Settings;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use WP_Post;

/**
 * List object.
 */
class List_Object {

	/**
	 * List object.
	 *
	 * @var WP_Post
	 */
	protected $article_post;

	/**
	 * List items count.
	 *
	 * @var int
	 */
	protected $list_items_count;

	/**
	 * Page
	 * @var string
	 */
	protected $page;

	/**
	 * @var mixed
	 */
	protected $per_page;

	/**
	 * List_Object constructor.
	 *
	 * @param WP_Post $article_post List post object.
	 */
	public function __construct( WP_Post $article_post ) {
		$this->article_post = $article_post;
	}

	/**
	 * Get list card.
	 *
	 * @param WP_Post $item  List item post object.
	 * @param int     $index List index.
	 *
	 * @return array
	 */
	public function get_list_card( $item, $index ): array {

		$list_order = get_post_meta( $this->article_post->ID, Lists_Settings::NUMBERING_OPT_KEY, true );

		$position = ( $index + 1 ) + ( ( $this->page - 1 ) * $this->per_page );

		if ( 'desc' === $list_order ) {
			$position = ( $this->list_items_count - $position ) + 1;
		}

		$image_id = empty( $item->custom_thumbnail_id ) ? get_post_thumbnail_id( $item->ID ) : $item->custom_thumbnail_id;

		$video_url = get_post_meta( $item->ID, '_pmc_featured_video_override_data', true );

		if ( empty( $video_url ) ) {
			$video_url = get_post_meta( $item->ID, 'pmc_top_video_source', true );
		}

		$featured_video = ( new Video_Object( $item ) )->get_video_output( $video_url );

		$subtitle = get_post_meta( $item->ID, 'pmc_list_item_subtitle', true );

		return [
			'ID'              => $item->ID,
			'position'        => $index,
			'positionDisplay' => $position,
			'date'            => $item->post_date,
			'title'           => html_entity_decode( $item->post_title ),
			'subtitle'        => html_entity_decode( $subtitle ),
			'slug'            => $item->slug,
			'caption'         => Lists::get_list_item_image_caption( $item ),
			'description'     => apply_filters( 'the_content', $item->post_content ),
			'image'           => Image::get_image( $image_id ),
			'featured-video'  => $featured_video,
		];
	}

	/**
	 * Get list items.
	 *
	 * @param array $params Request params.
	 *
	 * @return array
	 */
	public function items( $params ): array {

		// Get list ids with PMC global class.
		if ( ! class_exists( 'PMC' ) ) {
			return [];
		}

		$params = wp_parse_args(
			[
				'page'     => $params['page'],
				'per_page' => $params['per_page'],
			],
			[
				'page'     => '',
				'per_page' => 50,
			]
		);

		$list_items = Lists::get_instance()->get_all_list_items( $this->article_post->ID );

		$list_relation = get_term_by( 'slug', $this->article_post->ID, 'pmc_list_relation' );

		if ( ! empty( $list_relation->count ) ) {
			$this->list_items_count = $list_relation->count;
		}

		$this->page     = $params['page'];
		$this->per_page = $params['per_page'];

		return array_map(
			[ $this, 'get_list_card' ],
			(array) $list_items,
			array_keys( (array) $list_items )
		);
	}
}
