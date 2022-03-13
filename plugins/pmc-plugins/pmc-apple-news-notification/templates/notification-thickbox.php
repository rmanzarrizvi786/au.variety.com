<?php
/**
 * Thickbox template part.
 */

?>

<div id="notification-thickbox" style="display:none;">
	<p>
		<?php esc_html_e( "Everyone subscribing to this site's Apple News channel will receive a notification immediately, so double-check your headline before sending it.", 'pmc-apple-news-notification' ); ?>
	</p>

	<label for="notification-body">
		<?php esc_html_e( 'Alert headline (required, and cannot be longer than 130 characters)', 'pmc-apple-news-notification' ); ?>
	</label>
	<br>
	<input type="text" name="notification-body" id="notification-body" maxlength="130" class="notification-input">
	<div class="button-line">
		<input class="button-primary notification-input" type="submit" id="notification-submit" value="<?php esc_attr_e( 'Send notification', 'pmc-apple-news-notification' ); ?>">
		<img src="<?php echo esc_url( PMC_APPLE_NEWS_NOTIFICATION_URL . 'images/squares.svg' ); ?>" id="loading-img" style="display: none;" alt="<?php esc_attr_e( 'Loading Icon', 'pmc-apple-news-notification' ); ?>" width="30" height="30" />
	</div>
	<p class="error"></p>
	<p class="success"></p>
	<p class="explanation"></p>
</div>
