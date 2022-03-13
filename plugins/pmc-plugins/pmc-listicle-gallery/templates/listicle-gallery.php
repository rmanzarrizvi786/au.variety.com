<!-- authors and social media -->

<div class="post-meta">

  <?php if ( ! empty( $gallery[ 'authors' ] ) ): ?>

    <span class="post-meta__author">
      <?php
        echo wp_kses(
          sprintf( __( 'By %1$s on %2$s', 'robbreport' ), $gallery[ 'authors' ], $gallery[ 'post_date' ] ),
          [
            'a' => [
              'href'  => true,
              'class' => true,
              'title' => true,
              'rel'   => true,
            ],
          ]
        );
      ?>
    </span>

  <?php endif; ?>

  <?php PMC\Social_Share_Bar\Frontend::get_instance()->render(); ?>

</div>

<!-- header and nav -->

<div class="pmc-listicle-gallery-nav row">

  <div class="pmc-listicle-gallery-prev col-sm-4 col-xs-4">
    <?php if ( !empty( $gallery[ 'prev_gallery_url' ] ) ): ?>
      <a href="<?php echo esc_url( $gallery[ 'prev_gallery_url' ] ); ?>"><span>PREV</span></a>
    <?php endif; ?>
  </div>

  <div class="pmc-listicle-gallery-counter col-sm-4 col-xs-4">
    <?php echo wp_kses_post( $gallery[ 'current_gallery_number' ] ); ?> OF <?php echo wp_kses_post( $gallery[ 'total_galleries' ] ); ?>
  </div>

  <div class="pmc-listicle-gallery-next col-sm-4 col-xs-4">
    <?php if ( !empty( $gallery[ 'next_gallery_url' ] ) ): ?>
      <a href="<?php echo esc_url( $gallery[ 'next_gallery_url' ] ); ?>"><span>NEXT</span></a>
    <?php endif; ?>
  </div>

</div>

<div class="pmc-listicle-gallery-header row">

  <div class="pmc-listicle-gallery-index col-sm-4 col-xs-3">
    <span>#</span><?php echo wp_kses_post( $gallery[ 'current_gallery_number' ] ); ?>
  </div>

  <div class="pmc-listicle-gallery-title col-sm-8 col-xs-9">
    <?php echo wp_kses_post( $gallery[ 'title' ] ); ?>
  </div>

</div>

<!-- slides -->

<div id="<?php echo esc_attr( $gallery[ 'id' ] ); ?>"
     class="carousel slide carousel-fade pmc-listicle-gallery"
     data-ride="carousel"
     data-interval="<?php echo esc_attr( $gallery[ 'interval' ] ); ?>"
     data-pause="<?php echo esc_attr( $gallery[ 'pause' ] ); ?>"
     data-wrap="<?php echo esc_attr( $gallery[ 'wrap' ] ); ?>">

  <?php $loop_index = 0; ?>

  <div class="carousel-inner" role="listbox">

    <?php foreach ( $gallery[ 'slides' ] as $slide ): ?>

      <?php $slide_class = ( $gallery[ 'start_index' ] === $loop_index++ ) ? ' active' : ''; ?>

      <div class="item<?php echo esc_attr( $slide_class ); ?>">

        <div class="slide-image"
             data-src="<?php echo esc_url( $slide[ 'url' ] ); ?>"
             style="background: url('<?php echo esc_url( $slide[ 'url' ] ); ?>') no-repeat center center;">

          <?php if ( isset( $slide[ 'title' ] ) || isset( $slide[ 'alt' ] ) ): ?>
            <div class="carousel-annotations">
              <?php if ( isset( $slide[ 'title' ] ) ): ?>
                <div><?php echo wp_kses_post( $slide[ 'title' ] ); ?></div>
              <?php endif; ?>
              <?php if ( isset( $slide[ 'alt' ] ) ): ?>
                <div><?php echo wp_kses_post( $slide[ 'alt' ] ); ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div>

      </div>

    <?php endforeach; ?>

    <?php foreach ( $gallery[ 'slides' ] as $slide ): ?>

      <div class="pmc-listicle-gallery-modal">
        <div class="modal fade" id="modal-image" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <?php if ( isset( $slide[ 'title' ] ) ): ?>
                  <div><?php echo wp_kses_post( $slide[ 'title' ] ); ?></div>
                <?php endif; ?>
              </div>
              <div class="modal-body">
                <img src="" id="modal-image-preview" />
              </div>
              <div class="modal-footer">
                <?php if ( isset( $slide[ 'alt' ] ) ): ?>
                  <div><?php echo wp_kses_post( $slide[ 'alt' ] ); ?></div>
                <?php endif; ?>
                <?php if ( isset( $slide[ 'credit' ] ) ): ?>
                  <div><?php echo wp_kses_post( $slide[ 'credit' ] ); ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php endforeach; ?>


    <?php if ( isset( $gallery[ 'controls' ] ) && sizeof( $gallery[ 'slides' ] )  > 1 ): ?>
      <a class="carousel-control left" href="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>" data-slide="prev">
        <span class="glyphicon glyphicon-menu-left"></span>
      </a>
      <a class="carousel-control right" href="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>" data-slide="next">
        <span class="glyphicon glyphicon-menu-right"></span>
      </a>
    <?php endif; ?>

  </div>

