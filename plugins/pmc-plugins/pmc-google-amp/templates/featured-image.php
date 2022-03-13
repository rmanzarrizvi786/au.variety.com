<?php
/**
 * To render featured image on AMP page.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-06-07
 *
 * @package pmc-google-amp
 */

/**
 * We need to allow sizes and srcset attribute for img tag.
 */
$allow_html_tags = array(
	'img' => array(
		'width'     => array(),
		'height'    => array(),
		'src'       => array(),
		'class'     => array(),
		'alt'       => array(),
		'srcset'    => array(),
		'sizes'     => array(),
		'data-hero' => array(),
	),
);
?>
<div class="featured-image-container" >
	<div class="featured-image"><?php echo wp_kses( $image, $allow_html_tags ); ?></div>
	<div class="featured-image-captions">
		<?php
		if ( ! empty( $image_credit ) ) {
			printf( '<span class="image-credit" title="%s">%s</span>', esc_attr( $image_credit ), esc_html( $image_credit ) );
		}
		?>
	</div>
</div>

