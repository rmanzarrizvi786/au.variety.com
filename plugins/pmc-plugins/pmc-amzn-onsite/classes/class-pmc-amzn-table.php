<?php

namespace PMC\Amzn_Onsite;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;

/**
 * WP List Table class for PMC Amazon Onsite
 *
 * @since 2019-07-11 - Keanan Koppenhaver
 */

class Table extends \WP_Posts_List_Table {

	use Singleton;

	/**
	 * Set up the basics of the post list table
	 *
	 * @since 2019-07-11
	 *
	 * @return void
	 */
	function __construct() {
		global $avail_post_stati;

		parent::__construct(
			[
				'plural' => 'posts',
				'ajax'   => false,
			]
		);

		$this->screen->post_type = 'post';

		$avail_post_stati = wp_edit_posts_query();
	}

	/**
	 * Makes sure the correct postdata is passed to the table
	 *
	 * @since 2019-07-11
	 *
	 * @return void
	 */
	public function prepare_items() {
		global $post_type_object, $avail_post_stati, $wp_query, $per_page, $mode;

		$avail_post_stati = wp_edit_posts_query();

		$total_items = $wp_query->found_posts;

		$this->_column_headers = [
			[
				'title'      => 'Title',
				'author'     => 'Author',
				'categories' => 'Categories',
				'post_tags'  => 'Tags',
				'verticals'  => 'Verticals',
				'date'       => 'Date',
				'amzn_date'  => 'Amazon Date',
			],
			[],
			$this->get_sortable_columns(),
		];

		$post_type = 'post';
		$per_page  = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );
		$per_page  = apply_filters( 'edit_posts_per_page', $per_page, $post_type );

		$total_pages = $wp_query->max_num_pages;

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * Sets which columns should be sortable
	 *
	 * @since 2019-07-11
	 *
	 * @return array $sortable_columns The list of columns that will be sortable
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'title'     => 'title',
			'date'      => [ 'date', true ],
			'amzn_date' => [ 'amzn_date', true ],
		];

		return $sortable_columns;
	}

	/**
	 * Returns data for populating some of our columns
	 *
	 * @since 2019-07-11
	 *
	 * @return string $data Either the data to populate the column or empty string
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'amzn_date':
				$amazon_date = get_post_meta( $item->ID, 'amazon_date', true );

				if ( $amazon_date && 0 !== $amazon_date ) {
					return date( 'Y/m/d @ g:iA', $amazon_date );
				}

				return '';
			case 'categories':
				$term_list = get_the_term_list( $item->ID, 'category', '', ', ', '' );

				if ( ! $term_list ) {
					return '—';
				}

				return $term_list;
			case 'verticals':
				$term_list = get_the_term_list( $item->ID, 'vertical', '', ', ', '' );

				if ( empty( $term_list ) || is_wp_error( $term_list ) ) {
					return '—';
				}

				return $term_list;
			case 'post_tags':
				$term_list = get_the_term_list( $item->ID, 'post_tag', '', ', ', '' );

				if ( ! $term_list ) {
					return '—';
				}

				return $term_list;
			default:
				return '';
		}
	}

	/**
	 * Returns data for populating some of our columns
	 *
	 * @since 2019-07-11
	 *
	 * @return array $views List of filters to display above the post table
	 */
	public function get_views() {
		$views   = [];
		$current = \PMC::filter_input( INPUT_GET, 'amazon_onsite', FILTER_SANITIZE_STRING );

		if ( ! $current || '' === $current ) {
			$current = 'all';
		}

		//All link
		$class        = ( 'all' === $current ? ' class="current"' : '' );
		$all_url      = remove_query_arg( [ 'amazon_onsite', 'orderby', 'order' ] );
		$views['all'] = "<a href='{$all_url }' {$class} >All</a>";

		//Onsite link
		$amzn_onsite_url = add_query_arg( 'amazon_onsite', 'true' );
		$class           = ( 'true' === $current ? ' class="current"' : '' );
		$views['onsite'] = "<a href='{$amzn_onsite_url}' {$class} >Onsite Articles</a>";

		//Non-onsite link
		$amzn_onsite_url     = add_query_arg( 'amazon_onsite', 'false' );
		$class               = ( 'false' === $current ? ' class="current"' : '' );
		$views['not_onsite'] = "<a href='{$amzn_onsite_url}' {$class} >Non-Onsite Articles</a>";

		return $views;
	}
}
