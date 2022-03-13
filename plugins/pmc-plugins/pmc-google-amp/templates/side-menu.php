<?php
/**
 * Template for AMP article side menu.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-07-03
 *
 * @package pmc-google-amp
 */

?>
<amp-sidebar id="amp_side_menu" layout="nodisplay" side="<?php echo esc_attr( $location ); ?>">
	<?php
	$args = array(
		'theme_location' => 'amp_side_menu',
		'depth'          => 1,
	);
	if ( function_exists( 'wpcom_vip_cached_nav_menu' ) ) {
		wpcom_vip_cached_nav_menu( $args );
	} else {
		wp_nav_menu( $args );
	}
	?>
</amp-sidebar>
