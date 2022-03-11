<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-blurb // <?php echo esc_attr( $profile_blurb_classes ?? '' ); ?> ">

	<div class="<?php echo esc_attr( $profile_blurb_image_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_lazy_image ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
		<?php } ?>
	</div>

	<div class="<?php echo esc_attr( $profile_blurb_detail_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_title ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline_second ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline_second, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_dek ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_social_list ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-social-list.php', $o_social_list, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_button ) ) { ?>
			<div class="<?php echo esc_attr( $profile_blurb_c_button_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
			</div>
		<?php } ?>
	</div>

</div>
