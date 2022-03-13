<?php

$back_to_list = PMC\Larva\Json::get_instance()->get_json_data( 'modules/back-to-list.prototype' );

$profile_settings = get_option( 'profiles_sponsor_settings' );

$back_to_list['c_link']['c_link_url']  = $profile_settings['back_to_index_url'];
$back_to_list['c_link']['c_link_text'] = $profile_settings['back_to_index_text'];

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/back-to-list.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$back_to_list,
	true
);
