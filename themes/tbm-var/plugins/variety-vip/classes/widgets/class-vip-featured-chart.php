<?php

/**
 * VIP Featured Chart Widget.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP\Widgets;

/**
 * VIP Featured Chart widget
 */
class VIP_Featured_Chart extends \FM_Widget
{

	/**
	 * VIP_Featured_Chart constructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct()
	{
		parent::__construct(
			'variety_vip_featured_chart',
			__('Variety VIP Featured Chart', 'pmc-variety'),
			[
				'classname'   => 'variety-vip-featured-chart',
				'description' => __('Display a featured chart.', 'pmc-variety'),
			]
		);
	}

	/**
	 * Display widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 *
	 * @throws \Exception
	 *
	 * @codeCoverageIgnore
	 */
	public function widget($args, $instance)
	{
		$data                                      = [];
		$data['featured_chart_classes']            = 'u-max-width-300 lrv-u-margin-lr-auto lrv-u-margin-lr-auto u-margin-b-225 u-margin-t-175@tablet';
		$data['featured_chart_iframe_url']         = $instance['iframe_url'] ?? '';
		$data['featured_chart_iframe_height_attr'] = $instance['iframe_height'] ?? '';
		$data['c_button_text']                     = $instance['button_text'] ?? '';
		$data['c_button_url']                      = $instance['button_url'] ?? '';

		\PMC::render_template(
			sprintf('%s/template-parts/vip/widgets/featured-chart.php', untrailingslashit(CHILD_THEME_PATH)),
			$data,
			true
		);
	}

	/**
	 * Define the fields that should appear in the widget.
	 *
	 * @return array Fieldmanager fields.
	 *
	 * @codeCoverageIgnore
	 */
	protected function fieldmanager_children()
	{
		return [
			'iframe_url'    => new \Fieldmanager_Link(esc_html__('iFrame Embed URL', 'pmc-variety')),
			'iframe_height' => new \Fieldmanager_TextField(esc_html__('iFrame Height', 'pmc-variety')),
			'button_text'   => new \Fieldmanager_TextField(esc_html__('Button Text', 'pmc-variety')),
			'button_url'    => new \Fieldmanager_Link(esc_html__('Button URL', 'pmc-variety')),
		];
	}
}
