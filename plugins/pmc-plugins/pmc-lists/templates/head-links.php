<?php

if ( ! empty( $prev_url ) ) {
?>

	<link rel="prev" href="<?php echo esc_url( $prev_url ); ?>" />

<?php
}

if ( ! empty( $next_url ) ) {
?>

	<link rel="next" href="<?php echo esc_url( $next_url ); ?>" />

<?php
}
