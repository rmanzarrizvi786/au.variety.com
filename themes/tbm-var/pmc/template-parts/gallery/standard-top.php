<?php
if ( function_exists( 'pmc_gallery_back_to_linked_post' ) ) :
	$back_to_linked_post = pmc_gallery_back_to_linked_post();
else:
	$back_to_linked_post = false;
endif;
?>

<div class="gallery__top">
	<div class="gallery__top-gradient"></div>
	<div class="gallery__top-info">
		<div class="gallery__top-logo">
			<a href="/">
				<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/logo-pmc.php', [], true ); ?>
			</a>
		</div>

		<h1><?php the_title(); ?></h1>

		<div class="gallery__top-right">

			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/share-buttons.php', [], true ); ?>

			<?php if ( ! empty( $back_to_linked_post ) && is_array( $back_to_linked_post ) ) : ?>

				<?php if ( ! empty( $back_to_linked_post['url'] ) && ! empty( $back_to_linked_post['text'] ) ) : ?>

					<div class="gallery__back-to-linked-post">
						<a href="<?php echo esc_url( $back_to_linked_post['url'] ); ?>">
							<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/left-caret.php', [], true ); ?>
							<?php echo esc_html( $back_to_linked_post['text'] ); ?>
						</a>
					</div>

				<?php endif; ?>

			<?php endif; ?>
		</div>
	</div>
</div>
