<?php

/*
 * Plugin Name: PMC Google Site Search Widget V2
 * Description:	 Displays the google site search box using v2 version of Google custom site search => https://developers.google.com/custom-search/docs/element#supported_attributes.
 * This is built because for deadline we wanted results to be sorted by date, but using v1 version it always needs to update code on backend. V2 avoids that.
 * PPT-3210 for more details.
 *
 * Comments from google CSE below.
 *
 * After investigation, i observed that you have implemented GSS using V1 "Two Page" layout code on your website http://deadline.com/search/ and customized the code as per your requirement.
 Please note whenever you make any changes in GSS control panel, it is required to take the updated V1 code snippets and implement in your website yo reflect the changes.
 In your case, you haven't implemented the updated V1 code snippets in your website after enabling sorting and due to this reason, you are seeing the issue and not seeing the sorted options in your website.
 In order to resolve the issue, please take the updated V1 code snippets for searchbox and search results and implemented in your website to reflect the changes and then customize as per your requirement. As part of license agreement, we don't provide support services for customization.
 You can also consider implementing GSS V2 code snippets. V2 code is on the fly and whenever you make any changes in GSS control panel configuration, you don't need to reimplement the snippets again . All the changes will be reflected automatically.
 If you still see any issues, please let me know with examples and steps to reproduce the issue.
 *
 *
 *
 * Version: 1.0
 * License: PMC Proprietary.  All rights reserved.
 */


class PMC_Google_Site_Search_Widget_V2 extends WP_Widget {
	protected $_count = 0;

	/*
	 * Defines the widget name
	 */
	function __construct() {
		parent::__construct( false, 'Google Site Search V2 API' );
	}

	function widget( $args, $instance ) {

		$this->_count += 1;

		extract( $args, EXTR_SKIP );
		$instance        = wp_parse_args( $instance, array( 'site_search_key' => '' ) );
		$site_search_key = $instance['site_search_key'];

		?>
		<!-- Google Site Search -->
		<div id="cse">
			<div id="js-sticky-search-trigger" class="sticky-search-trigger"></div>
			<div id="cse-search-form<?php echo (int) $this->_count; ?>" class="cse-search-form"></div>
			<script type="text/javascript">
				var _pmc_google_site_search_id = "<?php echo esc_js( $site_search_key ) ?>";
				var _pmc_google_site_search_url = "<?php echo esc_url( home_url( "results/" ) ) ?>";
			</script>
		</div>
		<!-- End Google Site Search -->
		<?php
		wp_enqueue_script( 'pmc-gss-script-v2', plugins_url( 'assets/js/script-v2.js', __FILE__ ), array( 'jquery' ), false, true );
	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['site_search_key'] = sanitize_text_field( $new_instance['site_search_key'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'site_search_key' => '' ) );

		?>
		<label for="<?php echo $this->get_field_id( 'site_search_key' ) ?>"> Site Search Key
			<input id="<?php echo $this->get_field_id( 'site_search_key' ) ?>" name="<?php echo $this->get_field_name( 'site_search_key' ) ?>" value="<?php echo esc_attr( $instance['site_search_key'] ); ?>" type="text" />
		</label>
	<?php
	}
}

add_action(
	'widgets_init', function () {
		register_widget( 'PMC_Google_Site_Search_Widget_V2' );
	}, 20
);
//EOF