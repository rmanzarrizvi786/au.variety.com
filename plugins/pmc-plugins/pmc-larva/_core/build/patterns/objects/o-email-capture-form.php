<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<form class="o-email-capture-form lrv-js-EmailCapture <?php echo esc_attr( $o_email_capture_form_classes ?? '' ); ?>" method="post" action="<?php echo esc_url( $o_email_capture_form_action_url ?? '' ); ?>" name="<?php echo esc_attr( $o_email_capture_form_name_attr ?? '' ); ?>" target="_blank">

	<div class="o-email-capture-form__inner <?php echo esc_attr( $o_email_capture_form_inner_classes ?? '' ); ?>">
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-email-field', $c_email_field, true ); ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
	</div>


	<input type="hidden" name="__contextName" value="<?php echo esc_attr( $o_email_capture_form_context_name_attr ?? '' ); ?>"/>
	<input type="hidden" name="__executionContext" value="Post" />
	<input type="hidden" name="__successPage" data-email-capture-success-url="<?php echo esc_url( $o_email_capture_form_success_url ?? '' ); ?>" value="" />

	<?php foreach ( $o_email_capture_form_hidden_field_items ?? [] as $item ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-hidden-field', $item, true ); ?>
	<?php } ?>
</form>
