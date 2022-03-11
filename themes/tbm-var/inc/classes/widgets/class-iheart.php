<?php
/**
 * IHeart iframe widget
 *
 * @since   2018-02-28 Jignesh Nakrani READS-1071
 *
 * @package pmc-variety-2017
 *
 */

namespace Variety\Inc\Widgets;

class Iheart extends Variety_Base_Widget {

	const ID = 'iheart-iframe';

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		parent::__construct(
			static::ID,
			__( 'iHeart iframe widget', 'pmc-variety' ),
			array( 'description' => __( 'Displays an iframe embed of iHeart Radio to promote Variety-produced podcasts', 'pmc-variety' ) )
		);
	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @return array
	 */
	protected function get_fields() {
		return [
			'title'         => [
				'label' => __( 'iHeart Iframe Title', 'pmc-variety' ),
				'type'  => 'text',
			],
			'subtitle'      => [
				'label' => __( 'iHeart Iframe Subtitle', 'pmc-variety' ),
				'type'  => 'text',
			],
			'url'           => [
				'label' => __( 'iHeart Iframe URL', 'pmc-variety' ),
				'type'  => 'url',
			],
			'iframe_height' => [
				'label' => __( 'Height of iframe (in pixels)', 'pmc-variety' ),
				'type'  => 'text',
				'value' => '170',
			],
		];
	}

	/**
	 * To render output of widget.
	 *
	 * @param array $instance Widget instance data.
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	protected function _output( $instance ) {

		//Blocking for EU traffic until this cookie setting is addressed.
		$region = \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code();

		if ( 'eu' === $region || 'eu' === \PMC::filter_input( INPUT_GET, 'region' ) ) {
			return;
		}

			\PMC::render_template(
			CHILD_THEME_PATH . '/template-parts/widgets/iheart-widget.php',
			[
				'instance' => $instance,
			],
			true
		);

	}
}
