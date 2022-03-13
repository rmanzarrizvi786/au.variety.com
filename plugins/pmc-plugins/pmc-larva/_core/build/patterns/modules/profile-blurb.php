<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-blurb // <?php echo esc_attr( $profile_blurb_classes ?? '' ); ?> ">

	<div class="<?php echo esc_attr( $profile_blurb_image_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_lazy_image ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
		<?php } ?>
	</div>

	<div class="<?php echo esc_attr( $profile_blurb_detail_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_title ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline_second ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline_second, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_dek ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-dek', $c_dek, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_social_list ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-social-list', $o_social_list, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_button ) ) { ?>
			<div class="<?php echo esc_attr( $profile_blurb_c_button_classes ?? '' ); ?>">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
			</div>
		<?php } ?>
	</div>

</div>