</div>

<?php $loop_index = 0; ?>

<!-- thumbanils -->

<?php if ( sizeof( $gallery[ 'slides' ] )  > 1 ): ?>

  <div id="<?php echo esc_attr( $gallery[ 'id' ] ); ?>-thumbs"
       class="carousel slide pmc-listicle-gallery-thumbs"
       data-ride="carousel"
       data-interval="<?php echo esc_attr( $gallery[ 'interval' ] ); ?>"
       data-pause="<?php echo esc_attr( $gallery[ 'pause' ] ); ?>"
       data-wrap="<?php echo esc_attr( $gallery[ 'wrap' ] ); ?>">

    <div class="well">

      <div class="carousel-inner">

        <?php foreach ( $gallery[ 'slides' ] as $slide ): ?>

          <?php if ( $loop_index % LISTICLE_GALLERY_THUMBNAILS === 0 ): ?>

            <?php $slide_class = ( $gallery[ 'start_index' ] === $loop_index ) ? ' active' : ''; ?>

            <div class="item<?php echo esc_attr( $slide_class ); ?>">

              <div class="row">

                <?php if ( isset( $gallery[ 'slides' ][ $loop_index ] ) ): ?>
                  <div class="col-sm-3" data-target="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>"
                       data-slide-to="<?php echo esc_attr( $loop_index ); ?>">
                    <div class="thumb-image"
                         style="background: url('<?php echo esc_url( $gallery[ 'slides' ][ $loop_index ][ 'url' ] ); ?>') no-repeat center center;"></div>
                  </div>
                <?php endif; ?>

                <?php if ( isset( $gallery[ 'slides' ][ $loop_index + 1 ] ) ): ?>
                  <div class="col-sm-3" data-target="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>"
                       data-slide-to="<?php echo esc_attr( $loop_index + 1 ); ?>">
                    <div class="thumb-image"
                         style="background: url('<?php echo esc_url( $gallery[ 'slides' ][ $loop_index + 1 ][ 'url' ] ); ?>') no-repeat center center;"></div>
                  </div>
                <?php endif; ?>

                <?php if ( isset( $gallery[ 'slides' ][ $loop_index + 2 ] ) ): ?>
                  <div class="col-sm-3" data-target="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>"
                       data-slide-to="<?php echo esc_attr( $loop_index + 2 ); ?>">
                    <div class="thumb-image"
                         style="background: url('<?php echo esc_url( $gallery[ 'slides' ][ $loop_index + 2 ][ 'url' ] ); ?>') no-repeat center center;"></div>
                  </div>
                <?php endif; ?>

                <?php if ( isset( $gallery[ 'slides' ][ $loop_index + 3 ] ) ): ?>
                  <div class="col-sm-3" data-target="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>"
                       data-slide-to="<?php echo esc_attr( $loop_index + 3 ); ?>">
                    <div class="thumb-image"
                         style="background: url('<?php echo esc_url( $gallery[ 'slides' ][ $loop_index + 3 ][ 'url' ] ); ?>') no-repeat center center;"></div>
                  </div>
                <?php endif; ?>

              </div>

            </div>

          <?php endif; ?>

          <?php $loop_index++; ?>

        <?php endforeach; ?>

        <?php if ( isset( $gallery[ 'controls' ] ) ): ?>
          <a class="carousel-control left" href="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>-thumbs" data-slide="prev">
            <span class="glyphicon glyphicon-menu-left"></span>
          </a>
          <a class="carousel-control right" href="#<?php echo esc_attr( $gallery[ 'id' ] ); ?>-thumbs" data-slide="next">
            <span class="glyphicon glyphicon-menu-right"></span>
          </a>
        <?php endif; ?>

      </div>

    </div>

  </div>

<?php endif; ?>

<!-- body -->

<div class="pmc-listicle-gallery-body" >
	<div class="ad-right-rail-2" >
		<?php pmc_adm_render_ads( apply_filters( 'listicle_gallery_body_ad', 'right-rail-2' ) ); ?>
	</div>
	<?php echo wp_kses_post( $gallery[ 'body' ] ) ?>

</div>
