<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="c-email-field <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_email_field_classes ?? '' ); ?>">
	<label class="c-email-field__label <?php echo esc_attr( $c_email_field_label_classes ?? '' ); ?>" for="<?php echo esc_attr( $c_email_field_label_for_attr ?? '' ); ?>"><?php echo esc_html( $c_email_field_label_text ?? '' ); ?></label>
	<input class="c-email-field__input <?php echo esc_attr( $c_email_field_input_classes ?? '' ); ?>" name="<?php echo esc_attr( $c_email_field_input_name_attr ?? '' ); ?>" id="<?php echo esc_attr( $c_email_field_input_id_attr ?? '' ); ?>" required type="email" placeholder="<?php echo esc_attr( $c_email_field_input_placeholder_attr ?? '' ); ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" />
</div>
