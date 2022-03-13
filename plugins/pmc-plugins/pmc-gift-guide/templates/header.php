<!DOCTYPE html>
<!--[if lt IE 7]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if (IE 7)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!-->
<html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

<head>
	<meta charset="utf-8">
	<!-- Google Chrome Frame for IE -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>
		<?php echo wp_kses_post( \PMC\Gift_Guide\Common::get_instance()->get_title() ); ?>
	</title>

	<!-- Mobile  Meta -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta http-equiv="cleartype" content="on">
	<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">

	<?php wp_head(); ?>

	<?php
	/**
	 *
	 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
	 * @since  2018-07-12 READS-1176
	 */
	do_action( 'pmc_tags_head' );
	?>

	<?php get_template_part( 'inc/tags', 'head' ); ?>

</head>

<body <?php body_class(); ?> <?php if ( isset( $page_template ) )
	echo ' id="' . esc_attr( $page_template ) . '"' ?>>
<?php do_action( 'pmc-tags-top' ); ?>
<?php get_template_part( 'inc/tags', 'top' ); ?>

<header id="gift-guide-header">
	<div id="back-to-brand">
		<a href="<?php echo esc_url( home_url() ); ?>">
			<i class="fa fa-angle-double-left" aria-hidden="true"></i>Back to <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</a>
	</div>
	<div class="gift-guide-header--logo">
		<div class="gift-guide-header--logo--container <?php echo esc_html( strtolower( get_bloginfo( 'name' ) ) ); ?>"></div>
	</div>
	<input type="checkbox" id="show-menu">
	<label for="show-menu" onclick></label>
	<?php echo wp_kses_post( wp_nav_menu( array(
			'theme_location'  => 'pmc-gift-guide-menu',
			'container_class' => 'pmc-gift-guide-menu',
			'echo'            => false
		) ) ); ?>
</header>
