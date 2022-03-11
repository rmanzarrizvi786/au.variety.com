<?php
/**
 * Head Template
 *
 * Template for the Head element.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
	<meta charset="utf-8">
	<!-- Google Chrome Frame for IE -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php the_title(); ?> - <?php esc_html_e( 'Variety500 - Top 500 Entertainment Business Leaders | Variety.com', 'pmc-variety' ); ?></title>

	<meta name="description" content="<?php esc_html_e( 'For the latest news on the global media industry\'s 500 most influential business leaders, check out Variety500 on Variety.com today.', 'pmc-variety' ); ?>" />
	<!-- START: Maintained from variety.com -->
	<!-- Mobile  Meta -->
	<meta name="HandheldFriendly" content="True">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta http-equiv="cleartype" content="on">
	<!-- Favicon -->
	<link rel="shortcut icon" href="https://s0.wp.com/wp-content/themes/vip/pmc-variety-2014/library/images/icons/favicon.ico" />
	<!-- Tile icon for Win8 (144x144 + tile color) -->
	<meta name="msapplication-TileImage" content="https://s0.wp.com/wp-content/themes/vip/pmc-variety-2014/library/images/icons/apple-touch-icon-144x144-precomposed.png">
	<meta name="msapplication-TileColor" content="#ffffff">
	<link rel="pingback" href="https://variety.com/xmlrpc.php">

	<script>
		window.pmc_is_adblocked = false;
	</script>
	<link rel='dns-prefetch' href='//s0.wp.com' />
	<link rel='dns-prefetch' href='//s2.wp.com' />
	<link rel='dns-prefetch' href='//s1.wp.com' />
	<link rel='dns-prefetch' href='//pmcvariety.wordpress.com' />
	<link rel='dns-prefetch' href='//video-cdn.variety.com' />
	<link rel='dns-prefetch' href='//s.swiftypecdn.com' />
	<link rel='dns-prefetch' href='//load.instinctiveads.com' />
	<link rel='dns-prefetch' href='//fonts.googleapis.com' />

	<meta name="application-name" content="Variety" />
	<meta name="msapplication-window" content="width=device-width;height=device-height" />
	<meta name="msapplication-tooltip" content="Author posts, manage comments, and manage Variety." />
	<!-- END: Maintained from variety.com -->

	<?php
	wp_head();
	do_action( 'pmc_tags_head' );
	?>
</head>
<body>
<?php do_action( 'pmc-tags-top' ); //@codingStandardsIgnoreLine ?>

<div class="l-offcanvas__site">
