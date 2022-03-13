<?php
/**
 * Manage assets to be preloaded.
 *
 * @package pmc-preload
 */

namespace PMC\Preload;

use PMC\Global_Functions\Styles;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Manager.
 */
class Manager {
	use Singleton;

	/**
	 * Filter tag to modify automatically-preloaded assets.
	 */
	public const FILTER_TAG_ENQUEUED_HANDLES = 'pmc_preload_enqueued_handles';

	/**
	 * Action tag used to process preload queues.
	 */
	public const QUEUE_HOOK = Styles::INLINE_CSS_HOOK;

	/**
	 * Action priority at which preload queues are processed.
	 */
	public const QUEUE_PRIORITY = -999;

	/**
	 * Manager constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action(
			static::QUEUE_HOOK,
			[ $this, 'register_enqueued_scripts' ],
			static::QUEUE_PRIORITY - 1
		);

		$classes = [
			Fonts::class,
			Scripts\Enqueued::class,
			Scripts\Ad_Hoc::class,
		];

		foreach ( $classes as $class ) {
			add_action(
				static::QUEUE_HOOK,
				[
					call_user_func( [ $class, 'get_instance' ] ),
					'process'
				],
				static::QUEUE_PRIORITY
			);
		}
	}

	/**
	 * Automatically preload certain scripts.
	 */
	public function register_enqueued_scripts(): void {
		$handles = [
			'jquery',
			'pmc-adm-loader',
			'pmc-hooks',
		];

		$handles = apply_filters( static::FILTER_TAG_ENQUEUED_HANDLES, $handles );

		array_map( [ Scripts\Enqueued::class, 'add' ], (array) $handles );
	}
}
