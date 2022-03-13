<?php
/**
 * Posts List Table class.
 *
 * @since 0.9.0
 * @version 0.9.0
 */
class PMC_Print_View_List_Table extends WP_Posts_List_Table {

	/**
	 * Maps custom status slugs with proper names.
	 * @since  1.0.0
	 * @var array
	 */
	private $status_map = array(
		'_finalized' => 'Finalized',
	);

	/**
	 * After the base URL has been generated, stores it so it only need to be generated once.
	 * @since  1.0.1
	 * @var string
	 */
	private $_base_url = false;

	/**
	 * Creates the list table object for the print view
	 *
	 * @since 1.0.0
	 * @return  PMC_Print_view_List_Table
	 */
	public function __construct() {
		parent::__construct( array( 'screen' => get_current_screen() ) );
		add_filter( 'get_edit_post_link', array( $this, 'add_print_arg' ) );
		add_filter( 'request', array( $this, 'custom_author_column_orderby' ) );
	}

	/**
	 * Sort post table by custom author column
	 *
	 * @param array $vars
	 * @return array
	 */
	public function custom_author_column_orderby( $vars ) {
	    if ( isset( $vars['orderby'] ) && 'custom-author' == $vars['orderby'] ) {
	        $vars = array_merge( $vars, array(
	            'orderby' => 'author'
	        ) );
	    }

	    return $vars;
	}

	/**
	 * Filters the edit link to append our special view variable.
	 *
	 * @param string $edit_link The created edit link.
	 * @return string The edit link with our special view variable appended.
	 */
	public function add_print_arg( $edit_link ) {
		return add_query_arg( array( 'pmc_view' => 'print' ), $edit_link );
	}


	/**
	 * Gets the availabe status views, based on a custom hidden taxonomy
	 *
	 * @since  1.0.0
	 * @return array The available status links in an array for easy output.
	 */
	public function get_views() {
		$post_type = $this->screen->post_type;

		$status_links = array();
		$print_terms = get_terms( 'pmc_print_article' );
		$print_status_counts = array();

		foreach ( $print_terms as $print_status ) {

			/*$args = array(
				'post_type' => 'post',
				'pmc_print_article' => esc_attr( $print_status->slug ),
				'posts_per_page' => 5000,
				'no_found_rows' => true
			);

			$print_query = new WP_Query( $args );

			$count = 0;
			if ( $print_query->have_posts() ) {
				$count = count( $print_query->posts );
			}

			wp_reset_postdata();*/

			$print_status_counts[ $print_status->slug ] = 0;
		}

		$class = '';
		$allposts = '';
		$total_posts = isset( $print_status_counts['_print_post'] ) ? $print_status_counts['_print_post'] : 0;

		$class = empty( $class ) && empty( $_REQUEST['print_status'] ) ? ' class="current"' : '';

		$count_text = '';
		if ( $total_posts > 0 )
			$count_text = ' <span class="count">(' . (int) $total_posts . ')</span>';

		$status_links['all'] = '<a href="' . esc_url( admin_url( 'edit.php?page=PMC_Print_View' ) ) . '"' .$class . '>All' . $count_text . '</a>';

		foreach ( $print_status_counts as $print_status_slug => $print_status_count ){
			// reserve _print_post for all
			if ( '_print_post' === $print_status_slug )
				continue;

			// Setup vars
			$class = ( isset( $_REQUEST['print_status'] ) && $_REQUEST['print_status'] === $print_status_slug ) ? ' class="current"' : '';
			$name = isset( $this->status_map[ $print_status_slug ] ) ? $this->status_map[ $print_status_slug ] : $print_status_slug;

			$count_text = '';
			if ( $print_status_count > 0 )
				$count_text = ' <span class="count">(' . (int) $print_status_count . ')</span>';

			// Make the link
			$status_links[$print_status_slug] = '<a href="' . esc_url( admin_url( "edit.php?page=PMC_Print_View&print_status=$print_status_slug" ) ) . '"' . $class . '>' . esc_html( $name ) . $count_text . '</a>';
		}

		return $status_links;
	}

