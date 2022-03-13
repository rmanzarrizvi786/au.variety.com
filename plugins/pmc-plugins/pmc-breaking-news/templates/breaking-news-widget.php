<?php
/**
 * Display breaking news banner widget in dashboard.
 */

?>

<div id="<?php echo esc_attr( PMC_Breaking_News::KEY ); ?>" class="input-text-wrap">
	<p>
		<input class="title" type="text" value="<?php echo esc_attr( $title ); ?>" placeholder="Title" />
	</p>

	<p>
		<input class="link" type="text" value="<?php echo esc_url( $url ); ?>" placeholder="Link" />
	</p>

	<?php if ( $image_option ) { ?>

	<p>
		<input type="button" id="pmc_brk_news_add_image" class="button" value="<?php esc_attr_e( 'Add Image', 'pmc-plugins' ); ?>" />

		<span id="pmc_brk_news_image_wrapper" class="pmc_brk_news_image_wrapper">
			<?php if ( ! empty( $image_thumb ) ) { ?>
				<img src="<?php echo esc_url( $image_thumb ); ?>" height="100" />
				<a href="#"><?php esc_html_e( 'Clear', 'pmc-plugins' ); ?></a>
			<?php } ?>
		</span>

		<input type="hidden" id="pmc_brk_news_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
	</p>

	<?php } ?>

	<?php
	wp_nonce_field( 'save-breaking-news', PMC_Breaking_News::KEY . '-link-box' );

	if ( class_exists( 'PMC_LinkContent' ) ) {
		PMC_LinkContent::insert_field( $linked_data, '', PMC_Breaking_News::KEY );
	}
	?>

	<p>
		<input type="radio" name="pmc_brk_news_active" id="pmc_brk_news_active_on"
		       value="on" <?php checked( 'on', $active, true ); ?>/>
		<label for="pmc_brk_news_active_on">
			<?php esc_html_e( 'Active', 'pmc-plugins' ); ?>
		</label>
		<br/>
		<input type="radio" name="pmc_brk_news_active" id="pmc_brk_news_active_off"
		       value="off" <?php checked( 'off', $active, true ); ?>/>
		<label for="pmc_brk_news_active_off">
			<?php esc_html_e( 'Inactive', 'pmc-plugins' ); ?>
		</label>
	</p>
	<p>
		<a id="pmc_brk_news_save" class="button button-primary" href="#">
			<?php esc_html_e( 'Submit', 'pmc-plugins' ); ?>
		</a>
		<span class="saving"></span>
	</p>
</div>
