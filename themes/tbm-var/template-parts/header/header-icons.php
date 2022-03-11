<?php
/**
 * Header Icons Template.
 *
 * @package pmc-artnews-2019
 */

$style_url      = get_stylesheet_directory_uri();
$style_url_path = wp_parse_url( $style_url, PHP_URL_PATH );
$stylesheet_url = $style_url_path;
?>

<!-- Add to home screen for iOS -->
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo PMC::esc_url_ssl_friendly( CHILD_THEME_URL . '/assets/app/icons/apple-touch-icon.png' ); // WPCS: XSS okay. ?>">
<link rel="apple-touch-icon" href="images/icons/apple-touch-icon.png">

<!-- Tile icons for Windows -->
<meta name="msapplication-config" content="<?php echo PMC::esc_url_ssl_friendly( CHILD_THEME_URL . '/assets/app/browserconfig.xml' ); // WPCS: XSS okay. ?>">
<meta name="msapplication-TileImage" content="<?php echo PMC::esc_url_ssl_friendly( CHILD_THEME_URL . '/assets/app/icons/icon-144x144.png' ); // WPCS: XSS okay. ?>">
<meta name="msapplication-TileColor" content="#eff4ff">

<!-- Favicons -->
<link rel="icon" type="image/png" href="<?php echo PMC::esc_url_ssl_friendly( CHILD_THEME_URL . '/assets/app/icons/favicon.png' ); // WPCS: XSS okay. ?>">
<link rel="shortcut icon" href="<?php echo PMC::esc_url_ssl_friendly( CHILD_THEME_URL . '/assets/app/icons/favicon.ico' ); // WPCS: XSS okay. ?>">
