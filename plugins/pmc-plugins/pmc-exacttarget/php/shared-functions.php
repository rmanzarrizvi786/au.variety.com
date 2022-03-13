<?php

/*
 * Shared Functions for MMC_Newsletter plugin
 */

/*
 * Take in an IMG HTML tag & return wanted attributes in an array
 *
 * @since 2011-04-22 Amit Gupta
 * @param string $img Required. Image HTML Tag
 * @param array $array_attr Optional. Array containing attributes to be returned, by default returns all attributes
 */
function mmc_newsletter_get_img_attr( $img, $array_attr = 'all' ) {
	if ( empty( $img ) ) {
		return false;
	}
	$array_attr = ( empty( $array_attr ) ) ? 'all' : $array_attr;
	//lets extract all attributes from img tag
	preg_match_all( '/(\w+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $img, $matches );
	$array_img = array();
	foreach ( $matches[1] as $key => $attr_name ) {
		if ( ( !is_array( $array_attr ) && $array_attr == "all" ) || ( in_array( $attr_name, $array_attr ) ) ) {
			$array_img[$attr_name] = $matches[3][$key];
		}
	}
	return $array_img;
}

/*
 * Takes string and returns first image tag from it
 *
 * @since 2011-05-11 Prashant M
 * @param string $content The content to search the image tag in.
 * @return string|false First image tag if found
 */
function mmc_newsletter_get_first_image_tag( $content ) {

	$first_img = false;
	$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*\/>/i', $content, $matches );

	if ( count( $matches[0] ) > 0 ) {
		$first_img = $matches [0][0];
	}

	return $first_img;
}

/*
 * Takes string and returns first image id from it
 *
 * @since 2011-05-11 Prashant M
 * @param string $content The content to search the image tag in.
 * @param string $src The calling function can use this value.
 * @return string|false First image id if found
 */
function mmc_newsletter_get_first_image_id( $content, &$src ) {

	$first_img_id = false;

	// get the first image tag
	$img_tag = mmc_newsletter_get_first_image_tag( $content );

	// if image has been inserted from gallery, class attribute will have
	// id of the image
	$img_tag_class = mmc_newsletter_get_img_attr( $img_tag, array( 'class',
																 'src' ) );

	// in case the calling function needs the src
	$src = $img_tag_class['src'];

	// image does not belong to this site
	if ( stripos( $img_tag_class['src'], home_url() ) !== false ) {
		if ( !empty( $img_tag_class['class'] ) ) {
			$output = preg_match_all( '/wp-image-([0-9]+)/i', $img_tag_class['class'], $matches );
			if ( count( $matches[1] ) > 0 && !empty( $matches [1][0] ) ) {
				// found image id in class attribute
				$first_img_id = $matches [1][0];
			}
		}

		if ( !$first_img_id ) {
			// image id not found in class. Let's see if we can find it from image src
			$img_src = $img_tag_class['src'];
			$img_src = preg_replace( '/(\w+)-(\d+)x(\d+)(\.[jpg|gif|png|jpeg|bmp])/i', '${1}${4}', $img_src );

			global $wpdb;
			$img_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment'", $img_src ) );

			if ( $img_ids ) {
				// found image id
				$first_img_id = $img_ids[0];
			}
		}
	}

	return $first_img_id;
}


/*
 * Takes attachment id and returns thumb for size 'mmc_newsletter_thumb'
 *
 * @since 2011-05-11 Prashant M
 * @param int $imageID The attachment id.
 * @return array $src contains image source, width and height
 */
function mmc_newsletter_get_attachment_thumb( $imageID, $size = 'mmc_newsletter_thumb' ) {
	/* check if the thumbnail of size 'mmc_newsletter_thumb' exists
	 * wp_get_attachment_image_src will return an array. The format would be
	 * [0] = image url
	 * [1] = image width
	 * [2] = image height
	 * [3] = boolean value indicating if the returned image is of the requested size.
	 */
	$src = wp_get_attachment_image_src( $imageID, $size );

	if ( ! empty( $src ) && empty( $src[3] ) ) {
		global $_wp_additional_image_sizes;

		if ( isset( $_wp_additional_image_sizes[$size] ) ) {
			$size_attr = $_wp_additional_image_sizes[$size];
			$url = $src[0];
			$url = add_query_arg( 'w', $size_attr["width"], $url );
			$url = add_query_arg( 'h', $size_attr["height"], $url );
			$url = add_query_arg( 'crop', $size_attr["crop"], $url );
			$src[0] = $url;
			$src[3] = true;
		}
		// generate thumb
		$src = apply_filters( 'mmc_newsletter_url_filter', $src );
	}

	return $src;
}


/*
* Takes in an image path (S3 or local) & resizes it to specified size maintaining aspect ratio
* @since 2011-05-12 Satyanarayan Verma
*/
function create_mmcnewsletter_feature_image( $imagepath, $width, $height ) {
	if ( ! empty( $imagepath ) ) {
		$imagepath = add_query_arg( 'w', $width, $imagepath );
		$imagepath = add_query_arg( 'h', $height, $imagepath );
		$imagepath = add_query_arg( 'crop', 1, $imagepath );

		return $imagepath;
	}
}


//EOF