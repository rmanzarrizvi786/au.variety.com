<?php

namespace PMC\Core\Inc\Widgets;

class Social_Profiles extends Global_Curateable
{

	/**
	 * Social_Profiles widget constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			'social_profiles',
			__('PMC Core Social Profiles', 'pmc-core'),
			[
				'classname'   => 'pmc-core-social-profiles',
				'description' => __('Add the Social Profiles widget.', 'pmc-core'),
			]
		);
	}
}

//EOF
