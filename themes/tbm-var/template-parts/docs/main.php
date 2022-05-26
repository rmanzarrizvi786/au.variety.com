<?php

/**
 * Main Docs Template.
 *
 * @package pmc-variety-2020
 */

/**
 * Docs Header Setup
 */
// Get default header data.
$header = PMC\Core\Inc\Larva::get_instance()->get_json('modules/docs-header.prototype');

// Current page.
$current_page = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get global curation settings.
// $settings = get_option('global_curation', []);
// $settings = $settings['tab_variety_documentaries'];

$header['c_heading']['c_heading_text'] = !empty($settings['variety_heading_text']) ? $settings['variety_heading_text'] : $header['c_heading']['c_heading_text'];

$header['o_sponsored_by'] = []; // ['o_sponsored_by_text'] = !empty($settings['variety_sponsored_by_text']) ? $settings['variety_sponsored_by_text'] : $header['o_sponsored_by']['o_sponsored_by_text'];

$header['c_logo'] = []; // ['c_logo_url'] = !empty($settings['variety_sponsor_link']) ? $settings['variety_sponsor_link'] : $header['c_logo']['c_logo_url'];

?>
<div class="__documentaries lrv-a-wrapper">
	<?php
	\PMC::render_template(
		sprintf('%s/template-parts/patterns/modules/docs-header.php', untrailingslashit(CHILD_THEME_PATH)),
		$header,
		true
	);
	if (1 === $current_page) {
		\PMC::render_template(
			sprintf('%s/template-parts/docs/reviews.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
		\PMC::render_template(
			sprintf('%s/template-parts/docs/classics.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
	}

	\PMC::render_template(
		sprintf('%s/template-parts/common/latest-news.php', untrailingslashit(CHILD_THEME_PATH)),
		[
			'module'      => 'modules/latest-news-river.docs',
			'header_text' => __('Documentary News', 'pmc-variety'),
			'more_button' => __('More Documentary News', 'pmc-variety'),
		],
		true
	);
	if (1 === $current_page) {
		\PMC::render_template(
			sprintf('%s/template-parts/docs/lists.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
		\PMC::render_template(
			sprintf('%s/template-parts/docs/videos.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
	}
	?>

</div>