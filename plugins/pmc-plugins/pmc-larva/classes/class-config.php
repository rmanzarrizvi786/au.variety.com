<?php

/**
 * Larva Config.
 *
 * Class for handling configuration provided by themes.
 *
 * @package pmc-plugins
 * @since   2020-05-08
 */

namespace PMC\Larva;

use \PMC\Global_Functions\Traits\Singleton;

class Config {

	use Singleton;

	protected $_defaults = [
		'core_directory'            => PMC_LARVA_PLUGIN_PATH . '/_core',
		'core_url'                  => PMC_LARVA_PLUGIN_URL . '/_core',
		'core_templates_directory'  => PMC_LARVA_PLUGIN_PATH . '/_core/build/patterns',
		'brand_directory'           => PMC_LARVA_THEME_PATH . '/assets',
		'brand_url'                 => PMC_LARVA_THEME_URL . '/assets',
		'brand_templates_directory' => PMC_LARVA_THEME_PATH . '/template-parts/patterns',

		'css'                       => 'larva',
		'js'                        => null,
		'contexts'                  => false,
		'tokens'                    => 'default',
	];

	protected $_config = [];

	/**
	 * Set theme configuration
	 *
	 * @param array $brand_config Brand-specific configuration to override the defaults.
	 *
	 * @return self Instance of the config class so calls can be chained.
	 */

	public function init( array $brand_config ) : self {

		$this->_config = wp_parse_args( $brand_config, $this->_defaults );

		return $this;
	}

	/**
	 * Get configuration by key array
	 *
	 * @param string $key Key in the configuration array.
	 * @param string $default Default return value
	 *
	 * @return mixed Return value of key in array, or full configuration object
	 */

	public function get( string $key = '', string $default = '' ) {

		if ( empty( $key ) ) {
			return $this->_config;
		}

		if ( isset( $this->_config[ $key ] ) ) {
			return $this->_config[ $key ];
		}

		return $default;

	}

}
