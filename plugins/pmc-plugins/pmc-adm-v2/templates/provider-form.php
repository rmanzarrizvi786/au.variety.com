<?php

if ( empty( $provider ) ) {
	return;
}
$provider_id = $provider->get_id();
$templates   = $provider->get_admin_templates();
$method      = ( empty( $ad ) ) ? 'add' : 'edit';
$div_id      = ( empty( $ad ) ) ? $provider_id : $ad->ID;
?>

<div id="<?php echo esc_attr( 'provider-' . $div_id ); ?>" class="adm-provider">

	<p>
		<strong>Provider: <?php echo esc_html( $provider->get_title() ); ?></strong>
	</p>

	<form action="" method="post" class="adm-form">

		<?php wp_nonce_field( 'adm-admin-action', 'nonce' ); ?>

		<input type="hidden" name="action" value="adm_crud">

		<input type="hidden" name="method" value="<?php echo esc_attr( $method ) ?>">

		<input type="hidden" name="provider" value="<?php echo esc_attr( $provider_id ); ?>">

		<?php if ( ! empty( $ad->ID ) ) : ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $ad->ID ); ?>">
		<?php endif; ?>
		<div class="adm-provider-units">
			<?php foreach ( $templates as $template ) :

				$template_file = sprintf( '%s/templates/provider-admin/%s.php', PMC_ADM_DIR, sanitize_file_name( $template ) );

				PMC::render_template(
					$template_file,
					[
						'provider'            => $provider,
						'provider_id'         => $provider_id,
						'ad'                  => $ad,
						'manager'             => $manager,
						'provider_locations'  => $provider_locations,
						'condition_functions' => $condition_functions,
					],
					true );

			endforeach;

			?>
		</div>
		<div class="clear"></div>
		<div>
			<?php submit_button( __( 'Save', 'pmc-plugins' ), 'primary', 'submit', false ); ?>
			<a href="javascript:;" class="adm-form-cancel button"><?php esc_html_e( 'Cancel', 'pmc-plugins' ); ?></a>
			<span class="error-message"></span>
			<?php if ( ! empty( $ad ) ) : ?>
				<p>
					<?php
					$creator = get_userdata( $ad->post_author );
					if ( ! empty( $creator ) ) :
					?>
						<strong><?php esc_html_e( 'Created by:', 'pmc-plugins' ); ?></strong>
						<em><?php echo esc_html( $creator->user_nicename ); ?></em>
						<?php esc_html_e( 'on', 'pmc-plugins' ); ?>
						<em><?php echo esc_html( PMC_TimeMachine::create( $manager->timezone )->from_time( 'Y-m-d H:i:s', $ad->post_date )->format_as( 'jS M Y H:i' ) ); ?></em>
					<?php endif; ?>
					<?php
					$log = $manager->get_last_modified_log( $ad->ID, false );
					if ( ! empty( $log ) && is_array( $log ) ) :
						?>
						<p>
							<strong><?php esc_html_e( 'Last Modified by:', 'pmc-plugins' ); ?></strong>
							<?php foreach ( $log as $last_modified_time => $ad_user ) :
								$ad_user = get_userdata( $ad_user );
								$format             = ( is_numeric( $last_modified_time ) ) ? 'U' : 'Y-m-d H:i:s';
								$last_modified_time = PMC_TimeMachine::create( $manager->timezone )->from_time( $format, $last_modified_time )->format_as( 'jS M Y H:i' );
								?>
								<br>
								<em>
									<?php if ( ! empty( $ad_user->user_nicename ) ) :
										echo esc_html( $ad_user->user_nicename );
									endif; ?>
								</em>
								<?php esc_html_e( 'on ', 'pmc-plugins' ); ?>
								<em><?php echo esc_html( $last_modified_time ); ?></em>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</p>
				<br clear="both">
			<?php endif; ?>
		</div>
	</form>
</div>