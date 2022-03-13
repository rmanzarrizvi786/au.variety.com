<?php
/**
 * This file contains the Endpoints\Menu class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Menu_Item;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use PMC\Mobile_API\Menu_Walker;
use PMC\Mobile_API\Route_Registrar;

/**
 * Menu endpoint class.
 */
class Menu extends Public_Endpoint implements Has_Definitions {

	use Usable_Definitions;

	/**
	 * Get menu items.
	 *
	 * @return array
	 */
	protected function get_items(): array {
		return $this->get_menu_items();
	}

	/**
	 * Get mobile section links.
	 *
	 * @return array
	 */
	public function get_section_links(): array {
		return $this->get_menu_items(
			[
				'depth'          => 2,
			]
		);
	}

	/**
	 * Get mobile section links for the videos.
	 *
	 * @return array
	 */
	public function get_video_section_links(): array {
		return [
			'items' => [
				[
					'title' => __( 'All Videos', 'pmc-mobile-api' ),
					'link'  => \rest_url( '/' . Route_Registrar::NAMESPACE . '/video' ),
				],
				[
					'title' => __( 'Latest', 'pmc-mobile-api' ),
					'link'  => \rest_url( '/' . Route_Registrar::NAMESPACE . '/video/latest' ),
				],
				[
					'title' => __( 'Playlist', 'pmc-mobile-api' ),
					'link'  => \rest_url( '/' . Route_Registrar::NAMESPACE . '/video/playlist' ),
				],
			],
		];
	}

	/**
	 * Get the menu items as an array of items.
	 *
	 * @param array $args Array of nav menu arguments, same as `wp_nav_menu()`.
	 *                    Most arguments have no impact, they're maintained to
	 *                    avoid notices in the walker.
	 * @return array
	 */
	public function get_menu_items( $args = [] ): array {
		// Add default menu args to avoid issues in the walker.
		$args = (object) wp_parse_args(
			$args,
			[
				'menu'            => '',
				'container'       => '',
				'container_class' => '',
				'container_id'    => '',
				'menu_class'      => '',
				'menu_id'         => '',
				'echo'            => false,
				'fallback_cb'     => '__return_empty_array',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'items_wrap'      => '',
				'item_spacing'    => 'discard',
				'depth'           => 2,
				'walker'          => new Menu_Walker(),
				'theme_location'  => 'main_menu_mobile_app',
			]
		);

		// Get the nav menu based on the theme_location.
		$locations = get_nav_menu_locations();
		if ( isset( $locations[ $args->theme_location ] ) ) {
			$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );
		}
		if ( empty( $menu ) ) {
			return [];
		}

		// Get the menu's items.
		$menu_items = wp_get_nav_menu_items(
			$menu->term_id,
			[
				'update_post_term_cache' => false,
			]
		);

		if ( empty( $menu_items ) ) {
			return [];
		}

		// Sort the menu items.
		$sorted_menu_items = [];
		foreach ( $menu_items as $menu_item ) {
			$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
		}
		unset( $menu_items, $menu_item );

		// Walk the items and convert them into a nested tree.
		return (array) walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Menu', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'items' => [
					'type'  => 'array',
					'items' => $this->add_definition( new Menu_Item() ),
				],
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}
}
