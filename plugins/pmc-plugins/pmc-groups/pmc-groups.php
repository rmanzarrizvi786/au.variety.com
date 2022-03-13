<?php
/*
Plugin Name: PMC Groups
Description: Standalone user group management system used to test features on production sites
Version: 1.0
Author: PMC, Javier Martinez
License: PMC Proprietary.  All rights reserved.
*/
define( 'PMC_GROUPS_ROOT', __DIR__ );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * Initialize Admin UI class
 *
 * @since 2015-07-15
 * @version 2015-07-15 Javier Martinez - PPT-4923
 */
function pmc_groups_loader() {
	\PMC\Groups\Admin::get_instance();
}

/**
 * Returns TRUE if current user is a member of $group_name, FALSE if not
 *
 * @param $group_name
 * @return bool
 *
 * @since 2015-07-15
 * @version 2015-07-15 Javier Martinez - PPT-4923
 */
function pmc_current_user_is_member_of( $group_name ){
	return \PMC\Groups\Group::pmc_current_user_is_member_of( $group_name );
}

/**
 * Returns TRUE is given $user is member of given $group_name
 *
 * @param $user
 * @param $group_name
 * @return bool
 *
 * @since 2015-07-15
 * @version 2015-07-15 Javier Martinez - PPT-4923
 */
function pmc_user_is_member_of( $user, $group_name ){
	return \PMC\Groups\Group::pmc_user_is_member_of( $user, $group_name );
}

/**
 * Returns array of groups that $user belongs to. Returns empty array() if no groups are found.
 *
 * @param $user
 * @return mixed
 *
 * @since 2015-07-15
 * @version 2015-07-15 Javier Martinez - PPT-4923
 */
function pmc_get_user_groups( $user ){
	return \PMC\Groups\Admin::get_instance()->get_user_groups($user );
}

pmc_groups_loader();

//EOF