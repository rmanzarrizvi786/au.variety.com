<?php

$profile_body = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-body.prototype' );

$profile_body['c_dek']['c_dek_text'] = false;

$profile_body['profile_body_content_markup'] = apply_filters( 'the_content', get_the_content() );


\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-body.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_body,
	true
);
