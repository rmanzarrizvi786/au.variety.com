<?php

if ( isset( $list_title ) && isset( $list_id ) ) {
?>
<p>
	<?php esc_html_e( 'Currently viewing list: ' ); ?>
	<strong>
		<a href="<?php echo esc_url( $list_permalink ); ?>">
			<?php echo esc_html( $list_title ); ?>
		</a>
	</strong>
	(<a href="<?php echo esc_url( get_edit_post_link( $list_id ) ); ?>">
		<?php esc_html_e( 'edit' ); ?>
	</a>)
</p>
<?php
}
