<?php

namespace PMC\Listicle_Slideshow;

use PMC\Global_Functions\Traits\Singleton;
use Fieldmanager_Group;
use Fieldmanager_Textfield;
use Fieldmanager_RichTextArea;
use Fieldmanager_Media;
use Fieldmanager_Autocomplete;
use Fieldmanager_Datasource_Term;
use Fieldmanager_Checkbox;

/**
 * This class handles the Listicle Slideshow setup
 *
 */
class Listicle_Slideshow {

	use Singleton;

	const POST_TYPE = 'pmc-list-slideshow';  // note: post type should be between 1 and 20 characters
	const SLIDESHOW_SLUG_NAME = 'slideshow';
	const SLIDESHOW_ARCHIVE_SLUG_NAME = 'slideshows';
	const GALLERY_SLUG_NAME = 'gallery';

	/**
	 * Overrides the parent _init() method.
	 *
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'action_init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'action_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ] );
		add_action( 'fm_post_' . self::POST_TYPE, [ $this, 'action_fm_post' ] );
		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 1, 3 );
		add_filter( 'pmc_listicle_slideshow_slug', [ $this, 'filter_pmc_listicle_slideshow_slug' ] );
		add_filter( 'pmc_core_editorial_tax_object_types', [ $this, 'filter_pmc_core_editorial_tax_object_types' ] );
		add_filter( 'pmc_ga_event_tracking', [ $this, 'filter_pmc_ga_event_tracking' ] );
	}

	public function action_init() {

		$this->register_custom_post_type();
		$this->add_custom_rewrite_tag();
		$this->add_custom_rewrite_rule();
	}

	/**
	 * Adds the slidehow page scripts
	 */
	public function action_enqueue_scripts() {

		if ( Listicle_Slideshow::POST_TYPE == get_post_type() ) {
			wp_enqueue_style( 'listicle-slideshow-css', plugin_dir_url( __FILE__ ) . '../assets/css/listicle-slideshow.css' );
		}
	}

	/**
	 * Adds the admin page scripts
	 */
	public function action_admin_enqueue_scripts() {

		if ( Listicle_Slideshow::POST_TYPE == get_post_type() ) {
			wp_enqueue_style( 'listicle-slideshow-admin-css', plugin_dir_url( __FILE__ ) . '../assets/css/listicle-slideshow-admin.css' );
			wp_enqueue_script( 'listicle-slideshow-admin-js', plugin_dir_url( __FILE__ ) . '../assets/js/listicle-slideshow-admin.js', [], false, true );
		}
	}

	/**
	 * Registers the custom Listicle Slideshow post type
	 *
	 */
	protected function register_custom_post_type() {

		register_post_type( self::POST_TYPE, [
			'labels'             => [
				'name'               => __( 'Slideshows' ),
				'singular_name'      => __( 'Slideshow' ),
				'add_new'            => __( 'Add New' ),
				'add_new_item'       => __( 'Add New Slideshow' ),
				'edit'               => __( 'Edit' ),
				'edit_item'          => __( 'Edit Slideshow' ),
				'new_item'           => __( 'New Slideshow' ),
				'view'               => __( 'View' ),
				'view_item'          => __( 'View Slideshow' ),
				'search_items'       => __( 'Search Slideshows' ),
				'not_found'          => __( 'No Slideshows Found' ),
				'not_found_in_trash' => __( 'No Slideshows Found in Trash' ),
			],
			'public'             => true,
			'menu_position'      => 5.1,
			'supports'           => [
				'title',
				'editor',
				'author',
				'excerpt',
				'comments',
				'thumbnail',
				'custom-fields',
				'trackbacks',
				'revisions',
			],
			'taxonomies'         => [
				'category',
				'post_tag',
				'editorial'
			],
			'menu_icon'          => plugins_url( '../images/icon.png', __FILE__ ),
			'has_archive'        => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'rewrite'            => [
				'slug'       => apply_filters( 'pmc_listicle_slideshow_slug', self::SLIDESHOW_SLUG_NAME ),
				'with_front' => false,
			],
		] );
	}

