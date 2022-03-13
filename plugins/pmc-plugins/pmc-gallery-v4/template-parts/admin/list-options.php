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
				<?php esc_html_e( 'List Template', 'pmc-gallery-v4' ); ?>
			</label>
		</th>
		<td>
			<?php
			$template  = get_post_meta( $post->ID, 'pmc_list_template', true );
			$templates = apply_filters(
				'pmc_list_templates',
				[
					'item-featured-image' => __( 'Featured Image', 'pmc-gallery-v4' ),
					'item-album'          => __( 'Album', 'pmc-gallery-v4' ),
				]
			);
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
				<?php esc_html_e( 'List Numbering', 'pmc-gallery-v4' ); ?>
			</label>
		</th>
		<td>
			<select name="pmc_list_numbering">
				<?php
				$number    = get_post_meta( $post->ID, 'pmc_list_numbering', true );
				$numbering = array(
					'asc'  => __( 'Ascending (1 - 10)', 'pmc-gallery-v4' ),
					'desc' => __( 'Descending (10 - 1)', 'pmc-gallery-v4' ),
					'none' => __( 'No numbering', 'pmc-gallery-v4' ),
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
			<?php wp_nonce_field( 'set_list_display_options', 'pmc-listoptions-nonce' ); ?>
		</td>
	</tr>
	</tbody>
</table>
