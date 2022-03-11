<?php
/**
 * Top 10 Trending Shows Iframe Template.
 * Trending TV
 *
 * @package pmc-variety-2020
 */

$heading = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/heading.trending-tv' );

$heading['c_heading_main']['c_heading_id_attr'] = 'trending_tv_seven_days';
$heading['c_heading_main']['c_heading_text']    = $seven_days;
$heading['c_heading_time']['c_heading_id_attr'] = '';

?>
<div class="_trending_tv_seven_days lrv-u-border-t-3 lrv-u-border-color-brand-secondary-dark lrv-u-text-align-center">

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/heading.trending-tv', $heading, true ); ?>
	<div class="a-hidden@mobile-max">
		<iframe style="width: 960px; height: 1000px;" src="https://display.sprinklr.com/pub/8651/YaUklpHI/1" title="<?php esc_attr( $seven_days ); ?>"></iframe>
	</div>
	<div class="mobile-container // a-hidden@tablet">
		<iframe style="width: 360px; height: 1000px;" src="https://display.sprinklr.com/pub/8651/YaVWtmwV/1" title="<?php esc_attr( $seven_days ); ?>"></iframe>
	</div>
</div>
