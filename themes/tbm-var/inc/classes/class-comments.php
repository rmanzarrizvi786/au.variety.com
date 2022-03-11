<?php
/**
 * Class Comments
 *
 * Handlers for the Comment template.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Comments
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Comments {

	use Singleton;

	/**
	 * The nonce action.
	 */
	const NONCE_ACTION = 'variety_comments_load_more';

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_filter( 'pre_option_default_comments_page', array( $this, 'pre_option_default_comments_page' ) );
		add_action( 'comment_form_after', array( $this, 'comment_form_after_message' ) );
		add_filter( 'comment_form_defaults', [ $this, 'comment_form_defaults' ] );
		add_filter( 'body_class', [ $this, 'hide_comments_jump_link' ] );
		add_filter( 'comments_template_query_args', [ $this, 'filter_comments_template_query_args' ] );
	}

	/**
	 * Filter comments so that pingbacks and trackbacks do not show
	 *
	 * @param $comment_args
	 * @return mixed
	 */
	public function filter_comments_template_query_args( $comment_args ) {
		$comment_args['type__not_in'] = [ 'pingback', 'trackback' ];
		return $comment_args;
	}

	/**
	 * Pre Option Default Comments Page
	 *
	 * Show the first page by default, with the oldest comments
	 * at top of each page.
	 *
	 * @since 2017.1.0
	 * @filter pre_option_default_comments_page
	 */
	public function pre_option_default_comments_page() {
		return 'oldest';
	}

	/**
	 * Comment form Top Message
	 *
	 * Renders a message at the top of the comment form.
	 *
	 * @since 2017.1.0
	 * @action comment_form_after
	 */
	public function comment_form_after_message() {
		echo sprintf( '<span class="comments-are-moderated lrv-u-font-size-12">%s</span>', esc_html__( 'Comments are moderated. They may be edited for clarity and reprinting in whole or in part in Variety publications.', 'pmc-variety' ) );
	}

	/**
	 * Comment form defaults
	 *
	 * Renders comment form defaults such as message underneath comment form and text inside submit button.
	 *
	 * @param array $arg
	 * @return array|mixed
	 * @action comment_form_defaults
	 */
	public function comment_form_defaults( $arg = [] ) {
		$user          = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
		$logged_in     = sprintf(
			'<span class="commenting-as lrv-u-font-size-11">%s</span>',
			esc_html__( 'You are commenting as ', 'pmc-variety' ) . '<b>' . $user_identity . '</b><a href="' .
			esc_url( wp_logout_url( get_permalink() ), 'pmc-variety' ) . '">' .
			esc_html__( ' ( Log Out )', 'pmc-variety' ) . '</a>'
		);

		$logged_out =
			'<span class="email-address-msg lrv-u-font-size-12 lrv-u-font-weight-bold">' . esc_html__( 'Your email address will not be published. Required fields are marked ', 'pmc-variety' ) .
			esc_html__( '&#42;', 'pmc-variety' ) . '</span>';

		$arg['comment_notes_after'] = is_user_logged_in() ? $logged_in : $logged_out;
		$arg['label_submit']        = esc_html( 'Post', 'pmc-variety' );
		$arg['class_submit']        = 'lrv-u-text-transform-uppercase comment-submit-button';

		return $arg;
	}

	/**
	 * Hide jump links when comments are disabled
	 *
	 * Adds a body class for hiding comments jump links when comments are disabled
	 * @param $classes
	 * @return mixed
	 *
	 * @action hide_comments_jump_links
	 */
	public function hide_comments_jump_link( $classes ) {
		if ( ! comments_open() || get_post_type() === 'variety_top_video' || get_post_type() === 'variety_vip_video' ) {
			$classes[] = 'hide-comments-jump-link';
		}
		return $classes;
	}
}
