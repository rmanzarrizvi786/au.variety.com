<?php

namespace PMC\Core\Inc;


class Template {

	use \PMC\Global_Functions\Traits\Singleton;

	/**
	 * Output a link to the primary term in the given taxonomy.
	 *
	 * @see pmc_get_the_primary_term
	 *
	 * @param string $taxonomy Taxonomy for which to get the primary term.
	 * @param int $post_id  Optional. Post ID. If absent, uses current post.
	 */
	public static function the_primary_term_link( $taxonomy, $post_id = null ) {
		$term = pmc_get_the_primary_term( $taxonomy, $post_id );
		if ( $term ) {
			self::the_term_link( $term );
		}
	}

	/**
	 * Output a term link.
	 *
	 * Because this function outputs HTML, it must escape everything.
	 *
	 * @param WP_Term|array $term Optional. Term object or fields to look up the
	 *                            term. If an array, the key 'term' must be set and
	 *                            contain a term id or slug. If a slug, the key
	 *                            'taxonomy' should also be set. If $term is omitted
	 *                            altogether, and the queried object is a term, that
	 *                            term will be used.
	 * @param string $text The content for the link. Defaults to the term name. This
	 *                     can be customized to be any string, and can include the
	 *                     term name by adding '%s' in the string.
	 * @return boolean False on failure, otherwise it outputs the link.
	 */
	public static function the_term_link( $term = null, $text = '%s' ) {

		if ( is_array( $term ) && isset( $term['term'] ) ) {
			if ( is_int( $term['term'] ) ) {
				$term = get_term( $term['term'] );
			} else {
				if ( empty( $term['taxonomy'] ) ) {
					$term['taxonomy'] = null;
				}
				$term = get_term_by( 'slug', $term['term'], $term['taxonomy'] );
			}
		} elseif ( ! $term || ! isset( $term->term_id ) ) {
			$term = get_queried_object();
		}

		if ( ! isset( $term->term_id ) ) {
			return false;
		}

		if ( 'category' === $term->taxonomy ) {
			$link = Helper::get_category_link( $term );
		} else {
			$link = get_term_link( $term, $term->taxonomy );
		}

		if ( ! $link || is_wp_error( $link ) ) {
			return false;
		}

		printf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html( sprintf( $text, $term->name ) )
		);

	}

	/**
	 * Output a <picture> element.
	 *
	 * @param array $args          {
	 *                             Arguments used to generate the element. The only required argument is
	 *                             $sizes.
	 *
	 * @type array  $sizes         Array with media => size pairs, e.g.
	 *         [ '(min-width: 767px) and (max-width: 1023px)' => 'hero_tablet' ]
	 * @type string $default_size  Optional. The size for the image tag. If
	 *                                absent, the first element of the $sizes array
	 *                                will be used.
	 * @type int    $attachment_id Optional. If absent, the current post's thumb
	 *                              will be used.
	 * @type array  $image_attr    Optional. Array of image attributes passed to
	 *                             `wp_get_attachment_image()` for the <img> tag.
	 * }
	 *
	 * @version 2016-11-04 Brandon Camenisch - PPT-7057 - Adding height/width to args so that kses accepts them in output.
	 */
	public static function the_picture( array $args ) : void {
		$args = wp_parse_args(
			$args,
			[
				'sizes'         => [],
				'default_size'  => null,
				'attachment_id' => null,
				'image_attr'    => [],
			]
		);

		if ( empty( $args['sizes'] ) ) {
			return;
		}

		if ( empty( $args['attachment_id'] ) ) {
			$args['attachment_id'] = get_post_thumbnail_id();
		}

		if ( empty( $args['default_size'] ) ) {
			$args['default_size'] = reset( $args['sizes'] );
		}

		// Take full control over the default img tag.
		$kses_args = array(
			'img' => array(
				'alt'    => 1,
				'class'  => 1,
				'height' => 1,
				'src'    => 1,
				'title'  => 1,
				'width'  => 1,
			),
		);

		foreach ( $args['image_attr'] as $key => $value ) {
			$kses_args['img'][ $key ] = 1;
		}
		?>
		<picture>
			<?php foreach ( $args['sizes'] as $media => $size ) { ?>
				<source srcset="<?php echo esc_url( pmc_get_image_url( $size, $args['attachment_id'] ) ); ?>" media="<?php echo esc_attr( $media ); ?>">
			<?php } ?>

			<?php echo wp_kses( wp_get_attachment_image( $args['attachment_id'], $args['default_size'], false, $args['image_attr'] ), $kses_args ); ?>
		</picture>
		<?php
	}
}
