<?php

use \PMC\Larva\Controllers\Modules\Button;

Button::get_instance()->init(
	[
		'data' => $data,
	]
)->render( true );
