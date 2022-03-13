<?php
/**
 * List of whitelisting domains.
 *
 * @package pmc-iframe-widget
 */

if ( empty( $whitelist_domains ) || ! is_array( $whitelist_domains ) ) {
	return '';
}
?>

<h4><?php echo esc_html__( 'Make sure your url is containing below domains:', 'pmc-iframe-widget' ); ?></h4>

<ul>
	<?php foreach ( $whitelist_domains as $whitelist_domain ) { ?>

		<li><?php echo esc_html( $whitelist_domain ); ?></li>

	<?php } ?>
</ul>
