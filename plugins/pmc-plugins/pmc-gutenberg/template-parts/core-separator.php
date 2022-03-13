<?php

use \PMC\Larva\Controllers\Modules\Separator;

Separator::get_instance()->init(
	[
		'data'    => $data,
		'variant' => $variant,
	]
)->render( true );
