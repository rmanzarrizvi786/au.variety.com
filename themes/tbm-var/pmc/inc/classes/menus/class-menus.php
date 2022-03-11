<?php
/**
 * Menus
 *
 * The child theme menus.
 *
 * @package pmc-core-v2
 * @since   2017.1.0
 */

namespace PMC\Core\Inc\Menus;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Menus
 *
 * @since 2017.1.0
 * @see   \PMC\Global_Functions\Traits\Singleton
 */
class Menus {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Removes the parent theme menus and initializes the child theme menus.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct() {

		// Register the nav menus.
		add_action( 'init', [ $this, 'register_nav_menus' ] );

		add_filter( 'wp_nav_menu_container_allowedtags', [ $this, 'wp_nav_menu_container_allowedtags' ], 10, 4 );
		add_filter( 'wp_nav_menu_items', [ $this, 'wp_nav_menu_items' ], 10, 4 );

		// Filter nav menu attributes.
		add_filter( 'nav_menu_css_class', [ $this, 'nav_menu_css_class' ], 10, 4 );
		add_filter( 'nav_menu_item_id', [ $this, 'nav_menu_item_id' ], 10, 4 );
		add_filter( 'nav_menu_link_attributes', [ $this, 'nav_menu_link_attributes' ], 10, 4 );
		add_filter( 'nav_menu_item_args', [ $this, 'nav_menu_item_args' ], 10, 3 );

		// Dynamic menu item for mobiles.
		add_filter( 'wp_nav_menu_items', [ $this, 'vertical_editorial_mobile_menu_item' ], 10, 2 );

	}

	/**
	 * Adds dynamic menu item for mobile devices.
	 *
	 * @param string $items
	 *
	 * @param object $args
	 *
	 * @since 2017-09-18 Milind More CDWE-634
	 *
	 * @return string
	 */
	public function vertical_editorial_mobile_menu_item( $items, $args ) {

		if ( ! \PMC::is_mobile() || empty( $args->menu_id ) ) {

			return $items;

		}

		if ( 'vertical-header-menu' === $args->menu_id || 'editorial-header-menu' === $args->menu_id ) {

			$show_all_link = sprintf( '<li class="c-page-nav__item"><a href="javascript:void(0);" class="c-page-nav__link">%s</a></li>',
				esc_html__( 'All Categories', 'pmc-core' ) );
			// add the 'All Categories' link to the start of the menu.
			$items = $show_all_link . $items;

		}

		return $items;

	}

	/**
	 * Register nav menus for child theme.
	 *
	 * @since 2017.1.0
	 */
	public function register_nav_menus() {
		$menus = [
			'pmc_core_header'      => __( 'Header', 'pmc-core' ),
			'pmc_core_mega'        => __( 'Mega - Main', 'pmc-core' ),
			'pmc_core_mega_bottom' => __( 'Mega - Bottom', 'pmc-core' ),
			'pmc_core_trending'    => __( 'Trending News - Home Page', 'pmc-core' ),
			'pmc_core_footer'      => __( 'Footer', 'pmc-core' ),
			'pmc_core_social'      => __( 'Social', 'pmc-core' ),
			'pmc_core_exclusive'   => __( 'Top Bar Exclusive', 'pmc-core' ),
		];

		register_nav_menus( $menus );
	}

	/**
	 * Filters the CSS class(es) applied to a menu item's list item element.
	 *
	 * @since 2017.1.0
	 *
	 * @param array    $classes The CSS classes that are applied to the menu item's `<li>` element.
	 * @param WP_Post  $item    The current menu item.
	 * @param stdClass $args    An object of wp_nav_menu() arguments.
	 * @param int      $depth   Depth of menu item. Used for padding.
	 *
	 * @return array   $classes The modified CSS classes that are applied to the menu item's `<li>` element.
	 */
	public function nav_menu_css_class( $classes, $item, $args, $depth ) {
		if ( 'pmc_core_social' === $args->theme_location ) {
			if ( strstr( $args->menu_class, '__social' ) ) {
				$classes[] = 'c-top-bar__social-icon';
			} else {
				$classes[] = 'l-list__item';
			}
		}

		if ( 'pmc_core_header' === $args->theme_location ) {
			$classes[] = 'c-nav__item';
		}

		if ( 'pmc_core_mega' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$classes[] = 'c-nav__item';
			} else {
				$classes[] = 'c-nav__sub-item';
			}
		}

		if ( 'pmc_core_mega_bottom' === $args->theme_location ) {
			$classes[] = 'l-list__item';
		}

		if ( 'pmc_core_trending' === $args->theme_location ) {
			$classes[] = 'c-trending__item';
		}

