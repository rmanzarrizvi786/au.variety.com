<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-related-stories // <?php echo esc_attr( $profile_related_stories_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_card_list ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card-list', $o_card_list, true ); ?>
	<?php } ?>
</div>
