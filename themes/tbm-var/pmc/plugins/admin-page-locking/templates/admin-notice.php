<?php
/**
 * Admin notice for lock.
 */
?>
<div class="updated error" id="apl-lock-error">
	<p>
		<?php echo esc_html( $the_message ); ?>
	</p>
</div>
<input type="hidden" id="apl-user" name="apl-user" value="1"/>

<div id="apl-message" style="display:none;">
	<p id="apl-message-content"></p>
	<p>
		<a class="button-primary apl-confirm-button" data-confirm="yes"><?php esc_html_e( 'Yes', 'pmc-core' ); ?></a>
		<a class="button-secondary apl-confirm-button" data-confirm="no"><?php esc_html_e( 'No', 'pmc-core' ); ?></a>
	</p>
</div>
