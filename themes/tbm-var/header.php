<?php

/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and some parts of the <body>. See
 * template-parts/header/main.php and template-parts/header/main-mobile.php for
 * the remainder of the header.
 *
 * @package pmc-variety
 */

// Styling info in PHP because this is too high level for patterns.
$bg_color_class = is_home() || (is_archive() && !is_author() && !is_tag('documentaries-to-watch') && !is_tag('what-to-hear') && !is_tag('trending-tv')) ? 'lrv-u-background-color-grey-lightest' : '';

?>
<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="theme-color" content="#ffffff">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php get_template_part('template-parts/header/header-icons'); ?>
	<?php wp_head(); ?>
	<?php do_action('pmc_tags_head'); ?>

	<meta name='impact-site-verification' value='c9d092e3-f72c-4cca-b780-64a051b0a192'>

	<style>

	</style>
</head>

<body <?php body_class(); ?>>
	<?php do_action('pmc-tags-top'); // @codingStandardsIgnoreLine 
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WKG6893" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<div id="skin-ad-section">
		<div id="skin-ad-container">
			<?php pmc_adm_render_ads('skin'); ?>
		</div>
	</div>

	<?php if (!\PMC\Gallery\View::is_standard_gallery()) : ?>

		<div id="main-wrapper">
			<main class="lrv-u-padding-b-2 <?php echo esc_attr($bg_color_class); ?>">

				<?php

				// Exclude leaderboard on pages by default except for excluded page templates.
				global $post;

				$excluded_page_templates = [
					'page-editorial-hub.php',
				];

				$current_template = $post ? get_page_template_slug($post->ID) : null;

				if (!is_page() || in_array($current_template, (array) $excluded_page_templates, true)) {
					get_template_part('template-parts/header/leaderboard');
				}

				\PMC::render_template(
					sprintf('%s/template-parts/header/main.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);

				?>
				<div class="vy-leaderboard-ad"></div> <!-- marker for pmc-sticky-ad (aka Mobile Adhesion Ads) -->
				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/header/menu.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);
				?>

			<?php endif; ?>

			<?php
			do_action('before');
