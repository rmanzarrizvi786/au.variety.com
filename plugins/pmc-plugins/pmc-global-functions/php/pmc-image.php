<?php

namespace PMC\Image;

/**
 * Late filter to re-include any standard WordPress image sizes that may have been removed.
 * Removing them results in the admin using non-standard or full size images causing performance issues.
 *
 * To remove standard wp image sizes for Attachment Display Settings safely,
 * use the pmc_remove_standard_wp_image_sizes filter and set it to true.
 *
 * @param $sizes
 *
 * @return array
 */
function image_size_names_choose( $sizes ) {
	$sizes = array_merge( $sizes, array(
		'thumbnail' => __( 'Thumbnail' ),
		'medium'    => __( 'Medium' ),
		'large'     => __( 'Large' ),
		'full'      => __( 'Full' ),
	) );

	return $sizes;
}
add_filter( 'image_size_names_choose', '\PMC\Image\image_size_names_choose', 9999 );

/**
 * Enqueue admin scripts.
 */
function admin_enqueue_scripts() {
	$remove_standard_images = apply_filters( 'pmc_remove_standard_wp_image_sizes', false );
	$customize_remove_standard_images = apply_filters( 'pmc_customize_remove_standard_wp_image_sizes', array(
		'thumbnail',
		'medium',
		'large',
		'full',
	) );

	$image_warning_array = apply_filters( 'pmc_image_size_warning', array() );

	foreach ( $image_warning_array as $key => $image_array ) {
		$title = sanitize_text_field( $image_array['title'] );
		$image_warning_array[ $key ]['class'] = '';

		if ( empty( $title ) ) {
			continue;
		}

		$image_warning_array[ $key ]['class'] = 'pmc_' . sanitize_title( $title ) . '_dimension_alert';
	}

	if ( $remove_standard_images || ! empty( $image_warning_array ) ) {
		wp_enqueue_script( 'pmc-images', pmc_global_functions_url( '/js/pmc-images.js' ), array( 'jquery' ), '', true );

		$pmc_images = array(
			'remove_standard_images' => (bool) $remove_standard_images,
			'customize_remove_standard_images' => (array) $customize_remove_standard_images,
			'image_size_warning' => (array) $image_warning_array,
		);
		wp_localize_script( 'pmc-images', 'pmc_images', $pmc_images );
	}
}
add_action( 'admin_enqueue_scripts', '\PMC\Image\admin_enqueue_scripts' );

/**
 * Add warnings to DOM if some image sizes do not meet requirements set by pmc_image_size_warning filter.
 *
 * @param $form_fields
 * @param $post
 *
 * @return mixed
 */
