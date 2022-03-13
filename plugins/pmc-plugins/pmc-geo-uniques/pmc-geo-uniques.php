<?php

/**
Plugin Name: PMC GEO Unique
Description: Wrapper geo functions call between WPCOM & VIP GO geo functions
 */

if (defined('PMC_IS_VIP_GO_SITE') && PMC_IS_VIP_GO_SITE) {
	wpcom_vip_load_plugin("vip-go-geo-uniques");
	function pmc_geo_add_location($loc)
	{
		$loc = strtoupper($loc);
		return vip_geo_add_location($loc);
	}
	function pmc_geo_get_user_location()
	{
		// Force lowercase, which is what wpcom_geo_get_user_location() uses, and this inexplicably does not
		return strtolower(vip_geo_get_country_code());
	}
	function pmc_geo_set_default_location($loc)
	{
		$loc = strtoupper($loc);
		return vip_geo_set_default_location($loc);
	}
	function pmc_geo_is_valid_location($loc)
	{
		$loc = strtoupper($loc);
		return VIP_Go_Geo_Uniques::is_valid_location($loc);
	}
} else {
	wpcom_vip_load_plugin("wpcom-geo-uniques");
	function pmc_geo_add_location($loc)
	{
		return wpcom_geo_add_location($loc);
	}
	function pmc_geo_get_user_location()
	{
		return strtolower(wpcom_geo_get_user_location());
	}
	function pmc_geo_set_default_location($loc)
	{
		return wpcom_geo_set_default_location($loc);
	}
	function pmc_geo_is_valid_location($loc)
	{
		return WPCOM_Geo_Uniques::is_valid_location($loc);
	}
}

//This Geo_Uniques must not instantiate prior to the the code above if/else statements.
PMC\Geo_Uniques\Plugin::get_instance();
