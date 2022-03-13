<?php
/**
 * Plugin Name: Protected Embeds
 * Description: WordPress VIP like protected embed for VIP Go sites.
 * Version: 1.0
 * Text Domain: pmc-protected-embeds
 * Author: Vishal Dodiya
 */

define( 'PMC_PROTECTED_EMBEDS_ROOT', __DIR__ );
define( 'PMC_PROTECTED_EMBEDS_VERSION', '1.0' );

function pmc_protected_embeds_loader() {

	require_once PMC_PROTECTED_EMBEDS_ROOT . '/dependencies.php';

	\PMC\Protected_Embeds\Shortcode::get_instance();
}

pmc_protected_embeds_loader();
