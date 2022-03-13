<div class="wrap">
	<h2><?php esc_html_e( 'Google OAuth', 'pmc-plugins' ); ?></h2>

	<?php if ( ! empty( $_GET['success'] ) ) {
		switch ( $_GET['success'] ) {
			case 'google-connect':
				$message = __( 'Successfully connected to Google.', 'pmc-plugins' );
				break;
			case 'google-disconnect':
				$message = __( 'Disconnected from Google.', 'pmc-plugins' );
				break;
			default:
				$message = '';
				break;
		}
		if ( $message ) {
			echo '<div class="message updated"><p>' . esc_html( $message ) . '</p></div>';
		}
	} ?>

	<?php if ( ! $controller->get_client_id() || ! $controller->get_client_secret() ) : ?>
		<div class="message error"><p><?php esc_html_e( 'Client ID and Secret must be set before you can authenticate.', 'pmc-plugins' ); ?></p></div>
	<?php endif; ?>

	<?php foreach( (array) $services as $key => $value ) : ?>
		<div class="google-analytics-connection">
			<h3 class="alignleft"><?php echo esc_html( $value['description'] ); ?></h3>
			<div class="alignright">
			<?php if ( $controller->get_google_auth_details( $key ) ) : ?>
				<button class="button-primary" disabled="disabled"><?php
					esc_html_e( 'Connected', 'pmc-plugins' ); ?></button>
					<a class="button" onclick="javascript:if ( ! confirm( '<?php
						echo esc_js(
							__( 'Are you sure you want to disconnect this service?', 'pmc-plugins' )
						); ?>') ) { return false; }"
					href="<?php echo esc_url( $controller->get_disconnect_callback_url( $key ) ); ?>"><?php
					esc_html_e( 'Disconnect', 'pmc-plugins' ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( $controller->get_auth_callback_url( $key, $value['scope'] ) ); ?>"
					class="button button-primary"><?php echo esc_html_e( 'Connect', 'pmc-plugins' ); ?></a>
			<?php endif; ?>
			</div>
		</div>
		<hr class="clear" />
	<?php endforeach; ?>
</div>