<?php
/**
 * Template for Related link.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-07-07 CDWE-446
 *
 * @package pmc-google-amp
 */

?>
<div class="pmc-related-link <?php echo esc_attr( $type_slug ); echo $include_thumbnail ? ' have-image ' : ' have-not-image '; ?>">
	<strong class="pmc-related-type"><?php echo wp_kses_post( $attrs['type'] ) ?></strong>
	<a target="<?php echo esc_attr( $attrs['target'] ) ?>" href="<?php echo esc_url( $attrs['href'] ) ?>" title="<?php echo esc_attr( $content ) ?>">
		<?php
		if ( $include_thumbnail ) {
			printf( '<span class="image"><img src="%s" alt="%s"></span>', esc_url( $image_url ), esc_attr( $content ) );
		}
		?>
		<span class="text"><?php echo wp_kses_post( $content ); ?></span>
	</a>
</div>
