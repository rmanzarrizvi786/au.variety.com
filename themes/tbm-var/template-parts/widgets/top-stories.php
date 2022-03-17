<?php

/**
 * Top Stories Widget.
 *
 * This widget is a widget containing three widgets:
 *     1. Top stories carousel
 *     2. Most popular / Most viewed widget,
 *     3. Two ads
 *
 * In this file, we first set up the data for each pattern, then
 * render all of them with some containing markup at the end.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

if (empty($data['articles'])) {
	return;
}

$larva_populate = \Variety\Inc\Larva_Populate::get_instance();

$top_stories = PMC\Core\Inc\Larva::get_instance()->get_json('modules/top-stories.prototype');

$top_stories['c_heading']['c_heading_text'] = __('Top Stories', 'pmc-variety');

// Set up templates
$template_keys = [
	0 => 'o_story_first',
	1 => 'o_story_second',
	2 => 'o_story_third',
	3 => 'o_story_fourth',
	4 => 'o_story_fifth',
	5 => 'o_story_sixth',
];

$i = 0;

foreach ($data['articles'] as $story_data_item) {

	$template = $top_stories[$template_keys[$i]];

	if (0 === $i) {
		$template['c_title']['c_title_classes'] .= ' js-LatestNewsButton-WaypointStart';
	}

	$template['c_title']      = $larva_populate->c_title($story_data_item, $template);
	$template['c_dek']        = $larva_populate->c_dek($story_data_item, $template, $story_data_item);
	$template['c_lazy_image'] = $larva_populate->c_lazy_image($story_data_item, $template, ['image_size' => 'landscape-medium']);

	if (0 === $i) {
		// Show hard coded text for the first story vertical
		$template['c_span']['c_span_text'] = __('Top Story', 'pmc-variety');
	} else {
		/* if (
			in_array(
				get_post_type($story_data_item),
				[Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE],
				true
			)
		) {
			$template['c_span']['c_span_text']          = __('VIP+', 'pmc-variety');
			$template['c_span']['c_span_url']           = \Variety\Plugins\Variety_VIP\VIP::vip_url();
			$template['c_span']['c_span_link_classes'] .= ' u-color-brand-vip-primary ';
		} else {
			$template['c_span'] = $larva_populate->c_span_vertical($story_data_item, $template);
		} */
		$template['c_span'] = $larva_populate->c_span_vertical($story_data_item, $template);
	}

	$template['c_link']      = $larva_populate->c_link_author($story_data_item->ID, $template);
	$template['c_timestamp'] = $larva_populate->c_timestamp($story_data_item, $template);

	// TODO: Add this to Larva_Populate
	$template['video_permalink_url'] = false;

	if (PMC_Featured_Video_Override::get_instance()->has_featured_video($story_data_item) || \Variety_Top_Videos::POST_TYPE_NAME === get_post_type($story_data_item)) {
		$template['video_permalink_url'] = get_permalink($story_data_item);
	}

	$top_stories[$template_keys[$i]] = $template;

	$i++;
}

// 2. Most Popular / Most Viewed Widget

$count = !empty($data['popular_count']) ? (int) $data['popular_count'] : 8;

$days   = 7;
$period = 1;

if (!\PMC::is_production()) {
	$days   = 1000;
	$period = 1000;
}

$popular_posts = \PMC\Core\Inc\Top_Posts::get_posts($count, $days, $period, 'most_viewed');


// 3. Ad Widgets

// $top_ad    = PMC\Core\Inc\Larva::get_instance()->get_json('modules/vip-banner.300x250');
// $bottom_ad = PMC\Core\Inc\Larva::get_instance()->get_json('modules/cxense-widget.300x250');

// vip-banner.300x250 - top
// $top_ad['homepage_top_stories_ad_action'] = 'homepage-top-stories';

// cxense widget 300x250 - bottom
// $bottom_ad['cxense_id_attr'] = 'cx-module-300x250';

?>

<div class="lrv-u-flex@tablet lrv-u-padding-t-1 lrv-a-wrapper">

	<?php
	\PMC::render_template(
		sprintf('%s/template-parts/patterns/modules/top-stories.php', untrailingslashit(CHILD_THEME_PATH)),
		$top_stories,
		true
	);
	?>

	<aside class="u-width-320@tablet lrv-u-flex-shrink-0 lrv-u-flex@tablet lrv-u-flex-direction-column lrv-a-space-children-vertical lrv-a-space-children--1 u-padding-l-1@tablet">
		<!-- <div class="a-hidden@mobile-max">
			<?php
			// \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/vip-banner.php', $top_ad, true);
			?>
		</div> -->
		<?php
		\PMC::render_template(
			sprintf('%s/template-parts/widgets/most-viewed.php', untrailingslashit(CHILD_THEME_PATH)),
			[
				'data' => [
					'articles'                         => $popular_posts,
					'popular_count'                    => $count,
				],
			],
			true
		);
		?>
		<div class="a-hidden@mobile-max">
			<?php
			// \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $bottom_ad, true);
			?>
		</div>
	</aside>

</div>