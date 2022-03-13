<?php
if ( $notices ) {
	echo "<div class='notice notice-info'><ul>";
	foreach ( $notices as $notice ) {
		echo '<li>' . esc_html( $notice ) . '</li>';
	}
	echo '</ul></div>';
}
?>

<form method="post" action="<?php menu_page_url( 'sailthru_setup' ); ?>">
	<?php $nonce->render(); ?>
	<input type="hidden" name="action" value="post_types">
	<h2>API</h2>
	<p>
		<div>
			<a href="<?php menu_page_url( 'sailthru_setup' ); ?>&reset_api=1">Change API Settings</a>
			| <a href="<?php menu_page_url( 'sailthru_setup' ); ?>&action=flush_cache&<?php echo esc_attr( $nonce->get_query_string() ); ?>">Flush API Cache</a>
		</div>
	</p>

	<h2> News Letter and Breaking News Alert Post Settings</h2>
	<p>Below are the list of all the custom post types please select which ones you would like enabled for news letters and breaking news alerts.</p>
	<?php foreach ( $post_types as $post_type ) { ?>
		<div>
			<input type="checkbox" id="<?php echo esc_attr( $post_type ); ?>" name="post_types[<?php echo esc_attr( $post_type ); ?>]"
				   value="1" <?php checked( is_supported_sailthru_post_type( $post_type ), 1 ); ?>/>
			<label for="<?php echo esc_attr( $post_type ); ?>"><?php echo esc_html( $post_type ); ?></label>
		</div>
	<?php } ?>
	<div>
		<input type="submit" value="Update"/>
	</div>
</form>
