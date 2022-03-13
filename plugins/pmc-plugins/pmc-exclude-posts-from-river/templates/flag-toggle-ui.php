<?php

/**
 * UI for the River Post Exclusion flag toggle
 */
?>
<div class="misc-pub-section">
	<label for="<?php echo esc_attr( $key ); ?>">
		<input type="checkbox" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $value, true ); ?> />
		Exclude this post from the river.
	</label>
	<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>
</div>
