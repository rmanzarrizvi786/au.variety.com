<?php
/**
 * Plugin Name: PMC Print View
 * Description: Sets up print meta for posts and other content to allow WordPress to be the main content management system for all articles.
 * Author: Luke Woodward, 10up
 * Author URI: http://10up.com
 */

define( 'PMC_PRINT_VIEW_VERSION', '1.0.0' );
define( 'PMC_PRINT_VIEW_DIR', __DIR__ );
define( 'PMC_PRINT_VIEW_URL', plugins_url( '', __FILE__ ) );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once( __DIR__ . '/includes/class-pmc-print-view-ajax.php' );
require_once( __DIR__ . '/includes/class-pmc-print-post-xmlrpc.php' );

class PMC_Print_View {

	const MAX_LOG_ENTRIES = 20;

	/**
	 * Sets up all of the hooks needed to operate within WordPress.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_print_taxonomies' ) );
		add_action( 'init', array( __CLASS__, 'register_custom_post_types' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 0 );
			add_action( 'all_admin_notices', array( __CLASS__, 'display_finalized_warning' ) );
			add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'toggle_print_post' ), 1 );
			add_action( 'save_post', array( __CLASS__, 'toggle_print_save' ), 10, 2 );
			add_action( 'redirect_post_location', array( __CLASS__, 'redirect_view' ), 10, 2 );
			add_action( 'load-post.php', array( __CLASS__, 'edit_page_init' ) );
			add_action( 'load-post-new.php', array( __CLASS__, 'edit_page_init' ) );
			add_action( 'pmc_print_edit_init', array( __CLASS__, 'register_edit_page_actions' ), 1 );
			add_action( 'admin_print_styles-post.php', array( __ClASS__, 'edit_page_styles' ) );
			add_action( 'admin_print_styles-post-new.php', array( __ClASS__, 'edit_page_styles' ) );
			add_action( 'admin_print_styles-posts_page_PMC_Print_View', array( __CLASS__, 'edit_page_styles' ) );
			add_action( 'admin_print_scripts-post.php', array( __ClASS__, 'edit_page_scripts' ) );
			add_action( 'admin_print_scripts-post-new.php', array( __ClASS__, 'edit_page_scripts' ) );
			add_action( 'admin_print_scripts-posts_page_PMC_Print_View', array( __CLASS__, 'edit_page_scripts' ) );
			add_action( 'save_post', array( __CLASS__, 'extra_titles_save' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'save_finalize' ), 100, 2 );
			add_action( 'save_post', array( __CLASS__, 'save_not_for_print' ), 101, 2 ); // Needs to run AFTER finalize check
			add_action( 'save_post', array( __CLASS__, 'guarantee_print' ), 10, 2 );
			add_action( 'pre_post_update', array( __CLASS__, 'action_pre_post_update' ), 10, 2 );
			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'action_output_icons' ) );
		}
	}

	/**
	 * Modify screen with JS when we have no hooks
	 *
	 * @uses admin_url, esc_js, get_post_type
	 * @since 1.0.0
	 * @return void
	 */
	public static function action_output_icons() {
		global $pagenow;

		if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
			$icon_class = 'icon-cloud';
			if ( ! empty( $_GET['pmc_view'] ) ) {
				$icon_class = 'icon-print';
			}
		?>
			<script type="text/javascript">
			( function( $ ) {
				$( '<i class="<?php echo esc_js( $icon_class ); ?>"></i>' ).insertBefore( $( '.add-new-h2') );
			})( jQuery );
			</script>
		<?php
		} elseif ( 'edit.php' == $pagenow && 'post' == get_post_type() && empty( $_GET['page'] ) ) {
		?>
			<script type="text/javascript">
			( function( $ ) {
				$( '<a href="<?php echo admin_url( 'edit.php?page=PMC_Print_View' ); ?>" class="add-new-h2">All Print Articles</a>' ).insertAfter( $( 'h2 .add-new-h2:first-child') );
			})( jQuery );
			</script>
		<?php
		}
	}

	/**
	 * Saves the post as print when it is saved from the print view
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function guarantee_print( $post_id, $post ) {
		if ( ! self::_authenticate_save_action( '_pmc_title_nonce', 'pmc_exta_title_noncination', $post ) )
			return;

		$term_slugs = array();
		$terms = wp_get_post_terms( $post->ID, 'pmc_print_article' );

		if ( ! empty( $terms ) )
			$term_slugs = wp_list_pluck( $terms, 'slug' );

		if ( ! in_array( '_print_post', $term_slugs ) )
			$term_slugs[] = '_print_post';

		wp_set_post_terms( $post_id, $term_slugs, 'pmc_print_article' );
	}

	/**
	 * Registers actions that only occur on the print view edit screen.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function register_edit_page_actions(){
		add_filter( 'admin_body_class', array( __CLASS__, 'edit_page_body_class' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'edit_page_metaboxes' ) );
		add_action( 'edit_form_after_title', array( __CLASS__, 'edit_page_extra_titles' ) );
	}

	/**
	 * Checks for pmc view settings and modifies the post redirect accordingly.
	 *
	 * @since 1.0.0
	 * @param string $location The current URL we are redirecting to.
	 * @param int $post_id The ID of the post being redirected to.
	 * @return string The location, updated as needed.
	 */
	public static function redirect_view( $location, $post_id ) {
		if ( isset( $_POST['pmc-view-switch'] ) ) {
			// We're silently saving the data, no need to post an update message.
			$location = remove_query_arg( 'message', $location );
			if ( 'print' === $_POST['pmc-view-switch'] ) {
				$location = add_query_arg( array( 'pmc_view' => 'print' ), $location );
			}
		} elseif ( isset( $_POST['pmc_view'] ) && 'print' === $_POST['pmc_view'] ) {
				$location = add_query_arg( array( 'pmc_view' => 'print' ), $location );
		}

		return esc_url_raw( $location );
	}

	/**
	 * Displays a warning that a post has been finalized and editing will result in it losing that status.
	 *
	 * @since  1.0.0
	 * @return void.
	 */
	public static function display_finalized_warning() {
		global $pagenow;
		if ( 'post.php' !== $pagenow )
			return;

		if ( has_term( '_finalized', 'pmc_print_article' ) ) {
			echo '<div class="clear"></div>';
			echo '<div id="finalized-message" class="error below-h2 pmc-finalized-warning">';
			echo '<p><strong>WARNING:</strong> This article is flagged for export. Any edits to the title, content, or related print meta information will require it to be flagged as "Ready for export" again before it can be sent for publishing.</p>';
			echo '</div>';
		}
	}

	/**
	 * Runs and action to init anything needed only on the edit page for the print view
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function edit_page_init() {
		global $typenow;
		if ( 'post' === $typenow && ( isset( $_GET['pmc_view'] ) && 'print' === $_GET['pmc_view'] ) ){
			do_action( 'pmc_print_edit_init' );
		}
	}

	/**
	 * Registers the meta boxes used on the edit page in the print view.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function edit_page_metaboxes() {
		add_meta_box(
			'pmc_print_log',
			'Change Log',
			array( __CLASS__, 'edit_page_log' ),
			'post',
			'advanced',
			'high'
		);

		add_meta_box(
			'pmc_print_submitbox',
			'Finalize for Print',
			array( __CLASS__, 'edit_page_submitbox' ),
			'post',
			'side',
			'high'
		);
	}

	/**
	 * Logging meta box
	 *
	 * @param object $post
	 * @uses have_posts, the_post, wp_reset_postdata, esc_html, get_the_ID, get_the_date, the_time
	 * @return void
	 */
	public static function edit_page_log( $post ) {
		$args = array(
			'post_type' => 'pmc_print_log_entry',
			'post_status' => 'publish',
			'posts_per_page' => self::MAX_LOG_ENTRIES,
			'post_parent' => $post->ID,
		);

		$logs = new WP_Query( $args );

		if ( $logs->have_posts() ) {
			echo '<ul class="pmc-post-log">';
			while ( $logs->have_posts() ) {
				global $post;
				$logs->the_post();

				$old_sections = get_post_meta( get_the_ID(), '_pmc_old_sections', true );
				if ( ! empty( $old_sections ) ) {
					$old_sections = implode( ', ', $old_sections );
				} else {
					$old_sections = 'None';
				}

				$old_issues = get_post_meta( get_the_ID(), '_pmc_old_issues', true );
				if ( ! empty( $old_issues ) ) {
					$old_issues = implode( ', ', $old_issues );
				} else {
					$old_issues = 'None';
				}

				$new_sections = get_post_meta( get_the_ID(), '_pmc_new_sections', true );
				if ( ! empty( $new_sections ) ) {
					$new_sections = implode( ', ', $new_sections );
				} else {
					$new_sections = 'None';
				}

				$new_issues = get_post_meta( get_the_ID(), '_pmc_new_issues', true );
				if ( ! empty( $new_issues ) ) {
					$new_issues = implode( ', ', $new_issues );
				} else {
					$new_issues = 'None';
				}
			?>
				<li>
					<em>Log entry from <?php the_time('F j, Y'); ?> at <?php the_time(); ?></em>
					<div>
						<p>New post state:</p>
						<strong>Hed 2 (dek):</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_new_hed2_dek', true ) ); ?><br />
						<strong>Hed 3 (overline):</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_new_hed3_overline', true ) ); ?><br />
						<strong>Print Slug:</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_new_print_slug', true ) ); ?><br />
						<strong>Sections:</strong> <?php echo esc_html( $new_sections ); ?><br />
						<strong>Issues:</strong> <?php echo esc_html( $new_issues ); ?>
					</div>

					<div>
						<p>Old post state:</p>
						<strong>Hed 2 (dek):</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_old_hed2_dek', true ) ); ?><br />
						<strong>Hed 3 (overline):</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_old_hed3_overline', true ) ); ?><br />
						<strong>Print Slug:</strong> <?php echo esc_html( get_post_meta( get_the_ID(), '_pmc_old_print_slug', true ) ); ?><br />
						<strong>Sections:</strong> <?php echo esc_html( $old_sections ); ?><br />
						<strong>Issues:</strong> <?php echo esc_html( $old_issues ); ?>
					</div>
				</li>
			<?php
			}
			echo '</ul>';
		}

		wp_reset_postdata();

	}

	/**
	 * Adds a body class to the edit page only when in print view
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function edit_page_body_class( $classes ) {
		$classes .= 'pmc-print-view ';
		return $classes;
	}

	/**
	 * Outputs the extra print title fields.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function edit_page_extra_titles() {
		$post_id = get_the_id();
		$dek = get_post_meta( $post_id, '_pmc_hed2_dek', true );
		$overline = get_post_meta( $post_id, '_pmc_hed3_overline', true );
		$slug = get_post_meta( $post_id, '_pmc_print_slug', true );

		echo '<div id="pmc-extra-titles" class="pmc-extra-titles">';
		echo '<div class="pmc-extra-title-container">';
		echo '<label class="description">Hed 2 (dek)</label>';
		echo '<input class="pmc-extra-title" name="pmc_hed2_dek" id="pmc_hed2_dek" type="text" value="' . esc_attr( $dek ) . '" />';
		echo '</div>';
		echo '<div class="pmc-extra-title-container">';
		echo '<label class="description">Hed 3 (overline)</label>';
		echo '<input class="pmc-extra-title" name="pmc_hed3_overline" id="pmc_hed3_overline" type="text" value="' . esc_attr( $overline ) . '" />';
		echo '</div>';
		echo '<label class="description">Print Slug </label>';
		echo '<input class="widefat pmc-print-slug" name="pmc_print_slug" id="pmc_print_slug" type="text" value="' . esc_attr( $slug ) . '" />';
		echo '</div>';
		wp_nonce_field( 'pmc_exta_title_noncination', '_pmc_title_nonce' );
	}

	/**
	 * Prints the edit page submitbox
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function edit_page_submitbox( $post ){
		$has_print_issue_set = has_term( '', 'print-issues', $post );
		$has_print_section_set = has_term( '', 'pmc_print_section', $post );
		$is_finalized = has_term( '_finalized', 'pmc_print_article', $post );
		$no_longer_print = 'true' === get_post_meta( $post->ID, '_pmc_no_longer_print', true ) ? true : false;

		$print_issue_class = ( $has_print_issue_set ) ? 'complete' : 'pending ';
		$print_section_class = ( $has_print_section_set ) ? 'complete' : 'pending ';

		if ( $has_print_section_set && $has_print_issue_set && ! $no_longer_print ) {
			$button_disabled = null;
		} else {
			$button_disabled = array( 'disabled' => 'disabled' );
		}
		$datef = __( 'M j, Y @ G:i' );
		if ( 0 != $post->ID ) {
			if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
				$stamp = __('Scheduled for: <b>%1$s</b>');
			} else if ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
				$stamp = __('Published on: <b>%1$s</b>');
			} else if ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
				$stamp = __('Publish <b>immediately</b>');
			} else if ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
				$stamp = __('Schedule for: <b>%1$s</b>');
			} else { // draft, 1 or more saves, date specified
				$stamp = __('Publish on: <b>%1$s</b>');
			}
			$date = date_i18n( $datef, strtotime( $post->post_date ) );
		} else { // draft (no saves, and thus no date specified)
			$stamp = __('Publish <b>immediately</b>');
			$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
		}

		echo '<p><span id="timestamp">' . sprintf( $stamp, $date ) . '</span></p>';
		echo '<p>Status: <strong>' . esc_html( ucwords( str_replace( '-', ' ', $post->post_status ) ) ) . '</strong></p>';
		echo '<p id="pmc-print-web-only-container">';
		// If the current user is editor or above, let them change this - otherwise just display a message if no longer for print
		if ( current_user_can( 'edit_others_posts' ) ) { // If you change this capability, change in the save_not_for_print() as well!!!
			echo '<label for="pmc-print-web-only">';
			echo '<input type="checkbox" name="pmc-print-web-only" id="pmc-print-web-only" value="1" ' . checked( true, $no_longer_print, false ) . '/>';
			echo 'No Longer For Print';
			echo '</label>';
			echo '<span style="display:none" id="pmc-print-web-only-light"></span>';
		} else {
			if ( $no_longer_print ) {
				echo 'No Longer for Print';
			}
		}
		echo '</p>';
		echo '<button class="button alignleft">Save Changes</button>';
		echo '<button class="button alignright" name="pmc-view-switch" value="web">View Web Version</button>';
		echo '<div class="clear"></div>';
		echo '<div class="pmc-print-submit-container">';
		echo '<p class="description">All items must be completed before this article can be finalized for print.</p>';
		echo '<ul>';
		echo '<li id="pmc-print-issue-light" class="pmc-status pmc-' . esc_attr( $print_issue_class ) . '">Issue Selected</li>';
		echo '<li id="pmc-print-section-light" class="pmc-status pmc-' . esc_attr( $print_section_class ) . '">Section Selected</li>';
		echo '</ul>';

		$finalized_on_time = get_post_meta( $post->ID, '_pmc_finalized_on', true );
		$exported_on_time = get_post_meta( $post->ID, '_pmc_exported_on', true );

		if ( ! empty( $finalized_on_time ) ) {
			$finalized_on_full = ' on ' . date( 'n/d/Y \a\t g:i A', $finalized_on_time );
		} else {
			$finalized_on_full = '';
		}

		if ( ! $is_finalized ) {
			submit_button( 'Ready for Export', 'primary pmc-finalize-button alignright button-large', 'pmc_finalized', true, $button_disabled );
		} else {
			submit_button( 'Export Readied' . $finalized_on_full, 'primary pmc-finalize-button alignright button-large', 'pmc_finalized', true, array( 'disabled' => 'disabled' ) );
		}
		echo '<div class="clear"></div>';

		if ( ! empty( $exported_on_time ) ) {
			echo '<div class="pmc-last-exported">Last Exported on ' . date( 'n/d/Y \a\t g:i A', $exported_on_time ) . '</div>';
		}
		echo '</div>';

		echo '<input type="hidden" name="pmc_view" id="pmc_view" value="print" />';
	}

	/**
	 * Enqueues a styles sheet on the edit page, with print view both on and off
	 *
	 * @since  1.0.0
	 * @return void.
	 */
	public static function edit_page_styles() {
		global $typenow, $pagenow;
		if ( 'post-new.php' == $pagenow )
			if ( ! ( isset( $_GET['pmc_view'] )  && $_GET['pmc_view'] == 'print' ) )
				return;


		if ( 'post' === $typenow || ( 'edit.php' === $pagenow && isset( $_GET['page'] ) && 'PMC_Print_View' === $_GET['page'] ) ) {
			wp_enqueue_style( 'pmc-print-edit-page', PMC_PRINT_VIEW_URL . '/css/edit-page.css' );
			wp_enqueue_style( 'pmc-font-awesome', get_template_directory_uri() . '/library/css/font-awesome.css' );
		}
	}

	/**
	 * Enqueues a script file on the edit page, with print view both on and off
	 *
	 * @since  1.0.0
	 * @return void.
	 */
	public static function edit_page_scripts() {
		global $typenow, $pagenow;
		if ( 'post-new.php' == $pagenow )
			if ( ! ( isset( $_GET['pmc_view'] )  && $_GET['pmc_view'] == 'print' ) )
				return;

		if ( 'post' === $typenow || ( 'edit.php' === $pagenow && isset( $_GET['page'] ) && 'PMC_Print_View' === $_GET['page'] ) ) {
			wp_enqueue_script( 'pmc-print-edit-page', PMC_PRINT_VIEW_URL . '/js/print-view.js', array( 'jquery' ), '1.0.3', true );

			$print_vars = array(
				'pagenow' => $pagenow,
				'flagImgPath' => plugins_url( 'pmc-print-view/img/flag.png' , dirname(__FILE__) ),
				'onPrintView' => ( isset( $_GET['pmc_view'] ) && 'print' === $_GET['pmc_view'] ) ? 'y' : 'n',
				'onPostList' => ( isset( $_GET['page'] ) && 'PMC_Print_View' == $_GET['page'] ) ? 'y' : 'n',
			);

			if ( 'post' === $typenow ) {
				global $post;
				$print_vars['post_id'] = $post->ID;
				$print_vars['toggle_print_article_nonce'] = wp_create_nonce( 'toggle_print_article_nonce' );
			}

			wp_localize_script( 'pmc-print-edit-page', 'pmcPrintVars', $print_vars );
		}
	}

	/**
	 * Saves our extra titles and slug when the post gets saved.
	 *
	 * @since 1.0.0
	 * @param int $post_id The saved post's id
	 * @param WP_Post $post The saved post object
	 * @return void.
	 */
	public static function extra_titles_save( $post_id, $post ) {
		if ( ! self::_authenticate_save_action( '_pmc_title_nonce', 'pmc_exta_title_noncination', $post ) )
			return;

		foreach( array( 'pmc_hed2_dek', 'pmc_hed3_overline', 'pmc_print_slug' ) as $field ){
			if ( isset( $_POST[ $field ] ) ){
				update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}

	/**
	 * Saves the finalized state of the post if appropriate
	 *
	 * @since 1.0.0
	 * @param int $post_id The saved post's ID
	 * @param WP_Post $post The saved post object
	 * @return void.
	 */
	public static function save_finalize( $post_id, $post ) {
		// Authenticate, use the print toggle nonce, since is is present even if the finalize metabox is not.
		if ( ! self::_authenticate_save_action( 'pmc_print_toggle_nonce', '_pmc_print_toggle_noncination', $post ) )
			return;

		// Check if this is even a print post
		if ( ! has_term( '_print_post', 'pmc_print_article', $post ) )
			return;

		// See if this is a finalized or un-finalized post
		$is_finalized = has_term( '_finalized', 'pmc_print_article', $post );

		// If not finalized and requested, attempt to finalized, otherwise, make sure everything is the same.
		if ( isset( $_POST['pmc_finalized'] ) && 'Ready for Export' === $_POST['pmc_finalized'] && false === $is_finalized ) {

			$has_print_issue_set = has_term( '', 'print-issues', $post );
			$has_print_section_set = has_term( '', 'pmc_print_section', $post );
			$has_print_slug_set = (bool) get_post_meta( $post->ID, '_pmc_print_slug', true );

			if ( $has_print_issue_set && $has_print_section_set && $has_print_slug_set ){
				update_post_meta( $post_id, '_pmc_finalized_hash', self::get_finalized_hash( $post ) );
				update_post_meta( $post_id, '_pmc_finalized_on', time() );
				wp_set_post_terms( $post_id, array( '_print_post', '_finalized' ), 'pmc_print_article' );
				return;
			}
		} elseif ( $is_finalized ) {
			$old_hash = get_post_meta( $post->ID, '_pmc_finalized_hash', true );
			$new_hash = self::get_finalized_hash( $post );

			if ( $old_hash === $new_hash )
				return;
		}

		// If we make it here, something has changed and the post must be re-finalized, so kill the _finalized term.
		wp_set_post_terms( $post_id, '_print_post', 'pmc_print_article' );
	}

	/**
	 * Checks for the 'no longer print' flag, and adjusts things accordingly
	 *
	 * @param int $post_id The saved post's ID
	 * @param WP_Post $post The saved post object
	 * @return void
	 */
	public static function save_not_for_print( $post_id, $post ) {
		// Authenticate, use the print toggle nonce, since is is present even if the finalize metabox is not.
		if ( ! self::_authenticate_save_action( 'pmc_print_toggle_nonce', '_pmc_print_toggle_noncination', $post ) )
			return;

		// Check if this is even a print post
		if ( ! has_term( '_print_post', 'pmc_print_article', $post ) )
			return;

		// The relevant fields are not output unless you have this capability
		if ( ! current_user_can( 'edit_others_posts' ) )
			return;

		if ( isset( $_POST['pmc-print-web-only'] ) && '1' === $_POST['pmc-print-web-only'] ) {
			update_post_meta( $post_id, '_pmc_no_longer_print', 'true' );

			// Make sure it's not finalized - Does not make sense for no-longer-for-print articles to be finalized
			wp_set_post_terms( $post_id, array( '_print_post', '_no_longer_print' ), 'pmc_print_article' );
		} else {
			delete_post_meta( $post_id, '_pmc_no_longer_print' );
		}
	}

	/**
	 * Gets a hash of all the data needed to finalize a post, used for checking for changes in the data.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post A WP_Post object
	 * @return string An MD5 Hash of the posts print related data.
	 */
	public static function get_finalized_hash( $post ) {
		//$set_issues = serialize( wp_get_post_terms( $post->ID, 'print-issues' ) );
		//$set_sections = serialize( wp_get_post_terms( $post->ID, 'pmc_print_section' ) );
		$set_slug = get_post_meta( $post->ID, '_pmc_print_slug', true );
		$hed2 = get_post_meta( $post->ID, '_pmc_hed2_dek', true );
		$hed3 = get_post_meta( $post->ID, '_pmc_hed3_overline', true );
		$post_data = $post->post_title . $post->post_content;

		return md5( $set_slug . $hed2 . $hed3 . $post_data );
	}

	/**
	 * Add the admin menu page
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function admin_menu() {
		// The text to be displayed in the title tags of the page when the menu is selected
		$page_title = 'Print Articles';

		// The text to be used for the menu
		$menu_title = 'Print Articles';

		// The capability required for this menu to be displayed to the user.
		$capability = 'edit_others_posts';

		// The slug name to refer to this menu by (should be unique for this menu)
		$menu_slug = __CLASS__;

		// The function to be called to output the content for this page.
		$callback_function = array( __CLASS__, 'print_posts_list' );

		// Add the page to the Posts menu
		$page_hook = add_posts_page( $page_title, $menu_title, $capability, $menu_slug, $callback_function );

		// Hook in an init action to for this specific admin page
		add_action( 'load-' . $page_hook, array( __CLASS__, 'post_list_init' ) );
	}

	/**
	 * Sets up things needed only on the posts list page the the print view.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function post_list_init() {
		if ( ! class_exists( 'WP_POSTS_LIST_TABLE' ) ) {
			require_once ABSPATH . '/wp-admin/includes/class-wp-posts-list-table.php';
		}
		require_once PMC_PRINT_VIEW_DIR . '/includes/pmc-print-view-post-list.php';

		add_action( 'pre_get_posts', array( __CLASS__, 'setup_query' ), 8 );
	}

	/**
	 * Outputs the titles in a CSV style format for downloading
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	private static function download_title_list() {
		if ( have_posts() ) {
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename="titles.csv"' );
			while( have_posts() ) {
				the_post();
				the_title();
				echo PHP_EOL;
			}
		}

		exit();
	}

	/**
	 * Filters the query on the post list page so that it actually reflects the print filters selected
	 *
	 * @since  1.0.0
	 * @param  WP_Query $query The global WP_Query object.
	 * @return void. The query is passed by reference so there is no need to return it.
	 */
	public static function setup_query( $query ) {
		$tax_query = array();
		// Set up the initial tax query for the currently selected print status.
		if ( isset( $_REQUEST['print_status'] ) && term_exists( $_REQUEST['print_status'], 'pmc_print_article') ) {
			$status = $_REQUEST['print_status'];
		} else {
			$status = '_print_post';
		}
		$tax_query[] = array(
			'taxonomy' => 'pmc_print_article',
			'field' => 'slug',
			'terms' => sanitize_text_field( $status ),
		);

		if ( isset( $_REQUEST['nlfp_status'] ) && in_array( $_GET['nlfp_status'], array( '_print', '_web_and_print' ) ) ) {
			$nlfp_status = $_REQUEST['nlfp_status'];
		} else {
			$nlfp_status = '_print';
		}

		if ( $nlfp_status === '_print' ) {
			// Setup the query to ignore 'no longer for print', unless specified otherwise
			$tax_query[] = array(
				'taxonomy' => 'pmc_print_article',
				'field' => 'slug',
				'terms' => '_no_longer_print',
				'operator' => 'NOT IN'
			);
		}

		if ( $query->is_search ) {
			$search_string = $query->get( 's' );
			if ( ! empty( $search_string ) ) {
				add_filter( 'posts_search', array( __CLASS__, 'searchable_slug' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'searchable_slug_join' ), 10, 2 );
			}
		}

		$query->set( 'tax_query', $tax_query );

		// Check if this is a download request and if so set posts_per_page to 1000 to try and get everything.
		if ( isset( $_REQUEST['pmc_export'] ) && 'download' === $_REQUEST['pmc_export'] && isset( $_GET['noheader'] ) )
			$query->set( 'posts_per_page', 1000 );
	}

	/**
	 * Filters the search query string to add the slug as a searchable field.
	 *
	 * @since  1.0.0
	 * @param string $search The current 'search' portion of the coming query.
	 * @param WP_Query $query The global WP_Query object
	 * @return string The updated 'search' query portion.
	 */
	public static function searchable_slug( $search, $query ) {
		global $wpdb;

		$query_args = array( 'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_pmc_print_slug',
				'value' => $query->get( 's' ),
			),
		) );
		$meta_query = new WP_Meta_Query();
		$meta_query->parse_query_vars( $query_args );
		$sql = $meta_query->get_sql(
			'post',
			$wpdb->posts,
			'ID',
			null
		);

		$search .= ' OR ( 1=1 ' . $sql['where'] . ' )';

		return $search;
	}

	/**
	 * Adds the meta table to search queries so that the slug will be searchable.
	 *
	 * @since  1.0.0
	 * @param sting $join The current 'join' portion of the query string.
	 * @return sting The 'join' portion of the query string with post meta added.
	 */
	public static function searchable_slug_join( $join, $query ) {
		global $wpdb;

		$query_args = array( 'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_pmc_print_slug',
				'value' => $query->get( 's' ),
			),
		) );
		$meta_query = new WP_Meta_Query();
		$meta_query->parse_query_vars( $query_args );
		$sql = $meta_query->get_sql(
			'post',
			$wpdb->posts,
			'ID',
			null
		);

		$join .= ' ' . $sql['join'];

		return $join;
	}

	/**
	 * Sets up a globals for a new post list table and pulls in the template.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public static function print_posts_list() {
		// If not, set up the normal post list.
		global $typenow, $post_type, $post_type_object;
		$typenow = 'post';
		$post_type = $typenow;
		$post_type_object = get_post_type_object( $post_type );

		set_current_screen( 'pmc-print-view' );
		get_current_screen()->post_type = $post_type;

		$wp_list_table = new PMC_Print_View_List_Table();
		$wp_list_table->prepare_items();

		// Check if this is a download request.
		if ( isset( $_REQUEST['pmc_export'] ) && 'download' === $_REQUEST['pmc_export'] && isset( $_GET['noheader'] ) )
			if ( ! empty( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'pmc-titles-export' ) )
				if ( current_user_can( 'edit_posts' ) )
					self::download_title_list();

		include PMC_PRINT_VIEW_DIR . '/views/post-list.php';
	}

	public static function register_custom_post_types() {
		$args = array(
		 	'public' => false,
		 	'query_var' => false,
		 	'rewrite' => false,
		 	'capability_type' => 'post',
		 	'has_archive' => false,
		 	'hierarchical' => false,
		 	'can_export' => true,
		);
		register_post_type( 'pmc_print_log_entry', $args );
	}

	/**
	 * Log post before and after states before save
	 *
	 * @param int $post_id
	 * @param array $data
	 * @uses get_post, get_term
	 * @return void
	 */
	public static function action_pre_post_update( $post_id, $data ) {

		$old_post = get_post( $post_id );

		if ( 'post' != get_post_type( $post_id ) ) {
			return;
		}

		if ( ! self::_authenticate_save_action( 'pmc_print_toggle_nonce', '_pmc_print_toggle_noncination', $old_post ) )
			return;

		$old_hash = get_post_meta( $post_id, '_pmc_finalized_hash', true );

		$hed2_dek = ( ! empty( $_POST['pmc_hed2_dek'] ) ) ? $_POST['pmc_hed2_dek'] : '';
		$hed3_overline = ( ! empty( $_POST['pmc_hed3_overline'] ) ) ? $_POST['pmc_hed3_overline'] : '';
		$print_slug = ( ! empty( $_POST['pmc_print_slug'] ) ) ? $_POST['pmc_print_slug'] : '';

		$new_hash = md5( $print_slug . $hed2_dek . $hed3_overline . $data['post_content'] );

		if ( ! empty( $_POST['pmc_finalized'] ) || $old_hash != $new_hash ) {

			$new_post = array();
			$new_post['hed2_dek'] = $hed2_dek;
			$new_post['hed3_overline'] = $hed3_overline;
			$new_post['print_slug'] = $print_slug;

			$new_post['sections'] = array();
			if ( ! empty( $_POST['tax_input']['pmc_print_section'] ) ) {
				foreach ( $_POST['tax_input']['pmc_print_section'] as $section_id ) {
					if ( ! empty( $section_id ) ) {
						$section = get_term( $section_id, 'pmc_print_section' );
						$new_post['sections'][] = $section->slug;
					}
				}
			}

			$new_post['issues'] = array();
			if ( ! empty( $_POST['tax_input']['print-issues'] ) ) {
				foreach ( $_POST['tax_input']['print-issues'] as $issue_id ) {
					if ( ! empty( $issue_id ) ) {
						$issue = get_term( $issue_id, 'print-issues' );
						$new_post['issues'][] = $issue->slug;
					}
				}
			}

			self::log_action( $old_post, $new_post );
		}
	}

	/**
	 * Log a transition change
	 *
	 * @param object $old_post A post object
	 * @param array $new_post An array of post meta and tax stuff
	 * @param string message
	 * @uses get_post_meta, wp_list_pluck, get_the_terms, update_post_meta, wp_insert_post,
	 *		 sanitize_text_field, get_the_terms, wp_delete_post
	 * @return bool|int
	 */
	public static function log_action( $old_post, $new_post, $message = '' ) {

		if ( ! is_object( $old_post ) || ! is_array( $new_post ) )
			return false;

		// Delete an old log entry if we are over the limit
		$args = array(
			'post_type' => 'pmc_print_log_entry',
			'post_status' => 'publish',
			'posts_per_page' => 200,
			'post_parent' => $old_post->ID,
		);

		$logs = new WP_Query( $args );

		if ( count( $logs->posts ) >= self::MAX_LOG_ENTRIES ) {
			for ( $i = self::MAX_LOG_ENTRIES - 1; $i < count( $logs->posts ); $i++ ) {
				wp_delete_post( $logs->posts[$i]->ID, true );
			}
		}


		$args = array(
			'post_title' => 'log-entry-' . $old_post->ID . '-' . time(),
 			'post_content' => $message,
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'pmc_print_log_entry',
			'post_parent' => $old_post->ID,
		);

		$post_id = wp_insert_post( $args );

		if ( $post_id ) {

			$old_dek = get_post_meta( $old_post->ID, '_pmc_hed2_dek', true );
			$old_overline = get_post_meta( $old_post->ID, '_pmc_hed3_overline', true );
			$old_slug = get_post_meta( $old_post->ID, '_pmc_print_slug', true );

			$old_sections = get_the_terms( $old_post->ID, 'pmc_print_section' );

			if ( ! empty( $old_sections ) ) {
				$old_sections = wp_list_pluck( $old_sections, 'slug' );
				$old_sections = array_map( 'esc_attr', $old_sections );
			} else {
				$old_sections = '';
			}

			$old_issues = get_the_terms( $old_post->ID, 'print-issues' );

			if ( ! empty( $old_issues ) ) {
				$old_issues = wp_list_pluck( $old_issues, 'slug' );
				$old_issues = array_map( 'esc_attr', $old_issues );
			} else {
				$old_issues = '';
			}

			$new_dek = $new_post['hed2_dek'];
			$new_overline = $new_post['hed3_overline'];
			$new_slug = $new_post['print_slug'];

			$new_sections = $new_post['sections'];

			$new_issues = $new_post['issues'];

			update_post_meta( $post_id, '_pmc_old_hed2_dek', sanitize_text_field( $old_dek ) );
			update_post_meta( $post_id, '_pmc_old_hed3_overline', sanitize_text_field( $old_overline ) );
			update_post_meta( $post_id, '_pmc_old_print_slug', sanitize_text_field( $old_slug ) );
			update_post_meta( $post_id, '_pmc_old_sections', $old_sections );
			update_post_meta( $post_id, '_pmc_old_issues', $old_issues );

			update_post_meta( $post_id, '_pmc_new_hed2_dek', sanitize_text_field( $new_dek ) );
			update_post_meta( $post_id, '_pmc_new_hed3_overline', sanitize_text_field( $new_overline  ));
			update_post_meta( $post_id, '_pmc_new_print_slug', sanitize_text_field( $new_slug ) );
			update_post_meta( $post_id, '_pmc_new_sections', $new_sections );
			update_post_meta( $post_id, '_pmc_new_issues', $new_issues );
		}
	}

	/**
	 * Registers additional taxonomies for posts, specifically for print.
	 *
	 * @since  1.0.0
	 * @return void.
	 */
	public static function register_print_taxonomies() {
		register_taxonomy(
			'pmc_print_article',
			'post',
			array(
				'public' => false,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'pmc-print-article' )
			)
		);

		// Only register taxonomy if not registered in theme
		if ( ! taxonomy_exists( 'print-issues' ) ) {
			register_taxonomy(
				'print-issues',
				'post',
				array(
					'labels' => array(
						'name' => 'Issues',
						'singular_name' => 'Issue',
						'menu_name' => 'Print Issues',
						'all_items' => 'All Issues',
						'edit_item' => 'Edit Issue',
						'update_item' => 'Update Issue',
						'add_new_item' => 'Add New Issue',
						'new_item_name' => 'New Issue Name',
						'parent_item' => 'Parent Issue',
						'parent_item_colon' => 'Parent Issue:',
						'search_items' => 'Search Issues',
					),
					'public' => true,
					'show_tagcloud' => false,
					'hierarchical' => true,
					'query_var' => false,
					'rewrite' => false,
				)
			);
		}

		register_taxonomy(
			'pmc_print_section',
			'post',
			array(
				'labels' => array(
					'name' => 'Sections',
					'singular_name' => 'Section',
					'menu_name' => 'Print Sections',
					'all_items' => 'All Sections',
					'edit_item' => 'Edit Sections',
					'update_item' => 'Update Section',
					'add_new_item' => 'Add New Section',
					'new_item_name' => 'New Section Name',
					'parent_item' => 'Parent Section',
					'parent_item_colon' => 'Parent Setion:',
					'search_items' => 'Search Sections',
				),
				'public' => true,
				'show_tagcloud' => false,
				'hierarchical' => true,
				'query_var' => true,
				'rewrite' => false,
			)
		);
	}

	/**
	 * Outputs a row in the publish box for both toggling if the post is for print and between views.
	 *
	 * @since  1.0.0
	 * @return void.
	 */
	public static function toggle_print_post() {
		$post = get_post();
		$is_print_post = has_term( '_print_post', 'pmc_print_article', $post );

		echo '<div class="misc-pub-section pmc-print-pub-section">';
		echo '<span style="padding-top: 4px; display: inline-block;">';
		echo '<input type="checkbox" name="pmc-print-post" id="pmc-print-post" value="1" ' . checked( $is_print_post, true, false ) . ' />';
		echo ' <label for="pmc-print-post">Print Article</lable>';
		echo '</span>';
		echo '<button ' . ( ( $is_print_post ) ? '' : 'style="display: none"' ) . ' class="button alignright" name="pmc-view-switch" value="print">View Print Version</button>';
		echo '<div class="clear"></div>';
		wp_nonce_field( '_pmc_print_toggle_noncination', 'pmc_print_toggle_nonce' );
		if ( get_post_meta( $post->ID, '_print-issues_removal_blocked', true ) ){
			echo '<p class="description">Remove all Section and Issue designations before removing the print flag.</p>';
			delete_post_meta( $post->ID, '_print-issues_removal_blocked' );
		}
		echo '</div>';
	}

	/**
	 * Saves a posts "print" status, but only allows removal if it doesn't have print related data.
	 * @param  int $post_id The ID of the post being saved.
	 * @param  WP_Post $post The WP_Post object being saved.
	 * @return void.
	 */
	public static function toggle_print_save( $post_id, $post ) {
		if ( ! self::_authenticate_save_action( 'pmc_print_toggle_nonce', '_pmc_print_toggle_noncination', $post ) )
			return;

		$is_print_post = has_term( '_print_post', 'pmc_print_article', $post );
		// Split behavior based on value of the pmc-print-post checkbox
		if ( isset( $_POST['pmc-print-post'] ) && '1' === $_POST['pmc-print-post'] ) {
			if ( ! $is_print_post )
				wp_set_post_terms( $post_id, '_print_post', 'pmc_print_article' );
		} else {
			if ( $is_print_post ){
				$issues = get_the_terms( $post, 'print-issues' );
				$section = get_the_terms( $post, 'pmc_print_section' );
				if ( is_array( $issues ) || is_array( $section ) ) {
					add_post_meta( $post_id, '_print-issues_removal_blocked', true );
					return;
				}
			}
			wp_set_post_terms( $post_id, '', 'pmc_print_article' );
		}
	}

	/**
	 * A helper function that authenticates users and nonces for use when saving post data.
	 *
	 * @since  1.0.0
	 * @param string $nonce_name The name of the nonce to check
	 * @param string $nonce_action The nonce action to check against
	 * @param WP_Post $post A WP_Post object we will be saving data for.
	 * @return bool Whether or not the request has been authenticated.
	 */
	private static function _authenticate_save_action( $nonce_name, $nonce_action, $post ){
		// Bail if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return false;

		// Bail if this isn't a post
		if ( 'post' !== $post->post_type )
			return false;
		// Bail if user lacks capability
		if ( ! current_user_can( 'edit_posts', $post->ID ) )
			return false;

		$nonce = isset( $_POST[ $nonce_name ] ) ? $_POST[ $nonce_name ] : '';
		// Bail if we're not coming from the edit page
		if ( ! wp_verify_nonce( $nonce,  $nonce_action ) )
			return false;

		return true;
	}
}
add_action( 'after_setup_theme', array( 'PMC_Print_View', 'init' ) );
