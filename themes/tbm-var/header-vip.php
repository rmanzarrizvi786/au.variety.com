<?php
/**
 * VIP Header Template.
 *
 * @package pmc-variety-2020
 */
?><!DOCTYPE html>
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
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="theme-color" content="#ffffff">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<?php get_template_part( 'template-parts/header/header-icons' ); ?>
	<?php wp_head(); ?>
	<?php do_action( 'pmc_tags_head' ); ?>
</head>
<body <?php body_class(); ?>>

<?php do_action( 'pmc-tags-top' ); // @codingStandardsIgnoreLine ?>

<div id="main-wrapper">
	<main>

<?php
do_action( 'before' );

\PMC::render_template(
	sprintf( '%s/template-parts/vip/header/header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

//EOF
