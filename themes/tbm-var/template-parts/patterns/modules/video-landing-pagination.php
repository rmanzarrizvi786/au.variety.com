<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="lrv-a-wrapper">
	<div class="video-landing__pagination // <?php echo esc_attr( $video_landing_pagination_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_link_previous ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link_previous, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_link_next ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link_next, true ); ?>
		<?php } ?>
	</div>
</div>
