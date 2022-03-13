<?php

/**
 * This class displays user roles and capabilities for PMC sites
 *
 * @author Reef Fanous <rfanous@pmc.com>
 * @since 2021-10-05
 * @version 2021-10-05
 */

namespace PMC\Roles_Capabilities;

use phpseclib3\Common\Functions\Strings;

class Table_View extends \WP_List_Table {

	/**
	 * Get user roles
	 *
	 * @return array
	 */
	private function _get_roles() : array {
		global $wp_roles;

		return $wp_roles->roles;
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */
	public function prepare_items() {
		$table_data = $this->_table_data();

		foreach ( $table_data as $k => $data ) {
			$table_data[ $k ]['capability'] = ucwords( str_replace( '_', ' ', $data['capability'] ) );
		}

		$this->items           = $table_data;
		$columns               = $this->get_columns();
		$this->_column_headers = [ $columns ];
	}

	/**
	 * Get columns to display
	 *
	 * @return array
	 */
	public function get_columns() : array {
		$roles = $this->_get_roles();

		$columns               = [];
		$columns['capability'] = 'Capability';

		foreach ( $roles as $k => $roles[ $k ]['name'] ) {
			$columns[ $k ] = $roles[ $k ]['name'];
		}

		return $columns;
	}

	/**
	 * Get the table data
	 * Check against Admin capabilities (all) to determine which roles have what capabilities
	 *
	 * @return array
	 */
	private function _table_data() : array {
		$roles = $this->_get_roles();

		$data = [];

		// Admin Capabilities to check against
		$admin_caps = array_keys( (array) $roles['administrator']['capabilities'] );

		foreach ( $admin_caps as $k => $admin_cap ) {
			$data[ $k ]['capability'] = $admin_cap;

			$green_check = '<span style="color:green; font-size: 18px;"><strong>&check;</strong></span>';
			$red_x       = '<span style="color:red; font-size: 18px;"><strong>x</strong></span>';

			foreach ( $roles as $role_key => $role_val ) {
				$data[ $k ][ $role_key ] = array_key_exists( $admin_cap, $role_val['capabilities'] ) ? $green_check : $red_x;
			}
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item Item data
	 * @param  String $column_name Current column name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) : string {
		return $item[ $column_name ];
	}

}

//EOF
