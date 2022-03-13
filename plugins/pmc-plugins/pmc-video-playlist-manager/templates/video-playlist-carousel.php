<?php
/**
 * Videos Template
 *
 * @package pmc-plugins
 * @since 2018.03.
 */


if ( empty( $videos ) || ! is_array( $videos ) ) {
	return;
}

$has_jwplayer = false;

?>
<section class="l-pvm-video l-pvm-video--carousel" data-pvm-video-carousel>

	<header class="l-pvm-video__header">
		<h3 class="c-pvm-heading c-pvm-heading--section"><?php echo esc_html( $module_title ); ?></h3>
	</header>

	<div class="l-pvm-video__player">
		<nav>
			<button class="l-pvm-video__nav l-pvm-video__nav--prev" data-pvm-video-trigger="prev">
				<span class="screen-reader-text"><?php esc_html_e( 'Previous video', 'pmc-video-playlist-manager' ); ?></span>
			</button><!-- .l-video__nav -->
			<button class="l-pvm-video__nav l-pvm-video__nav--next" data-pvm-video-trigger="next">
				<span class="screen-reader-text"><?php esc_html_e( 'Next video', 'pmc-video-playlist-manager' ); ?></span>
			</button><!-- .l-video__nav -->
		</nav>

		<ul class="l-pvm-video__carousel" data-pvm-video-slider>
			<?php
			foreach ( $videos as $video ) {

				$modifiers = ( intval( $video->ID ) === intval( $featured_video ) ) ? ' is-active' : '';

				?>
				<li class="l-pvm-video__slide <?php echo esc_attr( $modifiers ); ?>" data-pvm-video="video-unique-slug-<?php echo intval( $video->ID ); ?>">

					<div class="c-pvm-player is-static">

						<div class="c-pvm-player__thumb">
							<a href="#" class="c-pvm-player__link">
								<img src="<?php echo esc_url( $video->image ); ?>" alt="<?php echo esc_attr( $video->image_alt ); ?>"/>
								<div class="l-pvm-video__shadow l-pvm-video__shadow-player"></div>
							</a>

							<?php
							// For YouTube, apply an iFrame. Caters for youtu.be links.
							$video_source = $video->video_source;
							if ( strpos( $video_source, 'youtu' ) !== false ) {
								$video_source = str_replace( 'www.', '', $video_source );

								if ( strpos( $video_source, 'youtu.be' ) ) {
									$video_source = preg_replace( '~^https?://youtu\.be/([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source );
								} elseif ( strpos( $video_source, 'youtube.com/watch' ) ) {
									$video_source = preg_replace( '~^https?://youtube\.com\/watch\?v=([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source );
								}

								$query_data = array(
									'enablejsapi'    => '1',
									'origin'         => site_url(),
									'version'        => '3',
									'rel'            => '1',
									'fs'             => '1',
									'autohide'       => '2',
									'showsearch'     => '0',
									'showinfo'       => '1',
									'iv_load_policy' => '1',
									'wmode'          => 'transparent',
									'autoplay'       => '0',
								);

								$video_source .= '?' . http_build_query( $query_data );

								echo '<iframe type="text/html" width="670" height="407" data-pvm-src="' . esc_url( $video_source ) . '" allowfullscreen="true" style="border:0;"></iframe>';

							} elseif ( false !== strpos( $video_source, 'jwplayer' ) || false !== strpos( $video_source, 'jwplatform' ) ) {

								// Yes, we have atleast one jwplayer.
								$has_jwplayer = true;

								global $jwplayer_shortcode_embedded_players;

								/**
								 * https://regex101.com/r/PfNZrt/1
								 */
								$regex = '/\[(jwplayer|jwplatform) (?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?\]/i';
								preg_match( $regex, $video_source, $matches, null, 0 );

								$player = ( ! empty( $matches['player'] ) ) ? $matches['player'] : false;
								$media  = ( ! empty( $matches['media'] ) ) ? $matches['media'] : false;
								$player = ( false === $player ) ? get_option( 'jwplayer_player' ) : $player;

								$content_mask = jwplayer_get_content_mask();
								$protocol     = ( is_ssl() && defined( 'JWPLAYER_CONTENT_MASK' ) && JWPLAYER_CONTENT_MASK === $content_mask ) ? 'https' : 'http';

								$json_feed = "$protocol://$content_mask/feeds/$media.json";

								\PMC\Video_Player\JWPlayer::get_instance()->render_tag( (string) $media, (string) $json_feed );

							} else {
								// Run it through the_content filter to process any oEmbed or Shortcode
								$video_source = apply_filters( 'the_content', $video_source );

								$allowed_tags = array(
									'span'   => array(
										'class' => array(),
										'style' => array(),
									),
									'iframe' => array(
										'class'           => array(),
										'type'            => array(),
										'width'           => array(),
										'height'          => array(),
										'src'             => array(),
										'data-pvm-src'    => array(),
										'allowfullscreen' => array(),
										'style'           => array(),
									),
								);

								echo wp_kses( $video_source, $allowed_tags );
							}
							?>
						</div><!-- .c-pvm-player__thumb -->

						<header class="c-pvm-player__title">
							<?php if ( is_single() ) { ?>
								<h1 class="c-pvm-heading c-pvm-heading--video"><?php echo esc_html( $video->post_title ); ?></h1>
							<?php } else { ?>
								<a href="<?php echo esc_url( get_permalink( $video->ID ) ); ?>">
									<h2 class="c-pvm-heading c-pvm-heading--video"><?php echo esc_html( $video->post_title ); ?></h2>
								</a>
							<?php } ?>
						</header><!-- .c-pvm-player__title -->
					</div><!-- .c-pvm-player -->
				</li><!-- .c-pvm-video__slide -->
				<?php
			}
			?>
			<?php if ( true === $has_jwplayer ) { ?>
				<li id="pvm-carousel-jwplayer" class="l-pvm-video__slide l-pvm-video__slide--jw-player">
					<div class="c-pvm-player">
						<div class="c-pvm-player__thumb">
							<div id="pvm_jwplayer_carousel_div"></div>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul><!-- .l-pvm-video__carousel -->

		<footer class="l-pvm-video--more-link">
			<a href="<?php echo esc_url( $playlist_url ); ?>"><?php echo esc_html( 'Watch More', 'pmc-video-playlist-manager' ); ?></a>
		</footer>

		<ul class="l-pvm-video__playlist">
			<?php
			foreach ( $videos as $video ) {
				$modifiers = ( intval( $video->ID ) === intval( $featured_video ) ) ? ' is-active' : '';
				?>
				<li class="l-pvm-video__item <?php echo esc_attr( $modifiers ); ?>" data-pvm-video-trigger="video-unique-slug-<?php echo intval( $video->ID ); ?>">
					<article class="c-pvm-card c-pvm-card--video" itemscope itemtype="http://schema.org/Article">

							<figure data-is-viewing-label="Viewing" class="c-pvm-card__image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject" style="display: block; background-image:url('<?php echo esc_url( esc_url( $video->image ) ); ?>');">
								<a href="<?php echo esc_url( $video->url ); ?>" class="c-pvm-card__video-link">
									<img src="<?php echo esc_url( $video->image ); ?>" alt="<?php echo esc_attr( $video->image_alt ); ?>" itemprop="contentUrl">
								</a>
							</figure>

							<header class="c-pvm-card__header">
								<h3 class="c-pvm-card__title" itemprop="headline"><a href="<?php echo esc_url( get_permalink( $video->ID ) ); ?>"><?php echo esc_html( $video->post_title ); ?></a>
								</h3>
							</header>
					</article>
				</li><!-- .l-pvm-video__item -->
				<?php
			}
			?>
		</ul><!-- .l-pvm-video__playlist -->
		<div class="l-pvm-video__shadow l-pvm-video__shadow-left"></div>
		<div class="l-pvm-video__shadow l-pvm-video__shadow-right"></div>
	</div>
</section><!-- .l-pvm-video -->
