<?php
/**
 * Front-end template for PMC Content Exchange widget
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @ticket CDWE-195
 * @since 2017-02-24
 */

if ( ! empty( $module_id ) ) {

	if ( $show_dummy_image > 0 ) {

		$image_name = strtolower( $module_id );

		// Note: Image must be same name as module name in lowercase with .png file format,
		// then only it will show image from assets directory.
		$image_url = sprintf(
			'%s/assets/%s.png',
			untrailingslashit( PMC_CONTENT_EXCHANGE_URL ),
			esc_attr( $image_name )
		);
		?>

		<img src="<?php echo esc_url( $image_url ); ?>" />

		<?php
	} else {
		?>

		<div class="OUTBRAIN" data-src="<?php echo esc_attr( $data_src ); ?>"
			data-widget-id="<?php echo esc_attr( $widget_id ); ?>"
			data-ob-template="<?php echo esc_attr( $module_id ); ?>">
		</div>

		<?php
	}
}

//EOF
