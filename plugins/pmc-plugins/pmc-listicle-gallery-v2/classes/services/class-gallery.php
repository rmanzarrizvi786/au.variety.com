<?php

namespace PMC\Listicle_Gallery_V2\Services;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use Fieldmanager_Group;
use Fieldmanager_Autocomplete;
use Fieldmanager_Datasource_Post;

/**
 * This class handles the Listicle Gallery post type.
 *
 * A Listical Gallery is a collection of one or more image carousels (a.k.a Listical Gallery Items),
 * plus accompanying rich text content.
 *
 */
class Gallery {

	use Singleton;

	const POST_TYPE = 'pmc-lst-gallery';
	const GALLERY_PATH = 'slideshow';
	const GALLERY_ARCHIVE_PATH = 'slideshows';
	const PUBLISHED = 'publish';
	const UNCATEGORIZED = 'uncategorized';

	/**
	 * Overrides the parent _init() method.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'action_init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'action_enqueue_scripts' ] );
		add_action( 'fm_post_' . self::POST_TYPE, [ $this, 'action_fm_post_listicle_gallery' ] );
		add_action( 'update_postmeta', [ $this, 'action_update_postmeta' ], 10, 4 );
		add_action( 'updated_post_meta', [ $this, 'action_updated_post_meta' ], 10, 4 );
		add_action( 'added_post_meta', [ $this, 'action_added_post_meta' ], 10, 4 );
		add_action( 'transition_post_status', [ $this, 'action_transition_post_status' ], 10, 3 );
		add_action( 'wp_head', [ $this, 'action_wp_head' ] );

		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 0, 2 );
		add_filter( 'pre_post_link', [ $this, 'filter_post_type_link' ], 0, 2 );
		add_filter( 'pmc_core_editorial_tax_object_types', [ $this, 'filter_pmc_core_editorial_tax_object_types' ] );
		add_filter( 'coauthors_supported_post_types', [ $this, 'filter_coauthors_supported_post_types' ] );
		add_filter( 'pmc-sticky-ads-mobile-post-types', [ $this, 'filter_pmc_sticky_ads_mobile_post_types' ] );
		add_filter( 'pmc-sticky-ads-mobile-post-types-onload', [ $this, 'filter_pmc_sticky_ads_mobile_post_types_onload' ] );
		add_filter( 'get_the_archive_title', [ $this, 'filter_get_the_archive_title' ], 10, 1 );
		add_filter( 'preview_post_link', [ $this, 'filter_preview_post_link' ], 10, 2 );
		add_filter( 'pmc_sitemaps_post_type_whitelist', [ $this, 'whitelist_post_type_for_sitemaps' ] );

	}

	public function action_init() {

		$this->_register_gallery_post_type();
		$this->add_custom_rewrite_rule();

	}

	/**
	 * Adds the gallery scripts
	 */
	public function action_enqueue_scripts() {

		if ( self::POST_TYPE === get_post_type() ) {
			$url = apply_filters( 'pmc_listicle_gallery_v2_gallery_css', LISTICLE_GALLERY_V2_ASSETS_URL . '/build/css/gallery.min.css' );
			wp_enqueue_style( 'listicle-gallery-css', $url );
		}

	}

	/**
	 * Registers the custom Listicle Gallery post type
	 *
	 */
	protected function _register_gallery_post_type() {

		register_post_type( self::POST_TYPE, [
			'labels'                => [
				'name'                => __( 'Listicle Galleries', 'pmc-plugins' ),
				'singular_name'       => __( 'Listicle Gallery', 'pmc-plugins' ),
				'add_new'             => __( 'Add New', 'pmc-plugins' ),
				'add_new_item'        => __( 'Add New Listicle Gallery', 'pmc-plugins' ),
				'edit'                => __( 'Edit', 'pmc-plugins' ),
				'edit_item'           => __( 'Edit Listicle Gallery', 'pmc-plugins' ),
				'new_item'            => __( 'New Listicle Gallery', 'pmc-plugins' ),
				'view'                => __( 'View', 'pmc-plugins' ),
				'view_item'           => __( 'View Listicle Gallery', 'pmc-plugins' ),
				'search_items'        => __( 'Search Listicle Galleries', 'pmc-plugins' ),
				'not_found'           => __( 'No Listicle Galleries Found', 'pmc-plugins' ),
				'not_found_in_trash'  => __( 'No Listicle Galleries Found in Trash', 'pmc-plugins' ),
			],
			'public'                => true,
			'menu_position'         => 5.1,
			'supports'              => [
				'title',
				'editor',
				'author',
				'excerpt',
				'comments',
				'thumbnail',
				'custom-fields',
				'revisions',
			],
			'taxonomies'            => [
				'category',
				'post_tag',
				'editorial',
			],
			'menu_icon'             => 'dashicons-format-gallery',
			'has_archive'           => true,
			'query_var'             => true,
			'publicly_queryable'    => true,
			'rewrite'               => [
				'slug'                => '%category%/' . self::GALLERY_PATH,
				'with_front'          => false,
			],
		] );

	}

