<?php
/**
 * Gallery Helpers
 **/


/**
 * Get the primary term in a given taxonomy for a given (or the current) post.
 *
 * Terms are ordered in this site, and this function grabs the first term in the
 * list, consider that the "primary" term in that taxonomy.
 *
 * @param  string $taxonomy Taxonomy for which to get the primary term.
 * @param  int $post_id  Optional. Post ID. If absent, uses current post.
 * @return boolean|WP_Term WP_Term on success, false on failure.
 */
function pmc_get_the_primary_term( $taxonomy, $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$primary_term = wp_cache_get( "pmc_primary_{$taxonomy}_{$post_id}" );
	if ( false === $primary_term ) {

		// This has to use `wp_get_object_terms()` because we order them
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'orderby' => 'term_order' ) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$primary_term = reset( $terms );
			$primary_term = $primary_term->term_id;
		} else {
			$primary_term = 'none'; // if there are no terms, still cache that so we don't db lookup each time
		}

		wp_cache_set( "pmc_primary_{$taxonomy}_{$post_id}", $primary_term, '', HOUR_IN_SECONDS ); // invalidated on change
	}

	return 'none' === $primary_term ? false : get_term( $primary_term, $taxonomy );
}

/**
 * Get details to link back to a post linked to a gallery
 *
 * @param null
 *
 * @return bool|array False on failure. Array of [url] and [text] on success.
 *                    ex: array(
 *                        'text' => 'Back to Review',
 *                        'url'  => 'http://url.to.linked.article
 *                    )
 */
function pmc_gallery_back_to_linked_post() {
	global $post;

	if ( ! is_singular( 'pmc-gallery' ) ) {
		return false;
	}

	$linked_post_id = get_post_meta( $post->ID, 'pmc-gallery-linked-post_id', true );

	if ( empty( $linked_post_id ) ) {
		return false;
	}

	$linked_post_type = get_post_type( $linked_post_id );

	if ( empty( $linked_post_type ) ) {
		return false;
	}

	$linked_post_type_object = get_post_type_object( $linked_post_type );

	if ( empty( $linked_post_type_object ) || ! is_a( $linked_post_type_object, 'WP_Post_type' ) || wp_is_post_revision( $linked_post_id ) ) {
		return false;
	}

	if ( empty( $linked_post_type_object->labels->singular_name ) ) {
		return false;
	}

	$linked_post_type_name = $linked_post_type_object->labels->singular_name;

	$linked_post_permalink = get_permalink( $linked_post_id );

	if ( empty( $linked_post_permalink ) ) {
		return false;
	}

	$linked_post_data = array(
		'id'   => $linked_post_id,
		'url'  => $linked_post_permalink,
		'text' => 'Back to ' . $linked_post_type_name,
	);

	return $linked_post_data;
}

/**
 * Get the URL for an image at the given size.
 *
 * @param  string $size Image size
 * @param  int $attachment_id Optional. If absent, current post thumb is used.
 *
 * @return string URL on success, empty string on failure.
 */
function pmc_get_image_url( $size, $attachment_id = null ) {
	if ( ! $attachment_id ) {
		$attachment_id = get_post_thumbnail_id();
	}
	if ( empty( $attachment_id ) ) {
		return '';
	}

	$src = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! empty( $src[0] ) ) {
		return $src[0];
	}

	return '';
}

/**
 * Get the upnext gallery
 *
 * This gallery is recommended to users when they reach
 * the end of the gallery they are on.
 *
 * @param string $same_taxonomy The name of the same taxonomy to
 *                              fetch galleries from. Default is empty string.
 *
 * @return bool|WP_Post WP_Post object on success, false on failure.
 */
function pmc_get_upnext_gallery( $same_taxonomy = '' ) {
	$up_next_post     = false;
	$in_same_taxonomy = false;

	if ( ! empty( $same_taxonomy ) ) {
		$in_same_taxonomy = true;
	}

	// Select previous galleries.. there will always be previous ones,
	// if we only selected next posts the user would likely get to the end
	// and have no more posts to see.
	$up_next_post = wpcom_vip_get_adjacent_post( $in_same_taxonomy, false, true, $same_taxonomy );

	if ( ! empty( $up_next_post ) ) {
		return $up_next_post;
	}

	return false;
}

/**
 * KSES args for img tags. Ensures that unwanted args like srcset and sizes are
 * removed.
 *
 * @param  array $extra_attr Additional attributes to allow, e.g. ['data-foo'].
 *
 * @return array KSES args.
 */
function pmc_img_kses_args( $extra_attr = [] ) {
	$kses_args = [
		'img' => [
			'src'          => 1,
			'alt'          => 1,
			'class'        => 1,
			'title'        => 1,
			'data-title'   => 1,
			'data-credit'  => 1,
			'data-slug'    => 1,
			'data-img-lrg' => 1,
		],
	];

	foreach ( $extra_attr as $attr ) {
		$kses_args['img'][ $attr ] = 1;
	}

	return $kses_args;
}

