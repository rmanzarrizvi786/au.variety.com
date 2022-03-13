<?php

namespace PMC\Export;

use PMC\Global_Functions\Traits\Singleton;

/**
 * This abstract class defined the required functions that Ajax API to use
 * Class Stream
 * @package PMC\Export
 */
abstract class Stream {
	use Singleton;
	const ID = 'stream';  // This ID should be assign to a unique id identifying the new data stream

	protected function __construct() {
		Ajax_Api::get_instance()->register_stream( $this );
	}

	/**
	 * @return string
	 */
	public function id() : string {
		return static::ID;
	}

	/**
	 * @return int
	 */
	public abstract function pages() : int;

	/**
	 * @param int $page
	 * @return mixed
	 */
	public abstract function data( int $page );

}
