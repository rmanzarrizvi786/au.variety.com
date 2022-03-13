<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Feed_Comments {

	use Singleton;

	/**
	 * PMC_Feed_Comments constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks
	 */
	protected function _setup_hooks() {
		add_filter( 'comment_post_redirect', [ $this, 'redirect_feed_comment_post' ] );
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'pmc_google_analytics_account_dev', [ $this, 'remove_analytics' ] );
	}

	public function add_rewrite_rules() {
		add_rewrite_tag( '%fc_pid%', '([0-9]+)' );
		add_rewrite_rule( '^comments_app/([0-9]+)?$', 'index.php?pagename=comments_app&fc_pid=$matches[1]', 'top' );
		add_rewrite_rule( '^comments_app/([0-9]+)/comment-page-([0-9]+)?$', 'index.php?pagename=comments_app&fc_pid=$matches[1]&cpage=$matches[2]', 'top' );
	}

	function remove_analytics( $val ) {

		//Had to use $_SERVER since no variable is present here for GA plugin.
		$query = $_SERVER['REQUEST_URI'];

		if ( 0 === stripos( $query, '/comments_app/' ) ) {
			return 0;
		}

		if ( PMC::is_production() ) {
			return false;
		} else {
			return $val;
		}
	}

	/**
	 * Make sure if the comment form is posted on ios feed, it does not get redirected to post, but should come back to ios feed comment url.
	 *
	 * @param $location
	 *
	 * @return bool|string
	 */
	function redirect_feed_comment_post( $location ) {

		$referer = wp_get_referer();

		$url = parse_url( $referer );

		if ( 0 === stripos( $url['path'], '/comments_app/' ) ) {
			return $referer;
		}

		return $location;
	}

	/**
	 * Function to make sure that the pagination link in the ios comments feed has proper url with post_id and comments page number.
	 *
	 * @param $result
	 *
	 * @return string
	 */
	function feed_comments_paginate_links( $result ) {

		global $pagename;

		$url = home_url() . '/' . $pagename . '/';

		$url_array = explode( 'comment-page-', $result );

		$post_id = intval( get_query_var('fc_pid') );

		if ( empty( $post_id ) ) {
			return $result;
		}

		if ( isset( $url_array[1] ) ) {

			$url_array = explode( '/', $url_array[1] );

			$url = $url. $post_id."/" . 'comment-page-' . $url_array[0];
		}

		return esc_url( $url );
	}

	function render_comments() {

		$post_id = intval( get_query_var('fc_pid') );

		if ( !$post_id ) {
			return;
		}

		$current_post = get_post( $post_id );

		if ( empty( $current_post ) ) {
			return;
		}

		add_filter( 'paginate_links', array( $this, 'feed_comments_paginate_links' ) );

		if ( comments_open( $post_id ) ) {
			comment_form(
				array(
					 'comment_notes_before' => '',
					 'comment_notes_after'  => '',
				), $post_id );
		}
		?>
		<ol class="commentlist">
			<?php
			//Gather comments for a specific page/post
			$comments = get_comments(
				array(
					 'post_id' => $post_id,
					 'status'  => 'approve'
					 //Change this to the type of comments to be displayed
				) );

			//Display the list of comments
			wp_list_comments(
				array(
					 'reverse_top_level' => false
				), $comments );

			if ( $comments ) {
				paginate_comments_links();
			}
			remove_filter( 'paginate_links', array( $this, 'feed_comments_paginate_links' ) );
			?>
		</ol>
	<?php
	}
}

PMC_Feed_Comments::get_instance();
//EOF
