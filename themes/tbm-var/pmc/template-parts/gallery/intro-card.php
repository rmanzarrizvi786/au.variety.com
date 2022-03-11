<?php if ( ! empty( $title ) && ! empty( $description ) && ! empty( $vertical ) ) { ?>
	<div class="gallery__intro-card-container">
		<div class="gallery__intro-card-close"><?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/close-thumbnails.php', [], true ); ?></div>
		<div class="gallery__intro-card-vertical-date">
			<span class="gallery__intro-card-vertical"><?php echo esc_html( $vertical->name ); ?></span>
			<span class="gallery__intro-card-date"><?php echo esc_html( get_the_date() ); ?></span>
		</div>
		<h1 class="gallery__intro-card-title"><?php echo esc_html( $title ); ?></h1>
		<div class="gallery__intro-card-description"><?php echo wp_kses_post( $description ); ?></div>
		<div class="gallery__intro-card-slideshow"><?php esc_html_e( 'Start Slideshow', 'pmc-core' );?></div>
		<div class="gallery__intro-card-socials"><?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/share-buttons.php', [], true ); ?></div>
	</div>
	<div class="gallery__intro-card-backdrop"></div>
<?php } ?>
