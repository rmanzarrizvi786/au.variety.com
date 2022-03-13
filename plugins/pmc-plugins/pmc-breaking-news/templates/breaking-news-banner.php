<?php
/**
 * Display breaking news banner.
 */

?>

<div class="pmc-breaking-news nocontent">
	<div class="box-left"></div>
	<div class="news-alert">

		<div>
			<?php esc_html_e( 'Breaking News', 'pmc-plugins' ); ?>
		</div>
		<a href="<?php echo esc_url( $link ); ?>">
			<?php if ( PMC::is_mobile() ) { ?>
				<span class="<?php echo esc_attr( $span_class ); ?>">
					<?php
					// have to use wp_kses() rather than esc_html() since title can have italics which will be escaped if we use esc_html()
					echo wp_kses(
						$title, array(
							'i'      => array(),
							'em'     => array(),
							'strong' => array(),
							'b'      => array(),
						)
					); ?>
				</span>
				<?php
				if ( ! empty( $image_thumb ) ) {
					printf( '<img class="breaking-news-image" src="%s" />', esc_url( $image_thumb ) );
				}
				?>
			<?php } else { ?>
				<?php
				if ( ! empty( $image_thumb ) ) {
					printf( '<img class="breaking-news-image" src="%s" />', esc_url( $image_thumb ) );
				}
				?>
				<span class="<?php echo esc_attr( $span_class ); ?>">
					<?php
					// have to use wp_kses() rather than esc_html() since title can have italics which will be escaped if we use esc_html()
					echo wp_kses(
						$title, array(
							'i'      => array(),
							'em'     => array(),
							'strong' => array(),
							'b'      => array(),
						)
					); ?>
				</span>
			<?php } ?>
		</a>
		<i class="fa fa-times"></i>
	</div>
	<div class="box-bottom"></div>
</div>
