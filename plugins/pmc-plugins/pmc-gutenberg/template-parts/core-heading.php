<?php

use \PMC\Larva\Controllers\Modules\Heading;

Heading::get_instance()->init(
	[
		'data'    => $data,
		'variant' => $variant,
	]
)->render( true );