	/**
	 * Prepends a category placeholder to our custom post's slug.
	 *
	 * We do this in a filter, as opposed to doing it directly in the custom post definition, because the % in the
	 * placeholder causes a text artifact in the admin UI's Permalink field while in draft mode.
	 *
	 * @return string
	 */
	public function filter_pmc_listicle_slideshow_slug() {
		return '%category%/' . self::SLIDESHOW_SLUG_NAME;
	}

	/**
	 * Replaces the post link's cateory placeholder with the actual category selected by the user
	 *
	 * @param $post_link
	 *
	 * @return mixed
	 */
	public function filter_post_type_link( $post_link ) {

		if ( self::POST_TYPE == get_post_type() ) {
			$categories    = get_the_category();
			$category_name = empty( $categories ) ? 'uncategorized' : strtolower( $categories[0]->name );
			$category_name = str_replace( ' ', '-', $category_name );
			$post_link     = str_replace( '%category%', $category_name, $post_link );
		}

		return $post_link;
	}

	/**
	 * Defines a rewite tag for the gallery slug
	 */
	public function add_custom_rewrite_tag() {

		add_rewrite_tag( '%' . self::GALLERY_SLUG_NAME . '%', '([^&]+)' );
	}

	/**
	 * Defines a rewite rule for the gallery slug
	 */
	public function add_custom_rewrite_rule() {

		add_rewrite_rule(
			'^(?:[^/]*/)+' . self::SLIDESHOW_SLUG_NAME . '/([^/]*)/([^/]*)/?',
			'index.php?' . self::POST_TYPE . '=$matches[1]&' . self::GALLERY_SLUG_NAME . '=$matches[2]',
			'top'
		);

		add_rewrite_rule(
			'^' . self::SLIDESHOW_ARCHIVE_SLUG_NAME . '/?$',
			'index.php?post_type=' . self::POST_TYPE,
			'top'
		);

		add_rewrite_rule(
			'^' . self::SLIDESHOW_ARCHIVE_SLUG_NAME . '/page/?([0-9]{1,})/?$',
			'index.php?post_type=' . self::POST_TYPE . '&paged=$matches[1]',
			'top'
		);

	}

	/**
	 * Defines admin fields
	 */
	public function action_fm_post() {

		$fm_images = new Fieldmanager_Group( [
			'name'           => 'images',
			'limit'          => 0,
			'minimum_count'  => 1,
			'extra_elements' => 0,
			'label'          => __( 'Image' ),
			'label_macro'    => [
				'Image: %s',
				'image_title'
			],
			'add_more_label' => __( 'Add another image' ),
			'sortable'       => true,
			'collapsible'    => true,
			'children'       => [
				'image'         => new Fieldmanager_Media( [
					'validation_rules' => [
						'required' => true
					],
				] ),
				'image_title'   => new Fieldmanager_Textfield( __( 'Image Title' ) ),
				'image_caption' => new Fieldmanager_RichTextArea( __( 'Image Caption' ) ),
				'image_alt'     => new Fieldmanager_Textfield( __( 'Alt Text' ) ),
				'image_credit'  => new Fieldmanager_Textfield( __( 'Image Credit' ) ),
			],
		] );

		$fm_galleries = new Fieldmanager_Group( [
			'name'           => 'galleries',
			'limit'          => 0,
			'minimum_count'  => 1,
			'extra_elements' => 0,
			'label'          => __( 'Slideshow Gallery' ),
			'label_macro'    => [
				'Gallery: %s',
				'title'
			],
			'add_more_label' => __( 'Add another slideshow gallery' ),
			'sortable'       => true,
			'collapsible'    => true,
			'collapsed'      => true,
			'tabbed'         => false,
			'children'       => [
				'title'   => new Fieldmanager_Textfield( [
					'label'            => __( 'Title' ),
					'validation_rules' => [
						'required' => true,
					]
				] ),
				'slug'    => new Fieldmanager_Textfield( [
					'label'            => __( 'Slug' ),
					'validation_rules' => [
						'required' => true,
					]
				] ),
				'body'    => new Fieldmanager_RichTextArea(),
				'authors' => new Fieldmanager_Autocomplete( [
					'label'              => __( 'Authors' ),
					'limit'              => 0,
					'sortable'           => true,
					'one_label_per_item' => false,
					'extra_elements'     => 0,
					'minimum_count'      => 0,
					'add_more_label'     => __( 'Add author' ),
					'datasource'         => new Fieldmanager_Datasource_Term( [
						'taxonomy'               => 'author',
						'taxonomy_save_to_terms' => false,
					] ),
				] ),
				'images'  => $fm_images,
			],
		] );

		$fm_galleries->add_meta_box( __( 'Slideshow Galleries' ), self::POST_TYPE );

		$fm_legacy = new Fieldmanager_Group( [
			'name'        => 'legacy_fields',
			'limit'       => 1,
			'sortable'    => false,
			'collapsible' => true,
			'children'    => [
				'custom_url_prefix' => new Fieldmanager_Textfield( __( 'Custom URL prefix' ) ),
				'media_bar_image'   => new Fieldmanager_Media( __( 'Media Bar Image' ) ),
				'web_optimized'     => new Fieldmanager_Checkbox( __( 'Web Optimized' ) ),
			],
		] );

		$fm_legacy->add_meta_box( __( 'Legacy Fields' ), self::POST_TYPE );
	}

