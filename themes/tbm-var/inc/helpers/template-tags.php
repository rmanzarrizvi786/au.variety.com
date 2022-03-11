<?php
/**
 * Variety Template Tags
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

/**
 * Get Card Image
 *
 * Returns the proper URL for an image used in a template part card.
 *
 * @since 2017.1.0
 * @param object|int   $post_obj A \WP_Post Object (can also handle a \WP_Post ID).
 * @param string|array $size Optional. The typical image size value used in wp_get_attachment_image_src().
 * @param string       $photon_size Optional. Resize value for jetpack_photon_url().
 *
 * @return string An image URL.
 */
function variety_get_card_image_url( $post_obj, $size = 'landscape-large', $photon_size = '700,393'  ) {
	if ( is_numeric( $post_obj ) ) {
		$post_obj = get_post( $post_obj );
	}

	// Return the featured image.
	if ( has_post_thumbnail( $post_obj ) ) {
		$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_obj ), $size );
		if ( isset( $img_src[0] ) ) {
			return $img_src[0];
		}
	}

	// Return the default Photon image.
	if ( function_exists( 'jetpack_photon_url' ) ) {
		$img_src = jetpack_photon_url( 'https://pmcvariety.files.wordpress.com/2013/02/defaultwebimage_640-480.png', array( 'resize' => $photon_size ) );
		if ( ! empty( $img_src ) ) {
			return $img_src;
		}
	}

	// Return the PMC Core transparent image.
	return get_template_directory_uri() . '/static/images/trans.gif';
}

/**
 * Get Card Image Alt
 *
 * Returns the alt text for a card image.
 *
 * @since 2017.1.0
 * @param object|int $post_obj A \WP_Post Object (can also handle a \WP_Post ID).
 *
 * @return string Image alt text.
 */
function variety_get_card_image_alt( $post_obj ) {
	if ( is_numeric( $post_obj ) ) {
		$post_obj = get_post( $post_obj );
	}

	if ( has_post_thumbnail( $post_obj->ID ) ) {
		return \PMC::get_attachment_image_alt_text( get_post_thumbnail_id( $post_obj->ID ), $post_obj );
	}

	return get_the_title( $post_obj->ID );
}

/**
 * Human Time Diff
 *
 * Generate a readable human time diff.
 *
 * @since 2017.1.0
 * @param int $post_id A \WP_Post ID.
 *
 * @return string A time diff.
 */
function variety_human_time_diff( $post_id = 0 ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_id();
	}
	$date = strtotime( get_the_date( 'F j, Y g:i a', $post_id ) );
	$time_diff = human_time_diff( $date , current_time( 'timestamp' ) );

	return sprintf( __( '%s', 'pmc-variety' ), $time_diff ); // phpcs:ignore
}

/**
 * Normalize Post
 *
 * Since the different plugins around the site return data in different formats
 * this method aims to normalize the data in to a single unified object that
 * somewhat resembles a \WP_Post object.
 *
 * @since 2017.1.0
 * @param object|array $post The post to normalize.
 *
 * @return mixed
 */
function variety_normalize_post( $post ) {
	if ( empty( $post ) ) {
		return $post;
	}

	if ( isset( $post->normalized ) ) {
		return $post;
	}

	if ( is_array( $post ) ) {
		$post = (object) $post;
	}

	// Deal with titles.
	// VIP: Stopping PHP warning "Attempt to assign property of non-object"
	if ( ! $post instanceof WP_Post ) {
		return $post;
	}

	if ( ! empty( $post->title ) ) {
		$post->post_title = $post->title;
	}

	if ( empty( $post->post_title ) ) {
		$post->post_title = get_the_title( $post->ID );
	}

	$post->normalized = true;

	return $post;
}

/**
 * Is Feature
 *
 * Determine if a post is a Feature.
 *
 * @since 2017.1.0
 * @globals $post
 * @param object|int $post_obj A \WP_Post Object.
 *
 * @return bool
 */
function variety_is_feature( $post_obj = 0 ) {
	global $post;
	if ( empty( $post_obj ) || ! is_object( $post_obj ) ) {
		$post_obj = $post;
	}
	return ( has_term( 'Featured Article', 'editorial', $post_obj ) );
}

/**
 * Is Video CPT
 *
 * Determine if a post is from the variety_top_videos CPT.
 *
 * @since 2017.1.0
 * @globals $post
 * @param object|int $post_obj A \WP_Post Object.
 *
 * @return bool
 */
function variety_is_video_cpt( $post_obj = 0 ) {
	global $post;
	if ( ! class_exists( '\\Variety_Top_Videos' ) ) {
		return false;
	}

	if ( is_tax( 'vcategory' ) ) {
		return true;
	}

	if ( empty( $post_obj ) || ! is_object( $post_obj ) ) {
		$post_obj = $post;
	}

	return ( ! empty( $post_obj->post_type ) && \Variety_Top_Videos::POST_TYPE_NAME === $post_obj->post_type );
}

/**
 * Is Dirt
 *
 * Determine if a post is a Dirt Vertical.
 *
 * @since 2017.1.0
 * @globals $post
 * @param object|int $post_obj A \WP_Post Object.
 *
 * @return bool
 */
function variety_is_dirt( $post_obj = 0 ) {
	global $post;
	if ( empty( $post_obj ) || ! is_object( $post_obj ) ) {
		$post_obj = $post;
	}
	return ( has_term( 'dirt', 'vertical', $post_obj ) || has_category( 'real-estalker', $post_obj ) );
}

/**
 * Is Review
 *
 * Determine if a post is a Review.
 *
 * @since 2017.1.0
 * @globals $post
 * @param object|int $post_obj A \WP_Post Object.
 *
 * @return bool
 */
function variety_is_review( $post_obj = 0 ) {
	global $post;
	if ( empty( $post_obj ) || ! is_object( $post_obj ) ) {
		$post_obj = $post;
	}
	return has_category( 'reviews', $post_obj );
}

/**
 * Get the title based on different override priorities.
 *
 * Priority:
 * - Carousel
 * - Post Override
 * - Default Title
 *
 * @param int|\WP_Post $post The post to normalize.
 *
 * @return mixed
 */
function variety_get_card_title( $_post = null ) {

	if ( empty( $_post ) ) {
		$_post = get_the_ID();
	}

	if ( $_post instanceof \WP_Post && ! empty( $_post->custom_title ) ) {
		return $_post->custom_title;
	}

	return pmc_get_title( $_post );
}
