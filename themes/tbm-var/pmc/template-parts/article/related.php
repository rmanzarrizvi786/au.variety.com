<?php
if ( empty( $items['posts'] ) || ! is_array( $items['posts'] ) || empty( $items['type'] ) ) {
	return;
}

?>

<section class="c-related c-related--<?php echo esc_attr( $items['type'] ); ?>">
	<h3 class="c-heading c-heading--related">Related</h3>
	<?php foreach ( $items['posts'] as $item ):
		if ( empty( $item ) ) {
			return;
		}

		// In case we're not sending a post object, but a term or anything else.
		if ( empty( $item->url ) && ! empty( $item->ID ) ) {
			$url = get_permalink( $item->ID );
			if ( ! empty( $item->tracking_link ) ) {
				// The tracking link is used for the legacy pmc-related shortcode.
				$url = $item->tracking_link;
			}
		} else {
			$url = $item->url;
		}
		?>
		<header class="c-card__header">
			<h3 class="c-card__title" itemprop="headline">
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $item->post_title ); ?></a>
			</h3>
		</header>
	<?php endforeach; ?>

</section>
