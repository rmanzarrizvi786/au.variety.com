<?php

use \PMC\Larva\Controllers\Modules\Paragraph;

Paragraph::get_instance()->init(
	[
		'data' => $data,
	]
)->render( true );
