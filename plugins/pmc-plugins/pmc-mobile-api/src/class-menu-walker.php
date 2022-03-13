<?php
/**
 * This file contains the Menu_Walker class.
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API;

use stdClass;
use Walker_Nav_Menu;
use WP_Post;

/**
 * Custom Walker to convert menu children to JSON.
 */
class Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Store a reference to the current menu item.
	 *
	 * @var array
	 */
	protected $current_item;

	/**
	 * Store a reference to the current hierarchy level.
	 *
	 * @var array
	 */
	protected $current_level = [];

	/**
	 * Store references to the depths of the menu as it is traversed.
	 *
	 * @var array
	 */
	protected $pointers = [];

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string   $output Used to append additional content (passed by
	 *                         reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$this->pointers[]    =& $this->current_item;
		$this->current_level =& $this->current_item['children'];

		// Unlink current_item.
		unset( $this->current_item );
	}

	/**
	 * Starts the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string   $output Used to append additional content (passed by
	 *                         reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$slug    = '';
		$term_id = '';

		// Add a new item at the current level, by reference.
		$this->current_level[] =& $this->current_item;

		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/** This filter is documented in wp-includes/class-walker-nav-menu.php */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$title = html_entity_decode( $title );

		$object_id     = \get_post_meta( $item->ID, '_menu_item_object_id', true );
		$nav_item_type = \get_post_meta( $item->ID, '_menu_item_type', true );

		switch ( $nav_item_type ) {
			case 'taxonomy':
				$term    = get_term( $object_id );
				$slug    = $term->taxonomy;
				$term_id = $term->term_id;
				$link    = \rest_url( '/' . Route_Registrar::NAMESPACE . "/section/{$slug}/{$term_id}" );
				break;
			case 'post_type':
				$link = \rest_url( '/' . Route_Registrar::NAMESPACE . "/article/{$object_id}" );
				break;
			case 'custom':
				$link = $item->url ?? '';

				if ( '/' === substr( $link, 0, 1 ) ) {
					$link = $this->format_relative_link( $link );
				} else {
					$link = $this->format_absolute_link( $link );
				}

				break;
			default:
				$link = '';
				break;
		}

		$menu_item = [
			'id'                => $term_id,
			'title'             => $title,
			'taxonomy'          => $slug,
			'use-parent'        => boolval( $item->target ),
			'custom-parent-id'  => empty( $item->xfn ) ? '' : intval( $item->xfn ),
			'custom-parent-tax' => $item->attr_title,
			'link'              => esc_url( $link ),
			'children'          => [],
		];

		/**
		 * Filter the menu item as it's added to the menu.
		 *
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$this->current_item = apply_filters( 'pmc_mobile_api_menu_item', $menu_item, $item, $args, $depth );
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string   $output Used to append additional content (passed by
	 *                         reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$last_pointer        = array_pop( $this->pointers );
		$this->current_item  =& $last_pointer;
		$this->current_level =& $this->pointers[ count( $this->pointers ) - 1 ]['children'];
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @param string   $output Used to append additional content (passed by
	 *                         reference).
	 * @param WP_Post  $item   Page data object. Not used.
	 * @param int      $depth  Depth of page. Not Used.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Remove the reference to the current item.
		unset( $this->current_item );
	}

	// Ignore this rule because it will either be incompatible with Walker_Nav_Menu or Walker.
	// Also we can't just :ignore this in front of the line, or the docblock won't be processed by phpcs.
	// phpcs:disable WordPressVIPMinimum.Classes.DeclarationCompatibility.DeclarationCompatibility
	/**
	 * Display array of elements hierarchically.
	 *
	 * Does not assume any existing order of elements.
	 *
	 * $max_depth = -1 means flatly display every element.
	 * $max_depth = 0 means display all levels.
	 * $max_depth > 0 specifies the number of display levels.
	 *
	 * @param array $elements  An array of elements.
	 * @param int   $max_depth The maximum hierarchical depth.
	 * @param mixed ...$args   Optional additional arguments.
	 * @return array The hierarchical menu as an array.
	 */
	public function walk( $elements, $max_depth, ...$args ) {
		// Initialize current item and pointers to match the expected structure.
		$root                = [ 'children' => [] ];
		$this->pointers[]    =& $root;
		$this->current_level =& $root['children'];

		parent::walk( $elements, $max_depth );

		$output = array_shift( $this->pointers );

		return $output['children'] ?? [];
	}
	// phpcs:enable WordPressVIPMinimum.Classes.DeclarationCompatibility.DeclarationCompatibility

	/**
	 * Maps a relative site URL to the corresponding REST resource.
	 *
	 * @param string $path Relative link to a page on the site.
	 * @return string REST resource URL.
	 */
	protected function format_relative_link( $path ): string {
		global $wp_rewrite;

		// Get path, remove leading slash.
		$trimmed_path = ltrim( $path, '/' );

		// Loop through rewrite rules.
		$rewrites = ! empty( $wp_rewrite->wp_rewrite_rules() ) ?
			$wp_rewrite->wp_rewrite_rules() :
			[];

		// Query arguments.
		$args = [];

		// Loop through rewrites to find a match.
		foreach ( $rewrites as $match => $rewrite_query ) {

			// Rewrite rule match.
			if ( preg_match( "#^$match#", $trimmed_path, $matches ) ) {

				// Handle Pages differently.
				if ( preg_match( '/pagename=\$matches\[([0-9]+)\]/', $rewrite_query, $varmatch ) ) {

					// This is a verbose page match, let's check to be sure about it.
					$page = wpcom_vip_get_page_by_path( $matches[ $varmatch[1] ], OBJECT, 'page' );

					if ( ! $page ) {
						continue;
					}

					// Ensure that this post type is publicly queryable.
					$post_status_obj = get_post_status_object( $page->post_status );
					if (
						! $post_status_obj->public &&
						! $post_status_obj->protected &&
						! $post_status_obj->private &&
						$post_status_obj->exclude_from_search
					) {
						continue;
					}
				}

				// Prep the query string into its parts.
				$query = preg_replace( '!^.+\?!', '', $rewrite_query );
				$query = addslashes( \WP_MatchesMapRegex::apply( $query, $matches ) );
				parse_str( $query, $args );

				// Ensure custom post types get mapped correctly. Loop through
				// all post types, ensure they're viewable, then map the
				// post_type and name appropriately.
				foreach ( get_post_types( [], 'objects' ) as $post_type_object ) {
					if (
						is_post_type_viewable( $post_type_object ) &&
						$post_type_object->query_var
					) {
						if ( isset( $args[ $post_type_object->query_var ] ) ) {
							$args['post_type'] = $post_type_object->query_var;
							$args['name']      = $args[ $post_type_object->query_var ];
						}
					}
				}
				break;
			}
		}

		if ( ! $args ) {
			return '';
		}

		// Get the correct REST URL based on the query args.
		switch ( true ) {
			case ( ! empty( $args['category_name'] ) ):
				$category    = get_term_by( 'slug', $args['category_name'], 'category' );
				$resource_id = $category->term_id ?? null;
				$endpoint    = 'section/category';

				break;
			case ( ! empty( $args['tag'] ) ):
				$tag         = get_term_by( 'slug', $args['tag'], 'post_tag' );
				$resource_id = $tag->term_id ?? null;
				$endpoint    = 'section/tag';

				break;
			case ( ! empty( $args['vertical'] ) ):
				$vertical    = get_term_by( 'slug', $args['vertical'], 'vertical' );
				$resource_id = $vertical->term_id ?? null;
				$endpoint    = 'section/vertical';

				break;
			case ( ! empty( $args['post_type'] ) && ! empty( $args['name'] ) ):
				$post_query = new \WP_Query(
					[
						'name'        => $args['name'],
						'post_type'   => $args['post_type'],
						'post_status' => 'publish',
						'numberposts' => 1,
					]
				);

				switch ( $args['post_type'] ) {
					case 'post':
						$endpoint = 'article';
						break;
					case 'pmc-gallery':
						$endpoint = 'gallery';
						break;
					case 'wwd_top_video':
						$endpoint = 'video';
						break;
					default:
						break;
				}

				if ( ! empty( $post_query->posts[0]->ID ) ) {
					$resource_id = $post_query->posts[0]->ID;
				}

				break;
			case ( ! empty( $args['post_type'] ) ):
				if ( 'wwd_top_video' === $args['post_type'] ) {
					$endpoint = 'video';
				}

				break;
			default:
				break;
		}

		// We could have an endpoint and an ID, or just an ID.
		if ( ! empty( $endpoint ) && ! empty( $resource_id ) ) {
			$rest_url = \rest_url(
				sprintf(
					'/%1$s/%2$s/%3$d',
					Route_Registrar::NAMESPACE,
					$endpoint,
					$resource_id
				)
			);
		} elseif ( ! empty( $endpoint ) ) {
			$rest_url = \rest_url(
				sprintf(
					'/%1$s/%2$s',
					Route_Registrar::NAMESPACE,
					$endpoint
				)
			);
		}

		return $rest_url ?: '';
	}

	/**
	 * Maps an absolute URL to the corresponding REST resource,
	 * if the URL belongs on this site.
	 *
	 * @param string $url Absolute URL to a page on the site.
	 * @return string REST resource URL.
	 */
	protected function format_absolute_link( $url ): string {

		if ( empty( $url ) ) {
			return '';
		}

		$url_hostname = wp_parse_url( $url, PHP_URL_HOST );

		$hostnames = [
			wp_parse_url( get_site_url(), PHP_URL_HOST ),
			'wwd.com',
		];

		if ( ! in_array( $url_hostname, (array) $hostnames, true ) ) {
			return $url;
		} else {
			// Use to allow wp-json endpoints.
			if ( strpos( $url, 'wp-json' ) !== false ) {
				return $url;
			}

			return $this->format_relative_link( wp_parse_url( $url, PHP_URL_PATH ) );
		}
	}
}
