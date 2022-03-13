<?php

namespace PMC\SEO_Backdoor;

use \PMC;
use \WP_List_Table;
use \WP_Query;

/**
 * Posts List Table class.
 *
 * @since 0.9.0
 * @version 0.9.0
 */
class List_Posts extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'plural' => 'posts',
			'ajax'   => true,
		) );

		if ( is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'seo_ready_query' ) );
		}
	}

	/**
	 * Adjusts query to grab posts that are SEO Ready
	 *
	 * @since 2015-07-20
	 * @version 2015-07-20 Mike Auteri - PPT-5178
	 *
	 * @param $query
	 *
	 * @return void
	 */
	function seo_ready_query( $query ) {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'PMC_SEO_Backdoor' && empty( $_GET['post_status'] ) ) {
			$query->set( 'meta_query', array(
					array(
						'key'     => 'pmc_seo_ready',
						'compare' => 'EXISTS',
					)
				)
			);
		}
	}

	function ajax_user_can() {
		global $post_type_object;

		return current_user_can( $post_type_object->cap->edit_posts );
	}

	function prepare_items() {
		global $post_type_object, $avail_post_stati, $wp_query, $per_page, $mode;

		$avail_post_stati = wp_edit_posts_query();

		$total_items = $wp_query->found_posts;

		$this->_column_headers = array(
			array(
				'title'      => 'Title',
				'author'     => 'Author',
				'categories' => 'Categories',
				'tags'       => 'Tags',
				'date'       => 'Date'
			),
			array(),
			$this->get_sortable_columns(),
		);

		$post_type = $post_type_object->name;
		$per_page  = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );
		$per_page  = apply_filters( 'edit_posts_per_page', $per_page, $post_type );

		$total_pages = $wp_query->max_num_pages;

		$mode = empty( $_GET['mode'] ) ? 'list' : sanitize_text_field( $_GET['mode'] );

		$this->is_trash = ! empty( $_GET['post_status'] ) && 'trash' === sanitize_text_field( $_GET['post_status'] );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page'    => $per_page
		) );
	}

	function has_items() {
		return have_posts();
	}

	function no_items() {
		global $post_type_object;

		echo esc_html( $post_type_object->labels->not_found );
	}

	/**
	 * Display the search box.
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	function search_box( $text, $input_id ) {
		if ( empty( $_GET['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_GET['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_GET['orderby'] ) . '" />';
		}
		if ( ! empty( $_GET['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_GET['order'] ) . '" />';
		}
		if ( ! empty( $_GET['author'] ) ) {
			echo '<input type="hidden" name="author" value="' . esc_attr( $_GET['author'] ) . '" />';
		}
		if ( ! empty( $_GET['category_name'] ) ) {
			echo '<input type="hidden" name="category_name" value="' . esc_attr( $_GET['category_name'] ) . '" />';
		}
		if ( ! empty( $_GET['tag'] ) ) {
			echo '<input type="hidden" name="tag" value="' . esc_attr( $_GET['tag'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text"
			       for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
			       value="<?php esc_attr( _admin_search_query() ); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<input type="hidden" name="post_status" class="post_status_page"
		       value="<?php echo esc_attr( ! empty( $_GET['post_status'] ) ? $_GET['post_status'] : 'all' ); ?>"/>
		<?php
	}

	/**
	 * Get cached count of posts that are SEO Ready
	 *
	 * @since 2015-07-20
	 * @uses $post_type_object
	 * @version 2015-07-20 Mike Auteri - PPT-5178
	 *
	 * @return int
	 */
	function seo_count() {
		global $post_type_object;

		$count = wp_cache_get( 'pmc_seo_count', $post_type_object->name );
		if ( ! $count ) {
			$query = new WP_Query( array(
				'post_type'  => $post_type_object->name,
				'meta_query' => array(
					array(
						'key'     => 'pmc_seo_ready',
						'compare' => 'EXISTS',
					),
				),
			) );
			$count = $query->found_posts;
			wp_cache_set( 'pmc_seo_count', $count, $post_type_object->name, 3600 );
		}

		return $count;
	}

	/**
	 * Get filtered links for different views of data in subsubsub
	 *
	 * @since 2015-07-20
	 * @uses $post_type_object, $locked_post_status, $avail_post_stati
	 * @version 2015-07-20 Mike Auteri - PPT-5178
	 *
	 * @return string
	 */
	function get_views() {
		global $post_type_object, $locked_post_status, $avail_post_stati;

		$post_type = $post_type_object->name;

		if ( ! empty( $locked_post_status ) ) {
			return array();
		}

		$status_links = array();
		$num_posts    = wp_count_posts( $post_type, 'readable' );
		$allposts     = '';

		$current_user_id = get_current_user_id();

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		$seo_class = '';
		$all_class = '';

		$seo_class = empty( $_GET['post_status'] ) ? 'current' : '';
		if ( ! empty( $_GET['post_status'] ) ) {
			$all_class = 'all' === $_GET['post_status'] ? 'current' : '';
		}
		$seo_count = $this->seo_count();

		$base_url = $_SERVER['REQUEST_URI'];
		$base_url = remove_query_arg( 'post_status', $base_url );
		$base_url = remove_query_arg( 'paged', $base_url );


		$status_links['seo-ready'] = '<a href="' . esc_url( $base_url ) . '" class="' . esc_attr( $seo_class ) . '">' . wp_kses_post( sprintf( _nx( 'SEO Ready <span class="count">(%s)</span>', 'SEO Ready <span class="count">(%s)</span>', intval( $seo_count ), 'posts' ), number_format_i18n( $seo_count ) ) ) . '</a>';

		$status_links['all'] = '<a href="' . esc_url( add_query_arg( 'post_status', 'all', $base_url ) ) . '" class="' . esc_attr( $all_class ) . '">' . wp_kses_post( sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( 'trash' === $status_name ) {
				continue;
			}

			if ( ! in_array( $status_name, $avail_post_stati, true ) ) {
				continue;
			}

			if ( empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( ! empty( $_GET['post_status'] ) && $status_name === $_GET['post_status'] ) {
				$class = 'current';
			}

			$status_url = add_query_arg( 'post_status', $status_name, $base_url );

			$status_links[ $status_name ] = '<a href="' . esc_url( $status_url ) . '" class="' . esc_attr( $class ) . '">' . wp_kses_post( sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) ) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Display the list of views available on this table.
	 */
	function views() {
		$screen = get_current_screen();

		$views = $this->get_views();
		$views = apply_filters( 'views_' . $screen->id, $views );

		if ( empty( $views ) ) {
			return;
		}

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			// $view is markup and cannot be escaped here.
			// $view was escaped in get_views() method
			$views[ $class ] = "\t<li class='" . esc_attr( $class ) . "'>" . $view;
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo "</ul>";

		if ( ! empty( $_GET['author'] ) || ! empty( $_GET['category_name'] ) || ! empty( $_GET['tag'] ) || ! empty( $_GET['s'] ) ) {
			$base_url = $_SERVER['REQUEST_URI'];
			$base_url = remove_query_arg( 'paged', $base_url );

			echo '<br class="clear" />';
			echo '<ul class="subsubsub">';
			echo '<li><strong>Active Filters (click to remove):</strong></li>';

			if ( ! empty( $_GET['author'] ) ) {
				echo '<li class="remove_author">';
				echo '<a class="add-new-h2" href="' . esc_url( remove_query_arg( 'author', $base_url ) ) . '" ' . $class . '>Author</a>';
				echo '</li>';
			}

			if ( ! empty( $_GET['category_name'] ) ) {
				echo '<li class="remove_category_name">';
				echo '<a class="add-new-h2" href="' . esc_url( remove_query_arg( 'category_name', $base_url ) ) . '" ' . $class . '>Category</a>';
				echo '</li>';
			}

			if ( ! empty( $_GET['tag'] ) ) {
				echo '<li class="remove_tag">';
				echo '<a class="add-new-h2" href="' . esc_url( remove_query_arg( 'tag', $base_url ) ) . '" ' . $class . '>Tag</a>';
				echo '</li>';
			}

			if ( ! empty( $_GET['s'] ) ) {
				echo '<li class="remove_search">';
				$url = remove_query_arg( array( 's', '_wpnonce', '_wp_http_referer', 'm', 'cat' ), $base_url );
				echo '<a class="add-new-h2" href="' . esc_url( $url ) . '" ' . $class . '>Search</a>';
				echo '</li>';
			}

			echo '</ul>';
		}
	}

	function get_bulk_actions() {
		$actions = array();

		// There are currently no SEO bulk actions to perform

		return $actions;
	}

	function extra_tablenav( $which ) {
		global $post_type_object, $cat;
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which && ! is_singular() ) {

				$this->months_dropdown( $post_type_object->name );

				if ( is_object_in_taxonomy( $post_type_object->name, 'category' ) ) {
					$dropdown_options = array(
						'show_option_all' => __( 'View all categories', 'pmc-seo-backdoor' ),
						'hide_empty'      => 0,
						'hierarchical'    => 1,
						'show_count'      => 0,
						'orderby'         => 'name',
						'selected'        => $cat
					);
					wp_dropdown_categories( $dropdown_options );
				}
				// @codeCoverageIgnoreStart
				do_action( 'restrict_manage_posts', $post_type_object->name, $which );
				// @codeCoverageIgnoreEnd
				submit_button( __( 'Filter', 'pmc-seo-backdoor' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
			}

			?>
		</div>
		<?php
	}

	function current_action() {
		return parent::current_action();
	}

	function pagination( $which ) {
		global $post_type_object, $mode;

		parent::pagination( $which );

		if ( 'top' === $which && ! $post_type_object->hierarchical ) {
			$this->view_switcher( $mode );
		}
	}

	function get_table_classes() {
		return array( 'widefat', 'fixed', 'posts' );
	}

	function get_columns() {
		$screen = get_current_screen();

		if ( empty( $screen ) ) {
			$post_type = 'post';
		} else {
			$post_type = $screen->post_type;
		}

		$posts_columns = array();

		/* translators: manage posts column name */
		$posts_columns['title'] = _x( 'Title', 'column name' );

		if ( post_type_supports( $post_type, 'author' ) ) {
			$posts_columns['author'] = __( 'Author', 'pmc-seo-backdoor' );
		}

		if ( empty( $post_type ) || is_object_in_taxonomy( $post_type, 'category' ) ) {
			$posts_columns['categories'] = __( 'Categories', 'pmc-seo-backdoor' );
		}

		if ( empty( $post_type ) || is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
			$posts_columns['tags'] = __( 'Tags', 'pmc-seo-backdoor' );
		}

		$posts_columns['date'] = __( 'Date', 'pmc-seo-backdoor' );

		if ( 'page' === $post_type ) {
			$posts_columns = apply_filters( 'manage_pages_columns', $posts_columns );
		} else {
			$posts_columns = apply_filters( 'manage_posts_columns', $posts_columns, $post_type );
		}
		$posts_columns = apply_filters( "manage_{$post_type}_posts_columns", $posts_columns );

		return $posts_columns;
	}

	function get_sortable_columns() {
		return array(
			'title'  => array( 'title', false ),
			'author' => array( 'author', false ),
			'parent' => array( 'parent', false ),
			'date'   => array( 'date', true )
		);
	}

	function display_rows( $posts = array() ) {
		global $wp_query;

		if ( empty( $posts ) ) {
			$posts = $wp_query->posts;
		}

		add_filter( 'the_title', 'esc_html' );

		$this->_display_rows( $posts );
	}

	function _display_rows( $posts ) {
		global $post;

		// Create array of post IDs.
		$post_ids = array();

		foreach ( $posts as $a_post ) {
			$post_ids[] = $a_post->ID;
		}

		foreach ( $posts as $post ) {
			$this->single_row( $post );
		}
	}

	function single_row( $a_post, $level = 0 ) {
		global $post, $mode;
		static $alternate;

		$global_post = $post;
		$post        = $a_post;
		setup_postdata( $post );

		$title            = _draft_or_post_title();
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

		$alternate = 'alternate' === $alternate ? '' : 'alternate';
		$classes   = $alternate . ' iedit author-' . ( get_current_user_id() === (int) $post->post_author ? 'self' : 'other' );

		$seo_ready = get_post_meta( $a_post->ID, 'pmc_seo_ready' ) ?: false;

		$lock_holder = wp_check_post_lock( $post->ID );
		if ( $lock_holder ) {
			$classes .= ' wp-locked';
		}

		?>
		<tr id="post-<?php echo esc_attr( $post->ID ); ?>"
		    class="<?php echo esc_attr( implode( ' ',  get_post_class( $classes, $post->ID ) ) ); ?>" valign="top">
			<?php

			// Initialize SEO variables
			$seo_title       = '';
			$seo_description = '';
			$seo_keywords    = '';
			/*
			 * @extra_meta
			$seo_extra_meta = '';
			 */
			$seo_slug = '';

			// Populate SEO vars with meta depending on which SEO plugin is enabled
			if ( class_exists( 'Add_Meta_Tags' ) ) {
				$seo_title       = get_post_meta( $post->ID, 'mt_seo_title', true );
				$seo_description = get_post_meta( $post->ID, 'mt_seo_description', true );
				$seo_keywords    = get_post_meta( $post->ID, 'mt_seo_keywords', true );
				/*
				 * @extra_meta
				$seo_extra_meta = get_post_meta( $post->ID, 'mt_seo_meta', true );
				 */
				$seo_slug = '';
			} elseif ( function_exists( 'wpseo_get_value' ) ) {
				$seo_title       = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
				$seo_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
				$seo_keywords    = get_post_meta( $post->ID, '_yoast_wpseo_metakeywords', true );
				/*
				 * @extra_meta
				$seo_extra_meta = '';
				 */
				$seo_slug = '';
			}

			// Fall back to PMC SEO meta if the vars are still empty
			if ( empty( $seo_title ) ) {
				$seo_title = get_post_meta( $post->ID, '_pmc_seo_title', true );
				if ( empty( $seo_title ) ) {
					$seo_title = $title;
				}
			}

			if ( empty( $seo_description ) ) {
				$seo_description = get_post_meta( $post->ID, '_pmc_seo_description', true );
			}

			if ( empty( $seo_keywords ) ) {
				$seo_keywords = get_post_meta( $post->ID, '_pmc_seo_keywords', true );
			}

			if ( empty( $seo_slug ) ) {
				$seo_slug = get_post_meta( $post->ID, '_pmc_seo_slug', true );
				if ( empty( $seo_slug ) ) {
					$seo_slug = apply_filters( 'editable_slug', $post->post_name );
				}
			}

			$base_url = $_SERVER['REQUEST_URI'];
			$base_url = remove_query_arg( 'paged', $base_url );

			list( $columns, $hidden ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {

				// Causing issues on Ajax call
				if ( 'editorial-metadata-needs-photo' === $column_name || 'status' === $column_name ) {
					continue;
				}
				$class = $column_name . ' column-' . $column_name;

				$style = '';
				if ( in_array( $column_name, $hidden, true ) ) {
					$style = 'display:none;';
				}

				switch ( $column_name ) {

					case 'title':

						$class = 'post-title page-title column-title';

						if ( $title === $seo_title ) {
							$dashicon = 'dashicons-no';
						} else {
							$dashicon = 'dashicons-yes';
						}
						$escaped_attributes = 'class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '"';
						?>
						<td <?php echo $escaped_attributes; ?>>
						<?php
						if ( $seo_ready ) {
							echo '<span title="SEO Ready" style="color: #a00; margin-right: 5px;" class="dashicons dashicons-flag alignleft"></span>';
						}
						?>
						<span class="dashicons <?php echo esc_attr( $dashicon ); ?> alignleft has-seo"></span>
						<strong><?php echo '<span class="original-title-' . intval( $post->ID ) . '">' . esc_html( $title ) . '</span>';
							esc_html( _post_states( $post ) ); ?></strong>
						<?php
						if ( 'excerpt' === $mode ) {
							the_excerpt();
						}

						if ( $can_edit_post && $post->post_status !== 'trash' ) {
							$lock_holder = wp_check_post_lock( $post->ID );

							if ( $lock_holder ) {
								$lock_holder   = get_userdata( $lock_holder );
								$locked_avatar = get_avatar( $lock_holder->ID, 18 );
								$locked_text   = sprintf( __( '%s is currently editing' ), $lock_holder->display_name );
							} else {
								$locked_avatar = $locked_text = '';
							}

							echo '<div class="locked-info"><span class="locked-avatar">' . esc_html( $locked_avatar ) . '</span> <span class="locked-text">' . esc_html( $locked_text ) . "</span></div>\n";
						}

						$actions = array();
						if ( $can_edit_post && 'trash' !== $post->post_status ) {
							$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr__( 'Edit this item inline', 'pmc-seo-backdoor' ) . '">' . esc_html__( 'Quick Edit', 'pmc-seo-backdoor' ) . '</a>';
						}

						if ( $post_type_object->public ) {
							if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ), true ) ) {
								if ( $can_edit_post ) {
									$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'pmc-seo-backdoor' ), $title ) ) . '" rel="permalink">' . esc_html__( 'Preview', 'pmc-seo-backdoor' ) . '</a>';
								}
							} elseif ( 'trash' !== $post->post_status ) {
								$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'pmc-seo-backdoor' ), $title ) ) . '" rel="permalink">' . esc_html__( 'View', 'pmc-seo-backdoor' ) . '</a>';
							}
						}

						$actions = apply_filters( 'post_row_actions', $actions, $post );
						echo $this->row_actions( $actions );

						echo '<div class="hidden" id="inline_' . intval( $post->ID ) . '">';
						echo '<div class="pmc_seo_title">' . esc_textarea( trim( $seo_title ) ) . '</div>';
						echo '<div class="pmc_seo_description">' . esc_textarea( trim( $seo_description ) ) . '</div>';
						echo '<div class="pmc_seo_keywords">' . esc_textarea( trim( $seo_keywords ) ) . '</div>';
						echo '<div class="pmc_seo_slug">' . esc_html( $seo_slug ) . '</div>';
						echo '<div class="post_status">' . esc_html( $post->post_status ) . '</div>';
						echo '<div class="post_content">' . esc_textarea( trim( $post->post_content ) ) . '</div>';
						echo '</div>'; // end hidden inline wrapper
						echo '</td>';
						break;

					case 'date':
						if ( '0000-00-00 00:00:00' === $post->post_date && 'date' === $column_name ) {
							$t_time    = $h_time = __( 'Unpublished', 'pmc-seo-backdoor' );
							$time_diff = 0;
						} else {
							$t_time = get_the_time( 'Y/m/d g:i:s A' );
							$m_time = $post->post_date;
							$time   = get_post_time( 'G', true, $post );

							$time_diff = time() - $time;

							if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
								$h_time = sprintf( __( '%s ago', 'pmc-seo-backdoor' ), human_time_diff( $time ) );
							} else {
								$h_time = mysql2date( 'Y/m/d', $m_time );
							}
						}

						echo '<td ' . $escaped_attributes . '>';
						if ( 'excerpt' === $mode ) {
							echo esc_html( apply_filters( 'post_date_column_time', $t_time, $post, $column_name, $mode ) );
						} else {
							echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, $column_name, $mode ) ) . '</abbr>';
						}
						echo '<br />';
						if ( 'publish' === $post->post_status ) {
							esc_html_e( 'Published', 'pmc-seo-backdoor' );
						} elseif ( 'future' === $post->post_status ) {
							if ( $time_diff > 0 ) {
								echo '<strong class="attention">' . __( 'Missed schedule', 'pmc-seo-backdoor' ) . '</strong>';
							} else {
								esc_html_e( 'Scheduled', 'pmc-seo-backdoor' );
							}
						} else {
							esc_html_e( 'Last Modified', 'pmc-seo-backdoor' );
						}
						echo '</td>';
						break;

					case 'categories':
						?>
						<td <?php echo $escaped_attributes ?>><?php
							$categories = get_the_category();
							if ( ! empty( $categories ) ) {
								$out = array();
								foreach ( $categories as $c ) {
									$term_field = sanitize_term_field( 'name', $c->name, $c->term_id, 'category', 'display' );
									$out[] = sprintf( '<a href="%s">%s</a>',
										esc_url( add_query_arg( 'category_name', $c->slug, $base_url ) ),
										esc_html( $term_field )
									);
								}
								/* translators: used between list items, there is a space after the comma */
								echo join( ', ', $out );
							} else {
								esc_html_e( 'Uncategorized', 'pmc-seo-backdoor' );
							}
							?></td>
						<?php
						break;

					case 'tags':
						?>
						<td <?php echo $escaped_attributes ?>><?php
							$tags = get_the_tags( $post->ID );
							if ( ! empty( $tags ) ) {
								$out = array();
								foreach ( $tags as $c ) {
									$term_field = sanitize_term_field( 'name', $c->name, $c->term_id, 'category', 'display' );
									$out[] = sprintf( '<a href="%s">%s</a>',
										esc_url( add_query_arg( 'tag', $c->slug, $base_url ) ),
										esc_html( $term_field )
									);
								}
								/* translators: used between list items, there is a space after the comma */
								echo join( ', ', $out );
							} else {
								esc_html_e( 'No Tags', 'pmc-seo-backdoor' );
							}
							?></td>
						<?php
						break;

					case 'author':
						?>
						<td <?php echo $escaped_attributes ?>><?php
							printf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( 'author', get_the_author_meta( 'ID' ), $base_url ) ),
								esc_html( get_the_author() )
							);
							?></td>
						<?php
						break;

					default:
						?>
						<td <?php echo $escaped_attributes ?>><?php
							do_action( 'manage_posts_custom_column', $column_name, $post->ID );
							do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
							?></td>
						<?php
						break;
				}
			}
			?>
		</tr>
		<?php
		$post = $global_post;
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 */
	function inline_edit() {
		global $mode;

		$screen = get_current_screen();

		$m = ( ! empty( $mode ) && 'excerpt' === $mode ) ? 'excerpt' : 'list';

		$core_columns = array( 'date'       => true,
		                       'title'      => true,
		                       'categories' => true,
		                       'tags'       => true,
		                       'author'     => true
		);
		?>
		<form method="get" action="">
			<table style="display: none">
				<tbody id="inlineedit">
				<?php
				/*
				 * @category
				$hclass = count( $hierarchical_taxonomies ) ? 'post' : 'page';
				 */
				$hclass     = 'post';
				$tr_classes = array(
					'inline-edit-row',
					'inline-edit-row-' . $hclass,
					'inline-edit-' . $screen->post_type,
					'quick-edit-row',
					'quick-edit-row-' . $hclass,
				);
				?>
				<tr id="inline-edit" class="inline-edit-row <?php echo esc_attr( implode( ' ', $tr_classes ) ); ?>"
				    style="display: none">
					<td colspan="<?php echo intval( $this->get_column_count() ); ?>" class="colspanchange">

						<fieldset class="inline-edit-col-left">
							<div class="inline-edit-col">
								<h4><?php esc_html_e( 'Quick Edit', 'pmc-seo-backdoor' ); ?></h4>

								<label>
									<span class="title"><?php esc_html_e( 'Title', 'pmc-seo-backdoor' ); ?></span>
									<span class="input-text-wrap"><input type="text" name="pmc_seo_title"
									                                     value=""/></span>
								</label>

								<label>
									<span class="title"><?php esc_html_e( 'Slug', 'pmc-seo-backdoor' ); ?></span>
									<span class="input-text-wrap"><input type="text" name="pmc_seo_slug"
									                                     value=""/></span>
								</label>

								<label>
									<span class="title"><?php esc_html_e( 'Desc.', 'pmc-seo-backdoor' ); ?></span>
									<span class="input-text-wrap"><textarea cols="22" rows="1"
									                                        name="pmc_seo_description"></textarea></span>
								</label>
								<label>
									<span class="title"><?php esc_html_e( 'Keywords', 'pmc-seo-backdoor' ); ?></span>
									<span class="input-text-wrap"><textarea cols="22" rows="1"
									                                        name="pmc_seo_keywords"></textarea></span>
								</label>
							</div>
						</fieldset>

						<fieldset class="inline-edit-col-center">
							<div class="inline-edit-col">
							</div>
						</fieldset>

						<fieldset class="inline-edit-col-right inline-edit-categories">
							<div class="inline-edit-col">
							</div>
						</fieldset>

						<?php
						$columns = $this->get_column_info();
						foreach ( $columns as $column_name => $column_display_name ) {
							if ( ! empty( $core_columns[ $column_name ] ) ) {
								continue;
							}
							do_action( 'quick_edit_custom_box', $column_name, $screen->post_type );
						}
						?>

						<textarea cols="22" rows="1" name="post_content"
						          style="clear: both; display: block; width: 100%; margin-top: 1px; margin-bottom: 1px; height: 175px; background-color: #eee;"
						          disabled="disabled"></textarea>

						<p class="submit inline-edit-save">
							<a accesskey="c" href="#inline-edit" title="<?php esc_attr_e( 'Cancel', 'pmc-seo-backdoor' ); ?>"
							   class="button-secondary cancel alignleft"><?php esc_html_e( 'Cancel', 'pmc-seo-backdoor' ); ?></a><span
								class="spinner waiting" style="float:left"></span>
							<?php
							wp_nonce_field( 'pmc-seo-inline-edit-nonce', '_pmc_seo_inline_edit', false );
							$update_text = __( 'Update', 'pmc-seo-backdoor' );
							?>
							<a accesskey="s" href="#inline-edit" title="<?php esc_attr_e( 'Update', 'pmc-seo-backdoor' ); ?>"
							   class="button-primary save alignright"><?php esc_attr_e( $update_text ); ?></a>

							<input type="hidden" name="post_view" value="<?php esc_attr_e( $m ); ?>"/>
							<input type="hidden" name="screen" value="<?php esc_attr_e( $screen->id ); ?>"/>
							<span class="error" style="display:none"></span>
							<br class="clear"/>
						</p>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
		<?php
	}
}
// EOF
