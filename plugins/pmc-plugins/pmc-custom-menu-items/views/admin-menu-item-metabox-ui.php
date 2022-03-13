<?php
/**
 * View file for rendering metabox for hook callback data on Custom Menu Item add/edit screen
 */
?>
<?php wp_nonce_field( $nonce['action'], $nonce['field'] ); ?>
<p id="<?php echo esc_attr( $plugin_id ); ?>-filter-section">
	<label for="<?php echo esc_attr( $plugin_id ); ?>-filter"><strong>Filter to Callback:</strong></label>&nbsp;&nbsp;
	<?php echo esc_html( $filter_prefix ); ?>
	<input type="text" id="<?php echo esc_attr( $plugin_id ); ?>-filter" name="<?php echo esc_attr( $plugin_id ); ?>-filter" size="25" value="<?php echo esc_attr( $callback_data['filter'] ); ?>">
	<br>
	<span class="description">
		Your listener function must listen for a filter with "<strong><?php echo esc_html( $filter_prefix ); ?></strong>" prefix otherwise it will not be called.
		So for example, if you enter "<strong>cool-stuff</strong>" in the text box above then your listener function must listen on "<strong><?php echo esc_html( $filter_prefix ); ?>cool-stuff</strong>" filter.
	</span>
</p>
<p id="<?php echo esc_attr( $plugin_id ); ?>-param1-section">
	<label for="<?php echo esc_attr( $plugin_id ); ?>-param1"><strong>First Parameter:</strong></label>&nbsp;&nbsp;
	<input type="text" id="<?php echo esc_attr( $plugin_id ); ?>-param1" name="<?php echo esc_attr( $plugin_id ); ?>-param1" size="25" value="<?php echo esc_attr( $callback_data['param1'] ); ?>">
	<br>
	<span class="description">
		Add the value that you want to be passed to listener function as first parameter. This is optional.
	</span>
</p>
<p id="<?php echo esc_attr( $plugin_id ); ?>-param2-section">
	<label for="<?php echo esc_attr( $plugin_id ); ?>-param2"><strong>Second Parameter:</strong></label>&nbsp;&nbsp;
	<input type="text" id="<?php echo esc_attr( $plugin_id ); ?>-param2" name="<?php echo esc_attr( $plugin_id ); ?>-param2" size="25" value="<?php echo esc_attr( $callback_data['param2'] ); ?>">
	<br>
	<span class="description">
		Add the value that you want to be passed to listener function as second parameter. This is optional.
	</span>
</p>
