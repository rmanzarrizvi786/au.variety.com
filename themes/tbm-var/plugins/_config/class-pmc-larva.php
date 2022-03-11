<?php
/**
 * PMC Larva config for Variety.
 *
 * @since   2021-09-29
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Larva;

class PMC_Larva {

	use Singleton;

	const BRAND_NAME = 'variety';

	public $config = [];

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->init();

	}

	/**
	 * Initialize Larva
	 */

	public function init() {

		$this->config = Larva\Config::get_instance()->init(
			[
				'brand_name'                => self::BRAND_NAME,
				'brand_url'                 => get_stylesheet_directory_uri() . '/assets',
				'brand_directory'           => CHILD_THEME_PATH . '/assets',
				'brand_templates_directory' => CHILD_THEME_PATH . '/template-parts/patterns',
				'contexts'                  => [
					\PMC\Hub\Post_Type::POST_TYPE,
				],
				'tokens'                    => self::BRAND_NAME,
				'css'                       => 'larva',
				'compat_css'                => true,
				'js'                        => [
					'common',
				],
			]
		)->get();

	}

}

