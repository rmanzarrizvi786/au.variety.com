<?php
/**
 * Template to render style vars for Label Badge
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-07-01
 */

if ( empty( $styles ) || ! is_array( $styles ) ) {
	return;
}

$is_amp = ( ! empty( $is_amp ) );
?>

<?php if ( ! $is_amp ) { ?>
<style type="text/css">
<?php } ?>
	:root {
		<?php
		foreach ( $styles as $var => $val ) {
			printf( '--%1$s: %2$s;', $var, $val );  // phpcs:ignore
		}
		?>
	}
<?php if ( ! $is_amp ) { ?>
</style>
<?php } ?>
