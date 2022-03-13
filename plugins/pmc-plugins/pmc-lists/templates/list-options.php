<?php

// $post may be passed in by PMC::render_template.
if ( ! isset( $post ) ) {
	$post = $GLOBALS['post'];
}
?>

<table class="form-table">
	<tbody>
	<tr>
		<th scope="row">
			<label for="pmc_list_template">
				<?php esc_html_e( 'List Template', 'pmc-lists' ); ?>
			</label>
		</th>
		<td>
			<?php
			$template  = get_post_meta( $post->ID, 'pmc_list_template', true );
			$templates = apply_filters( 'pmc_list_templates', [
				'default' => __( 'Default', 'pmc-lists' ),
			] );
			?>
			<select name="pmc_list_template">
				<?php if ( ! empty( $templates ) && is_array( $templates ) ) : ?>

					<?php foreach ( $templates as $key => $name ) : ?>

						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $template, true ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>

					<?php endforeach; ?>

				<?php endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="pmc_list_numbering">
				<?php esc_html_e( 'List Numbering', 'pmc-lists' ); ?>
			</label>
		</th>
		<td>
			<select name="pmc_list_numbering">
				<?php
				$number    = get_post_meta( $post->ID, 'pmc_list_numbering', true );
				$numbering = array(
					'asc'  => __( 'Ascending (1 - 10)', 'pmc-lists' ),
					'desc' => __( 'Descending (10 - 1)', 'pmc-lists' ),
					'none' => __( 'No numbering', 'pmc-lists' ),
				);

				foreach ( $numbering as $key => $name ) :
					?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $number, true ); ?>>
						<?php echo esc_html( $name ); ?>
					</option>
				<?php
				endforeach;
				?>
			</select>
		</td>
	</tr>
	</tbody>
</table>
