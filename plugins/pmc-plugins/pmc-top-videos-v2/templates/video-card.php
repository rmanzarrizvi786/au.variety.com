<article class="pmc-top-videos-list-item">

	<?php if ( ! empty( $card_permalink_url ) ) { ?>
		<a href="<?php echo esc_url( $card_permalink_url ?? '' ); ?>" class="pmc-top-videos-list-item-inner <?php echo esc_attr( $card_permalink_classes ?? '' ); ?>">
	<?php } ?>

	<figure class="figure">
		<img class="figure__image" src="<?php echo esc_url( $image_url ?? '' ); ?>" alt="<?php echo esc_attr( $image_alt_attr ?? '' ); ?>">
	</figure>

	<?php if ( ! empty( $video_title_text ) ) { ?>
		<figcaption class="figure-caption">
			<?php echo esc_html( $video_title_text ); ?>
		</figcaption>
	<?php } ?>

	<?php if ( ! empty( $card_permalink_url ) ) { ?>
		</a>
	<?php } ?>

</article>
