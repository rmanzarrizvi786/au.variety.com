<?php

namespace PMC\Subscription_Banners;

use PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	const COLOR_PICKER = 'color-picker';
	const IMAGE        = 'image';
	const HYPER_LINK   = 'hyper_link';
	const RICHTEXTAREA = 'richtext-area';

	/**
	 * This is our class constructor which
	 * is initialization method that
	 * runs when this class is instantiated.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
	}

	/**
	 * @return array
	 */
	public function get_default_fields() {

		$default_fields = [
			'background_image'           => [
				'name'        => 'background_image',
				'description' => esc_html__( 'Background Image for the placement', 'pmc-subscription-banners' ),
				'field'       => self::IMAGE,
			],
			'background_color'           => [
				'name'        => 'background_color',
				'description' => esc_html__( 'Background Color', 'pmc-subscription-banners' ),
				'field'       => self::COLOR_PICKER,
			],
			'title'                      => [
				'name'        => 'title',
				'description' => esc_html__( 'Title (max 20 characters)', 'pmc-subscription-banners' ),
				'field'       => self::RICHTEXTAREA,
			],
			'body_copy'                  => [
				'name'        => 'body_copy',
				'description' => esc_html__( 'Body Copy (max 70 characters)', 'pmc-subscription-banners' ),
				'field'       => self::RICHTEXTAREA,
			],
			'body_sub_copy'              => [
				'name'        => 'body_sub_copy',
				'description' => esc_html__( 'Body Sub Copy (max 240 characters)', 'pmc-subscription-banners' ),
				'field'       => self::RICHTEXTAREA,
			],
			'cover_image'                => [
				'name'        => 'cover_image',
				'description' => esc_html__( 'Cover Image- Aspect ratio 1:1.2 (width:height) - Normally 308x374px', 'pmc-subscription-banners' ),
				'field'       => self::IMAGE,
			],
			'subscribe_background_color' => [
				'name'        => 'subscribe_background_color',
				'description' => esc_html__( 'Subscribe button Background Color', 'pmc-subscription-banners' ),
				'field'       => self::COLOR_PICKER,
			],
			'subscribe_copy'             => [
				'name'        => 'subscribe_copy',
				'description' => esc_html__( 'Subscribe button Copy (max 12 characters)', 'pmc-subscription-banners' ),
				'field'       => self::RICHTEXTAREA,
			],
			'subscribe_link'             => [
				'name'        => 'subscribe_link',
				'description' => esc_html__( 'Subscribe Link', 'pmc-subscription-banners' ),
				'field'       => self::HYPER_LINK,
			],
			'gift_copy'                  => [
				'name'        => 'gift_copy',
				'description' => esc_html__( 'Gift Copy (max 25 characters)', 'pmc-subscription-banners' ),
				'field'       => self::RICHTEXTAREA,
			],
			'gift_link'                  => [
				'name'        => 'gift_link',
				'description' => esc_html__( 'Gift Link', 'pmc-subscription-banners' ),
				'field'       => self::HYPER_LINK,
			],
		];

		return $default_fields;

	}

	/**
	 * @return array
	 */
	public function get_banners(): array {
		$banners = apply_filters( 'pmc_subscription_banners_list', [] );
		return is_array( $banners ) ? $banners : [];
	}

	/**
	 * @param string $name
	 * @param string $description
	 *
	 * @return array
	 */
	public function get_image_field( string $name = '', string $description = '' ): array {
		return [
			$name => new \Fieldmanager_Media(
				[
					'label' => $description,
				]
			),
		];
	}

	/**
	 * @param string $name
	 * @param string $description
	 *
	 * @return array
	 */
	public function get_color_picker_field( string $name = '', string $description = '' ): array {
		return [
			$name => new \Fieldmanager_Colorpicker(
				[
					'label' => $description,
				]
			),
		];
	}

	/**
	 * @param string $name
	 * @param string $description
	 *
	 * @return array
	 */
	public function get_richtextarea_field( string $name = '', string $description = '' ): array {

		$buttons_1       = [ 'forecolor', 'bold', 'italic', 'underline' ];
		$buttons_2       = [];
		$editor_settings = [
			'media_buttons' => false,
			'teeny'         => false,
			'quicktags'     => false,
			'textarea_rows' => 2,
		];

		return [
			$name => new \Fieldmanager_RichTextArea(
				[
					'label'           => $description,
					'buttons_1'       => $buttons_1,
					'buttons_2'       => $buttons_2,
					'editor_settings' => $editor_settings,
				]
			),
		];
	}

	/**
	 * @param string $name
	 * @param string $description
	 *
	 * @return array
	 */
	public function get_link_field( string $name = '', string $description = '' ): array {

		return [
			$name => new \Fieldmanager_Link(
				[
					'label' => $description,
				]
			),
		];
	}

	/**
	 * @param int $cover_image_id
	 *
	 * @return string
	 */
	public function get_magazine_cover_url( int $cover_image_id = 0 ): string {

		// @todo: create a new placeholder image in the below folder thats more generic and not brand specific
		$url = apply_filters( 'pmc_subscriptions_default_cover_image', sprintf( '%s/assets/images/mag-cover-placeholder.jpg', PMC_SUBSCRIPTION_BANNERS_URI ) );

		// Return the image URL if available.
		if ( ! empty( $cover_image_id ) ) {
			$url = wp_get_attachment_url( $cover_image_id );
		}

		return $url;

	}

}
