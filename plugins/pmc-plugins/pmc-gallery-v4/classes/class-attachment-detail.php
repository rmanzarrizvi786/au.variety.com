<?php
namespace PMC\Gallery;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class is used to register custom post type `pmc-attachments`.
 * Which is used to save custom data of attachment.
 * Like variant of attachment
 *
 * @codeCoverageIgnore
 */
class Attachment_Detail {

	use Singleton;

	/**
	 * Slug of post type.
	 */
	const NAME = 'pmc-attachments';

	/**
	 * @deprecated
	 */
	const name = self::NAME; // @codingStandardsIgnoreLine - backward compatibility.

	/**
	 * Array will contain list tag that allow in html.
	 *
	 * @var array Allow tag list.
	 */
	protected $allowed_tags;

	/**
	 * __construct function of class.
	 */
	protected function __construct() {

		$this->allowed_tags = array(
			'strong' => array(),
			'em'     => array(),
			'h3'     => array(),
			'span'   => array(
				'style' => array(),
			),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
			),
		);
	}

	/**
	 * Initialize function.
	 */
	protected function _init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Function is used to register post `pmc-attachments`.
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => _x( 'Gallery Attachment', 'post type general name', 'pmc-gallery-v4' ),
			'singular_name'      => _x( 'Gallery Attachment', 'post type singular name', 'pmc-gallery-v4' ),
			'add_new'            => _x( 'Add New', 'Gallery Attachment', 'pmc-gallery-v4' ),
			'add_new_item'       => __( 'Add New Gallery Attachment', 'pmc-gallery-v4' ),
			'edit_item'          => __( 'Edit Gallery Attachment', 'pmc-gallery-v4' ),
			'new_item'           => __( 'New Gallery Attachment', 'pmc-gallery-v4' ),
			'all_items'          => __( 'All Gallery Attachments', 'pmc-gallery-v4' ),
			'view_item'          => __( 'View Gallery Attachment', 'pmc-gallery-v4' ),
			'search_items'       => __( 'Search Gallery Attachment', 'pmc-gallery-v4' ),
			'not_found'          => __( 'No Gallery Attachment found', 'pmc-gallery-v4' ),
			'not_found_in_trash' => __( 'No Gallery Attachment found in the Trash', 'pmc-gallery-v4' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Gallery Attachment', 'pmc-gallery-v4' ),
		);

		$args = array(
			'labels'        => $labels,
			'description'   => __( 'Holds gallery specific attachment\'s data', 'pmc-gallery-v4' ),
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor' ),
			'public'        => false,
			'show_ui'       => false,
		);

		register_post_type( self::NAME, $args );
	}

	/**
	 * Function is used to get Attachment custom data got gallery,
	 * Which is stored in private post.
	 *
	 * @param int $variant_id Private post id.
	 *
	 * @return array|boolean Attachment custom data stored in private post.
	 */
	public function get_variant_meta( $variant_id ) {
		$response = array();

		if ( get_post_type( $variant_id ) !== self::NAME ) {
			return false;
		}

		$variant_meta  = get_post_meta( $variant_id );
		$allow_html_in = array(
			'caption',
		);

		$keys = array(
			'title',
			'description',
			'caption',
			'alt',
			'image_credit',
			'image_source_url',
			'gallery_id',
		);

		if ( 'yes' === cheezcap_get_option( 'pmc_gallery_enable_pinterest_description' ) ) {
			$keys[] = 'pinterest_description';
		}

		foreach ( $keys as $key ) {
			if ( ! empty( $variant_meta[ $key ][0] ) ) {
				$response[ $key ] = $variant_meta[ $key ][0];
			} else {
				$response[ $key ] = '';
			}
			if ( in_array( $key, (array) $allow_html_in, true ) ) {
				$response[ $key ] = wp_kses( $response[ $key ], $this->allowed_tags );
			} else {
				$response[ $key ] = sanitize_text_field( $response[ $key ] );
			}
		}

		return $response;
	}

	/**
	 * Create private post to store attachment custom data for gallery,
	 * If post is already available then it will update.
	 *
	 * @param int         $gallery_id Gallery id for which attachment's data are storing.
	 * @param array       $data       Attachment's custom data.
	 * @param int|boolean $variant_id Optional. Private post id if it is available.
	 *
	 * @return boolean|int false on fail. Post id on success.
	 */
	public function add_attachment_variant( $gallery_id, $data = array(), $variant_id = false ) {
		if ( ! ( isset( $gallery_id ) && is_numeric( $gallery_id ) ) ) {
			return false;
		}

		if ( $variant_id && is_numeric( $variant_id ) && get_post_type( $variant_id ) === self::NAME ) {
			return $this->update_attachment_variant( $variant_id, $data );
		}

		$attachment_id = absint( $data['id'] );
		$allow_html_in = array(
			'caption',
		);

		$allow_keys = array(
			'title',
			'description',
			'caption',
			'alt',
			'image_credit',
			'image_source_url',
		);

		if ( 'yes' === cheezcap_get_option( 'pmc_gallery_enable_pinterest_description' ) ) {
			$allow_keys[] = 'pinterest_description';
		}

		$meta_input = array();

		foreach ( $data as $key => $value ) {
			// If value is empty than do not create meta field.
			if ( ! empty( $value ) && in_array( $key, (array) $allow_keys, true ) ) {
				if ( in_array( $key, (array) $allow_html_in, true ) ) {
					$meta_input[ $key ] = wp_kses( $value, $this->allowed_tags );
				} else {
					$meta_input[ $key ] = sanitize_text_field( $value );
				}
			}
		}

		$meta_input['gallery_id'] = absint( $gallery_id );
		$keywords                 = $this->get_unique_word( $data );
		$attachment_url           = wp_get_attachment_url( $attachment_id );

		if ( $attachment_url ) {
			$keywords[] = $attachment_url;
		}

		$post_array = array(
			'post_title'   => sanitize_text_field( $data['id'] . '-' . $gallery_id ),
			'post_content' => sanitize_text_field( implode( ' ', $keywords ) ),
			'post_type'    => self::NAME,
			'post_parent'  => absint( $data['id'] ), // Set attachment id as post_parent. it will be helpful in getting no of gallery where attachment id used.
			'post_status'  => 'publish',
			'meta_input'   => $meta_input,
		);

		$variant_id = wp_insert_post( $post_array, true );

		if ( is_wp_error( $variant_id ) ) {
			return false;
		}

		return $variant_id;
	}

	/**
	 * Update attachment's data with provided, In private post.
	 *
	 * @param int   $variant_id custom post id.
	 * @param array $data       Attachment's custom data.
	 *
	 * @return boolean|int false on fail. Post id on success.
	 */
	public function update_attachment_variant( $variant_id, $data = array() ) {
		if ( ! ( isset( $variant_id ) && is_numeric( $variant_id ) ) ) {
			return false;
		}

		$allow_html_in = array(
			'caption',
		);

		$meta_data = $this->get_variant_meta( $variant_id );

		if ( ! empty( $meta_data ) ) {
			foreach ( $meta_data as $key => $value ) {
				if ( isset( $data[ $key ] ) ) {
					if ( in_array( $key, (array) $allow_html_in, true ) ) {
						$meta_data[ $key ] = wp_kses( $data[ $key ], $this->allowed_tags );
					} else {
						$meta_data[ $key ] = sanitize_text_field( $data[ $key ] );
					}
				}

				// Delete meta field if it is empty, or it will empty.
				if ( empty( $meta_data[ $key ] ) ) {
					delete_post_meta( $variant_id, $key );
					unset( $meta_data[ $key ] );
				}
			}
		}

		$keywords = $this->get_unique_word( $meta_data );

		$post_array = array(
			'ID'           => absint( $variant_id ),
			'post_content' => sanitize_text_field( implode( ' ', $keywords ) ),
			'meta_input'   => $meta_data,
		);

		$variant_id = wp_update_post( $post_array, true );

		if ( is_wp_error( $variant_id ) ) {
			return false;
		}

		return $variant_id;
	}

	/**
	 * Function use to fetch data from array and create list of unique words.
	 *
	 * @param array $data data to fetch word.
	 *
	 * @return array return array with unique word.
	 */
	public function get_unique_word( $data = array() ) {
		$keywords = array();

		$common_word_list = array(
			'he',
			'she',
			'they',
			'there',
			'their',
			'we',
			'it',
			'am',
			'is',
			'are',
			'can',
			'do',
			'does',
			'will',
			'was',
			'were',
			'like',
			'an',
			'not',
			'the',
			'of',
			'to',
			'and',
			'or',
			'so',
		);

		$allow_keys_for_keyword = array(
			'title',
			'description',
			'caption',
			'alt',
			'image_credit',
			'image_source_url',
		);

		foreach ( $allow_keys_for_keyword as $key ) {
			if ( empty( $data[ $key ] ) ) {
				continue;
			}

			$word     = wp_strip_all_tags( $data[ $key ] );
			$word     = trim( strtolower( $word ) );
			$word     = explode( ' ', $word );
			$keywords = array_merge( $keywords, $word );
		}

		$keywords = array_unique( (array) $keywords );

		foreach ( $keywords as $key => $word ) {
			if ( strlen( $word ) <= 1 ) {
				unset( $keywords[ $key ] );
			}

			if ( in_array( $word, (array) $common_word_list, true ) ) {
				unset( $keywords[ $key ] );
			}
		}

		return $keywords;
	}

}