	/**
	 * Defines additional fields for Listical Gallery.
	 *
	 * Fields:
	 * gallery_items - a collection of Listicle Gallery Item posts that are linked to this gallery
	 *
	 */
	public function action_fm_post_listicle_gallery() {

		$fm_gallery_items = new Fieldmanager_Group( [
			'name'                    => 'gallery_items',
			'limit'                   => 1,
			'children'                => [
				'ids'                 => new Fieldmanager_Autocomplete( [
					'limit'               => 0,
					'sortable'            => true,
					'one_label_per_item'  => false,
					'extra_elements'      => 0,
					'minimum_count'       => 1,
					'add_more_label'      => __( 'Add gallery', 'pmc-plugins' ),
					'show_edit_link'      => true,
					'datasource'          => new Fieldmanager_Datasource_Post( [
						'query_args'        => [
							'post_type'       => Gallery_Item::POST_TYPE,
							'post_status'     => 'any',
						],
					] ),
				] ),
			],
		] );

		$fm_gallery_items->add_meta_box( __( 'Galleries', 'pmc-plugins' ), self::POST_TYPE );

		pmc_core_fields_relationships();

	}

	/**
	 * Defines the rewite rules for the gallery archive page
	 *
	 */
	public function add_custom_rewrite_rule() {

		$regex = sprintf( '^%s/?$', self::GALLERY_ARCHIVE_PATH );
		$query = sprintf( 'index.php?post_type=%s', self::POST_TYPE );
		add_rewrite_rule( $regex, $query, 'top' );

		$regex = sprintf( '^%s/page/?([0-9]{1,})/?$', self::GALLERY_ARCHIVE_PATH );
		$query = sprintf( 'index.php?post_type=%s&paged=$matches[1]', self::POST_TYPE );
		add_rewrite_rule( $regex, $query, 'top' );

	}

	/**
	 * Replaces the url's category placeholder with actual data.
	 *
	 * @param string $url
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function filter_post_type_link( $url, $post ) {

		if ( self::POST_TYPE !== get_post_type( $post ) ) {
			return $url;
		}

		$gallery_url = $this->_get_gallery_url( $post );

		if ( ! empty( $gallery_url ) ) {
			$url = $gallery_url;
		}

		return $url;

	}

	/**
	 * Returns the data required for rendering the current gallery.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	public function get_data( $post = null ) {

		if ( empty( $post ) ) {
			$post = get_post();
		}

		// get the gallery items attached to this gallery

		$gallery_items = get_post_meta( $post->ID, 'gallery_items', true );

		// get the url of the first gallery item

		$first_gallery_item_url = '';

		if ( ! empty( $gallery_items['ids'] ) ) {
			$first_gallery_item = get_post( $gallery_items['ids'][0] );
			if ( ! empty( $first_gallery_item ) ) {
				$gallery_url = $this->_get_gallery_url( $post );
				$path = wp_parse_url( $gallery_url, PHP_URL_PATH );
				$first_gallery_item_url = trailingslashit( $path ) . $first_gallery_item->post_name;
				$first_gallery_item_url = trailingslashit( $first_gallery_item_url );
				$first_gallery_item_url = ( is_preview() ) ? add_query_arg( array( 'preview_id' => $post->ID ), $first_gallery_item_url ) : $first_gallery_item_url;
			}
		}

		// get the author links

		$author_links = '';

		if ( function_exists( 'coauthors_posts_links' ) ) {

			$authors = coauthors_posts_links( null, null, null, null, false );

			if ( ! empty( wp_strip_all_tags( $authors, true ) ) ) {
				$author_links = sprintf( __( 'By %1$s on %2$s', 'pmc-plugins' ), $authors, get_the_date() );
			} else {
				$author_links = get_the_date();
			}

		}

		// put it all together

		$data = [
			'title'                   => get_the_title( $post->ID ),
			'excerpt'                 => get_the_excerpt( $post->ID ),
			'content'                 => apply_filters( 'the_content', $post->post_content ),
			'date'                    => get_the_date( '', $post ),
			'first_gallery_item_url'  => $first_gallery_item_url,
			'authors'                 => $author_links,
			'template_path'           => LISTICLE_GALLERY_V2_ROOT_DIR . '/templates/gallery.php',
			'tags'                    => $this->_get_tags( $post ),
		];

		return $data;

	}

	/*
	 * Filter to add this post type to Editorial Taxonomy Object Types.
	 *
	 * @since 2017-06-01
	 * @version 2017-06-01 Archana Mandhare PMCBA-586
	 *
	 * @return array
	 *
	 */
	public function filter_pmc_core_editorial_tax_object_types( $post_types ) {

		$types = [];

		if ( ! empty( $post_types ) ) {
			if ( ! is_array( $post_types ) ) {
				$types[] = $post_types;
			} else {
				$types = $post_types;
			}
		}

		$types[] = self::POST_TYPE;

		return $types;

	}