		if ( 'pmc_core_footer' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$classes[] = 'l-footer__nav__item';
			} else {
				$classes[] = 'c-nav__item';
			}
		}

		return $classes;
	}

	/**
	 * Removes the ID applied to a menu item's list item element.
	 *
	 * @since 2017.1.0
	 *
	 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
	 * @param WP_Post  $item    The current menu item.
	 * @param stdClass $args    An object of wp_nav_menu() arguments.
	 * @param int      $depth   Depth of menu item. Used for padding.
	 *
	 * @return mixed $menu_id The ID if string or nothing if false or empty string.
	 */
	public function nav_menu_item_id( $menu_id, $item, $args, $depth ) {
		if ( in_array( $args->theme_location, [ 'pmc_core_social', 'pmc_core_header', 'pmc_core_mega' ], true ) ) {
			$menu_id = false;
		}

		return $menu_id;
	}

	/**
	 * Filters the HTML attributes applied to a menu item's anchor element.
	 *
	 * @since 2017.1.0
	 *
	 * @param array    $atts   {
	 *                         The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
	 *
	 * @type string    $title  Title attribute.
	 * @type string    $target Target attribute.
	 * @type string    $rel    The rel attribute.
	 * @type string    $href   The href attribute.
	 * }
	 *
	 * @param WP_Post  $item   The current menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 *
	 * @return array $atts The HTML attributes for the anchor element.
	 */
	public function nav_menu_link_attributes( $atts, $items, $args, $depth ) {
		$new_atts = array();

		if ( 'pmc_core_social' === $args->theme_location ) {
			$new_atts = array(
				'class'      => 'c-icon',
				'data-track' => wp_parse_url( $atts['href'], PHP_URL_HOST ),
				'rel'        => ( empty( $atts['rel'] ) ) ? 'nofollow' : $atts['rel'],
				'target'     => ( empty( $atts['target'] ) ) ? '_blank' : $atts['target'],
			);

			if ( false === strstr( $args->menu_class, '__social' ) ) {
				$new_atts['class'] = 'c-icon c-icon--dark-pin';
			}

			if ( 'li' === $args->container ) {
				$new_atts['class'] = 'c-nav__link';
			}
		}

		if ( in_array( $args->theme_location, [ 'pmc_core_header', 'pmc_core_mega', 'pmc_core_mega_bottom' ], true ) ) {
			$new_atts['class'] = 'c-nav__link';
		}

		// Submenu items of the mega menu has a different class.
		if ( 'pmc_core_mega' === $args->theme_location && 0 !== $depth ) {
			$new_atts['class'] = 'c-nav__sub-link';
		}

		if ( 'pmc_core_trending' === $args->theme_location ) {
			$new_atts['class'] = 'c-trending__link';
		}

		if ( 'pmc_core_footer' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$new_atts['class'] = 'c-heading c-heading--mobile-expander js-expander';
			} else {
				$new_atts['class'] = 'c-nav__link';
			}
		}

		return array_unique( array_merge( $atts, $new_atts ) );
	}

	/**
	 * Filters the arguments for a single nav menu item.
	 *
	 * @since 2017.1.0
	 *
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @param WP_Post  $item  Menu item data object.
	 * @param int      $depth Depth of menu item. Used for padding.
	 *
	 * @return array $args
	 */
	public function nav_menu_item_args( $args, $item, $depth ) {
		if ( 'pmc_core_mega' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$args->after = '<button class="c-button c-button--mega-expander js-expander"></button>';
			} else {
				$args->after = '';
			}
		}

		return $args;
	}

	/**
	 * Filters the HTML list content for a specific navigation menu.
	 *
	 * @since 2017.1.0
	 *
	 * @param string   $items The HTML list content for the menu items.
	 * @param stdClass $args  An object containing wp_nav_menu() arguments.
	 *
	 * @return string $items
	 */
	public function wp_nav_menu_items( $items, $args ) {
		// Appends the social menu to the footer.
		if ( 'pmc_core_footer' === $args->theme_location ) {
			$social = wp_nav_menu( [
				'menu_class'      => 'sub-menu',
				'theme_location'  => 'pmc_core_social',
				'container'       => 'li',
				'container_class' => 'l-footer__nav__item',
				'items_wrap'      => '<a href class="c-heading c-heading--mobile-expander js-expander">' . esc_html( 'Connect' ) . '</a><ul class="%2$s">%3$s</ul>',
				'echo'            => false,
			] );

			$items .= $social;
		}

		return $items;
	}

	/**
	 * Filters the list of HTML tags that are valid for use as menu containers.
	 *
	 * @since 2017.1.0
	 *
	 * @param array $tags The acceptable HTML tags for use as menu containers.
	 *                    Default is array containing 'div' and 'nav'.
	 *
	 * @return array $tags
	 */
	public function wp_nav_menu_container_allowedtags( $tags ) {
		$tags[] = 'li';

		return $tags;
	}
}
