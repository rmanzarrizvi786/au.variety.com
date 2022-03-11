<?php
/**
 * Uber Navigation Walker
 *
 * @package pmc-core-v2
 */

namespace PMC\Core\Inc\Menus;

class Uber_Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Keep track of the latest top-level item
	 *
	 * @var WP_Post
	 */
	public $current_parent_object;

	/**
	 * Base $db_fields. Necessary for Walker_Nav_Menu.
	 *
	 * @var array
	 */
	public $db_fields = array(
		'parent' => 'menu_item_parent',
		'id'     => 'db_id',
	);

	/**
	 * Beginning of a new element.
	 *
	 * @param string   &$output
	 * @param WP_Post  $item
	 * @param integer  $depth
	 * @param stdClass $args An object of wp_nav_menu() arguments.
	 * @param int      $id   Current item ID.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

		// Save the top level item, and only display non-top level items
		if ( 0 === $depth ) {
			$this->current_parent_object = $item;
		} else {
			parent::start_el( $output, $item, $depth, $args, $id );
		}
	}

	/**
	 * Beginning of a new level.
	 *
	 * @param string   &$output
	 * @param int      $depth Depth of menu item. Used for padding.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		// Build and sanitize menu name
		$menu_name = sanitize_title_with_dashes( $this->current_parent_object->title );

		// Open the left column
		$output .= '<div id="uber-nav-' . esc_attr( $menu_name ) . '" class="uber-nav__panel">';
		$output .= '<div class="uber-nav__menu">';
		$output .= '<ul class="uber-nav__menu-list">';
	}

	/**
	 * End of a new level
	 *
	 * @param string   &$output
	 * @param int      $depth Depth of menu item. Used for padding.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		// Prep $latest_posts_args
		$latest_posts_args = array(
			'post_type'           => [ 'post', 'pmc-gallery' ],
			'posts_per_page'      => 3,
			'post_status'         => 'publish',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		// If $current_parent_object is a category, add additional args
		if ( 'taxonomy' === $this->current_parent_object->type && 'category' === $this->current_parent_object->object ) {
			$latest_posts_args['cat'] = $this->current_parent_object->object_id;
		}

		// VIP: Cache query for 5 mins to avoid duplicate queries hitting the DB
		$cache_key = md5( serialize( $latest_posts_args ) );
		if ( false === ( $latest_posts = wp_cache_get( $cache_key, 'uber-menu' ) ) ) {
			// Execute $latest_posts query
			$latest_posts = new \WP_Query( $latest_posts_args );
			wp_cache_set( $cache_key, $latest_posts, 'uber-menu', MINUTE_IN_SECONDS * 5 );
		}

		// Parent item setup
		$parent_title = strtoupper( sprintf( __( 'All %1$s', 'pmc-core' ), $this->current_parent_object->title ) );
		$parent_url   = $this->current_parent_object->url;

		// Begin output.
		$output .= '</ul>';

		// All X link below menu items
		$output .= '<a href="' . esc_url( $parent_url ) . '" class="uber-nav__menu-all">' . esc_html( $parent_title ) . '</a>';

		$output .= '</div>';
		$output .= '<ul class="uber-nav__highlights">';

		// Display posts
		$x          = 0;
		$li_classes = array( 'uber-nav__highlight' );
		if ( $latest_posts->have_posts() ) {
			while ( $latest_posts->have_posts() ) {
				$latest_posts->the_post();

				if ( 0 === $x ) {
					$li_classes[] = 'uber-nav__highlight--primary';
				} else {
					$li_classes[] = 'uber-nav__highlight--secondary';
				}

				$output .= '<li class="' . implode( ' ', $li_classes ) . '">';

				// First one has an image
				if ( 0 === $x && has_post_thumbnail() ) {
					$output .= '<a href="' . esc_url( get_the_permalink() ) . '">';
					\PMC\Core\Inc\Theme::get_instance()->get_the_post_thumbnail( get_the_ID(), 'uber-nav', 'null', true,
						array( 'class' => 'uber-nav__highlight-img' ) );

					$output .= '</a>';
				}

				$output .= '<h3 class="uber-nav__highlight-title"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h3>';

				$output .= '</li>';

				// Increment $x
				$x ++;
			}
		}
		wp_reset_postdata();

		$output .= '</ul>';
		$output .= '</div>';
	}
}
