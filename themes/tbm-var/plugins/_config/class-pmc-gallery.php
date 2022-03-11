<?php
/**
 * Config file for pmc-gallery-v4 plugin from pmc-plugins
 *
 * @since   2019-07-31
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Sponsored_Content\Sponsored_Content;

class PMC_Gallery {

	use Singleton;

	/**
	 * Construct Method.
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 *
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc_gallery_v4_config', [ $this, 'filter_gallery_settings' ] );
		add_filter( 'pmc_list_v4_config', [ $this, 'filter_list_settings' ] );

	}

	/**
	 * Filter pmc gallery settings.
	 *
	 * @param array $settings gallery settings.
	 *
	 * @return array
	 */
	public function filter_gallery_settings( $settings ) {

		$settings['logo'] = array(
			'src'    => get_stylesheet_directory_uri() . '/assets/build/svg/variety-logo.svg',
			'width'  => 140,
			'height' => 41,
		);

		$settings['sponsored']      = Sponsored_Content::get_instance()->get_sponsored_flag_text( get_the_ID(), __( 'PARTNER CONTENT', 'pmc-variety' ) );
		$settings['sponsoredStyle'] = [
			'fontFamily'      => '"IBM Plex Mono", monospace',
			'color'           => 'rgb(244, 126, 55)',
			'fontSize'        => '13px',
			'backgroundColor' => 'transparent',
			'verticalAlign'   => 'middle',
			'padding'         => '0',
		];

		$settings['styles']['theme-color']                       = 'var(--color-brand-primary)';
		$settings['styles']['vertical-headline-font-weight']     = 700;
		$settings['styles']['vertical-caption-font-weight']      = 400;
		$settings['styles']['vertical-headline-font-family']     = 'var(--font-family-secondary)';
		$settings['styles']['vertical-caption-font-family']      = 'var(--font-family-body)';
		$settings['styles']['horizontal-intro-card-font-family'] = 'var(--font-family-secondary)';

		$settings['styles']['horizontal-header-title-style'] = [
			'fontFamily'   => '"IBM Plex Sans", sans-serif',
			'fontWeight'   => 700,
			'fontSize'     => '26px',
			'paddingRight' => '6px',
		];

		$settings['styles']['horizontal-sidebar-style'] = [
			'display'       => 'flex',
			'flexDirection' => 'column',
		];

		$settings['styles']['horizontal-sidebar-gallery-title-style'] = [
			'order' => 1,
		];

		$settings['styles']['horizontal-sidebar-timestamp-style'] = [
			'order'      => 2,
			'fontFamily' => '"IBM Plex Mono", monospace',
			'fontWeight' => 600,
			'color'      => '#89959D',
			'fontSize'   => '11px',
			'padding'    => '6px 0',
		];

		$settings['styles']['horizontal-sidebar-slide-title-style'] = [
			'order'      => 3,
			'fontFamily' => '"IBM Plex Sans", sans-serif',
			'fontWeight' => 700,
			'fontSize'   => '24px',
		];

		$settings['styles']['horizontal-sidebar-description-style'] = [
			'order'      => 4,
			'fontFamily' => '"IBM Plex Sans", sans-serif',
			'fontSize'   => '16px',
		];

		$settings['styles']['horizontal-sidebar-caption-style'] = [
			'order'      => 5,
			'fontFamily' => '"IBM Plex Sans", sans-serif',
			'fontSize'   => '16px',
		];

		$settings['styles']['horizontal-sidebar-image-credit-style'] = [
			'order'      => 6,
			'fontFamily' => '"IBM Plex Mono", monospace',
			'fontWeight' => 600,
			'fontSize'   => '11px',
			'color'      => '#89959D',
		];

		$settings['subscriptionsLink'] = 'https://www.pubservice.com/variety/?PC=VY&PK=M674DTI';
		$settings['adsProvider']       = 'boomerang';

		return $settings;
	}

	/**
	 * Filter pmc list settings.
	 *
	 * @param array $settings list settings.
	 *
	 * @return array
	 */
	public function filter_list_settings( $settings ) {

		$settings['listNavBar']['parentElementQuerySelector']                               = '.header-sticky .lrv-a-wrapper';
		$settings['listNavBar']['parentElementStyle']['marginBottom']                       = '0';
		$settings['listNavBar']['containerElementAttributes']['style']['height']            = '36px';
		$settings['listNavBar']['renderElementAttributes']['style']['borderTop']            = 'solid 1px #ffffff';
		$settings['listNavBar']['renderElementAttributes']['style']['backgroundColor']      = 'var(--background-color-brand-accent)';
		$settings['listNavBar']['rangeElementAttributes']['className']                      = 'u-font-size-15 lrv-u-font-weight-bold';
		$settings['listNavBar']['rangeElementAttributes']['style']['color']                 = 'var(--color-grey-light)';
		$settings['listNavBar']['activeRangeElementAttributes']['style']['color']           = 'var(--color-brand-primary)';
		$settings['listNavBar']['progressBarElementAttributes']['style']['backgroundColor'] = 'var(--background-color-brand-primary)';

		$settings['styles']['theme-color']                       = 'var(--color-brand-primary)';
		$settings['styles']['vertical-headline-font-weight']     = 700;
		$settings['styles']['vertical-caption-font-weight']      = 400;
		$settings['styles']['vertical-headline-font-family']     = 'var(--font-family-secondary)';
		$settings['styles']['vertical-caption-font-family']      = 'var(--font-family-body)';
		$settings['styles']['horizontal-intro-card-font-family'] = 'var(--font-family-secondary)';

		$settings['subscriptionsLink'] = 'https://www.pubservice.com/variety/?PC=VY&PK=M674DTI';

		$settings['adsProvider'] = 'boomerang';

		return $settings;

	}

}
