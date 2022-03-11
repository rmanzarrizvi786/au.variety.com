<div class="gallery__left">

	<?php if ( is_array( $gallery ) && ! empty( $gallery ) ) : ?>

		<?php foreach ( $gallery as $gallery_image ) : ?>

			<?php
			$image_details = wp_get_attachment_metadata( $gallery_image['ID'] );

			// The mobile display uses slightly larger thumbnails
			if ( PMC::is_mobile() ) {
				$thumbnail_size = 'standard-gallery-uncropped-thumb-mobile';
			} else {
				$thumbnail_size = 'standard-gallery-uncropped-thumb';
			}

			// The standard gallery supports both landscape and portrait images..
			// ..display the corresponding image size
			if ( isset( $image_details['width'], $image_details['height'] ) && $image_details['width'] > $image_details['height'] ) {
				$large_image_size = 'standard-gallery-uncropped-main';
			} else {
				$large_image_size = 'collection-gallery-uncropped-main';
			}

			$credit = function_exists( 'pmc_get_photo_credit' ) ? pmc_get_photo_credit( $gallery_image['ID'] ) : false;
			$slug = ! empty( $gallery_image['slug'] ) ? $gallery_image['slug'] : '';
			?>

			<div class="gallery__thumbnail">

				<?php
				pmc_the_attachment_image( $gallery_image['ID'], $thumbnail_size, [
					'alt'          => esc_attr( strip_tags( $gallery_image['caption'] ) ),
					'class'        => 'gallery__thumbnail__img',
					'data-title'   => esc_attr( $gallery_image['title'] ),
					'data-credit'  => esc_attr( $credit ),
					'data-slug'    => esc_attr( $slug ),
					'data-img-lrg' => pmc_get_image_url( $large_image_size, $gallery_image['ID'] ),
					'data-zoom-src' => pmc_get_image_url( 'full', $gallery_image['ID'] ),
				] );
				?>

			</div>

		<?php endforeach; ?>

	<?php endif; ?>

	<div class="gallery__viewthumbs gallery__close-thumbnails">

		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/close-thumbnails.php', [], true ); ?>

	</div>

</div>
