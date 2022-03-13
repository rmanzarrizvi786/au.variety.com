<?php

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Post_Listing_Filters {

	use Singleton;

	const MAX_SAVED_SEARCH_FILTER = 20;	// maximum of saved search filter
	const NONCE_ACTION            = 'pmc_plf';
	const NONCE_NAME              = '_wp_pmc_plf_nonce';

	private $_default_post_types = array( 'post' );
	private $_default_taxonomy   = array( 'editorial', 'vertical', 'category' );
	private $_query              = array();
	private $_select_filler      = array();

	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks
	 */
	protected function _setup_hooks() : void {

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'views_edit-post', array( $this, 'filter_views' ), 100, 1 );
		add_filter( 'pmc_global_cheezcap_options', [ $this, 'post_listing_filters_cheezcap' ] );

	}

	/**
	 * Add Post Listing Filters Cheezcap Options
	 *
	 * @param array $cheezcap_options List of Cheezcap options.
	 *
	 * @return array $cheezcap_options
	 */
	public function post_listing_filters_cheezcap( array $cheezcap_options ) : array {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			__( 'Enable Elasticsearch (ES) in Post Filters WP Query', 'pmc-post-listing-filters' ),
			__( 'Turn on Elasticsearch assisted search for PMC Post Listing Filters', 'pmc-post-listing-filters' ),
			'pmc_post_listing_filters_es_query',
			[ 'no', 'yes' ],
			0, // 1st option => no by default
			[ 'No', 'Yes' ]
		);

		return $cheezcap_options;
	}

	/*
	 * Override and remove standard view filters
	 */
	function filter_views( $views ) {
		return array();
	}

	function admin_enqueue_scripts() {
		global $typenow, $pagenow;

		$this->_default_post_types = $this->get_default_post_types();

		if ( !in_array( $typenow, $this->_default_post_types ) ) {
			return;
		}

		if ( $pagenow !== 'edit.php' ) {
			return;
		}

		$pfs_data = array(
			'suggest_link' => add_query_arg(
				array(
					'action' => 'coauthors_ajax_suggest',
					'post_type' => get_post_type(),
				),
				wp_nonce_url( 'admin-ajax.php', 'coauthors-search' )
			),
		);

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'pmc-post-listing-filter-chosen', plugins_url( 'chosen/chosen.jquery.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'pmc-post-listing-filter-script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery', 'jquery-ui-dialog', 'pmc-post-listing-filter-chosen', 'suggest' ) );
		wp_localize_script( 'pmc-post-listing-filter-script', 'pmc_pfs_data', $pfs_data );

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'pmc-post-listing-filter-chosen', plugins_url( 'chosen/chosen.css', __FILE__ ) );
		wp_enqueue_style( 'pmc-post-listing-filter-styles', plugins_url( 'css/styles.css', __FILE__ ) );
	}

	/**
	 * Check which post types will support filtering.
	 * @param WP_Query $query
	 * @return bool
	 */
	function check_post_type( $query = null ) {
		// Check that we're on the right screen.
		// If a query was provided, also ensure the post type matches.
		global $current_screen;

		$this->_default_post_types = $this->get_default_post_types();

		if ( ( isset( $current_screen->post_type ) && in_array( $current_screen->post_type, $this->_default_post_types ) )
			&& ( empty( $query ) || ( ! empty( $query ) && $query->get( 'post_type' ) == $current_screen->post_type ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Default post types.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @since 2018-06-01 READS-1155
	 *
	 * @return array $this->_default_post_types updated post types.
	 */
	public function get_default_post_types() {

		static $applied_filter = false;

		if ( ! $applied_filter ) {
			$this->_default_post_types = apply_filters( 'pmc_post_listing_filters_post_types', $this->_default_post_types );
			$applied_filter            = true;
		}

		return $this->_default_post_types;
	}

	/**
	 * Prepare variable that will be used to select drop down for saved queries.
	 * @param $key
	 * @param $value
	 */
	function fill_query_for_html_select( $key, $value ) {
		switch ( $key ) {
			case 's':
			case "post_status":
				$this->_select_filler[$key] = $value;
			break;

			default:
				foreach ( $value as $i => $tax ) {
					if ( isset( $tax['taxonomy'] ) ) {
						$this->_select_filler[$tax['taxonomy']] = $tax['terms'];
					}
				}
			break;
		}
	}

	/**
	 * Get saved queries from long options
	 * @param        $query
	 * @param string $args
	 */
	function pre_get_saved_filter( $query, $args="" ){

		$saved_query = $this->get_user_saved_search_filters();

		if ( !empty( $saved_query ) && isset( $saved_query[$args]['query'] ) && is_array( $saved_query[$args]['query'] ) ) {
			$query_filter = $saved_query[$args]['query'];
			foreach ( $query_filter as $key => $value ) {
				$this->fill_query_for_html_select( $key, $value );
				$query->set( $key, $value );
			}
		}
	}

	/**
	 * Filters the post using the $_POST we get from restrict_manage_posts
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function pre_get_posts( $query ) {
		global $pagenow;

		static $is_sub_query = false;

		// Bail out if not edit and not in post edit
		if ( !is_admin() || $pagenow !== 'edit.php' ) {
			return;
		}

		// Bail out if not supported post type
		if ( !$this->check_post_type( $query ) ) {
			return;
		}

		// Bail out if none of the filters is sent
		if ( empty( $_GET['s'] ) && empty( $_GET['pmc-post-listing-filters'] ) ) {
			return;
		}

		// Grab filter params, returning array data
		$query_args = isset( $_GET['pmc-post-listing-filters'] ) ? $_GET['pmc-post-listing-filters'] : array();

		//Check if we want to delete and saved query
		if ( !empty( $_GET['pmc-post-listing-delete-query'] ) ) {
			$this->delete_custom_filter( sanitize_text_field( $_GET['pmc-post-listing-delete-query'] ) );
			return;
		}

		// Special handling for saved query. If saved query is sent, then other filters will be ignored and only saved query will be run.
		if ( !empty( $query_args['saved-query'][0] ) ) {
			$this->pre_get_saved_filter( $query, sanitize_text_field( $query_args['saved-query'][0] ) );
			return;
		}

		$tax_query = array();
		$meta_query = array();

		foreach ( $query_args as $key => $values ) {

			if ( empty( $key ) || empty( $values ) ) {
				continue;
			}

			// Seems like empty saved query is sent here, dont consider it.
			if ( 'saved-query' == $key ) {
				continue;
			}

			if ( is_array( $values ) ) {
				array_walk( $values, 'sanitize_text_field' );
			} else {
				$values = sanitize_text_field( $values );
			}

			// if query filter is a post status, we need to query by post status
			if ( 'post_status' == $key ) {
				$query->set( 'post_status', $values );
				$this->_query['post_status'] = $values;
			}
			// else we check for valid taxonomy to construct the taxonomy query
			else if ( taxonomy_exists( $key ) ) {
				$tax_query[] = array(
					'taxonomy' => sanitize_text_field( $key ),
					'field'    => 'slug',
					'terms'    => $values
				);
			}
			// otherwise, we construct the meta query
			else {
				$meta_query[] = array(
					'key'	=> sanitize_text_field( $key ),
					'value'	=> $values,
					'compare' => ( is_array( $values ) ) ? 'IN' : 'LIKE',
				);
			}
		}

		// All query's are "AND" query
		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
			$this->_query['tax_query'] = $tax_query;
		}
		if ( ! empty( $meta_query ) ) {
			$meta_query['relation'] = 'AND';
			$query->set( 'meta_query', $meta_query );
			$this->_query['meta_query'] = $meta_query;
		}
		$this->_query['s'] = get_query_var('s');

		if ( $this->should_use_elasticsearch() && ! $is_sub_query ) {
			$query->set( 'es', true );
			$is_sub_query = true;
		}

		return $query;
	}

	/**
	 * Tells whether we should use ES or not.
	 *
	 * @return boolean true if Cheezcap says yes, false if not.
	 */
	public function should_use_elasticsearch(): bool {

		// Setting Elasticsearch to true in WP Query if Cheezcap Setting is turned on.
		$should_use_es = PMC_Cheezcap::get_instance()->get_option( 'pmc_post_listing_filters_es_query' );

		return (bool) ( 'yes' === $should_use_es );
	}

	/**
	 * Renders post status option html tag
	 */
	function render_post_status_options() {
		$post_statuses = array_keys( get_post_stati() );

		foreach ( $post_statuses as $status ) {
			?>
		<option <?php $this->selected( $status, $this->get_selector( 'post_status' ) ); ?> value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status ); ?>
		</option>

		<?php
		}
	}

	/**
	 * Renders Edit flow select html tag
	 */
	function render_edit_flow_select() {
		if ( !class_exists( 'EF_Editorial_Metadata' ) ) {
			return;
		}
		// Check to make sure taxonomy exist first
		if ( !taxonomy_exists( EF_Editorial_Metadata::metadata_taxonomy ) ) {
			return;
		}
		$terms = get_terms( EF_Editorial_Metadata::metadata_taxonomy, 'hide_empty=0' );
		// get_terms might return wp errors
		if ( is_wp_error( $terms ) ) {
			return;
		}
		$EF_Editorial_Metadata = new EF_Editorial_Metadata();

		?>
		<td class="pmc-filter-label">EditFlow Filters :</td>
		<td class="pmc-filter-field"><select size="1" data-placeholder="Choose..." class="chzn-select" multiple name="pmc-post-listing-filters[<?php echo esc_attr( EF_Editorial_Metadata::metadata_taxonomy ); ?>][]">
				<?php
					foreach ( $terms as $term ) :
						$term = $EF_Editorial_Metadata->get_editorial_metadata_term_by( 'slug', $term->slug);
						if ( empty($term->type) || 'checkbox' != $term->type ) {
							continue;
						}
					?>
					<option <?php $this->selected( $term->slug, $this->get_selector( EF_Editorial_Metadata::metadata_taxonomy ) ); ?> value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach;?>
			</select>
		</td>
	<?php
	}

	function selected( $needle, $haystack = "", $echo = true ) {
		if ( empty( $haystack ) ) {
			return;
		}
		if ( in_array( $needle, $haystack ) ) {
			return selected( "1", "1", $echo );
		}
	}

	/**
	 * Which option is selected in <select/>
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function get_selector( $key = "" ) {
		if ( empty( $key ) ) {
			return;
		}

		//Dont select anything if we're deleting saved query
		if ( !empty( $_GET['pmc-post-listing-delete-query'] ) ) {
			return;
		}

		// we select saved-query always from get variable
		if ( 'saved-query' !== $key && !empty( $this->_select_filler ) ) {
			if ( isset( $this->_select_filler[$key] ) ) {
				return $this->_select_filler[$key];
			}
		}

		if ( ! empty( $_GET['pmc-post-listing-filters'][$key] ) ) {
			return $_GET['pmc-post-listing-filters'][$key];
		}

	}

	function get_user_saved_search_filters() {
		$wp_user = wp_get_current_user();
		$saved_filters = get_user_attribute( $wp_user->ID, 'pmc-post-listing-filters' );

		if ( ! $saved_filters ) {
			return false;
		}

		return $saved_filters;
	}

	function update_user_saved_search_filters( $saved_filteres ) {
		$wp_user = wp_get_current_user();
		update_user_attribute ( $wp_user->ID, 'pmc-post-listing-filters', $saved_filteres );
	}

	/**
	 * Renders Edit flow select html tag
	 */
	function render_saved_filter() {
		$saved_query = $this->get_user_saved_search_filters();

		?>
		<tr style="height: 60px;">
			<?php if ( !empty( $saved_query ) ) : ?>
				<td>Quick Filters :</td>
				<td><select id="pmc-post-listing-filters-saved-query" data-placeholder="Choose..." class="chzn-select" name="pmc-post-listing-filters[saved-query][]">
						<option></option>
						<?php foreach ( $saved_query as $slug => $query ) : ?>
							<option <?php $this->selected( $slug, $this->get_selector( 'saved-query' ) ); ?> value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $query['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			<?php endif; ?>

			<?php if ( empty( $saved_query ) || count( $saved_query ) < self::MAX_SAVED_SEARCH_FILTER ) : ?>
				<td>
					<input id="pmc-post-save-filter" type="button" value="Save Filter" class="button pmc-post-listing-button">
				</td>
			<?php endif;

			if ( !empty( $saved_query ) ) : ?>
				<td>
					<input id="pmc-post-delete-filter" type="button" value="Delete Filter" class="button pmc-post-listing-delete-button">
					<input type="hidden" name="pmc-post-listing-delete-query" id="pmc-post-listing-delete-query">
				</td>
			<?php endif; ?>
		</tr>
	<?php
	}

	/**
	 * Delete saved query.
	 *
	 * @param string $slug
	 */
	function delete_custom_filter( $slug = "" ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $_GET[self::NONCE_NAME] ), self::NONCE_ACTION ) ) {
			return;
		}

		$slug        = sanitize_text_field( $slug );
		$saved_query = $this->get_user_saved_search_filters();

		if ( !empty( $saved_query ) ) {
			unset( $saved_query[$slug] );
			$this->update_user_saved_search_filters( $saved_query );
		}
	}

	/**
	 * Save custom filter is long option
	 */
	function save_custom_filters() {

		if ( ! isset( $_GET[self::NONCE_NAME] ) || ! wp_verify_nonce( sanitize_text_field( $_GET[self::NONCE_NAME] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( isset( $_GET['pmc-post-listing-save-filter'] ) ) {

			$title = sanitize_text_field( $_GET['pmc-post-listing-save-filter'] );

			if ( !empty( $title ) && !empty( $this->_query ) ) {
				$saved_query = $this->get_user_saved_search_filters();

				if ( empty( $saved_query ) ) {
					$saved_query = array();
				}

				$slug               = sanitize_title_with_dashes( $title );
				$saved_query[$slug] = array(
					'title' => $title,
					'query' => $this->_query
				);

				$this->update_user_saved_search_filters( $saved_query );
			}
		}
	}

	// refactor into function to be re-implement at a later stage: ppt-702
	// current anticipate author list to be around 200 entries...
	function render_author_html_select() {
		global $coauthors_plus;

		$cache_expires = 300;
		$cache_key   = 'author_list';
		$cache_group = 'pmc_post_listing_filter';
		$author_list = wp_cache_get( $cache_key, $cache_group );
		$coauthors_plus_local = $coauthors_plus;

		if ( !isset( $coauthors_plus ) ) {
			$coauthors_plus_local = new coauthors_plus();
		}

		if ( empty ( $author_list ) ) {
			$author_list = array();
			$terms = get_terms( $coauthors_plus_local->coauthor_taxonomy, 'hide_empty=0' );
			foreach ( $terms as $term ) {
				$author = $coauthors_plus_local->get_coauthor_by( 'user_nicename', $term->slug );

				if ( !empty($author) ) {

					if ( is_a( $author, 'WP_User' ) ) {

						if ( in_array( 'subscriber', $author->roles ) ) {
							continue;
						}
						$display_name = $author->data->display_name;
					} else {
						$display_name = $author->display_name;
					}

					if ( $display_name != $term->name ) {
						$display_name .= " ({$term->name})";
					}
				} else {
					$display_name = $term->name;
				}
				$author_list[$term->slug] = $display_name;
			} // foreach
			wp_cache_set( $cache_key, $author_list, $cache_group, $cache_expires );
		} // if

		printf('<select size="1" data-placeholder="Choose..." class="chzn-select" multiple name="pmc-post-listing-filters[%s][]">', esc_attr( $coauthors_plus_local->coauthor_taxonomy ) );
		foreach( $author_list as $key => $value ) {
			printf( '<option %s value="%s">%s</option>',
						$this->selected( $key, $this->get_selector( $coauthors_plus_local->coauthor_taxonomy ), false ),
						esc_attr( $key ),
						esc_html( $value ) );
		}
		echo "</select>";
	}

	/**
	 * Renders all the select html tags for filtering the posts
	 */
	function restrict_manage_posts() {

		global $typenow;

		$this->_default_post_types = $this->get_default_post_types();

		if ( !in_array( $typenow, $this->_default_post_types ) ) {
			return;
		}

		$this->save_custom_filters();

		$taxonomies = apply_filters( 'pmc_post_listing_filters_taxonomies', $this->_default_taxonomy );

		global $coauthors_plus;

		$coauthors_plus_local = $coauthors_plus;

		if ( !isset( $coauthors_plus ) ) {
			$coauthors_plus_local = new coauthors_plus();
		}

		if ( empty( $taxonomies ) ) {
			return;
		}

		?>
		<div id="post-list-filter-panel" class="pmc-post-list-wrapper">
			<?php
			// IMPORTANT: need to call wp_nonce_field here insize div block, otherwise nonce data won't submit due to script.js trigger div move/replacement
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
			?>
			<div style="float: left;">
				<table>
					<?php
						// Add custom fields.
						// For now, they must preload their options using the Chosen format.
						$custom_fields = apply_filters( 'pmc_post_listing_filters_custom_fields', array() );
						if ( ! empty( $custom_fields ) ):
							?>
							<tr>
							<?php
							$field_count = 0;
							foreach( $custom_fields as $id => $field ):
								// Validate the field data
								if ( empty( $field['label'] ) ) {
									continue;
								}

								// Don't display too many fields in one row
								if ( 0 === $field_count % 3 && count( $custom_fields ) > $field_count ) {
									echo '</tr><tr>';
								}
								?>
								<td id="<?php echo esc_attr( $id . '-filter-label' ) ?>" class="pmc-filter-label">
									<?php echo esc_html( $field['label'] ) . ":"; ?>
								</td>
								<td id="<?php echo esc_attr( $id . '-filter-field' ) ?>" class="pmc-filter-field">
									<?php if ( empty( $field['data'] ) ): ?>
									<input type="text" name="pmc-post-listing-filters[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $this->get_selector( $id ) ) ?>" />
									<?php else: ?>
									<select size="1" data-placeholder="Choose..." class="chzn-select" multiple name="pmc-post-listing-filters[<?php echo esc_attr( $id ); ?>][]" >
										<?php foreach ( $field['data'] as $value => $name ) : ?>
											<option <?php $this->selected( $value, $this->get_selector( $id ) ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php endif; ?>
								</td>
								<?php
								$field_count++;
							endforeach;
							?>
							</tr>
							<?php
						endif;
					?>
				</table>
				<table>
					<tr>
						<!-- Render select for all taxonomy. -->
						<?php
							foreach ( $taxonomies as $tax ) :
								if ( !taxonomy_exists( $tax ) ) {
									continue;
								}
								$terms = get_terms( $tax, 'hide_empty=0' );
								$attr_id = sanitize_key( $tax ) . '-filter';
								$tax_name = preg_replace( '/[\_\-]/', ' ', $tax );
								$tax_name = ( empty( $tax_name ) ) ? $tax : $tax_name;
						?>
							<td id="<?php echo esc_attr( $attr_id . '-label' ) ?>" class="pmc-filter-label">
								<?php echo esc_html( ucwords( $tax_name ) ) . ':'; ?>
							</td>
							<td id="<?php echo esc_attr( $attr_id . '-field' ) ?>" class="pmc-filter-field">
								<select size="1" data-placeholder="Choose..." class="chzn-select" multiple name="pmc-post-listing-filters[<?php echo esc_attr( $tax ); ?>][]" >
									<?php foreach ( $terms as $term ) : ?>
										<option <?php $this->selected( $term->slug, $this->get_selector( $tax ) ); ?> value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
									<?php endforeach; ?>

								</select>
							</td>
						<?php endforeach; ?>

						<!-- Co-authors -->
						<td id="author-filter-label" rowspan="2" style="vertical-align:top" class="pmc-filter-label">Author :</td>

						<td id="author-filter-field" rowspan="2" style="vertical-align:top" class="pmc-filter-field post-listing-filters-author">
							<?php
							$selectors = $this->get_selector( 'author' );
							if ( empty( $selectors ) ) {
								$selectors = array();
							}
							?>
							<div id="pfs-authors-list">
								<?php foreach( $selectors as $author ) : ?>
									<div class="pfs-author-row">
										<?php echo esc_html( str_replace( 'cap-', '', $author ) ); ?>
										<span class="delete-button" style="padding-left: 4px;">&times;</span>
										<input class="pfs-author-hidden-input" name="pmc-post-listing-filters[author][]" type="hidden" value="<?php echo esc_attr( $author ); ?>">
									</div>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>

					<tr>
						<!-- Post Status -->
						<td style="vertical-align:top" class="pmc-filter-label">Status :</td>
						<td style="vertical-align:top" class="pmc-filter-field">
							<select size="1" data-placeholder="Choose..." class="chzn-select" multiple name="pmc-post-listing-filters[post_status][]">
								<?php $this->render_post_status_options(); ?>
							</select>
						</td>

						<td style="vertical-align:top" colspan="2" class="pmc-filter-field">
							<span>Search keywords : </span><span id="search-input-holder">
								<input type="search" value="<?php echo esc_attr( get_query_var('s') );?>" name="s" id="post-filter-search">
							</span>
						</td>

						<!-- Edit flow options -->
						<?php $this->render_edit_flow_select(); ?>
					</tr>

					<tr>
						<td colspan="6" align="right">
							<input id="post-filter-clear" type="button" value="Clear Filters" class="button" >
							<input id="post-filter-submit" type="submit" value="Filter Posts" class="button">
						</td>
					</tr>

					<!-- Saved filters -->
					<?php $this->render_saved_filter(); ?>
				</table>
				<div id="pmc-post-listing-dialog-modal" title="Save Filter" style="display: none;">
					<div>
						Name: <input type="text">
					</div>
				</div>
				<input type="hidden" name="pmc-post-listing-save-filter" id="pmc-post-listing-save-filter">
			</div>
		</div>
	<?php
	}

}

//EOF
