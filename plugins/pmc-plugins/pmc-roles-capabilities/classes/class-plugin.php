<?php

/**
 * This class displays user roles and capabilities for PMC sites
 *
 * @author Reef Fanous <rfanous@pmc.com>
 * @since 2021-10-05
 */

namespace PMC\Roles_Capabilities;

use \PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * Class Constructor
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks
	 */
	protected function _setup_hooks() {
		add_action( 'admin_menu', [ $this, 'add_user_submenu' ] );
	}

	/**
	 * Menu item to load the page to display the table
	 */
	public function add_user_submenu() {
		add_submenu_page(
			'users.php',
			'User Roles/Capabilities',
			'Roles/Capabilities',
			'manage_options',
			'roles-capabilities',
			[ $this, 'list_table_page' ]
		);
	}

	/**
	 * Display the list table page
	 *
	 * @return void
	 */
	public function list_table_page() {
		$roles_cap_table = new Table_View();
		$roles_cap_table->prepare_items();

		echo '<div class="wrap"><h2>Roles/Capabilities</h2>';
		$roles_cap_table->display();
		echo '</div>';

	}

}

//EOF
