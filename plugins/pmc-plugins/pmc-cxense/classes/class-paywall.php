<?php

namespace PMC\Cxense;
use \PMC_Cheezcap;

/**
 * Class Paywall
 * @package pmc-cxense
 */
class Paywall implements \JsonSerializable {
	/**
	 * @var string
	 */
 	protected $div_id;

	/**
	 * @var string
	 */
	protected $widget_id;

	/**
	 * @var bool
	 */
	protected $enabled_filter;

	/**
	 * Paywall constructor.
	 */
	public function __construct() {
		$paywall_id           = \PMC_Cheezcap::get_instance()->get_option( 'pmc_cxense_paywall_id' );
		$this->widget_id      = apply_filters( 'pmc_cxense_paywall_module', $paywall_id );
		$this->div_id         = 'cx-paywall';
		$this->enabled_filter = apply_filters( 'pmc_cxense_paywall_enabled', true );
	}

	/**
	 * @return bool
	 */
	public function is_enabled(): bool {
		return $this->enabled_filter && ! empty( $this->widget_id );
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'widgetId'        => $this->widget_id,
			'targetElementId' => $this->div_id,
		];
	}
}
