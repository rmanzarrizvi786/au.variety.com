<?php
/**
 * Class to add settings for Branded Landing Pages
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-09-30
 */

namespace PMC\Top_Videos_V2\Landing_Pages;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;
use \PMC\Global_Functions\Utility\Number;
use \PMC_Carousel;
use \PMC_Cache;
use \PMC\Top_Videos_V2\PMC_Top_Videos;
use \Fieldmanager_Group;
use \Fieldmanager_Checkbox;
use \Fieldmanager_TextField;
use \Fieldmanager_Select;
use \ErrorException;

class Branded_Page {

	use Singleton;

	const ID = 'pmc-top-videos-v2-lp-branded';

	const CACHE_LIFE = 300;  // 5 minutes

	const MIN_FIELDS = 0;    // Min number of fields allowed of any type
	const MAX_FIELDS = 5;    // Max number of fields allowed of any type

	/**
	 * @var array Post types on which this UI is enabled.
	 */
	protected $_post_types = [ 'page' ];

	protected $_fields         = [];
	protected $_default_fields = [
		'carousel' => 2,
		'playlist' => 1,
	];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/**
		 * Actions
		 */
		add_action( 'fm_post_page', [ $this, 'add_metabox' ] );

	}

	/**
	 * Method used via array_map() on fields array to enforce max number of fields that can be added for any type
	 *
	 * @param int $field_count
	 *
	 * @return int
	 */
	public function enforce_fields_limit( $field_count = 0 ) : int {

		$field_count = intval( $field_count );
		$field_count = ( self::MAX_FIELDS < $field_count ) ? self::MAX_FIELDS : $field_count;
		$field_count = ( self::MIN_FIELDS > $field_count ) ? self::MIN_FIELDS : $field_count;

		return $field_count;

	}

	/**
	 * Method to set the number of fields to add to admin UI
	 *
	 * @return bool
	 *
	 * @throws \ErrorException
	 */
	protected function _set_fields() : bool {

		if ( ! empty( $this->_fields ) ) {
			return false;
		}

		$fields = apply_filters( 'pmc_top_videos_branded_landing_page_fields', $this->_default_fields );

		if ( ! is_array( $fields ) ) {
			throw new ErrorException(
				sprintf(
					'\'%s\' expects an array value',
					'pmc_top_videos_branded_landing_page_fields'
				)
			);
		}

		$fields        = array_map( [ $this, 'enforce_fields_limit' ], (array) $fields );
		$this->_fields = \PMC::parse_allowed_args( $fields, $this->_default_fields );

		return true;

	}

	/**
	 * Method to add metabox with configuration options for Branded Video Landing Page
	 *
	 * @return object
	 *
	 * @throws \ErrorException
	 * @throws \FM_Developer_Exception
	 */
	public function add_metabox() : object {

		$this->_set_fields();    // Make sure $this->_fields is not empty
		$number = Number::get_instance();

		$fm_group = [
			'name'     => self::ID,
			'children' => [
				'enable_landing_page'      => new Fieldmanager_Checkbox(
					[
						'label'           => __( 'Enable Branded Video Landing Page', 'pmc-top-videos-v2' ),
						'description'     => __( 'This option needs to be enabled in addition to selecting appropriate template to show correct data for a Branded Video Landing Page.', 'pmc-top-videos-v2' ),
						'checked_value'   => 'yes',
						'unchecked_value' => 'no',
					]
				),
				'top_banner_image_url'     => new Fieldmanager_TextField( __( 'Banner Image URL', 'pmc-top-videos-v2' ) ),
				'top_banner_mob_image_url' => new Fieldmanager_TextField( __( 'Banner Image URL (Mobile)', 'pmc-top-videos-v2' ) ),
				'top_banner_link_url'      => new Fieldmanager_TextField( __( 'Banner Link URL', 'pmc-top-videos-v2' ) ),
				'js_tag_title'             => new Fieldmanager_TextField( __( 'JS Embed Title', 'pmc-top-videos-v2' ) ),
				'js_tag_url'               => new Fieldmanager_TextField( __( 'JS Embed Tag URL', 'pmc-top-videos-v2' ) ),
			],
		];

		if ( ! empty( $this->_fields['carousel'] ) ) {

			$carousels = [];

			for ( $i = 1; $i <= $this->_fields['carousel']; $i++ ) {

				$ordinal_number = $number->get_ordinal( $i );
				$ordinal_label  = $number->get_ordinal_as_label( $i );

				$carousel_key         = sprintf( '%s_carousel', $ordinal_number );
				$carousel_label       = sprintf( 'Select %s Carousel', $ordinal_label );
				$carousel_title_key   = sprintf( '%s_carousel_title', $ordinal_number );
				$carousel_title_label = sprintf( '%s Carousel Title', $ordinal_label );

				$carousels[ $carousel_title_key ] = new Fieldmanager_TextField( __( $carousel_title_label, 'pmc-top-videos-v2' ) );    //phpcs:ignore
				$carousels[ $carousel_key ]       = new Fieldmanager_Select(
					[
						'label'   => __( $carousel_label, 'pmc-top-videos-v2' ),    //phpcs:ignore
						'options' => $this->_get_terms_dropdown_list( PMC_Carousel::modules_taxonomy_name ),
					]
				);

				unset( $carousel_title_label, $carousel_title_key, $carousel_label, $carousel_key );
				unset( $ordinal_label, $ordinal_number );

			}

			$fm_group['children'] = array_merge( (array) $fm_group['children'], (array) $carousels );

		}

		if ( ! empty( $this->_fields['playlist'] ) ) {

			$playlists = [];

			for ( $i = 1; $i <= $this->_fields['playlist']; $i++ ) {

				$ordinal_number = $number->get_ordinal( $i );
				$ordinal_label  = $number->get_ordinal_as_label( $i );

				$playlist_key         = sprintf( '%s_playlist', $ordinal_number );
				$playlist_label       = sprintf( 'Select %s Playlist', $ordinal_label );
				$playlist_title_key   = sprintf( '%s_playlist_title', $ordinal_number );
				$playlist_title_label = sprintf( '%s Playlist Title', $ordinal_label );

				$playlists[ $playlist_title_key ] = new Fieldmanager_TextField( __( $playlist_title_label, 'pmc-top-videos-v2' ) );    //phpcs:ignore
				$playlists[ $playlist_key ]       = new Fieldmanager_Select(
					[
						'label'   => __( $playlist_label, 'pmc-top-videos-v2' ),    //phpcs:ignore
						'options' => $this->_get_terms_dropdown_list( 'vcategory', true ),
					]
				);

				unset( $playlist_title_label, $playlist_title_key, $playlist_label, $playlist_key );
				unset( $ordinal_label, $ordinal_number );

			}

			$fm_group['children'] = array_merge( (array) $fm_group['children'], (array) $playlists );

		}

		$fm = new Fieldmanager_Group( $fm_group );

		return $fm->add_meta_box(
			__( 'Branded Video Landing Page Settings', 'pmc-top-videos-v2' ),
			$this->_post_types
		);

	}

	/**
	 * Method to get an array of Terms from a taxonomy to display dropdown in admin UI
	 *
	 * @param string $taxonomy
	 * @param bool   $hide_empty
	 *
	 * @return array
	 */
	protected function _get_terms_dropdown_list( string $taxonomy, bool $hide_empty = false ) : array {

		$data = [
			'' => __( 'Select one', 'pmc-top-videos-v2' ),
		];

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => $hide_empty,
			]
		);

		if ( is_array( $terms ) ) {

			foreach ( $terms as $term ) {
				$data[ $term->slug ] = $term->name;
			}

		}

		return $data;

	}

	/**
	 * Method to get Branded Video Landing Page configuration options for a post
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	protected function _get_settings_raw( int $post_id = 0 ) : array {

		$meta = get_post_meta( $post_id, self::ID, true );
		$meta = ( empty( $meta ) || ! is_array( $meta ) ) ? [] : $meta;

		return $meta;

	}

	/**
	 * Method to get Branded Video Landing Page configuration options for a post.
	 * This will return an empty array if this is not enabled for the post.
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_settings( int $post_id = 0 ) : array {

		$meta = [];

		if ( $this->is_enabled_for_post( $post_id ) ) {
			$meta = $this->_get_settings_raw( $post_id );
		}

		return $meta;

	}

	/**
	 * Method to check if Branded Video Landing Page feature is enabled for a post or not
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_enabled_for_post( int $post_id = 0 ) : bool {

		$meta = $this->_get_settings_raw( $post_id );

		if ( ! empty( $meta ) && isset( $meta['enable_landing_page'] ) ) {

			return ( 'yes' === strtolower( $meta['enable_landing_page'] ) );

		}

		return false;

	}

	/**
	 * Magic function to get posts from a carousel or playlist.
	 * Eg.
	 * - get_first_carousel( 123 ) would call _get_carousel( 'first', 123 )
	 * - get_second_playlist( 123, 10 ) would call _get_playlist( 'second', 123, 10 )
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @return array
	 */
	public function __call( string $method, array $args ) : array {

		$data = [];

		if ( empty( $method ) || 2 !== substr_count( $method, '_' ) ) {
			return $data;
		}

		$method_parts = explode( '_', $method );
		$method_name  = sprintf( '_%s_%s', $method_parts[0], $method_parts[2] );

		if ( ! method_exists( $this, $method_name ) ) {
			return $data;
		}

		$parameters = [ $method_parts[1] ];
		$parameters = array_merge( (array) $parameters, (array) $args );

		$data = call_user_func_array(
			[ $this, $method_name ],
			$parameters
		);

		return $data;

	}

	/**
	 * Method to get carousel data
	 *
	 * @param string $name
	 * @param int    $post_id
	 * @param int    $count
	 *
	 * @return array
	 */
	protected function _get_carousel( string $name = 'first', int $post_id = 0, int $count = 10 ) : array {

		$name = sanitize_title( $name );

		$cache = new PMC_Cache(
			sprintf( '%s-carousel-%s-%d-%d', __CLASS__, $name, $post_id, $count )
		);

		$data = $cache->expires_in( self::CACHE_LIFE )
					->updates_with( [ $this, 'get_carousel_uncached' ], [ $name, $post_id, $count ] )
					->get();

		$data = ( empty( $data ) ) ? [] : $data;

		return $data;

	}

	/**
	 * Method to get playlist data
	 *
	 * @param string $name
	 * @param int    $post_id
	 * @param int    $count
	 *
	 * @return array
	 */
	protected function _get_playlist( string $name = 'first', int $post_id = 0, int $count = 10 ) : array {

		$name = sanitize_title( $name );

		$cache = new PMC_Cache(
			sprintf( '%s-playlist-%s-%d-%d', __CLASS__, $name, $post_id, $count )
		);

		$data = $cache->expires_in( self::CACHE_LIFE )
					->updates_with( [ $this, 'get_playlist_uncached' ], [ $name, $post_id, $count ] )
					->get();

		$data = ( empty( $data ) ) ? [] : $data;

		return $data;

	}

	/**
	 * Method to get carousel data
	 * This method returns uncached data, do not use it directly.
	 *
	 * @param string $name
	 * @param int    $post_id
	 * @param int    $count
	 *
	 * @return array
	 */
	public function get_carousel_uncached( string $name = 'first', int $post_id = 0, int $count = 10 ) : array {

		$data = [];
		$name = sanitize_title( $name );
		$meta = $this->get_settings( $post_id );

		if ( empty( $name ) || empty( $meta ) ) {
			return $data;
		}

		$title     = sprintf( '%s_carousel_title', $name );
		$term_slug = sprintf( '%s_carousel', $name );

		$title     = ( ! empty( $meta[ $title ] ) ) ? $meta[ $title ] : '';
		$term_slug = ( ! empty( $meta[ $term_slug ] ) ) ? trim( $meta[ $term_slug ] ) : '';

		if ( empty( $term_slug ) ) {
			return $data;
		}

		$posts = pmc_render_carousel(
			PMC_Carousel::modules_taxonomy_name,
			$term_slug,
			$count,
			'',
			[
				'add_filler' => false,
			]
		);

		if ( empty( $posts ) ) {
			return $data;
		}

		$term_posts = [];

		foreach ( $posts as $post ) {

			if ( ! empty( $post['ID'] ) && 0 < intval( $post['ID'] ) ) {

				// Since the Carousel doesn't provide us a \WP_Post object, we will create one
				$term_post = get_post( $post['ID'] );

				if ( ! empty( $term_post ) ) {

					$term_post->curation_id         = ( 0 < intval( $post['parent_ID'] ) ) ? intval( $post['parent_ID'] ) : 0;
					$term_post->custom_title        = ( ! empty( trim( $post['title'] ) ) ) ? $post['title'] : '';
					$term_post->custom_excerpt      = ( ! empty( trim( $post['excerpt'] ) ) ) ? $post['excerpt'] : '';
					$term_post->custom_thumbnail_id = ( has_post_thumbnail( $post['parent_ID'] ) ) ? intval( $post['parent_ID'] ) : 0;

					$term_posts[] = $term_post;

				}

				unset( $term_post );

			} else {

				$term_post = get_post( $post['parent_ID'] );

				if ( ! empty( $term_post ) ) {

					$term_post->url = ( ! empty( $post['url'] ) ) ? $post['url'] : '';
					$term_posts[]   = $term_post;

				}

				unset( $term_post );

			}

		}

		$data = [
			'title' => $title,
			'posts' => $term_posts,
		];

		return $data;

	}

	/**
	 * Method to get playlist data
	 * This method returns uncached data, do not use it directly.
	 *
	 * @param string $name
	 * @param int    $post_id
	 * @param int    $count
	 *
	 * @return array
	 */
	public function get_playlist_uncached( string $name = 'first', int $post_id = 0, int $count = 10 ) : array {

		$data = [];
		$name = sanitize_title( $name );
		$meta = $this->get_settings( $post_id );

		if ( empty( $name ) || empty( $meta ) ) {
			return $data;
		}

		$title     = sprintf( '%s_playlist_title', $name );
		$term_slug = sprintf( '%s_playlist', $name );

		$title     = ( ! empty( $meta[ $title ] ) ) ? $meta[ $title ] : '';
		$term_slug = ( ! empty( $meta[ $term_slug ] ) ) ? trim( $meta[ $term_slug ] ) : '';

		if ( empty( $term_slug ) ) {
			return $data;
		}

		$posts_args = [
			'posts_per_page'   => $count,
			'post_type'        => PMC_Top_Videos::POST_TYPE_NAME,
			'suppress_filters' => false,
			'tax_query'        => [ // phpcs:ignore
				[
					'taxonomy' => 'vcategory',
					'field'    => 'slug',
					'terms'    => $term_slug,
				],
			],
		];

		$posts = get_posts( $posts_args );    // phpcs:ignore

		if ( empty( $posts ) ) {
			return $data;
		}

		$data = [
			'title' => $title,
			'posts' => $posts,
		];

		return $data;

	}

	/**
	 * Method to render the image banner on frontend
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function render_banner( int $post_id = 0 ) : void {

		$meta = $this->get_settings( $post_id );

		if ( empty( $meta ) ) {
			return;
		}

		$img_url  = ( ! empty( $meta['top_banner_image_url'] ) ) ? $meta['top_banner_image_url'] : '';
		$img_url  = ( PMC::is_mobile() && ! empty( $meta['top_banner_mob_image_url'] ) ) ? $meta['top_banner_mob_image_url'] : $img_url;
		$link_url = ( ! empty( $meta['top_banner_link_url'] ) ) ? $meta['top_banner_link_url'] : '';

		$html = '';

		if ( ! empty( $link_url ) && ! empty( $img_url ) ) {
			$html = '<a href="%2$s"><img src="%1$s"></a>';
		} elseif ( ! empty( $img_url ) ) {
			$html = '<img src="%1$s">';
		}

		if ( empty( $html ) ) {
			return;
		}

		$html = sprintf(
			$html,
			esc_url( $img_url ),
			esc_url( $link_url )
		);

		echo wp_kses_post( $html );

	}

}    //end of class

//EOF
