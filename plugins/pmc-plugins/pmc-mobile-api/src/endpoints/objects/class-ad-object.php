<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\Ad_Object class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

/**
 * Ad Object.
 */
class Ad_Object {

	/**
	 * Ad object data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Ad constructor.
	 *
	 * @param array $data Ad config.
	 */
	public function __construct( $data = [] ) {
		$this->data = $data;
	}

	/**
	 * Get ad.
	 *
	 * @return array
	 */
	public function get_ad(): array {
		return [
			'height' => absint( $this->data['height'] ?? 320 ),
			'width'  => absint( $this->data['width'] ?? 250 ),
		];
	}
}
