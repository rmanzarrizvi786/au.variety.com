<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<form class="profile-filter // <?php echo esc_attr( $profile_filter_classes ?? '' ); ?>" method="post" action="<?php echo esc_url( $profile_filter_action_url ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>

	<div class="lrv-a-space-children-vertical lrv-a-space-children--2">
		<?php foreach ( $o_checkbox_input_list ?? [] as $item ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-checkbox-input-list.php', $item, true ); ?>
		<?php } ?>
	</div>

	<?php if ( ! empty( $c_button ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
	<?php } ?>
</form>
