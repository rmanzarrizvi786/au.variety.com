<?php
/**
 * Template for related links
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-variety-2017
 */

$type  = 'posts';
$title = esc_html__( 'Related', 'pmc-variety' );

if ( ! empty( $items['type'] ) ) {
	$type = $items['type'];
}

if ( ! empty( $items['title'] ) ) {
	$title = $items['title'];
}

if ( 'review' === $items['type'] ) {
	$title = __( 'More Reviews', 'pmc-variety' );
} elseif ( 'dirt' === $items['type'] ) {
	$title = __( 'More Dirt', 'pmc-variety' );
}

unset( $items['title'], $items['type'] );

// This need to after getting title.
// After fetching title. there will only post data in array.
// So after that if items is empty mean there is not posts to show.
if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

// Thumbnail size.
$size = 'landscape-medium';
?>
<section class="c-related c-related--<?php echo esc_attr( $type ); ?>">
	<h3 class="c-heading c-heading--related"><?php echo esc_html( $title ); ?></h3>

	<?php
	foreach ( $items as $index => $item ) {
		echo '<article class="c-card c-card--focus-featured" >';

		$item = get_post( $item['id'] );
		$item = variety_normalize_post( $item );

		$item->target = '_blank';

		if ( 0 === $index ) {
			\PMC::render_template(
				sprintf( '%s/template-parts/article/card-image.php', untrailingslashit( CHILD_THEME_PATH ) ),
				compact( 'item', 'size' ),
				true
			);
		}

		\PMC::render_template(
			sprintf( '%s/template-parts/article/card-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
			compact( 'item' ),
			true
		);

		echo '</article>';
	}
	?>

</section>