	/**
	 * Adds this post type to the list of supported Co-Authors Plus post types.
	 *
	 * @param array $post_types Array of post types currently supported by Co-Authors Plus
	 * @return array
	 */
	public function filter_coauthors_supported_post_types( $post_types ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		$post_types[] = self::POST_TYPE;
		return $post_types;

	}

	/**
	 * Adds this post type to the list of types on which sticky ads should
	 * be displayed on mobile devices.
	 *
	 * @param array $post_types Array of post types currently showing sticky ads
	 * @return array
	 */
	public function filter_pmc_sticky_ads_mobile_post_types( $post_types = [] ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		$post_types[] = self::POST_TYPE;
		return $post_types;

	}

	/**
	 * Adds this post type to the list of types on which sticky ads should
	 * be displayed on load, rather than appearing after leaderboard
	 * has scrolled out of screen.
	 *
	 * @param array $post_types Array of post types currently showing sticky ads on load
	 * @return array
	 */
	public function filter_pmc_sticky_ads_mobile_post_types_onload( $post_types = [] ) {

		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		$post_types[] = self::POST_TYPE;
		return $post_types;

	}

	/**
	 * This triggers before the post meta data are updated and determines if this post should be
	 * detached from the child gallery items that it is currently attached to.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_update_postmeta( $meta_id, $post_id, $meta_key, $meta_value ) {

		if ( self::POST_TYPE !== get_post_type() ) {
			return;
		}

		if ( 'gallery_items' !== $meta_key ) {
			return;
		}

		$old_meta_value = get_post_meta( $post_id, 'gallery_items', true );

		if ( ! empty( $old_meta_value ) ) {
			$this->_detach_gallery_items_from_this_post( $post_id, $old_meta_value['ids'] );
		}

	}

	/**
	 * This triggers after the post meta data are updated and determines if this post should be
	 * attached to new child gallery items.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_updated_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

		if ( self::POST_TYPE !== get_post_type() ) {
			return;
		}

		if ( 'gallery_items' !== $meta_key ) {
			return;
		}

		if ( ! empty( $meta_value ) ) {
			$this->_attach_gallery_items_to_this_post( $post_id, $meta_value['ids'] );
		}

	}

	/**
	 * This triggers after the post meta data are added and determines if this post should be
	 * attached to new child gallery items.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_added_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

		$this->action_updated_post_meta( $meta_id, $post_id, $meta_key, $meta_value );

	}

	/**
	 * This triggers before the post meta data are deleted and determines if this post should be
	 * detached from the child gallery items that it is currently attached to.
	 *
	 * @param $post_id
	 */
	public function action_before_delete_post( $post_id ) {

		if ( self::POST_TYPE !== get_post_type() ) {
			return;
		}

		$meta_value = get_post_meta( $post_id, 'gallery_items', true );

		if ( ! empty( $meta_value ) ) {
			$this->_detach_gallery_items_from_this_post( $post_id, $meta_value['ids'] );
		}

	}

