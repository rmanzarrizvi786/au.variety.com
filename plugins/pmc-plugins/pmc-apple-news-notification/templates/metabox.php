<?php
/**
 * Metabox template part.
 */
?>

<?php if ( $is_sending_notifications_allowed ) { ?>
	<?php esc_html_e( 'Apple News Status', 'pmc-apple-news-notification' ); ?> : <?php echo esc_html( $apple_news_status ); ?><br/><br/>
	<input type="button" value="<?php esc_attr_e( 'Send Notification', 'pmc-apple-news-notification' ); ?>"
		<?php echo 'LIVE' !== $apple_news_status ? 'disabled="disabled" class="button"' : 'class="button thickbox notification-thickbox-link"'; ?> data-anf-post-title="<?php echo esc_attr( $anf_post_title ); ?>"  data-post-id=<?php echo absint( $post_id ); ?>
	/>
	<?php
}
