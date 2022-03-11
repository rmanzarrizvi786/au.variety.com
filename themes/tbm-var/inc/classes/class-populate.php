<?php
/**
 * Populates theme data.
 *
 * @package pmc-variety
 */

namespace Variety\Inc;

/**
 * Class Populate
 */
class Populate {

	/**
	 * Holds Post data.
	 *
	 * @var array|\WP_Post|null
	 */
	private $_post;

	/**
	 * The original data template to be populated.
	 *
	 * @var array
	 */
	private $_data;

	private $_options;

	/**
	 * @codeCoverageIgnore
	 *
	 * Populate constructor.
	 *
	 * @param        $_post
	 * @param array  $data_template
	 * @param string $image_size
	 */
	public function __construct( $_post, $data_template = [], $options = [] ) {

		if ( is_int( $_post ) ) {
			$_post = get_post( $_post );
		}

		$this->_options = wp_parse_args(
			$options,
			[
				'image_size'           => 'landscape-large',
				'image_srcset_enabled' => true,
			]
		);

		$this->_post = $_post;
		$this->_data = $data_template;

		$this->tease_url();
		$this->timestamp();
		$this->title();
		$this->dek();
		$this->lazy_image();
		$this->primary_vertical();
	}

	/**
	 * Return the populated data template.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	public function get() {
		return $this->_data;
	}

	/**
	 * Format the title.
	 *
	 * @codeCoverageIgnore
	 */
	private function title() {
		if ( ! empty( $this->_data['c_title'] ) ) {
			$c_title                 = [];
			$c_title['c_title_text'] = \PMC::truncate( variety_get_card_title( $this->_post ), 150 );
			$c_title['c_title_url']  = get_permalink( $this->_post );
			$this->store( $c_title, 'c_title' );
		}
	}

	/**
	 * Format the dek.
	 *
	 * @codeCoverageIgnore
	 */
	private function dek() {

		if ( ! empty( $this->_data['c_dek'] ) ) {
			$c_dek = [];

			if ( ! empty( $this->_post->custom_excerpt ) ) {
				$c_dek_text = wp_strip_all_tags( $this->_post->custom_excerpt );
			} else {
				$c_dek_text = \PMC\Core\Inc\Helper::get_the_excerpt( $this->_post->ID );
			}

			$c_dek['c_dek_text'] = \PMC::truncate( $c_dek_text, 200 );

			$this->store( $c_dek, 'c_dek' );
		}
	}

	/**
	 * Format the tease URL.
	 *
	 * @codeCoverageIgnore
	 */
	private function tease_url() {
		if ( ! empty( $this->_data['o_tease_url'] ) ) {
			$this->store( get_permalink( $this->_post ), 'o_tease_url', true );
		}
	}

	/**
	 * Format the timestamp.
	 *
	 * @codeCoverageIgnore
	 */
	private function timestamp() {
		if ( ! empty( $this->_data['c_timestamp'] ) ) {
			$c_timestamp                     = [];
			$c_timestamp['c_timestamp_text'] = variety_human_time_diff( $this->_post->ID );
			$this->store( $c_timestamp, 'c_timestamp' );
		}
	}

	/**
	 * Format the lazy image.
	 *
	 * @codeCoverageIgnore
	 */
	private function lazy_image() {
		if ( ! empty( $this->_data['c_lazy_image'] ) ) {
			$c_lazy_image = [];

			if ( ! empty( $this->_post->image_id ) ) {
				$thumbnail = $this->_post->image_id;
			} else {
				$thumbnail = get_post_thumbnail_id( $this->_post );
			}

			$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, $this->_options['image_size'] );

			if ( ! empty( $thumbnail ) && ! empty( $image ) ) {
				$c_lazy_image['c_lazy_image_link_url']        = get_permalink( $this->_post );
				$c_lazy_image['c_lazy_image_alt_attr']        = $image['image_alt'];
				$c_lazy_image['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();

				if ( true === $this->_options['image_srcset_enabled'] ) {
					$c_lazy_image['c_lazy_image_srcset_attr'] = \wp_get_attachment_image_srcset( $thumbnail );
					$c_lazy_image['c_lazy_image_sizes_attr']  = \wp_get_attachment_image_sizes( $thumbnail );
				} else {
					$c_lazy_image['c_lazy_image_srcset_attr'] = false;
					$c_lazy_image['c_lazy_image_sizes_attr']  = false;
				}

				$c_lazy_image['c_lazy_image_src_url']        = $image['src'];
				$c_lazy_image['c_figcaption_caption_markup'] = $image['image_caption'];
				$c_lazy_image['c_figcaption_credit_text']    = $image['image_credit'];
			}

			$this->store( $c_lazy_image, 'c_lazy_image' );
		}
	}

	/**
	 * Format the primary vertical.
	 *
	 * @codeCoverageIgnore
	 */
	private function primary_vertical() {
		if ( ! empty( $this->_data['c_span'] ) ) {
			$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $this->_post->ID, 'vertical' );

			if ( ! empty( $vertical ) ) {
				$c_span['c_span_text'] = $vertical->name;
				$c_span['c_span_url']  = get_term_link( $vertical );

				$this->store( $c_span, 'c_span' );
			}
		}
	}

	/**
	 * Store data to the template once formatted.
	 *
	 * @codeCoverageIgnore
	 */
	private function store( $data, $key, $single = false ) {
		if ( $single ) {
			$this->_data[ $key ] = $data;
		} else {
			$this->_data[ $key ] = array_merge( $this->_data[ $key ], $data );
		}
	}

}