function attachment_fields_to_edit( $form_fields, $post ) {
	$image_meta = wp_get_attachment_metadata( $post->ID );

	$mime_array = array(
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/jpg',
	);

	if ( ! in_array( $post->post_mime_type, $mime_array ) ) {
		return $form_fields;
	}

	$image_warning_array = apply_filters( 'pmc_image_size_warning', array() );

	if ( ! empty( $image_warning_array ) && is_array( $image_warning_array ) ) {
		foreach ( $image_warning_array as $key => $image_array ) {
			if ( ! is_array( $image_array ) ) {
				continue;
			}
			$image_array_defaults = array(
				'title' => '',
				'width' => 0,
				'height' => 0,
				'message' => '',
			);

			$image_array = array_merge( $image_array_defaults, $image_array );

			$title = sanitize_text_field( $image_array['title'] );
			$width = intval( $image_array['width'] );
			$height = intval( $image_array['height'] );
			$message = ( ! empty( $image_array['message'] ) && is_string( $image_array['message'] ) ) ? sanitize_text_field( $image_array['message'] ) : sprintf( 'Image is too small. %dx%d is the minimum required size.', $width, $height );

			if ( $title && $width && $height ) {
				$alert = 'pmc_' . sanitize_title( $title ) . '_dimension_alert';

				if ( $image_meta['width'] < $width || $image_meta['height'] < $height ) {
					$form_fields[ $alert ] = array(
						'label' => '',
						'input' => 'html',
						'html'  => '<span class="' . esc_attr( $alert ) . '" style="display: none;"><span style="display: block; color: #bc0b0b; background-color: #fff0f5; border: 1px solid #bc0b0b; padding: 5px; margin-bottom: 5px;">' . esc_html( $message ) . '</span></span>',
						'helps' => '',
					);
				}
			}
		}
	}

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', '\PMC\Image\attachment_fields_to_edit', 10, 2 );

/**
 * Get all registered intermediate image sizes
 *
 * This is in response to https://wordpressvip.zendesk.com/hc/en-us/requests/42928
 * get_intermediate_image_sizes do not work as expected on VIP and it is causing us unexpected
 * behaviour in Gallery V2.
 *
 * @param null
 *
 * @return array An array of all registered image sizes
 */
function get_intermediate_image_sizes() {
	global $_wp_additional_image_sizes;

	// @see /wp-content/mu-plugins/wpcom-media.php
	$had_filter = remove_filter( 'intermediate_image_sizes', 'wpcom_intermediate_sizes' );

	$sizes = \get_intermediate_image_sizes();

	if ( $had_filter ) {
		add_filter( 'intermediate_image_sizes', 'wpcom_intermediate_sizes' );
	}
	return $sizes;
}

/**
 * Get size information for all currently-registered image sizes.
 *
 * @author Mike Auteri <michael.auteri@pmc.com>
 *
 * @global $_wp_additional_image_sizes
 *
 * @uses   \PMC\Image\get_intermediate_image_sizes To fetch all registered image sizes
 *
 * @return array $sizes Data for all currently-registered image sizes.
 */
function get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {

			$width  = intval( get_option( "{$_size}_size_w" ) );
			$height = intval( get_option( "{$_size}_size_h" ) );

			if ( ! $width || ! $height ) {
				continue;
			}

			$sizes[ $_size ]['width']  = (int) $width;
			$sizes[ $_size ]['height'] = (int) $height;
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );

		} else if ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$width  = intval( $_wp_additional_image_sizes[ $_size ]['width'] );
			$height = intval( $_wp_additional_image_sizes[ $_size ]['height'] );

			if ( ! $width || ! $height ) {
				continue;
			}

			$sizes[ $_size ] = array(
				'width'  => (int) $width,
				'height' => (int) $height,
				'crop'   => (bool) $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

/**
 * Get size information for a specific image size.
 *
 * @author Mike Auteri <michael.auteri@pmc.com>
 *
 * @uses  \PMC\Image\get_image_sizes To obtain all registered image sizes
 *
 * @param string $size The image size for which to retrieve data.
 *
 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
 */
function get_image_size( $size ) {
	$sizes = get_image_sizes();

	if ( isset( $sizes[ $size ] ) ) {
		return $sizes[ $size ];
	}

	return false;
}

/**
 * Determine the maximum proportional crop for an image size
 *
 * This helper is meant for use with Photon when requesting
 * a cropped image. You can use the return width/height to
 * crop your image with Photon and then resize to the requested
 * image size width/height.
 *
 * Given an image which is 682x1024, if we want a cropped
 * version of that image at 640x415, this function would return
 * [ 682, 442 ]. That means the image should be cropped at those
 * dimensions then may be proportionally resized to 640x415.
 *
 * E.g. with Photon you would do: ?crop=0px,0px,682px,442px&resize=640,415
 *
 * @author James Mehorter <james.mehorter@pmc.com>
 *
 * @uses \PMC\Image\get_image_size  To obtain details about a given image size
 * @uses wp_get_attachment_metadata To obtain details about a given attachment
 *
 * @param int    $attachment_id The attachment to use for bounds
 * @param string $image_size    The image size to use for the potential crop
 *
 * @return array|bool
 */
function get_image_size_max_possible_crop( $attachment_id = 0, $image_size = '' ) {

	if ( empty( $attachment_id ) || empty( $image_size ) ) {
		return false;
	}

	// Gather information about the requested image size
	$requested_size = get_image_size( $image_size );

	// Store a reference to the requested size aspect ratio
	$requested_size['aspect_ratio'] = $requested_size['width'] / $requested_size['height'];

	// Gather some information about the originally-uploaded image
	$full_image = wp_get_attachment_metadata( $attachment_id );

	// Determine the max width/height we can crop the full image
	if ( $requested_size['aspect_ratio'] < 1 ) {

		// Image is a portrait
		$crop_width  = min( $full_image['height'] * $requested_size['aspect_ratio'], $full_image['width'] );
		$crop_height = $crop_width / $requested_size['aspect_ratio'];

	} else {

		// Image is a landscape or a square
		$crop_height = min( $full_image['width'] / $requested_size['aspect_ratio'], $full_image['height'] );
		$crop_width  = $crop_height * $requested_size['aspect_ratio'];
	}

	return array( intval( $crop_width ), intval( $crop_height ) );
}

/**
 * Convert an array of named crops to an array of pixel dimensions
 *
 * If an attachment is 1000x1000 and the named crop is [ center, top ]
 * and the crop dimensions are 800x200, the returned array would contain [ 100, 0 ].
 * Those pixel values then may be used in a Photon image url,
 * E.g. ?crop=100px,0px,800px,200px
 *
 * @author James Mehorter <james.mehorter@pmc.com>
 *
 * @uses wp_get_attachment_metadata To obtain details about a given attachment
 *
 * @param int   $attachment_id   An attachment to use
 * @param array $named_crops     An array of the named crops, e.g. [ center, top ]
 * @param array $crop_dimensions An array of crop dimensions (in pixels), e.g. [ 800, 200 ]
 *
 * @return array|bool An array containing width/height pixel values representing a named crop
 *                    False on failure.
 */
function get_pixel_coordinates_for_named_crop( $attachment_id = 0, $named_crops = array(), $crop_dimensions = array() ) {

	if ( empty( $attachment_id ) || empty( $named_crops ) || 2 < count( $named_crops ) || empty( $crop_dimensions ) || 2 < count( $crop_dimensions ) ) {
		return false;
	}

	if ( empty( $crop_dimensions[0] ) || empty( $crop_dimensions[1] ) ) {
		return false;
	}

	$crop_x = $crop_y = false;

	// Gather some information about the originally-uploaded image
	$full_image = wp_get_attachment_metadata( $attachment_id );

	if ( empty( $full_image['width'] ) || empty( $full_image['height'] ) ) {
		return false;
	}

	// Determine the crop's starting position along the x-axis
	switch( $named_crops[0] ) {
		case 'left' :
			$crop_x = 0;
		break;
		case 'center' :
			$crop_x = ( $full_image['width'] - $crop_dimensions[0] ) / 2;
		break;
		case 'right' :
			$crop_x = $full_image['width'] - $crop_dimensions[0];
		break;
	}

	// Determine the crop's starting position along the y-axis
	switch( $named_crops[1] ) {
		case 'top' :
			$crop_y = 0;
		break;
		case 'center' :
			$crop_y = ( $full_image['height'] - $crop_dimensions[1] ) / 2;
		break;
		case 'bottom' :
			$crop_y = $full_image['height'] - $crop_dimensions[1];
		break;
	}

	if ( false !== $crop_x && false !== $crop_y ) {
		return array( intval( $crop_x ), intval( $crop_y ) );
	}

	return false;
}


// EOF