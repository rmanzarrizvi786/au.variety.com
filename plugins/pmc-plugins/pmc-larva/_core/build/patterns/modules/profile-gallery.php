<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-gallery // <?php echo esc_attr( $profile_gallery_classes ?? '' ); ?> ">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>

	<div class="js-Flickity js-Flickity--profile js-Flickity--isContained js-Flickity--nav-top-right js-Flickity--bordered-buttons js-Flickity--isFreeScroll">
		<?php foreach ( $galleries ?? [] as $item ) { ?>
			<div class="js-Flickity-cell lrv-u-margin-lr-050 u-margin-lr-1@tablet">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-figure', $item, true ); ?>
			</div>
		<?php } ?>
	</div>
</div>