	/**
	 * Clears the bulk actions so that we don't get the dropdown
	 *
	 * @since  1.0.0
	 * @return array An empty array so the bulk actions don't appear.
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Sets up the extra table nav section which includes dropdowns for the print specific taxonomies.
	 *
	 * @since  1.0.0
	 * @param string $which Whether we are on the top or bottom row
	 * @return void.
	 */
	public function extra_tablenav( $which ) {
		?><div class="alignleft actions"><?php

		if ( 'top' == $which && !is_singular() ) {
			$this->_tax_dropdown( 'pmc_print_section', 'View all sections' );
			$this->_tax_dropdown( 'print-issues', 'View all issues' );

			$this->_finalized_dropdown();
			$this->_not_for_print_dropdown();

			do_action( 'pmc_print_manage_posts' );
			submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
		}

		?></div><?php
	}

	/**
	 * Displays the active filters for easy removal.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public function active_filters(){

		// Allowed filters parameters.
		$filters = array(
			'Search' => 's',
			'Issue' => 'print-issues',
			'Section' => 'pmc_print_section',
			'Status' => 'print_status',
		);

		$base_url = $this->_get_base_url();

		// Build the list of active filters as needed.
		$markup = '';
		foreach ( $filters as $name => $query_arg ) {
			$markup .= $this->_active_filter( $query_arg, $name, $base_url );
		}

		if ( ! empty( $markup ) ) {
			$before = '<br class="clear" />';
			$before .= '<ul class="subsubsub">';
			$before .= '<li><strong>Active Filters (click to remove):</strong></li>';

			$after ='</ul>';
			echo apply_filters( 'pmc_remove_list_filters', $before . $markup . $after, $before, $after );
		}
	}

	/**
	 * Sets up the markup for a single active filter, used in creating the list of active filters.
	 *
	 * As a private method, this can't be used out side this class. As such the data entering this
	 * method is completely within the contol of this class. This method expects sanitized data. It
	 * is intended to be used as a helper method in the active_filters. If used elsewhere, ensure data
	 * passed to this method is sanitized and validated properly.
	 *
	 * @since 1.0.0
	 * @param string $slug The slug of the filter which is active
	 * @param string $title The title to use on the filter
	 * @param string $base_url The base URL that this link will modify
	 * @return string The markup for this filter, or an empty string if the filter isn't active.
	 */
	private function _active_filter( $slug, $title, $base_url ){
		$markup = '';
		if ( isset( $_GET[ $slug ] ) && ! empty( $_GET[ $slug ] ) && $_GET[ $slug ] != '_print_post' ) {
			$markup .= '<li class="remove_search">';
			$markup .= '<a class="add-new-h2" href="' . esc_url( remove_query_arg( $slug, $base_url ) ) . '">';
			$markup .= esc_html( $title );
			$markup .= '</a>';
			$markup .= '</li>';
		}
		return $markup;
	}

