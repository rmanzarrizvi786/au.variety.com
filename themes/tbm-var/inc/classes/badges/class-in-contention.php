<?php
/**
 * Handler for Category In-Contention Badge.
 *
 * CDWE-490 Need "in Contention" style added to article pages in said category.
 *
 * @author Vishal Kakadiya
 *
 * @since 2017-09-01
 *
 * @package pmc-variety-2017
 */

namespace Variety\Inc\Badges;

class In_Contention extends Badge {

	/**
	 * Taxonomy slug for badge term.
	 *
	 * @var string
	 */
	const TAXONOMY_SLUG = 'category';

	/**
	 * Slug of Term that is used as badge.
	 *
	 * @var string
	 */
	const TERM_SLUG = 'in-contention';

	/**
	 * Slug of Term that is used as badge.
	 *
	 * @var string
	 */
	const TERM_NAME = 'In Contention';

	/**
	 * Constructor.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @since BR-184
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		parent::__construct();
		$this->_setup_hooks();
	}

	/**
	 * To register Action and Filter Hooks.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @since BR-184
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		/**
		 * Filters.
		 */
		add_filter( 'get_category', [ $this, 'get_category_name' ] );
	}

	/**
	 * Replace In Contention category with Features text because of copyright issue.
	 *
	 * @param WP_Term $term term object.
	 *
	 * @return WP_Term
	 */
	public function get_category_name( $term ) {

		if ( is_a( $term, 'WP_Term' ) && self::TERM_SLUG === $term->slug ) {
			$term->name = 'In The Running';
		}

		return $term;
	}

}
