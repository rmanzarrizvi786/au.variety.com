<?php
/**
 * Ad template
 *
 * @package pmc-adm
 */

?>
	<div class="admz" id="adm-<?php echo esc_attr( $ad_type_id ); ?>">
		<?php
		if ( ! empty( $title ) ) {
			printf( '<p>%s</p>', esc_html( $title ) );
		}

		foreach ( $ad_types as $size => $ad ) {

			$ad_provider = $manager->get_provider( $ad['provider'] );

			if ( empty( $ad_provider ) ) {
				continue;
			}

			?>
			<div class="adma <?php echo esc_attr( $ad['provider'] ); ?>" data-device="<?php echo esc_attr( $ad['device'] ); ?>" data-width="<?php echo esc_attr( $ad['width'] ); ?>">
				<?php
				$ad_provider->render_ad( $ad, true );
				?>
			</div>
			<?php

		}
		?>
	</div>
<?php
//EOF