	/**
	 * This sets the 'parent gallery' meta data of one or more child gallery items.
	 *
	 * @param int $post_id
	 * @param array $gallery_item_ids
	 */
	protected function _attach_gallery_items_to_this_post( $post_id, $gallery_item_ids ) {

		if ( ! empty( $gallery_item_ids ) ) {

			foreach ( $gallery_item_ids as $gallery_item_id ) {

				$meta = get_post_meta( $gallery_item_id, 'parent_gallery', true );
				$id = ( ! empty( $meta['id'] ) ) ? $meta['id'] : null;
				$parent_galleries = ( ! empty( $meta['parent_galleries'] ) ) ? $meta['parent_galleries'] : [];

				if ( ! empty( $id ) ) {
					$parent_galleries[] = [ 'id' => $post_id ];
				} else {
					$id = $post_id;
				}

				$new_meta['id'] = $id;
				$new_meta['parent_galleries'] = $parent_galleries;

				update_post_meta( $gallery_item_id, 'parent_gallery', $new_meta );

			}

		}

	}

	/**
	 * This deletes the 'parent gallery' meta data of one or more child gallery items.
	 *
	 * @param int $post_id
	 * @param array $gallery_item_ids
	 */
	protected function _detach_gallery_items_from_this_post( $post_id, $gallery_item_ids ) {

		if ( ! empty( $gallery_item_ids ) ) {

			foreach ( $gallery_item_ids as $gallery_item_id ) {

				$meta = get_post_meta( $gallery_item_id, 'parent_gallery', true );
				$id = ( ! empty( $meta['id'] ) ) ? $meta['id'] : null;
				$parent_galleries = ( ! empty( $meta['parent_galleries'] ) ) ? $meta['parent_galleries'] : [];
				$key_to_remove = null;

				if ( $id === $post_id ) {
					$id = null;
				} else {
					if ( ! empty( $parent_galleries ) ) {
						foreach ($parent_galleries as $key => $parent_gallery) {
							if ( $parent_gallery['id'] === $post_id ) {
								$key_to_remove = $key;
								break;
							}
						}
					}
				}

				if ( ! empty( $key_to_remove ) ) {
					unset( $parent_galleries[ $key_to_remove ] );
				}

				$new_meta['id'] = $id;
				$new_meta['parent_galleries'] = $parent_galleries;

				update_post_meta( $gallery_item_id, 'parent_gallery', $new_meta );

			}

		}

	}

	/**
	 * Changes the title of the archive page.
	 *
	 * @param string $title
	 * @return string|\Underscore\Underscore|void
	 */
	public function filter_get_the_archive_title( $title ) {

		if ( self::POST_TYPE !== get_post_type() ) {
			return $title;
		}

		return __( 'Slideshows', 'pmc-plugins' );

	}

