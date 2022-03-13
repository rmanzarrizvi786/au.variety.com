<section class="pmc-top-videos-playlist-module">
	<?php if ( ! empty( $playlist_title ) ) { ?>
		<div class="a-wrapper">
			<?php echo wp_kses_post( apply_filters( 'pmc_top_videos_widget_playlist_title', "<h2><a href='$playlist_link'>$playlist_title</a></h2>", $playlist_title, $playlist_link ) ); ?>
		</div>
	<?php } ?>

	<div class="pmc-top-videos-list-container">
		<?php foreach ( $video_cards ?? [] as $item ) { ?>
			<?php \PMC::render_template( __DIR__ . '/video-card.php', $item, true ); ?>
		<?php } ?>
	</div>
</section>
