<?php
/**
 * Class Featured Article
 *
 * Handlers for the Featured Article post option.
 * Copied from pmc-artnews-2019.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Post_Options\Api as Post_Options_Api;

class Featured_Article {

	use Singleton;

	/**
	 * Featured article options.
	 */
	const OPTION_NAME                = 'variety-featured-article';
	const VERTICAL_IMAGE_OPTION_NAME = 'variety-featured-article-vertical-image';

	/**
	 * Whether or not the current post is a featured article.
	 *
	 * @var null|boolean
	 */
	private $_is_featured_article;

	/**
	 * Whether or not the current featured article contains a vertical image.
	 *
	 * @var null|boolean
	 */
	private $_is_vertical_image;

	/**
	 * Returns whether the current post is a featured article.
	 *
	 * @return boolean True if the current post is a featured article.
	 */
	public function is_featured_article( \WP_Post $post_obj = null ) {

		if ( ! is_single() ) {
			return false;
		}

		if ( empty( $post_obj ) ) {
			$post_obj = get_queried_object();
		}

		if ( ! is_bool( $this->_is_featured_article ) ) {
			Post_Options_Api::get_instance()->post( $post_obj );

			$this->_is_featured_article = has_term( self::OPTION_NAME, '_post-options', $post_obj );

		}

		return $this->_is_featured_article;
	}

	/**
	 * Returns whether the current featured article post
	 * should contain a vertical image.
	 *
	 * @return boolean True if the current post should have a vertical image.
	 */
	public function is_vertical_image( \WP_Post $post_obj = null ) {

		if ( ! is_single() ) {
			return false;
		}

		if ( empty( $post_obj ) ) {
			$post_obj = get_queried_object();
		}

		Post_Options_Api::get_instance()->post( $post_obj );

		$this->_is_vertical_image = has_term( self::VERTICAL_IMAGE_OPTION_NAME, '_post-options', $post_obj );

		return $this->_is_vertical_image;
	}

}
