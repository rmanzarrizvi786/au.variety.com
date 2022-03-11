<?php
/**
 * Template part for Top Videos Admin Page.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<style>
	.form-table {
		width: 600px;
		max-width: 95%;
	}

	.postbox {
		display: inline-block;
		padding: 10px;
	}
</style>
<div class = "wrap">
	<h2><?php echo esc_html( $menu_label ); ?></h2>
	<form action = "options.php" method = "post">
		<?php
		settings_fields( $options_setings );
		do_settings_sections( $options_setings );
		submit_button( __( 'Save Options', 'pmc-variety' ) );

		$terms = get_terms( 'vcategory',
			array(
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 100,
			)
		);
		?>
		<div class="postbox">
			<h3>
				<span>
					<strong>
						<?php esc_html_e( 'Playlist assigned to videos', 'pmc-variety' ); ?>
					</strong>
				</span>
			</h3>
			<div class="inside">
				<?php
				foreach ( $terms as $term ) {
					printf( '<div>%1$s %2$s</div>', esc_html( ucwords( $term->name ) ), esc_html( $term->count ) );
				}
				?>
			</div>
		</div>
	</form>
</div>
