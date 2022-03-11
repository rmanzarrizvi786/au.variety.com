<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_select_option_url ) ) { ?>
	<option data-select-url="<?php echo esc_url( $c_select_option_url ?? '' ); ?>" value="<?php echo esc_attr( $c_select_option_value_attr ?? '' ); ?>"><?php echo esc_html( $c_select_option_text ?? '' ); ?></option>
<?php } else { ?>
	<option value="<?php echo esc_attr( $c_select_option_value_attr ?? '' ); ?>"><?php echo esc_html( $c_select_option_text ?? '' ); ?></option>
<?php } ?>