/**
 * Output a one-line grid of gallery thumbnail images
 *
 * @param string $gallery_url The url of the gallery
 * @param array $gallery_items An array of attachment IDs within the gallery
 * @param string $image_size The image size to display the thumbnails
 * @param int $num_items_to_display
 * @param bool $offset Skip first item or not
 *
 * @return null
 */
function pmc_gallery_thumbnails( $gallery_url = '', $gallery_items = array(), $image_size = 'gallery-teaser-square', $num_items_to_display = 4, $offset = true ) {

	if ( empty( $gallery_url ) || empty( $gallery_items ) || ! is_array( $gallery_items ) || empty( $image_size ) || empty( $num_items_to_display ) ) {
		return;
	} ?>

	<div class="gallery-thumbnails">

		<?php
		// Using a counter here, as keys don't appear safe
		$counter = 1;
		foreach ( $gallery_items as $key => $attachment_id ) :

			// Skip first image if it appears above as featured.
			if ( $offset && ( 0 === $counter ) ) {
				continue;
			}

			$attachment = get_post( $attachment_id );
			$image_url  = wp_get_attachment_image_url( $attachment_id, $image_size );
			if ( empty( $image_url ) ) {
				continue;
			}
			$size       = \PMC\Image\get_image_size( $image_size );
			?>

			<a href="<?php echo esc_url( $gallery_url . '#!' . $counter . '/' . $attachment->post_name ); ?>" class="gallery-thumbnail">
				<img src="<?php echo esc_url( $image_url ); ?>" height="<?php echo esc_attr( $size['height'] ); ?>" width="<?php echo esc_attr( $size['width'] ); ?>"/>
			</a>

			<?php
			if ( $num_items_to_display == $counter ) {
				break;
			}

			$counter ++;
		endforeach;
		?>
	</div>
	<?php
}

/**
 * Fetch a given gallery's teaser image
 *
 * Teaser image = the featured image if there is one,
 * otherwise, the first gallery image
 *
 * @param int $gallery_id The gallery attachment post ID
 * @param int $first_gallery_attachment_id The ID of the first attachment in the gallery
 * @param string $image_size The image size for which to return it's URL
 *
 * @return bool|string False on failure, string image URL on success
 */
function pmc_get_gallery_teaser_image_url( $gallery_id = 0, $first_gallery_attachment_id = 0, $image_size = 'landscape-large' ) {

	if ( empty( $gallery_id ) || empty( $first_gallery_attachment_id ) || empty( $image_size ) ) {
		return false;
	}

	$teaser_attachment_id = false;
	$gallery_teaser_attachment = false;
	$gallery_teaser_attachment_url = false;

	// Does this gallery have a featured image?
	$featured_attachment_id = get_post_thumbnail_id( $gallery_id );

	// If so, use the featured image as the teaser..
	if ( ! empty( $featured_attachment_id ) ) {
		$teaser_attachment_id = $featured_attachment_id;
	} else {
		// ..if not, use the first gallery item
		if ( ! empty( $first_gallery_attachment_id ) ) {
			$teaser_attachment_id = $first_gallery_attachment_id;
		}
	}

	if ( ! empty( $teaser_attachment_id ) ) {
		$gallery_teaser_attachment = wp_get_attachment_image_src( $teaser_attachment_id, $image_size );

		if ( ! empty( $gallery_teaser_attachment ) && ! empty( $gallery_teaser_attachment[0] ) ) {
			return $gallery_teaser_attachment[0];
		}
	}

	return false;
}

/**
 * Get an attachment's photo credit.
 *
 * The photo credit field is added by the PMC Gallery Plugin.
 *
 * @param  int $attachment_id
 *
 * @return string|bool
 */
function pmc_get_photo_credit( $thumb_id ) {
	return get_post_meta( $thumb_id, '_image_credit', true );
}

/**
 * Output an attachment image for a given attachment id, size, and attributes.
 *
 * @param  int $attachment_id Attachment ID.
 * @param  string|array $size Optional. Image size. Defaults to 'thumbnail'.
 * @param  string|array $attr Image attributes.
 */
function pmc_the_attachment_image( $attachment_id, $size = 'thumbnail', $attr = [] ) {
	echo wp_kses(
		wp_get_attachment_image( $attachment_id, $size, false, $attr ),
		pmc_img_kses_args( array_keys( $attr ) )
	);
}

function pmc_core_gallery_options( array $args ) {

	$new_args = array(
		'auto_start'   => 8000, // set to 0 to disable auto start
		'loop_on_last' => false,
		'scale_image'  => false,
	);

	return array_merge( $args, $new_args );
}

add_filter( 'pmc-gallery-javascript-vars', 'pmc_core_gallery_options' );