	/**
	 * Determins whether the slideshow's intro page should be rendered instead of a gallery.
	 *
	 * @return boolean
	 */
	public static function is_intro_page() {

		global $wp_query;
		$gallery_slug = $wp_query->query_vars[ self::GALLERY_SLUG_NAME ];

		return isset( $gallery_slug ) ? false : true;
	}

	/**
	 * Finds the gallery related to the current gallery slug and
	 * returns the data required for rendering it.
	 *
	 * @return array
	 */
	public static function get_gallery_page_data() {

		global $post;
		global $wp_query;
		$gallery_data = [];

		// get all the galleries belonging to this slideshow

		$galleries = get_post_meta( $post->ID, 'galleries', true );

		// get the current gallery slug

		$gallery_slug = $wp_query->query_vars[ self::GALLERY_SLUG_NAME ];

		// match the current gallery slug to a gallery

		$gallery_index = 0;

		foreach ( $galleries as $gallery ) {
			if ( $gallery['slug'] === $gallery_slug ) {
				$gallery_data = self::get_gallery_data( $gallery, $gallery_index, $galleries );
				break;
			}
			$gallery_index ++;
		}

		return $gallery_data;
	}

	/**
	 * Extracts data from a gallery meta field
	 *
	 * @param $gallery
	 * @param $gallery_index
	 * @param $galleries
	 *
	 * @return array
	 */
	protected static function get_gallery_data( $gallery, $gallery_index, $galleries ) {

		global $post;
		$gallery_data = [];

		if ( ! empty( $gallery ) && is_array( $gallery ) ) {

			// get the url for the previous gallery, if exists

			$prev_gallery_url = '';

			if ( $gallery_index > 0 ) {
				$index = $gallery_index - 1;
				if ( isset( $galleries [ $index ] ) ) {
					if ( isset( $galleries [ $index ]['slug'] ) ) {
						$prev_gallery_url = self::get_gallery_permalink( $galleries [ $index ]['slug'] );
					}
				}
			}

			// get the url for the next gallery, if exists

			$next_gallery_url = '';

			if ( $gallery_index < sizeof( $galleries ) - 1 ) {
				$index = $gallery_index + 1;
				if ( isset( $galleries [ $index ] ) ) {
					if ( isset( $galleries [ $index ]['slug'] ) ) {
						$next_gallery_url = self::get_gallery_permalink( $galleries [ $index ]['slug'] );
					}
				}
			}

			// setup the data array

			$gallery_data = [
				'controls'               => true,
				'indicators'             => false,
				'wrap'                   => true,
				'start_index'            => 0,
				'interval'               => 0,
				'pause'                  => 'hover',
				'title'                  => isset( $gallery['title'] ) ? $gallery['title'] : '',
				'alt'                    => isset( $gallery['alt'] ) ? $gallery['alt'] : '',
				'caption'                => isset( $gallery['caption'] ) ? $gallery['caption'] : '',
				'credit'                 => isset( $gallery['credit'] ) ? $gallery['credit'] : '',
				'body'                   => isset( $gallery['body'] ) ? apply_filters( 'the_content', $gallery['body'] ) : '',
				'slides'                 => self::get_gallery_image_data( $gallery ),
				'prev_gallery_url'       => $prev_gallery_url,
				'next_gallery_url'       => $next_gallery_url,
				'current_gallery_number' => $gallery_index + 1,
				'total_galleries'        => sizeof( $galleries ),
				'authors'                => self::get_gallery_authors( $gallery ),
				'post_date'              => get_the_date( '', $post->ID ),
			];

		}

		return $gallery_data;
	}

