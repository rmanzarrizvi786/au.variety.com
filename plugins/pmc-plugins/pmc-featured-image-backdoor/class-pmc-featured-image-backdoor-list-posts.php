<?php

/**
 * Posts List Table class.
 *
 * @since 0.9.0
 * @version 0.9.0
 */
class PMC_Featured_Image_Backdoor_List_Table extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'plural' => 'posts',
		) );
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
				'edit_flow'  => 'Edit Flow',
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

		$mode = empty( $_REQUEST['mode'] ) ? 'list' : sanitize_text_field( $_REQUEST['mode'] );

		$this->is_trash = ! empty( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash';

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
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['author'] ) ) {
			echo '<input type="hidden" name="author" value="' . esc_attr( $_REQUEST['author'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['category_name'] ) ) {
			echo '<input type="hidden" name="category_name" value="' . esc_attr( $_REQUEST['category_name'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['tag'] ) ) {
			echo '<input type="hidden" name="tag" value="' . esc_attr( $_REQUEST['tag'] ) . '" />';
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
		       value="<?php echo esc_attr( ! empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all' ); ?>"/>
		<?php
	}

	function get_views() {
		global $post_type_object, $locked_post_status, $avail_post_stati;

		$post_type = $post_type_object->name;

		if ( ! empty( $locked_post_status ) ) {
			return array();
		}

		$status_links = array();
		$num_posts    = wp_count_posts( $post_type, 'readable' );
		$class        = '';

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		$class = empty( $class ) && empty( $_REQUEST['post_status'] ) ? ' class="current"' : '';

		$base_url = $_SERVER['REQUEST_URI'];
		$base_url = remove_query_arg( 'post_status', $base_url );
		$base_url = remove_query_arg( 'paged', $base_url );

		$status_links['all'] = '<a href="' . esc_url( $base_url ) . '" ' . $class . '>' . wp_kses_post( sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( 'trash' === $status_name ) {
				continue;
			}

			if ( ! in_array( $status_name, $avail_post_stati ) ) {
				continue;
			}

			if ( empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( ! empty( $_REQUEST['post_status'] ) && $status_name === $_REQUEST['post_status'] ) {
				$class = ' class="current"';
			}

			$status_url = add_query_arg( 'post_status', $status_name, $base_url );

			$status_links[ $status_name ] = '<a href="' . esc_url( $status_url ) . '" ' . $class . '>' . wp_kses_post( sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) ) . '</a>';
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
			$views[ $class ] = "\t<li class='$class'>$view";
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

		// There are currently no bulk actions to perform

		return $actions;
	}

	function extra_tablenav( $which ) {
		global $post_type_object, $cat;
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which && ! is_single() ) {

				$this->months_dropdown( $post_type_object->name );

				if ( is_object_in_taxonomy( $post_type_object->name, 'category' ) ) {
					$dropdown_options = array(
						'show_option_all' => __( 'View all categories', 'pmc_featured_image_backdoor' ),
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
				submit_button( __( 'Filter', 'pmc_featured_image_backdoor' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
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
			$posts_columns['author'] = __( 'Author', 'pmc_featured_image_backdoor' );
		}

		if ( empty( $post_type ) || is_object_in_taxonomy( $post_type, 'category' ) ) {
			$posts_columns['categories'] = __( 'Categories', 'pmc_featured_image_backdoor' );
		}

		if ( empty( $post_type ) || is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
			$posts_columns['tags'] = __( 'Tags', 'pmc_featured_image_backdoor' );
		}

		$posts_columns['edit_flow'] = _( 'Edit Flow', 'pmc_featured_image_backdoor' );

		$posts_columns['date'] = __( 'Date', 'pmc_featured_image_backdoor' );

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

		$lock_holder = wp_check_post_lock( $post->ID );
		if ( $lock_holder ) {
			$classes .= ' wp-locked';
		}

		?>
		<tr id="post-<?php echo esc_attr( $post->ID ); ?>"
		    class="<?php echo esc_attr( implode( ' ', get_post_class( $classes, $post->ID ) ) ); ?>" valign="top">
			<?php

			$base_url = $_SERVER['REQUEST_URI'];
			$base_url = remove_query_arg( 'paged', $base_url );

			list( $columns, $hidden ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {
				$class = 'class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '"';

				$style = '';
				if ( in_array( $column_name, $hidden ) ) {
					$style = ' style="display:none;"';
				}

				$attributes_escaped                = "$class$style";

				switch ( $column_name ) {


					case 'title':

						$attributes_escaped = 'class="post-title page-title column-title"' . $style;
						?>
						<td <?php echo $attributes_escaped ?>>
						<a href="#" class="editinline" id="<?php echo esc_attr( $post->ID . '-featured-image' ) ?>"
						   style="display:block;"
						   data-image-id="<?php echo esc_attr( get_post_thumbnail_id( $post->ID ) ); ?>">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'thumbnail', array( 'style' => 'width:35px;height:35px;float:left;padding: 0 10px 5px 0;' ) );
							} else {
								echo '<div style="width:34px;height:34px;float:left;margin: 0 10px 5px 0;background-color:#CCC;border:1px solid #999;"></div>';
							}
							?>
						</a>

						<?php do_action( 'pmc_featured_image_backdoor_list_posts_row_html', $post ) ?>

						<strong><?php echo '<span class="' . esc_attr( 'original-title-' . $post->ID ) . '">' . esc_html( $title ) . '</span>';
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
								$locked_text   = sprintf( __( '%s is currently editing', 'pmc_featured_image_backdoor' ), $lock_holder->display_name );
							} else {
								$locked_avatar = $locked_text = '';
							}

							echo '<div class="locked-info"><span class="locked-avatar">' . wp_kses_post( $locked_avatar ) . '</span> <span class="locked-text">' . esc_html( $locked_text ) . "</span></div>\n";
						}

						$actions = array();
						if ( $can_edit_post && 'trash' !== $post->post_status ) {
							$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="Set Featured Image">Set Featured Image</a>';
						}

						if ( $post_type_object->public ) {
							if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
								if ( $can_edit_post ) {
									$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'pmc_featured_image_backdoor' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'pmc_featured_image_backdoor' ) . '</a>';
								}
							} elseif ( 'trash' !== $post->post_status ) {
								$actions['view'] = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'pmc_featured_image_backdoor' ), $title ) ) . '" rel="permalink">' . __( 'View', 'pmc_featured_image_backdoor' ) . '</a>';
							}
						}

						$actions = apply_filters( 'post_row_actions', $actions, $post );
						$actions = apply_filters( 'pmc_featured_image_backdoor_post_row_actions', $actions, $post );
						echo wp_kses_post( $this->row_actions( $actions ) );
						echo '</td>';
						break;

					case 'date':
						if ( '0000-00-00 00:00:00' === $post->post_date && 'date' === $column_name ) {
							$t_time    = $h_time = __( 'Unpublished', 'pmc_featured_image_backdoor' );
							$time_diff = 0;
						} else {
							$t_time = get_the_time( 'Y/m/d g:i:s A' );
							$m_time = $post->post_date;
							$time   = get_post_time( 'G', true, $post );

							$time_diff = time() - $time;

							if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
								$h_time = sprintf( __( '%s ago', 'pmc_featured_image_backdoor' ), human_time_diff( $time ) );
							} else {
								$h_time = mysql2date( 'Y/m/d', $m_time );
							}
						}

						echo '<td ' . $attributes_escaped . '>';
						if ( 'excerpt' === $mode ) {
							echo esc_html( apply_filters( 'post_date_column_time', $t_time, $post, $column_name, $mode ) );
						} else {
							echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, $column_name, $mode ) ). '</abbr>';
						}
						echo '<br />';
						if ( 'publish' === $post->post_status ) {
							_e( 'Published', 'pmc_featured_image_backdoor' );
						} elseif ( 'future' === $post->post_status ) {
							if ( $time_diff > 0 ) {
								echo '<strong class="attention">' . esc_html__( 'Missed schedule', 'pmc_featured_image_backdoor' ) . '</strong>';
							} else {
								esc_html_e( 'Scheduled', 'pmc_featured_image_backdoor' );
							}
						} else {
							esc_html_e( 'No Tags', 'pmc_featured_image_backdoor' );
						}
						echo '</td>';
						break;

					case 'categories':
						?>
						<td <?php echo $attributes_escaped ?>><?php
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
								_e( 'Uncategorized', 'pmc_featured_image_backdoor' );
							}
							?></td>
						<?php
						break;

					case 'tags':
						?>
						<td <?php echo $attributes_escaped ?>><?php
							$tags = get_the_tags( $post->ID );
							if ( ! empty( $tags ) ) {
								$out = array();
								foreach ( $tags as $c ) {
									$term_field = sanitize_term_field( 'name', $c->name, $c->term_id, 'tag', 'display' );
									$out[] = sprintf( '<a href="%s">%s</a>',
										esc_url( add_query_arg( 'tag', $c->slug, $base_url ) ),
										esc_html( $term_field )
									);
								}
								/* translators: used between list items, there is a space after the comma */
								echo join( ', ', $out );
							} else {
								esc_html_e( 'No Tags', 'pmc_featured_image_backdoor' );
							}
							?></td>
						<?php
						break;

					case 'author':
						?>
						<td <?php echo $attributes_escaped ?>><?php
							printf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( 'author', get_the_author_meta( 'ID' ), $base_url ) ),
								esc_html( get_the_author() )
							);
							?></td>
						<?php
						break;

					case 'edit_flow':
						$edit_flow = EditFlow();
						$editorial_metadata_fields = $edit_flow->editorial_metadata->get_editorial_metadata_terms();
						?>
						<td <?php echo $attributes_escaped ?>>

							<?php foreach ( $editorial_metadata_fields as $field ) :
								$meta_key = $edit_flow->editorial_metadata->get_postmeta_key( $field );
								$meta_value = get_post_meta( $post->ID, $meta_key, true );
								?>
								<?php if ( empty ( $meta_value ) || empty( $field->viewable ) ) {
								continue;
							} ?>

								<strong><?php echo esc_html( $field->name ); ?>:</strong>
								<?php if ( 'date' === $field->type ) : ?>
								<?php echo esc_html( date( 'g:i A F j, Y', $meta_value ) ); ?>
							<?php elseif ( 'checkbox' === $field->type ) : ?>
								<?php
								if ( 1 === $meta_value ) {
									esc_html_e( 'Yes', 'pmc_featured_image_backdoor' );

								} else {
									esc_html_e( 'No', 'pmc_featured_image_backdoor' );

								}
								?>
							<?php else : ?>
								<?php echo esc_html( $meta_value ); ?>
							<?php endif; ?>
								<br/>
							<?php endforeach; ?>

						</td>
						<?php
						break;

					default:
						?>
						<td <?php echo $attributes_escaped ?>><?php
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

		wp_reset_postdata();
		$post = $global_post;
	}
}
