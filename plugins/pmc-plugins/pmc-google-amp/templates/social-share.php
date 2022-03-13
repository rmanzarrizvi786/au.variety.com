<?php
/**
 * Add Social Share bar to footer
 *
 * @ticket PMCVIP-2582
 *
 * @since 2016-11-22 - Debabrata Karfa
 *
 * @version 2017-07-14 CDWE-446
 *
 * @package pmc-google-amp
 */

?>
<div  class="amp-social-share-bar-container">
	<div class="share-this"><?php esc_html_e( 'Share This', 'pmc-google-amp' ); ?></div>
	<div class="amp-social-share-bar <?php echo esc_attr( $location ); ?>">
		<?php
		if ( ! empty( $social_share_icons ) && is_array( $social_share_icons ) ) {
			foreach ( $social_share_icons as $social_share_icon ) {
				?>
				<amp-social-share type="<?php echo esc_attr( $social_share_icon ); ?>"
					width="35" height="35"
					<?php
					$params = array(
						'utm_medium'   => 'social',
						'utm_source'   => $social_share_icon,
						'utm_campaign' => 'social_bar',
						'utm_content'  => $location . '_amp', // Location on page.
						'utm_id'       => get_the_ID(),
					);

					$share_url = get_permalink( get_the_ID() ) . '#' . http_build_query( $params );

					switch ( $social_share_icon ) {
						case 'facebook':
							echo 'data-param-href="' . esc_url( $share_url ) . '"';
							echo 'data-param-app_id="' . esc_attr( $fb_data_param_app_id ) . '"';
							break;
						case 'twitter':
							echo 'data-param-url="' . esc_url( $share_url ) . '"';
							break;
						case 'pinterest':
							echo 'data-param-url="' . esc_url( $share_url ) . '"';
							break;
						case 'email':
							echo 'data-param-body="' . esc_url( $share_url ) . '"';
							break;
					}
					?>></amp-social-share>
				<?php
			}
		}
		?>
	</div>
</div>
