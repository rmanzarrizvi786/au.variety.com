<?php
/**
 * This class is written to do a one time backup of all PMC ET related configuration
 * In case we need to rollback due to code changes that destroy the data
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Options;

class Backup {
	use Singleton;

	// Defined the related constant values to be used by this plugin
	const OPTION_GROUP = 'pmc_exacttarget_backup';

	private $_option = null;

	protected function __construct() {
		$this->_option = PMC_Options::get_instance( self::OPTION_GROUP );
		$this->do_backup( PMC_EXACTTARGET_VERSION );
	}

	public function do_backup( $version ) {
		$backup_key = 'backup-' . $version;
		$value      = $this->_option->get_option( $backup_key );
		if ( ! empty( $value ) ) {
			return;
		}
		$backup_data = [
			'repeats'     => \Sailthru_Blast_Repeat::get_repeats(),
			'newsletters' => \sailthru_get_fast_newsletter(),
			'options'     => [
				'pmc_newsletter_senddefinition'      => pmc_get_option( 'pmc_newsletter_senddefinition', 'exacttarget' ),
				'pmc_alert_senddefinition'           => pmc_get_option( 'pmc_alert_senddefinition', 'exacttarget' ),
				'pmc_newsletter_api_token'           => pmc_get_option( 'pmc_newsletter_api_token', 'exacttarget' ),
				'global_default_image'               => pmc_get_option( 'global_default_image' ),
				'pmc_post_tag_custom_field_sailthru' => pmc_get_option( 'pmc_post_tag_custom_field_sailthru' ),
			],
		];

		$this->_option->update_option( $backup_key, $backup_data );
	}

	public function render_json( $version ) {
		$backup_key = 'backup-' . $version;
		$value      = $this->_option->get_option( $backup_key );
		echo wp_json_encode( $value, JSON_PRETTY_PRINT );
	}

}

