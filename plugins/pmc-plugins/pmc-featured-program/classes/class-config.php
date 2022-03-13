<?php
/**
 * Featured program Config class.
 *
 * @package pmc-featured-program
 *
 */

namespace PMC\Featured_Program;

use \PMC\Global_Functions\Traits\Singleton;

class Config {

	use Singleton;

	protected $_applied        = false;
	protected $_prefix         = 'pmc';
	protected $_post_type      = 'featured-program';
	protected $_post_slug      = 'special-series';
	protected $_category_group = 'featured-program-group';
	protected $_tag_group      = 'tag-group';
	
	/**
	 * Initialize the values
	 * 
	 * @return void
	 */
	protected function _apply_filters() {

		if ( $this->_applied ) {
			return;
		}

		$this->_applied        = true;
		$this->_prefix         = apply_filters( 'pmc_fp_prefix', $this->_prefix );
		$this->_post_type      = apply_filters( 'pmc_fp_post_type', sprintf( '%s-%s', self::prefix(), $this->_post_type ) );
		$this->_post_slug      = apply_filters( 'pmc_fp_post_slug', $this->_post_slug );
		$this->_category_group = apply_filters( 'pmc_fp_category_group', sprintf( '%s-%s', self::prefix(), $this->_category_group ) );
		$this->_tag_group      = apply_filters( 'pmc_fp_tag_group', sprintf( '%s-%s', self::prefix(), $this->_tag_group ) );

	}

	/**
	 * Get the prefix to be used for slugs and meta keys throughout the plugin.
	 * 
	 * @return string
	 */
	public function prefix() {
		$this->_apply_filters();
		return $this->_prefix;
	}
	
	/**
	 * Get the post type for the featured-programs post.
	 * 
	 * @return string
	 */
	public function post_type() {
		$this->_apply_filters();
		return $this->_post_type;
	}
	
	
	/**
	 * Get the URL slug for the featured-programs post type.
	 * 
	 * @return string
	 */
	public function post_slug() {
		$this->_apply_filters();
		return $this->_post_slug;
	}
	
	/**
	 * Get the category group taxonomy slug for the featured-programs post type.
	 * 
	 * @return string
	 */
	public function category_group() {
		$this->_apply_filters();
		return $this->_category_group;
	}
	
	
	/**
	 * Get the tag group taxonomy slug for the featured-programs post type.
	 * 
	 * @return string
	 */
	public function tag_group() {
		$this->_apply_filters();
		return $this->_tag_group;
	}

}
