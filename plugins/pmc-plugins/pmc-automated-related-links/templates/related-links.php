<?php
/**
 * Template for related link
 *
 * @package pmc-automated-related-links
 */

$title = __( 'Related', 'pmc-automated-related-links' );

if ( ! empty( $items['title'] ) ) {
	$title = $items['title'];
}

unset( $items['title'] );

// This need to after getting title.
// After fetching title. there will only post data in array.
// So after that if items is empty mean there is not posts to show.
if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

?>

<section class="c-related">
	<h3 class="c-heading"><?php echo esc_html( $title ); ?></h3>
	<ul class="c-related__list">
		<?php
		foreach ( $items as $index => $item ) {
			if ( empty( $item['url'] ) || empty( $item['title'] ) ) {
				continue;
			}
			?>
			<li class="c-related__list-item" data-index="<?php echo absint( $index + 1 ); ?>">
				<a class="c-card__title" target="_blank" href="<?php echo esc_url( $item['url'] ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $item['title'] ) ); ?>">
					<?php echo esc_html( wp_strip_all_tags( $item['title'] ) ); ?>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</section>
