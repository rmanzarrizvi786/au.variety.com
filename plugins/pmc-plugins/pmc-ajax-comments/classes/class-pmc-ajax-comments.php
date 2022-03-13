<?php

namespace PMC\Ajax;

use PMC\Global_Functions\Traits\Singleton;

class Comments {

	use Singleton;

	public function __construct() {

		add_action( 'init', [ $this, 'register_ajax_endpoint' ] );
		add_filter( 'query_vars', [ $this, 'ajax_query_vars' ] );

		if ( ! is_admin() ) {

			add_filter( 'next_comments_link_attributes', [ $this, 'previous_comments_link_attributes' ] );
			add_filter( 'pre_option_default_comments_page', [ $this, 'pre_option_default_comments_page' ] );
			add_filter( 'get_comment_link', [ $this, 'get_comment_link' ], 100, 3 );
			add_action( 'comment_form_top', [ $this, 'comment_form_top' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'template_include', [ $this, 'single_comments_template' ], 11 );
			add_filter( 'comments_template', [ $this, 'comments_template' ], 10 );

		}

	}

	public function register_ajax_endpoint() {

		add_rewrite_endpoint( "pmc-lazy-comments", EP_PERMALINK );
		add_rewrite_rule( '^pmc-lazy-comments/([a-z-]+)/([0-9]+)/?$',
			'index.php?pmc-lazy-comments=$matches[1]&pmc_aj_com_pid=$matches[2]&cpage=1', 'top' );
		add_rewrite_rule( '^pmc-lazy-comments/([a-z-]+)/([0-9]+)/comment-page-([0-9]+)?$',
			'index.php?pmc-lazy-comments=$matches[1]&pmc_aj_com_pid=$matches[2]&cpage=$matches[3]', 'top' );

	}

	public function ajax_query_vars( $vars ) {

		$vars[] = "pmc_aj_com_pid";
		$vars[] = "cpage";

		return $vars;

	}

	public function enqueue_scripts() {

		if ( ! is_single() ) {
			return;
		}

		$js_path = 'assets/build/js/pmc-ajax-comments.min.js';

		if ( ! \PMC::is_production() ) {
			$js_path = 'assets/src/js/pmc-ajax-comments.js';
		}

		wp_enqueue_script( 'pmc-ajax-comments_js', plugins_url( $js_path, __DIR__ ) );
		wp_localize_script( 'pmc-ajax-comments_js', 'pmc_ajax_comments_obj',
			[ 'ajaxurl' => '/pmc-lazy-comments/aj-comments/' ] );

	}

	/**
	 * alters previous comments link to hold information necessary for our ajaxified version
	 */
	public function previous_comments_link_attributes( $attrs ) {

		return $attrs . sprintf( ' id="more-comments" onclick="pmc_ajax_comments.more_comments(%u); return false;" pagenum="0" maxpages="%u" ',
			intval( get_the_ID() ), esc_attr( get_comment_pages_count() ) );
	}

	/**
	 * for our "more comments" feature to work, the first page has to be shown by default, with the oldest comments at
	 * the top of each page
	 */
	public function pre_option_default_comments_page( $value ) {
		return 'oldest';
	}

	/**
	 * due to craziness of paging comments, comment links should always just go to the top of the post comments
	 */
	public function get_comment_link( $link, $comment, $args ) {

		if ( empty( $comment->comment_post_ID ) ) {
			return $link;
		}

		return trailingslashit( get_permalink( $comment->comment_post_ID ) ) . '#comment-list-wrapper';
	}

	/**
	 * Shows more comments.
	 */
	public function single_comments_template( $template ) {

		if ( 'aj-comments' !== get_query_var( "pmc-lazy-comments" ) ) {
			return $template;
		}

		$post_id  = intval( get_query_var( 'pmc_aj_com_pid' ) );
		$page_num = intval( get_query_var( 'cpage' ) );

		if ( empty( $post_id ) || empty( $page_num ) ) {
			return $template;
		}

		if ( ! $post = get_post( $post_id ) ) {
			return $template;
		}

		$per_page = get_option( 'comments_per_page' );

		$comments = get_comments( [
			'order'   => ( ! get_option( 'comment_order' ) ) ? 'asc' : get_option( 'comment_order' ),
			'post_id' => $post_id,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
		] );

		wp_list_comments( [
			'page'     => $page_num,
			'per_page' => $per_page
		], $comments );
		die();
	}

	public function comments_template( $template ) {
		return dirname( __DIR__ ) . '/templates/comments.php';
	}

	/*
	 * Add copy above Comments
	 */
	public function comment_form_top() {
		esc_html_e( 'Comments are monitored, so don’t go off topic', 'pmc-ajax-comments' );
	}

	/**
	 * Get the number of top level comments for a post.
	 *
	 * @param  int $post_id
	 *
	 * @return int
	 */
	public function top_comment_count( $post_id ) {

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return 0;
		}

		add_filter( 'comments_clauses', [ $this, 'where_top_comments_only' ] );

		$comments = get_comments( [
			'post_id' => $post_id
		] );

		remove_filter( 'comments_clauses', [ $this, 'where_top_comments_only' ] );

		if ( ! $comments ) {
			return 0;
		}

		return count( $comments );

	}

	/**
	 * Filter comment query for top level comments.
	 *
	 * @wp-hook comments_clauses
	 *
	 * @param   array $clauses
	 *
	 * @return  array
	 */
	function where_top_comments_only( $clauses ) {

		$clauses['where'] .= ' AND comment_parent = 0';

		return $clauses;
	}

}