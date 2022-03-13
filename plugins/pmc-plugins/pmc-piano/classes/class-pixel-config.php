<?php

namespace PMC\Piano;

use JsonSerializable;

/**
 * Manage pixel data for subscription tracking.
 *
 * @package PMC\Piano
 */
class Pixel_Config implements JsonSerializable {
	/**
	 * @var string
	 */
	private $ga_checkout_conversion_id;

	/**
	 * @var string
	 */
	private $facebook_checkout_content_name;

	/**
	 * @var string
	 */
	private $linkedin_start_checkout_pixel;

	/**
	 * @var string
	 */
	private $linkedin_complete_checkout_pixel;

	/**
	 * @var string
	 */
	private $digioh_checkout_pixel;

	/**
	 * @param string $id
	 * @return self
	 */
	public function set_ga_checkout_conversion_id( string $id ): self {
		$this->ga_checkout_conversion_id = $id;

		return $this;
	}

	public function set_facebook_checkout_content_name( string $content_name ): self {
		$this->facebook_checkout_content_name = $content_name;

		return $this;
	}

	public function set_linkedin_start_checkout_pixel( string $pixel ): self {
		$this->linkedin_start_checkout_pixel = $pixel;

		return $this;
	}

	public function set_linkedin_complete_checkout_pixel( string $pixel ): self {
		$this->linkedin_complete_checkout_pixel = $pixel;

		return $this;
	}

	public function set_digioh_checkout_pixel( string $pixel ): self {
		$this->digioh_checkout_pixel = $pixel;

		return $this;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'ga'       => [
				'conversion_id' => $this->ga_checkout_conversion_id,
			],
			'facebook' => [
				'content_name' => $this->facebook_checkout_content_name,
			],
			'linkedin' => [
				'pixel_checkout_start'    => $this->linkedin_start_checkout_pixel,
				'pixel_checkout_complete' => $this->linkedin_complete_checkout_pixel,
			],
			'digioh'   => [
				'pixel_checkout_start' => $this->digioh_checkout_pixel,
			],
		];
	}
}
