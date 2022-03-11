<?php
/**
 * Reviews Widget - Vertical Term template.
 *
 * This is the partial that represents the overall
 * container for a vertical of reviews on the homepage.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

if ( empty( $vertical ) || ! is_string( $vertical ) ) {
	return;
}


if ( empty( $data['articles'][ $vertical ] ) || ! is_array( $data['articles'][ $vertical ] ) ) {
	return;
}

$link = get_term_link( $vertical, 'vertical' );

$term_link = ( ! empty( $link ) && ! is_wp_error( $link ) ) ? $link : '';

?>
<li id="reviews-<?php echo esc_attr( sanitize_title( $vertical ) ); ?>" class="l-reviews__panel <?php echo ( 0 === $index ) ? esc_attr( 'is-active' ) : ''; ?>" data-tabs-panel data-tabs-href="<?php echo esc_url( $term_link ); ?>reviews/">
	<div class="l-reviews__panel__cards">
		<ul class="l-grid l-grid--reviews">

			<?php
			foreach ( $data['articles'][ $vertical ] as $item ) {
				PMC::render_template(
					CHILD_THEME_PATH . '/template-parts/widgets/reviews-item.php',
					compact( 'item' ),
					true
				);
			}
			?>

		</ul><!-- .l-grid -->
	</div>
</li>
