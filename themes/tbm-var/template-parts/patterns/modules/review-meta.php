<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="review-meta // <?php echo esc_attr( $review_meta_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_title ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_title, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-meta-list.php', $o_meta_list, true ); ?>
</div>
