<?php
/**
 * Plugin dependencies for PMC Mobile API.
 */

namespace PMC\Mobile_API;

if ( function_exists( '\wpcom_vip_load_plugin' ) ) {
	wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

	load_pmc_plugins(
		[
			'pmc-plugins' => [
				'jwt-auth',
			],
		]
	);

	if ( function_exists( '\jwt_auth_loader' ) ) {
			\jwt_auth_loader();
	}
}
