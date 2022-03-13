<?php
/**
 * Register AJAX actions for plugin
 */

class PMC_Print_View_AJAX {

	/**
	 * Setup actions and filters. This is a singleton.
	 *
	 * @uses add_action
	 */
	public function __construct() {
		add_action( 'wp_ajax_toggle_print_article', array( $this, 'action_toggle_print_article' ) );
	}

	/**
	 * Get commenter info for an array of emails
	 *
	 * @uses check_ajax_referer, current_user_can, wp_set_post_terms, wp_get_post_terms, add_post_meta
	 * @return void
	 */
	public function action_toggle_print_article() {
		$output = array();
		$output['success'] = false;

		if ( check_ajax_referer( 'toggle_print_article_nonce', 'nonce', false ) && isset( $_POST['new_state'] )  && ! empty( $_POST['post_id'] ) && current_user_can( 'edit_post', $_POST['post_id'] ) ) {
		
			$post_id = (double) $_POST['post_id'];

			$output['success'] = true;

			if ( $_POST['new_state'] ) {
				wp_set_post_terms( $post_id, '_print_post', 'pmc_print_article' );
			} else {
				$issues = wp_get_post_terms( $post_id, 'print-issues' );
				$section = wp_get_post_terms( $post_id, 'pmc_print_section' );
				if ( is_array( $issues ) || is_array( $section ) ) {
					add_post_meta( $post_id, '_print-issues_removal_blocked', true );
				} else {
					wp_set_post_terms( $post_id, '', 'pmc_print_article' );
				}
			}
		}

		echo json_encode( $output );
		
		die();
	}

}

new PMC_Print_View_AJAX;