<?php $gallery_item = PMC\Listicle_Gallery_V2\Services\Gallery_Item::get_instance(); ?>

<div class="pmc-listicle-gallery-v2">

	<!-- gallery navigation -->

	<div class="gallery-nav row">

		<div class="nav prev col-4">
			<?php if ( ! empty( $data[ 'prev_gallery_item_url' ] ) ): ?>
				<a href="<?php echo esc_url( $data[ 'prev_gallery_item_url' ] ); ?>"><span><?php esc_html_e( 'prev' ); ?></span></a>
			<?php endif; ?>
		</div>

		<div class="nav counter col-4">
			<?php echo esc_html( $data[ 'current_gallery_item_number' ] ) . esc_html__( ' of ', 'pmc-plugins' ) . esc_html( $data[ 'total_gallery_items' ] ); ?>
		</div>

		<div class="nav next col-4">
			<?php if ( ! empty( $data[ 'next_gallery_item_url' ] ) ): ?>
				<a href="<?php echo esc_url( $data[ 'next_gallery_item_url' ] ); ?>"><span><?php esc_html_e( 'next' ); ?></span></a>
			<?php endif; ?>
		</div>

	</div>

	<!-- header -->

	<div class="gallery-header row">

		<div class="gallery-index col-1">
			<span>#</span><?php echo esc_html( $data[ 'current_gallery_item_number' ] ); ?>
		</div>

		<div class="gallery-title col-11">
			<?php echo esc_html( $data[ 'title' ] ); ?>
		</div>

	</div>

	<div class="gallery-wrapper">

		<!-- slides -->

		<div class="gallery-slides">

			<?php if( ! empty($data[ 'slides' ] ) ): ?>

				<?php foreach ( $data[ 'slides' ] as $key => $slide ): ?>

					<div class="slide">

						<!-- slide image -->

						<img
							src="<?php if ( 0 === $key) { echo esc_url( $slide[ 'url' ] ); } ?>"
							data-lazy="<?php echo esc_url( $slide[ 'url' ] ); ?>"
							title ="<?php echo esc_attr( $slide[ 'title' ] ); ?>"
							alt ="<?php echo esc_attr( $slide[ 'alt' ] ); ?>"
						/>

						<!-- slide annotations -->

						<?php if ( ! empty( $slide[ 'title' ] ) || ! empty( $slide[ 'credit' ] ) ): ?>
							<div class="slide-annotations">
								<div class="slide-title">
									<?php echo $gallery_item->sanitize_annotation( $slide[ 'title' ] ); ?>
								</div>
								<div class="slide-credit">
									<?php echo $gallery_item->sanitize_annotation( $slide[ 'credit' ] ); ?>
								</div>
							</div>
						<?php endif; ?>

					</div>

				<?php endforeach; ?>

			<?php endif; ?>

		</div>

		<!-- thumbnails -->

		<?php if ( count( $data[ 'slides' ] ) > 1 ): ?>

			<div class="gallery-thumbs">

				<!-- thumbanil image -->

				<?php foreach ( $data[ 'slides' ] as $slide ): ?>
					<div class="thumbnail">
						<img data-lazy="<?php echo esc_url( $slide[ 'thumb_url' ] ); ?>" />
					</div>
				<?php endforeach; ?>

			</div>

		<?php endif; ?>

		<!-- captions -->

		<div class="gallery-captions">

			<?php foreach ( $data[ 'slides' ] as $slide ): ?>
				<div class="caption"><?php echo wp_kses_post( $slide[ 'caption' ] ); ?></div>
			<?php endforeach; ?>

		</div>

	</div>

	<!-- body -->

	<div class="gallery-body">
		<div class="ad">
			<?php pmc_adm_render_ads( apply_filters( 'listicle_gallery_item_override_body_ad', 'right-rail-2' ) ); ?>
		</div>
		<?php echo wp_kses_post( $data[ 'content' ] ); ?>
	</div>

	<!-- modal dialog -->

	<div class="gallery-modal">
		<div class="modal-content">
			<div class="modal-header row">
				<div class="modal-title col-11"></div>
				<div class="modal-close col-1">&times;</div>
			</div>
			<div class="modal-image">
				<!-- src will be dynamically set to the src attribute of the clicked slide -->
				<img src="#" />
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>

</div>