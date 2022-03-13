<?php
$options              = apply_filters( 'pmc_buy_now_options', [] );
$button_type_settings = \PMC\Buy_Now\Admin_UI::get_instance()->get_button_type_settings();
$button_type          = [
	'title'          => __( 'Button Type', 'pmc-buy-now' ),
	'name'           => 'button_type',
	'type'           => 'select',
	'select_options' => [],
];
?>
<script>
	var PMC_BUY_NOW_TEXT = <?php echo wp_json_encode(
		[
			'title' => __( 'Buy Now', 'pmc-buy-now' ),
		]
	); ?>
</script>

<div id="pmc-buy-now-dialog" class="hidden">
	<a class="pmc-buy-now-close" href="#">&times;</a>
	<form>
		<?php
		if ( ! empty( $options ) && is_array( $options ) ) {

			foreach ( $button_type_settings as $key => $value ) {
				if ( ! empty( $value['label'] ) ) {
					$button_type['select_options'][ $key ] = $value['label'];
					echo sprintf( '<input type="hidden" name="_%s" value="%s" />', esc_attr( $key ), esc_attr( implode( ',', (array) $value['fields'] ) ) );
				}
			}

			$options[] = $button_type;

			foreach ( $options as $option ) {
				$path = sprintf( '%s/templates/modal-%s.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ), $option['type'] );
				if ( file_exists( $path ) ) {
					\PMC::render_template(
						$path,
						$option,
						true
					);
				}
			}

		}
		?>
		<button class="button button-primary"><?php esc_html_e( 'Insert Code', 'pmc-buy-now' ); ?></button>
	</form>
</div>
