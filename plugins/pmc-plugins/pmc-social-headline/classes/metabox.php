<?php

namespace PMC\Social_Headline;

use PMC;
use PMC\Global_Functions\Traits\Singleton;

class MetaBox {

	use Singleton;

	const META_KEY_HEADLINE = "pmc-social-headline-title";
	const META_KEY_GROUP = "social-headline-grp";

	/**
	 * Set up hooks
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-541
	 */
	protected function __construct() {

		if ( is_admin() ) {
			add_action( 'custom_metadata_manager_init_metadata', array( $this, 'meta_box_setup' ) );
		}

		add_action( 'wp_head', array( $this, 'render_meta_tag' ) );

	}

	/**
	 * Add the Social Headline meta box
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-541
	 *
	 */
	public function meta_box_setup() {

		$grp_args = array(
			'label'   => 'Social Headline',
			'context' => 'normal',
		);

		$post_array = array( 'post' );

		$group = self::META_KEY_GROUP;

		x_add_metadata_group( $group, $post_array, $grp_args );

		x_add_metadata_field(
			self::META_KEY_HEADLINE,
			$post_array,
			array(
				'group'             => $group,
				'field_type'        => 'text',
				'label'             => 'Title',
				'description'       => 'Please enter the Social Headline',
				'sanitize_callback' => array( $this, 'sanitize_social_headline_field' )
			)
		);

	}

	/**
	 *
	 * Sanitize the metabox value
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-541
	 *
	 * @param $field_slug
	 * @param $field
	 * @param $object_type
	 * @param $object_id
	 * @param $value
	 *
	 * @return mixed|string
	 */
	public function sanitize_social_headline_field( $field_slug, $field, $object_type, $object_id, $value ) {

		return sanitize_text_field( $value );
	}

	/**
	 * Check if there is a social headline saved for a post.
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-541
	 *
	 * @param $post_id int
	 * @return string
	 */
	public function has_social_headline( $post_id ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$social_headline = get_post_meta( $post_id, self::META_KEY_HEADLINE, true );

		return ! empty( $social_headline ) ? $social_headline : '';

	}

	/**
	 * Generates the meta tag that renders the social headline in the head section.
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-541
	 *
	 */
	public function render_meta_tag() {

		if ( is_single() ) {

			$post_id = get_queried_object_id();

			if ( $social_headline = $this->has_social_headline( $post_id ) ){
				$headline = get_post_meta( $post_id, self::META_KEY_HEADLINE, true );
			} else {
				$headline = get_the_title( $post_id );
			}

			$template_path = PMC_SOCIAL_HEADLINE_ROOT . '/templates/meta-tag.php';

			echo PMC::render_template( $template_path, array(
				'social_headline' => $headline,
			) );
		}

	}

}
