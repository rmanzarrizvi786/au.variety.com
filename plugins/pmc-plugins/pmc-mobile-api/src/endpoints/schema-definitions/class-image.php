<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Image class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

use stdClass;

/**
 * Image schema.
 */
class Image implements Definition {

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'image';
	}

	/**
	 * Get image response.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $variant_image Variant image info.
	 * @return array|stdClass
	 */
	public static function get_image( $attachment_id, $variant_image = [] ) {

		// Bail early if no attachment ID.
		if ( empty( $attachment_id ) ) {
			return new stdClass();
		}

		$credit  = \get_post_meta( $attachment_id, '_image_credit', true );
		$alt     = \get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		// Override fields for a gallery image if available.
		if ( ! empty( $variant_image ) && \is_array( $variant_image ) ) {
			if ( ! empty( $variant_image['title'] ) ) {
				$title = $variant_image['title'];
			}

			if ( ! empty( $variant_image['caption'] ) ) {
				$caption = $variant_image['caption'];
			}
		}

		if ( empty( $title ) ) {
			$title = \get_post_field( 'post_title', $attachment_id );
		}

		if ( empty( $caption ) ) {
			$caption = \get_post_field( 'post_excerpt', $attachment_id );
		}

		/**
		 * Add image crop sizes.
		 *
		 * @param array $crops Image crop names.
		 */
		$crops = \apply_filters( 'pmc_mobile_api_image_crop_sizes', [] );

		return [
			'alt'     => self::field_sanitizer( (string) $alt ),
			'title'   => self::field_sanitizer( (string) $title ),
			'credit'  => self::field_sanitizer( (string) $credit ),
			'caption' => self::field_sanitizer( (string) $caption ),
			'crops'   => array_map(
				function( $crop ) use ( $attachment_id ) {
					return [
						'name' => $crop,
						'url'  => \esc_url(
							\remove_query_arg(
								[ 'resize', 'w', 'h', 'fit' ],
								\wp_get_attachment_image_src( $attachment_id, $crop )[0] ?? ''
							)
						),
					];
				},
				(array) $crops
			),
		];
	}

	/**
	 * Sanitize field.
	 *
	 * @param string $field Field.
	 * @return string
	 */
	public static function field_sanitizer( $field ): string {
		return \html_entity_decode( \wp_strip_all_tags( $field ) );
	}

	/**
	 * Get image from post.
	 *
	 * @param \WP_Post|int $post Post object or Post ID.
	 * @return array|stdClass
	 */
	public static function get_image_from_post( $post ) {
		return self::get_image( \get_post_thumbnail_id( $post ) );
	}

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'alt'     => [
					'type'        => 'string',
					'description' => __( 'Alt text for the image.', 'pmc-mobile-api' ),
				],
				'title'   => [
					'type'        => 'string',
					'description' => __( 'Title text for the image.', 'pmc-mobile-api' ),
				],
				'credit'  => [
					'type'        => 'string',
					'description' => __( 'Credit text for the image.', 'pmc-mobile-api' ),
				],
				'caption' => [
					'type'        => 'string',
					'description' => __( 'Caption text for the image.', 'pmc-mobile-api' ),
				],
				'crops'   => [
					'type'        => 'array',
					'description' => __( 'Available crops of the image.', 'pmc-mobile-api' ),
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'name' => [
								'type'        => 'string',
								'description' => __( 'Name of the crop ratio.', 'pmc-mobile-api' ),
							],
							'url'  => [
								'type'        => 'string',
								'format'      => 'uri',
								'description' => __( 'URL of the image crop.', 'pmc-mobile-api' ),
							],
						],
					],
				],
			],
		];
	}
}
