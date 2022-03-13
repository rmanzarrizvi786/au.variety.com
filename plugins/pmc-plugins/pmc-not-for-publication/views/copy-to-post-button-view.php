<?php
/**
 * View file for rendering "Copy to Post" button on NFP Article add/edit screen
 */
?>
<div id="<?php echo esc_attr( $div_id ); ?>" class="misc-pub-section">
	<input type="hidden" name="<?php echo esc_attr( $input_hidden_name ); ?>" id="<?php echo esc_attr( $input_hidden_name ); ?>" value="no" />
	<input type="submit" name="<?php echo esc_attr( $input_submit_name ); ?>" id="<?php echo esc_attr( $input_submit_name ); ?>" class="button button-primary" value="Copy to a Post" />
</div>