	/**
	 * Publishes any unpublished child gallery items when this post is published.
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function action_transition_post_status( $new_status, $old_status, $post ) {

		if ( self::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( self::PUBLISHED !== $new_status || self::PUBLISHED === $old_status ) {
			return;
		}

		// get the gallery items attached to this gallery and publish them if needed

		$gallery_items = get_post_meta( $post->ID, 'gallery_items', true );

		if ( ! empty( $gallery_items ) && ! empty( $gallery_items['ids'] ) ) {
			foreach ( $gallery_items['ids'] as $key => $id ) {
				if ( self::PUBLISHED !== get_post_status( $id ) ) {
					wp_update_post( [
							'ID'            => $id,
							'post_status'   => self::PUBLISHED,
							'post_date'     => date( 'Y-m-d H:i:s' ),
							'post_date_gmt' => gmdate( 'Y-m-d H:i:s' ),
						]
					);
				}
			}
		}

	}

	/**
	 * Returns a delimited string of one or more categories for the given post.
	 *
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function _get_category( $post ) {

		// get the category terms attached to this post

		$terms = get_the_terms( $post, 'category' );

		// if this post has no category...

		if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {

			$default_category_id = get_option( 'default_category' );

			if ( ! empty( $default_category_id ) ) {

				$default_category = get_category( $default_category_id );

				if ( ! empty( $default_category->slug ) ) {
					return $default_category->slug;
				}

			}

			return self::UNCATEGORIZED;

		}

		// if we have a subcategory (i.e. a category term with a parent), then include
		// both category and subcategory slugs in the url

		foreach ( $terms as $term ) {
			if ( ! empty( $term->parent ) ) {
				$parent = get_term( $term->parent, 'category' );
				if ( ! empty( $parent->slug ) && ! empty( $term->slug ) ) {
					return $parent->slug . '/' . $term->slug;
				}
			}
		}

		// we haven't found a subcategory, so just use the category

		if ( $terms[0]->slug ) {
			return $terms[0]->slug;
		}

	}

	/**
	 * Builds the URL for the gallery.
	 *
	 * @param \WP_Post $post
	 * @return bool|string
	 */
	public function _get_gallery_url( $post ) {

		// get the slug

		if ( ! empty( $post->post_name ) ) {
			$slug = $post->post_name;
		} else {
			$slug = sanitize_title_with_dashes( $post->post_title );
		}

		// get the category terms attached to this post

		$category = $this->_get_category( $post );

		// create the url string

		$link = sprintf( '%1$s/%2$s/%3$s/%4$s', untrailingslashit( home_url() ), $category, self::GALLERY_PATH, $slug );

		return $link;

	}

	/**
	 * Changes the url of the Preview button so we can preview the gallery before it is saved.
	 * This is required to counter the effect of the 'post_type_link' filter on the preview button.
	 *
	 * @param string $preview_link
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_preview_post_link( $preview_link, $post ) {

		if ( self::POST_TYPE !== $post->post_type ) {
			return $preview_link;
		}

		$link = sprintf( '%1$s?p=%2$d&post_type=%3$s&preview_id=%2$d', untrailingslashit( home_url() ), absint( $post->ID ), self::POST_TYPE );

		return $link;

	}

	/**
	 * Gets the tags for this post.
	 *
	 * @param \WP_Post $post
	 * @return array|bool
	 */
	protected function _get_tags( $post ) {

		$terms = get_the_terms( $post->ID, 'post_tag' );

		if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
			return false;
		}

		$tags = [];

		foreach ( $terms as $term ) {

			$term_link = get_term_link( $term->term_id );

			if ( ! is_wp_error( $term_link ) ) {
				$tags[] = [
					'link' => $term_link,
					'name' => $term->name,
				];
			}

		}

		return $tags;

	}

	/**
	 * adds rel=next meta tag for pmc-lst-gallery post in <head> section
	 *
	 * @return bool
	 */
	public function action_wp_head() {

		// Process only if current page is for pmc-lst-gallery post type
		if ( ! is_singular( self::POST_TYPE ) ) {
			return false;
		}

		$post = get_post();

		if ( empty( $post ) ) {
			return false;
		}

		// get the gallery items attached to this gallery
		$gallery_items = get_post_meta( $post->ID, 'gallery_items', true );

		// get the url of the first gallery item
		$first_gallery_item_url = '';

		if ( ! empty( $gallery_items['ids'] ) ) {
			$first_gallery_item = get_post( $gallery_items['ids'][0] );
			if ( ! empty( $first_gallery_item ) ) {
				$gallery_url            = $this->_get_gallery_url( $post );
				$first_gallery_item_url = trailingslashit( $gallery_url ) . $first_gallery_item->post_name;
				$first_gallery_item_url = trailingslashit( $first_gallery_item_url );
			}
		}

		if ( ! empty( $first_gallery_item_url ) ) {
			echo sprintf( '<link rel="next" href="%s" >', esc_url( $first_gallery_item_url ) );
		}
	}

	/**
	 * Whitelist post type for sitemap.
	 *
	 * @param  array $post_types List of post type for site map.
	 *
	 * @return array List of post type for site map.
	 */
	public function whitelist_post_type_for_sitemaps( $post_types ) {

		$post_types = ( ! empty( $post_types ) && is_array( $post_types ) ) ? $post_types : [];

		if ( ! in_array( self::POST_TYPE, (array) $post_types, true ) ) {
			$post_types[] = self::POST_TYPE;
		}

		return $post_types;
	}

}
