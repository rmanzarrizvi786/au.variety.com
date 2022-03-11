<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-related-stories // <?php echo esc_attr( $profile_related_stories_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_card_list ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-card-list.php', $o_card_list, true ); ?>
	<?php } ?>
</div>
