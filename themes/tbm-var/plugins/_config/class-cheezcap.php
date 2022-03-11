<?php
/**
 * Cheezcap plugin config
 *
 * @author  Amit Gupta <agupta@pmc.com>
 * @since   2015-10-29
 * @version 2017-08-10 Divyaraj Masani <divyaraj.masani@rtcamp.com>
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapGroup;
use \CheezCapDropdownOption;
use \CheezCapTextOption;
use \CheezCapBooleanOption;

class Cheezcap {

	use Singleton;

	/**
	 * Constructor.
	 *
	 */
	protected function __construct() {

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );
	}

	/**
	 * Adds new global cheezcap options.
	 *
	 * @param array $cheezcap_options
	 *
	 * @return array
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = [] ) {

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'Related Module Visibility', 'pmc-variety' ),
			__(
				'Choose whether to show the related articles modules on every article, hide it on every article, or respect the settings that are already in place on each individual post. This setting should usually be set to \'Inherit\'.',
				'pmc-variety'
			),
			'related-posts-module-visibility',
			[
				__( 'false', 'pmc-variety' ),
				__( 'inherit', 'pmc-variety' ),
				__( 'true', 'pmc-variety' ),
			],
			1,
			[
				__( 'Show on all Articles', 'pmc-variety' ),
				__( 'Respect Post-level settings', 'pmc-variety' ),
				__( 'Hide on All Articles', 'pmc-variety' ),
			]
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'Related Module Visibility (AMP)', 'pmc-variety' ),
			__(
				"Choose whether to show the related articles modules on every AMP article, hide it on every AMP article, or respect the settings that are already in place on each individual post. This setting should usually be set to 'Inherit'.",
				'pmc-variety'
			),
			'related-posts-module-amp-visibility',
			[
				__( 'false', 'pmc-variety' ),
				__( 'inherit', 'pmc-variety' ),
				__( 'true', 'pmc-variety' ),
			],
			1, // inherit by default
			[
				__( 'Show on all AMP Articles', 'pmc-variety' ),
				__( 'Respect Post-level AMP settings', 'pmc-variety' ),
				__( 'Hide on All AMP Articles', 'pmc-variety' ),
			]
		);

		return $cheezcap_options;

	}

	/**
	 * Adds new theme settings tabs ( cheezcap group ).
	 *
	 * @param $variety_cheezcap_groups
	 *
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups( $variety_cheezcap_groups ) {

		if ( empty( $variety_cheezcap_groups ) || ! is_array( $variety_cheezcap_groups ) ) {
			$variety_cheezcap_groups = [];
		}

		$variety_cheezcap_groups[] = new CheezCapGroup(
			__( 'Toggle Launch Options', 'pmc-variety' ),
			'toggle_launch_options',
			[
				new CheezCapDropdownOption(
					__( 'Turn on ajax protection', 'pmc-variety' ),
					__( 'enable or disable ajax protection', 'pmc-variety' ),
					'enable_ajax_authentication',
					[ 0, 1 ],
					0,
					[ 'Disabled', 'Enabled' ]
				), // CheezCapBooleanOption
				new CheezCapDropdownOption(
					__( 'Bypass authentication', 'pmc-variety' ),
					__( 'when enabled, all protected pages will not require authentication', 'pmc-variety' ),
					'enable_bypass_authentication',
					[ 0, 1 ],
					0,
					[ 'Disabled', 'Enabled' ]
				), // CheezCapBooleanOption
			] // array
		); // CheezCapGroup

		return $variety_cheezcap_groups;
	}

}   //end of class

//EOF