	/**
	 * Returns the galleries image data: url, title, alt, etc.
	 *
	 * @param $gallery
	 *
	 * @return array
	 */
	protected static function get_gallery_image_data( $gallery ) {

		$images = [];

		foreach ( $gallery['images'] as $key => $value ) {

			$src = wp_get_attachment_image_src( $value['image'], 'full' );

			$images[] = [
				'url'     => $src[0],
				'title'   => $value['image_title'],
				'alt'     => $value['image_alt'],
				'caption' => $value['image_caption'],
				'credit'  => $value['image_credit'],
			];
		}

		return $images;
	}

	/**
	 * Returns a delimited string of author links for the given gallery.
	 *
	 * Note that each gallery can have its own authors independent of the
	 * slideshow's authors. The slideshow authors are handled by 'coauthors'
	 * whereas gallery authors are handled by the 'author' taxonomy because
	 * a gallery is not a "post" by itself, rather it is part of the slideshow post.
	 *
	 * @param $gallery
	 *
	 * @return string
	 */
	protected static function get_gallery_authors( $gallery ) {

		global $coauthors_plus;
		$authors = '';

		if ( isset( $gallery[ 'authors' ] ) ) {

			foreach ( $gallery['authors'] as $key => $value ) {

				$term = get_term( $value, 'author' );

				if ( !empty( $term ) && ! is_wp_error( $term ) ) {

					// use the term name to get the author data

					$author = $coauthors_plus->get_coauthor_by( 'user_login', $term->name );

					if ( false !== $author ) {

						// determine the appropriate delimiter for this author

						$delimiter = '';

						if ( $key > 0 ) {
							if ( $key === sizeof( $gallery[ 'authors' ] ) - 1 ) {
								$delimiter = ' and';
							} else {
								$delimiter = ',';
							}
						}

						// build the author link

						$name = esc_html( $author->display_name );
						$href = esc_url( '/author/' . $author->user_nicename );
						$title = 'Posts by ' . esc_attr( $name );
						$authors .= "$delimiter <a href=\"$href\" title=\"$title\" class=\"author url fn\" rel=\"author\">$name</a>";
					}
				}
			}
		}

		return $authors;
	}

	/**
	 * Returns the data required for rendering the intro page
	 *
	 * @return array
	 */
	public static function get_intro_page_data() {

		global $post;

		$galleries         = get_post_meta( $post->ID, 'galleries', true );
		$first_gallery_url = isset( $galleries[0]['slug'] ) ? self::get_gallery_permalink( $galleries[0]['slug'] ) : '';

		$intro = [
			'post_title'        => get_the_title( $post->ID ),
			'post_excerpt'      => get_the_excerpt( $post->ID ),
			'post_content'      => apply_filters( 'the_content', $post->post_content ),
			'first_gallery_url' => $first_gallery_url,
		];

		return $intro;
	}

	/**
	 * @param string $slug slug of the gallery.
	 *
	 * @return string permalink with the galley slug.
	 */
	public static function get_gallery_permalink( $slug = '' ) {

		global $post;

		if ( empty( $slug ) ) {
			return '';
		}

		if ( 'publish' !== get_post_status( $post->ID ) ) {

			$link = get_post_permalink( $post->ID ) . '&' . self::GALLERY_SLUG_NAME . '=' . $slug;
		} else {

			$link = get_post_permalink( $post->ID ) . $slug;
		}

		return $link;
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

		$types = array();

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
	 * Generate event tracking for the gallery buttons
	 *
	 * @param array $events
	 * @return array
	 */
	public function filter_pmc_ga_event_tracking( $events = [ ] ) {

		if ( is_singular( Listicle_Slideshow::POST_TYPE ) ) {
			return array_merge( [
				[
					'selector' => '.pmc-listicle-slideshow.intro a',
					'category' => 'slideshow',
					'label' => 'start-slideshow',
					'nonInteraction' => true,
				],
			], $events );
		}

		return $events;
	}

}

Listicle_Slideshow::get_instance();