	/**
	 * Generates a dropdown for custom print taxonomies
	 *
	 * @since  1.0.0
	 * @param strng $tax The taxonomy to drop down
	 * @param string $all_text Optional. The text to use as the 'View All' option in the dropdown.
	 * @param integer $hierarchical Optional. Whether or not the taxonomy is hierachical.
	 * @return void The markup is output when this method is called so there is no return value.
	 */
	private function _tax_dropdown( $tax, $all_text = 'View All', $hierarchical = 1 ) {
		if ( is_object_in_taxonomy( $this->screen->post_type, $tax ) ) {

			$selected = ( isset( $_GET[ $tax ] ) ) ? $_GET[ $tax ] : false;

			$terms = get_terms( $tax );

			if ( empty( $terms ) )
				return;
			?>
			<select name="<?php echo esc_attr( $tax ); ?>" id="tax-dd-<?php echo esc_attr( $tax ); ?>" class="postform">
				<option value="0"><?php echo esc_html( $all_text ); ?></option>
				<?php foreach ( $terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $term->slug, $selected ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php
		}
	}

	/**
	 * Generates a dropdown for finalized posts
	 *
	 * @uses is_object_in_taxonomy, get_term_by, selected
	 * @return void
	 */
	private function _finalized_dropdown() {
		if ( is_object_in_taxonomy( $this->screen->post_type, 'pmc_print_article' ) ) {

			$selected = ( isset( $_GET['print_status'] ) ) ? $_GET['print_status'] : false;
		?>
			<select name="print_status" id="tax-dd-pmc_print_status" class="postform">
				<option value="_print_post">Finalized and unfinalized</option>
				<option class="level-0" <?php selected( $selected, '_finalized' ); ?> value="_finalized">Finalized Only</option>
			</select>
		<?php
		}
	}

	/**
	 * Generates a dropdown for including/excluding no-longer-for-print posts
	 *
	 * @return void
	 */
	private function _not_for_print_dropdown() {
		if ( is_object_in_taxonomy( $this->screen->post_type, 'pmc_print_article' ) ) {

			// nlfp -> no longer for print
			$selected = ( isset( $_GET['nlfp_status'] ) && in_array( $_GET['nlfp_status'], array( '_print', '_web_and_print' ) ) ) ? $_GET['nlfp_status'] : '_print';
			?>
			<select name="nlfp_status" id="nlfp_status" class="postform">
				<option <?php selected( $selected, '_print' ); ?> value="_print">Print Only</option>
				<option class="level-0" <?php selected( $selected, '_web_and_print' ); ?> value="_web_and_print">Print & No Longer for Print</option>
			</select>
		<?php
		}
	}

	/**
	 * Adds the pagniation area to the post list table. Display a download link for the current filters.
	 *
	 * This uses a reflection method to call the granparent WP_Post_List classes pagination method because
	 * the parent class's method does too much, but the code is already written, so we may as well use it!
	 *
	 * @since  1.0.0
	 * @param  string $which Whether we are outputting on the top or bottom
	 * @return void.
	 */
	public function pagination( $which ) {
		// Add the download button for the current filters
		if ( 'bottom' === $which ) {
			$download_url = $this->_get_base_url();
			$download_url = add_query_arg( array(
				'noheader' => 'true',
				'pmc_export' => 'download',
				'nonce' => wp_create_nonce( 'pmc-titles-export' ),
			), $download_url );
			echo '<a href="' . esc_url( $download_url ) . '"class="button">Download Titles for Current Filters</a>';
		}
		// Invoke the grandparent method for basic pagination.
		$reflectionMethod = new ReflectionMethod( get_parent_class( get_parent_class( $this ) ), 'pagination');
		$reflectionMethod->invoke( $this, $which );
	}

	/**
	 * Gets the available columns for the print view.
	 *
	 * @since  1.0.0
	 * @return array The array of slugs and column names.
	 */
	public function get_columns() {
		$post_type = $this->screen->post_type;

		$posts_columns = apply_filters( 'pmc_print_columns', array(
			'finalized_status' => 'Status',
			'title' => 'Title',
			'print_slug' => 'Print Slug',
			'tax-print-issues' => 'Issue',
			'tax-pmc_print_section' => 'Section',
			'custom-author' => 'Author',
			'note' => 'Latest Note',
			'exported' => 'Exported',
		) );

		return $posts_columns;
	}

	/**
	 * Tells the list table which rows are sortable and which are not. Only title in our case.
	 *
	 * @since  1.0.0
	 * @return array The slug of the rows that are sortable.
	 */
	public function get_sortable_columns() {
		return array(
			'title' => 'title',
			'custom-author' => 'custom-author',
		);
	}

	/**
	 * Sets up the display of individual rows.
	 *
	 * @since 1.0.0
	 * @param array $posts The posts to display, the global WP_Query object will be used if empty.
	 * @param integer $level The level to allow.
	 * @return void.
	 */
	public function display_rows( $posts = array(), $level = 0 ) {
		global $wp_query;

		if ( empty( $posts ) ) {
			$posts = $wp_query->posts;
		}

		add_filter( 'the_title', 'esc_html' );
		add_action( 'manage_posts_custom_column', array( $this, 'print_columns' ), 10, 2 );

		foreach ( $posts as $post ) {
			$this->single_row( $post, $level );
		}
		// Clean up after ourselves
		remove_filter( 'the_title', 'esc_html' );
	}

	/**
	 * Outputs markup for a custom print related columns.
	 *
	 * @since 1.0.0
	 * @param string $column_name The name of the column being printed
	 * @param integer $post_id The ID of the post being printed
	 * @return void.
	 */
	public function print_columns( $column_name, $post_id ) {
		$post = get_post( $post_id );

		$exported_on_time = get_post_meta( $post_id, '_pmc_exported_on', true );
		$finalized_on_time = get_post_meta( $post_id, '_pmc_finalized_on', true );

		switch ( $column_name ) {
			case 'finalized_status':
				$isFinalized = has_term( '_finalized', 'pmc_print_article', $post_id );
				$wasFinalized = ( ! empty( $finalized_on_time ) && ! $isFinalized ) ? true : false;
				$noLongerPrint = 'true' === get_post_meta( $post->ID, '_pmc_no_longer_print', true ) ? true : false;
				if ( $noLongerPrint ) {
					$char = 'NLP';
				} elseif ( $isFinalized ) {
					$char = '&#10003;';
				} elseif ( $wasFinalized ) {
					$char = '&times;';
				} else {
					$char = '';
				}
				echo '<div class="pmc-pl-status" style="font-size: 125%;">' . $char . '</div>';
				break;
			case 'print_slug':
				echo esc_html( get_post_meta( $post_id, '_pmc_print_slug', true ) );
				break;
			case 'custom-author':
				printf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'page' => 'PMC_Print_View', 'author' => get_the_author_meta( 'ID' ) ), 'edit.php' )),
					get_the_author()
				);
				break;
			case 'tax-print-issues':
			case 'tax-pmc_print_section':

				$taxonomy = 'print-issues';
				if ( $column_name == 'tax-pmc_print_section' )
					$taxonomy = 'pmc_print_section';

				$taxonomy_object = get_taxonomy( $taxonomy );

				if ( $terms = get_the_terms( $post->ID, $taxonomy ) ) {
					$out = array();
					foreach ( $terms as $t ) {
						$posts_in_term_qv = array();
						if ( $taxonomy_object->query_var ) {
							$posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
						} else {
							$posts_in_term_qv['taxonomy'] = $taxonomy;
							$posts_in_term_qv['term'] = $t->slug;
						}

						$posts_in_term_qv['page'] = 'PMC_Print_View';

						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )
						);
					}
					/* translators: used between list items, there is a space after the comma */
					echo join( __( ', ' ), $out );
				}
				break;
			case 'note':
				$args = array(
					'number' => 1,
					'order' => 'DESC',
					'comment_type' => EF_Editorial_Comments::comment_type,
					'status' => EF_Editorial_Comments::comment_type,
					'post_id' => $post_id
				);
				$comment = ef_get_comments_plus( $args );
				if ( isset( $comment[0] ) && isset( $comment[0]->comment_content ) ) {
					echo esc_html( $comment[0]->comment_content );
				}
				break;
			case 'exported':
				if ( ! empty( $exported_on_time ) ) {
					echo 'Last Exported on ' . date( 'n/d/Y \a\t g:i A', $exported_on_time );
				}
				break;
		}
	}

	/**
	 * Gets the base URL of this page using a white list of query args that can will pass through.
	 *
	 * @since  1.0.1
	 * @return string The generated base URL for this page with all of the current filters in place.
	 */
	private function _get_base_url() {
		if ( ! $this->_base_url ) {

			// Allowed $_GET parameters.
			$allowed_params = array(
				's',
				'print_status',
				'_wp_nonce',
				'_wp_http_referer',
				'print-issues',
				'pmc_print_section',
			);

			// Build a base URL based on available and whitlisted args.
			$base_url = admin_url( 'edit.php?page=PMC_Print_View' );
			foreach ( $allowed_params as $allowed_param ) {
				if ( isset( $_GET[ $allowed_param ] ) ) {
					$base_url = add_query_arg( $allowed_param, urlencode( $_GET[ $allowed_param ] ) );
				}
			}
			// Store the URL so it only needs to be generated once.
			$this->_base_url = $base_url;
		}

		return $this->_base_url;
	}
}
