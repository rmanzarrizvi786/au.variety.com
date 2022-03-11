<?php
/**
 * Top 3 Engaging Shows Iframe Template.
 * Trending TV
 *
 * @package pmc-variety-2020
 */

$heading = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/heading.trending-tv' );

$heading['c_heading_main']['c_heading_id_attr'] = 'trending_tv_engagement';
$heading['c_heading_main']['c_heading_text']    = $engagement;
$heading['c_heading_time']['c_heading_id_attr'] = '';
$heading['c_heading_time']['c_heading_text']    = 'Last 24 Hours';

?>
<div class="_trending_tv_engagement lrv-u-border-t-3 lrv-u-border-color-brand-secondary-dark lrv-u-text-align-center">

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/heading.trending-tv', $heading, true ); ?>
	<div class="a-hidden@mobile-max">
		<iframe style="width: 960px; height: 640px;" src="https://display.sprinklr.com/pub/8651/YaUq8_FQ/1" title="<?php esc_attr( $engagement ); ?>"></iframe>
	</div>
	<div class="mobile-container // a-hidden@tablet">
		<iframe style="width: 360px; height: 640px;" src="https://display.sprinklr.com/pub/8651/YaVZY_UI/1" title="<?php esc_attr( $engagement ); ?>"></iframe>
	</div>
</div>
