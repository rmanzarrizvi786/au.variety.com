<?php
/**
 * Menus
 *
 * The child theme menus.
 *
 * @package pmc-variety
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Menus
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Menus {

	use Singleton;

	/**
	 * It manages a list of playlist menus.
	 * If in future, we need to enable new menu on other playlist term,
	 * than we just need to push new term slug.
	 */
	const PLAYLIST_MENUS = [
		'cannes-lions',
	];

	/**
	 * Class constructor.
	 *
	 * Removes the parent theme menus and initializes the child theme menus.
	 *
	 * @since 2017.1.0
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		// Remove parent theme nav menus.
		remove_action( 'wp_loaded', 'pmc_core_register_menus' );

		// Register the nav menus.
		add_action( 'init', [ $this, 'register_nav_menus' ], 11 );

		add_filter( 'wp_nav_menu_container_allowedtags', [ $this, 'wp_nav_menu_container_allowedtags' ], 10, 4 );
		add_filter( 'wp_nav_menu_items', [ $this, 'wp_nav_menu_items' ], 10, 4 );

		// Filter nav menu attributes.
		add_filter( 'nav_menu_css_class', [ $this, 'nav_menu_css_class' ], 10, 4 );
		add_filter( 'nav_menu_submenu_css_class', [ $this, 'nav_menu_submenu_css_class' ], 10, 2 );
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

			$show_all_link = sprintf( '<li class="c-page-nav__item"><a href="javascript:void(0);" class="c-page-nav__link">%s</a></li>', esc_html__( 'All Categories', 'pmc-variety' ) );
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
		$menus = array(
			'pmc_variety_header'            => __( 'Header', 'pmc-variety' ),
			'pmc_variety_mega'              => __( 'Mega - Main', 'pmc-variety' ),
			'pmc_variety_mega_bottom'       => __( 'Mega - Bottom', 'pmc-variety' ),
			'pmc_variety_trending'          => __( 'Trending News - Home Page', 'pmc-variety' ),
			'pmc_variety_footer'            => __( 'Footer', 'pmc-variety' ),
			'pmc_variety_footer_simplified' => __( 'Footer - Simplified', 'pmc-variety' ),
			'pmc_variety_social'            => __( 'Social', 'pmc-variety' ),
			'pmc_variety_exclusive'         => __( 'Top Bar Exclusive', 'pmc-variety' ),
			'pmc_variety_subscribe-options' => __( 'Top Bar Subscribe Options', 'pmc-variety' ),
			'pmc_variety_loggedin-options'  => __( 'Top Bar Logged In Options', 'pmc-variety' ),
		);

		// Editorial menu's.
		$menus['contenders-editorial-menu'] = __( 'Awards Editorial Menu', 'pmc-variety' );

		// Category Menu's.
		$menus['podcasts-category-menu'] = __( 'Podcasts Category Menu', 'pmc-variety' );

		// Video Page Menu's.
		$menus['variety-top-video-menu']     = __( 'Variety Top Video Menu', 'pmc-variety' );
		$menus['variety-top-video-dropdown'] = __( 'Variety Top Video Dropdown Menu', 'pmc-variety' );

		// What to Watch Hub Menu.
		$menus['what-to-watch-menu'] = __( 'Variety What To Watch Menu', 'pmc-variety' );

		// Global Category Menu.
		$menus['global-category-menu'] = __( 'Global Category Menu', 'pmc-variety' );

		register_nav_menus( $menus );

		// Remove unneeded PMC Core menus.
		unregister_nav_menu( 'pmc_core_header' );
		unregister_nav_menu( 'pmc_core_mega' );
		unregister_nav_menu( 'pmc_core_mega_bottom' );
		unregister_nav_menu( 'pmc_core_trending' );
		unregister_nav_menu( 'pmc_core_footer' );
		unregister_nav_menu( 'pmc_core_social' );
		unregister_nav_menu( 'pmc_core_exclusive' );
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
		if ( 'pmc_variety_social' === $args->theme_location ) {
			if ( strstr( $args->menu_class, '__social' ) ) {
				$classes[] = 'c-top-bar__social-icon';
			} else {
				$classes[] = 'l-list__item';
			}
		}

		if ( 'pmc_variety_header' === $args->theme_location ) {
			$classes[] = 'c-nav__item';
		}

		if ( 'pmc_variety_mega' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$classes[] = 'c-nav__item';
			} else {
				$classes[] = 'c-nav__sub-item';
			}
		}

		if ( 'pmc_variety_mega_bottom' === $args->theme_location ) {
			$classes[] = 'l-list__item';
		}

		if ( 'pmc_variety_trending' === $args->theme_location ) {
			$classes[] = 'c-trending__item';
		}

		if ( 'pmc_variety_footer' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$classes[] = 'l-footer__nav__item';
			} else {
				$classes[] = 'c-nav__item';
			}
		}

		if ( 'pmc_variety_subscribe-options' === $args->theme_location ) {
			$classes[] = 'c-subscribe-options__item';
		}

		if ( 'pmc_variety_loggedin-options' === $args->theme_location ) {
			$classes[] = 'c-loggedin-options__item';
		}

		if ( 'podcasts-category-menu' === $args->theme_location ) {
			$classes[] = 'c-page-nav__item';
		}

		if ( 'variety-top-video-menu' === $args->theme_location ) {
			$classes[] = ( is_post_type_archive( 'variety_top_video' ) && ! \PMC::is_mobile() ) ? 'c-video-page-nav__item' : 'c-page-nav__item';
		}

		if ( self::is_playlist_menu( $args->menu ) && self::the_playlist_page() ) {
			$classes[] = 'c-page-nav__item';
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
		if ( in_array( $args->theme_location, array( 'pmc_variety_social', 'pmc_variety_header', 'pmc_variety_mega' ), true ) ) {
			$menu_id = false;
		}

		return $menu_id;
	}

	/**
	 * Filters the HTML attributes applied to a menu item's anchor element.
	 *
	 * @since 2017.1.0
	 *
	 * @param array $atts {
	 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
	 *
	 *     @type string $title  Title attribute.
	 *     @type string $target Target attribute.
	 *     @type string $rel    The rel attribute.
	 *     @type string $href   The href attribute.
	 * }
	 * @param WP_Post  $item  The current menu item.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @param int      $depth Depth of menu item. Used for padding.
	 *
	 * @return array $atts The HTML attributes for the anchor element.
	 */
	public function nav_menu_link_attributes( $atts, $items, $args, $depth ) {
		$new_atts = array();

		if ( 'pmc_variety_social' === $args->theme_location ) {
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

		if ( in_array( $args->theme_location, array( 'pmc_variety_header', 'pmc_variety_mega', 'pmc_variety_mega_bottom' ), true ) ) {
			$new_atts['class'] = 'c-nav__link';
		}

		// Submenu items of the mega menu has a different class.
		if ( 'pmc_variety_mega' === $args->theme_location && 0 !== $depth ) {
			$new_atts['class'] = 'c-nav__sub-link';
		}

		if ( 'pmc_variety_trending' === $args->theme_location ) {
			$new_atts['class'] = 'c-trending__link';
		}

		if ( 'pmc_variety_footer' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$new_atts['class'] = 'c-heading c-heading--mobile-expander js-expander';
			} else {
				$new_atts['class'] = 'c-nav__link';
			}
		}

		if ( 'podcasts-category-menu' === $args->theme_location ) {
			$new_atts['class'] = 'c-page-nav__link';
		}

		if ( 'variety-top-video-menu' === $args->theme_location ) {
			$new_atts['class'] = ( is_post_type_archive( 'variety_top_video' ) && ! \PMC::is_mobile() ) ? 'c-video-page-nav__link' : 'c-page-nav__link';
		}

		if ( self::is_playlist_menu( $args->menu ) && self::the_playlist_page() ) {
			$new_atts['class'] = 'c-page-nav__link';
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
		if ( 'pmc_variety_mega' === $args->theme_location ) {
			if ( 0 === $depth ) {
				$args->after = '<button class="c-button c-button--mega-expander js-expander"></button>';
			} else {
				$args->after = '';
			}
		}

		if ( self::is_playlist_menu( $args->menu ) && self::the_playlist_page() ) {

			if ( 0 === $depth && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
				$args->after = '<button class="c-button c-button--expander js-expander"></button>';
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
		if ( 'pmc_variety_footer' === $args->theme_location ) {
			$social = wp_nav_menu( array(
				'menu_class'      => 'sub-menu',
				'theme_location'  => 'pmc_variety_social',
				'container'       => 'li',
				'container_class' => 'l-footer__nav__item',
				'items_wrap'      => '<a href class="c-heading c-heading--mobile-expander js-expander">' . esc_html( 'Connect' ) . '</a><ul class="%2$s">%3$s</ul>',
				'echo'            => false,
			) );

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

	public function nav_menu_submenu_css_class( $classes, $args ) {

		if ( ! empty( $args ) && 'variety-top-video-menu' === $args->theme_location ) {
			$classes[] = ( is_post_type_archive( 'variety_top_video' ) && ! \PMC::is_mobile() ) ? 'c-video-page-nav' : 'c-page-nav';
			$classes[] = 'is-verticle';
		}

		return $classes;
	}

	/**
	 * To check current query is for custom playlist menu page.
	 *
	 * @return boolean|WP_Term
	 */
	public static function the_playlist_page() {

		$object_id = get_queried_object_id();

		if ( is_tax( 'vcategory', $object_id ) ) {

			$term = get_term( $object_id );

			if ( in_array( $term->slug, (array) self::PLAYLIST_MENUS, true ) ) {
				return $term;
			}
		} elseif ( is_singular( 'variety_top_video' ) && has_term( self::PLAYLIST_MENUS, 'vcategory', $object_id ) ) {

			$terms = wp_get_post_terms( $object_id, 'vcategory', [ 'slug' => self::PLAYLIST_MENUS ] );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) && is_array( $terms ) ) {

				/**
				 * In case we use this code on another playlist term than,
				 * if the video post has multiple playlist terms assigned to it,
				 * and if it includes the list of terms which has custom playlist menu,
				 * than from them any first playlist term will be selected.
				 * eg. custom playlist menu is for [ 'cannes-lions', 'artisans' ],
				 * Now if video post has both of this terms assigned to it than,
				 * first term from the return array of terms is selected.
				 */
				return reset( $terms );
			}
		} else {
			return false;
		}

		return false;
	}

	/**
	 * Check for current menu is from playlist custom menus.
	 *
	 * @param string $menu Menu name.
	 *
	 * @return boolean
	 */
	public static function is_playlist_menu( $menu ) {
		return in_array( $menu, (array) self::PLAYLIST_MENUS, true );
	}
}
