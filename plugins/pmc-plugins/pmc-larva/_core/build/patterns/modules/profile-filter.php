<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<form class="profile-filter // <?php echo esc_attr( $profile_filter_classes ?? '' ); ?>" method="post" action="<?php echo esc_url( $profile_filter_action_url ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>

	<div class="lrv-a-space-children-vertical lrv-a-space-children--2">
		<?php foreach ( $o_checkbox_input_list ?? [] as $item ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-checkbox-input-list', $item, true ); ?>
		<?php } ?>
	</div>

	<?php if ( ! empty( $c_button ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
	<?php } ?>
</form>
