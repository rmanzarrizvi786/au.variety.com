<?php

/**
 * PMC Custom Menu Items front-end class
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-08-18
 */

class PMC_Custom_Menu_Items_Maker extends PMC_Custom_Menu_Items {

	/**
	 * Setup hooks for front-end
	 *
	 * @return void
	 */
	protected function _setup_child_hooks() {
		/**
		 * filters
		 */
		add_filter( 'walker_nav_menu_start_el', array( $this, 'make_menu_item' ), 10, 4 );
	}


	/**
	 * This function hooks into WP nave menu walker using 'walker_nav_menu_start_el' filter
	 * and is called for every menu item. This function is listening into this filter for only the
	 * PMC Custom Menu Item post type and if it comes across that then it grabs the callback hook
	 * set for that item and calls it.
	 *
	 * @param string $item_output The default HTML for current menu item
	 * @param WP_POST $item Nav menu item object for the current menu item
	 * @param int $depth Tree depth for current menu item
	 * @param object $args Misc arguments for current menu item
	 * @return string HTML to display current menu item
	 */
	public function make_menu_item( $item_output, $item, $depth, $args ) {
		if (
			empty( $item->object ) || $item->object !== parent::POST_TYPE
			|| empty( $item->object_id ) || intval( $item->object_id ) < 1
		) {
			//not our menu item, bail out
			return $item_output;
		}

		//get callback data of menu item
		$callback_data = $this->_get_callback_data( $item->object_id );

		if ( empty( $callback_data ) || ! is_array( $callback_data ) ) {
			//nothing set
			return $item_output;
		}

		//call the filter for custom menu item to fetch HTML for this menu item
		$item_data = apply_filters( parent::FILTER_PREFIX . $callback_data['filter'], $callback_data['param1'], $callback_data['param2'] );

		if ( ! empty( $item_data ) && $item_data !== $callback_data['param1'] ) {
			//custom HTML received for the menu item, hurray!
			//now return it back to WP
			return $item_data;
		}

		if ( ! PMC::is_production() ) {
			//its not production, return default menu item created by WP
			return $item_output;
		}

		//we're on production, make this custom menu item disappear as there's no implementation of it yet
		return '';
	}

}	//end of class


//EOF