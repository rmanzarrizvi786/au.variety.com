<?php

use \PMC\Larva\Controllers\Modules\Container;

Container::get_instance()->init(
	[
		'data' => $data,
	]
)->render( true );
