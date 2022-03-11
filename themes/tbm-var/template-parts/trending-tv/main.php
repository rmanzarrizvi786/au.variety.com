<?php

/**
 * Main Trending TV Template.
 *
 * @package pmc-variety-2020
 */

/**
 * Docs Header Setup
 */
// Get default header data.
$header = PMC\Core\Inc\Larva::get_instance()->get_json('modules/docs-header.trending-tv');

// Current page.
$current_page = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get global curation settings.
$settings = get_option('global_curation', []);
$settings = $settings['tab_variety_trending_tv'];

$sponsor_heading = empty($settings['variety_sponsor_widget']) ? 'DirectTV Trending' : $settings['variety_sponsor_widget'];

$trending_text = [
	'seven_days'  => empty($settings['variety_trending_shows']) ? 'Top 10 Trending Shows' : $settings['variety_trending_shows'],
	'engagement'  => empty($settings['variety_engagement']) ? 'Engagement of Top 3 Shows' : $settings['variety_engagement'],
	'continental' => empty($settings['variety_continental']) ? 'Top 2 Shows in the Continental U.S.' : $settings['variety_continental'],
	'methodology' => empty($settings['variety_trending_methodology']) ? '' : $settings['variety_trending_methodology'],
];

$header['c_heading']['c_heading_classes'] .= ' u-letter-spacing-012';
$header['c_heading']['c_heading_text']     = !empty($settings['variety_heading_text']) ? $settings['variety_heading_text'] : $header['c_heading']['c_heading_text'];

$header['o_sponsored_by']['o_sponsored_by_text'] = !empty($settings['variety_sponsored_by_text']) ? $settings['variety_sponsored_by_text'] : $header['o_sponsored_by']['o_sponsored_by_text'];

$header['c_logo']['c_logo_url'] = !empty($settings['variety_sponsor_link']) ? $settings['variety_sponsor_link'] : $header['c_logo']['c_logo_url'];

?>
<div class="__trending-tv lrv-a-wrapper">
	<?php
	\PMC::render_template(
		sprintf('%s/template-parts/patterns/modules/docs-header.trending-tv.php', untrailingslashit(CHILD_THEME_PATH)),
		$header,
		true
	);
	if (1 === $current_page) {

		\PMC::render_template(
			sprintf('%s/template-parts/trending-tv/seven-days.php', untrailingslashit(CHILD_THEME_PATH)),
			$trending_text,
			true
		);
		\PMC::render_template(
			sprintf('%s/template-parts/trending-tv/engagement.php', untrailingslashit(CHILD_THEME_PATH)),
			$trending_text,
			true
		);
		\PMC::render_template(
			sprintf('%s/template-parts/trending-tv/top-shows.php', untrailingslashit(CHILD_THEME_PATH)),
			$trending_text,
			true
		);
	?>
		<div class="_trending_tv_methodology lrv-u-border-t-1 lrv-u-border-color-grey-light u-font-family-secondary a-font-secondary-regular-s">
			<p>
				<?php echo wp_kses_post($trending_text['methodology']); ?>
			</p>
		</div>
	<?php
		// DirectTV Trending Widget
		/* the_widget(
			'\Variety\Inc\Widgets\What_To_Watch',
			[
				'stream_heading' => $sponsor_heading,
				'stream_module'  => 'vy-trending-tv-directtv',
			] 
		); */

		// AKA top stories
		\PMC::render_template(
			sprintf('%s/template-parts/trending-tv/leads.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
	}

	\PMC::render_template(
		sprintf('%s/template-parts/common/latest-news.php', untrailingslashit(CHILD_THEME_PATH)),
		[
			'module'      => 'modules/latest-news-river.docs',
			'header_text' => __('TV News', 'pmc-variety'),
			'more_button' => __('More TV News', 'pmc-variety'),
		],
		true
	);
	?>

</div>