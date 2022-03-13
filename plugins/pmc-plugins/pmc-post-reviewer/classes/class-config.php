<?php
/**
 * Class to hold plugin configuration
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

namespace PMC\Post_Reviewer;


use \PMC\Global_Functions\Traits\Singleton;


class Config {

	use Singleton;

	/**
	 * @var array Container for all configuration values
	 */
	protected $_config = [

		'post_types'       => [ 'post', 'page' ],
		'taxonomies'       => [
			'category' => 'Categories',
			'post_tag' => 'Tags',
			'vertical' => 'Verticals',
		],

		/*
		 * Its an array of arrays with the following format:
		 *
		 * [
		 * 	'post_type' => [
		 * 		'meta_key' => 'meta name',
		 * 	],
		 * ]
		 *
		 */
		'post_meta_labels' => [],

		/*
		 * Its an array of arrays with the following format:
		 *
		 * [
		 * 	'post_type' => [ 'edit_others_posts' ],
		 * ]
		 *
		 */
		'allowed_caps'     => [
			'default' => [ 'edit_others_posts' ],
		],

	];

	/**
	 * Method to specify supported post types
	 *
	 * @param array $types An array containing post types which are to be supported by the plugin
	 * @param bool $overwrite Boolean value specifying whether the existing value in config should be overwritten or not
	 *
	 * @return \PMC\Post_Reviewer\Config
	 */
	public function add_post_types( array $types, bool $overwrite = false ) : Config {

		if ( ! empty( $types ) ) {

			if ( true !== $overwrite ) {

				$types = array_merge( $this->_config['post_types'], $types );

			}

			$types = apply_filters(
				'pmc_post_reviewer_config_add_post_types',
				array_unique( array_filter( $types ) ),
				$overwrite
			);

			$this->_config['post_types'] = array_unique( array_filter( $types ) );

		}

		return $this;

	}

	/**
	 * Method to fetch the configured post types
	 *
	 * @return array An array containing post types supported by the plugin
	 */
	public function get_post_types() : array {
		return $this->_config['post_types'];
	}

	/**
	 * Method to check if a specific post type is supported by the plugin or not
	 *
	 * @param string $post_type
	 *
	 * @return bool Returns TRUE if the passed post type is supported by the plugin else FALSE
	 */
	public function is_post_type_supported( string $post_type ) : bool {

		return in_array( $post_type, (array) $this->_config['post_types'], true );

	}

	/**
	 * Method to specify supported taxonomies
	 *
	 * @param array $taxonomies An array containing taxonomies which are to be supported by the plugin
	 * @param bool $overwrite Boolean value specifying whether the existing value in config should be overwritten or not
	 *
	 * @return \PMC\Post_Reviewer\Config
	 */
	public function add_taxonomies( array $taxonomies, bool $overwrite = false ) : Config {

		$taxonomies = array_filter( $taxonomies );

		if ( ! empty( $taxonomies ) ) {

			if ( true !== $overwrite ) {

				$taxonomies = array_merge( $this->_config['taxonomies'], $taxonomies );

			}

			$taxonomies = apply_filters( 'pmc_post_reviewer_config_add_taxonomies', array_filter( $taxonomies ), $overwrite );

			$this->_config['taxonomies'] = array_filter( $taxonomies );

		}

		return $this;

	}

	/**
	 * Method to fetch the configured taxonomies
	 *
	 * @return array An array containing taxonomies supported by the plugin
	 */
	public function get_taxonomies() : array {
		return $this->_config['taxonomies'];
	}

	/**
	 * Method to check if a specific taxonomy is supported by the plugin or not
	 *
	 * @param string $taxonomy
	 *
	 * @return bool Returns TRUE if the passed taxonomy is supported by the plugin else FALSE
	 */
	public function is_taxonomy_supported( string $taxonomy ) : bool {

		return ( ! empty( $this->_config['taxonomies'][ $taxonomy ] ) );

	}

	/**
	 * Method to specify labels for post meta for a supported post type.
	 *
	 * @param string $type Post type for which post meta labels are being specified
	 * @param array $labels An array containing post meta labels
	 * @param bool $overwrite Boolean value specifying whether the existing value in config should be overwritten or not
	 *
	 * @return \PMC\Post_Reviewer\Config
	 */
	public function add_post_meta_labels( string $type, array $labels, bool $overwrite = false ) : Config {

		if ( empty( $type ) || empty( $labels ) ) {
			return $this;
		}

		if ( true !== $overwrite && ! empty( $this->_config['post_meta_labels'][ $type ] ) ) {

			$labels = array_merge( $this->_config['post_meta_labels'][ $type ], $labels );

		}

		$labels = apply_filters( 'pmc_post_reviewer_config_add_post_meta_labels', $labels, $type, $overwrite );

		$this->_config['post_meta_labels'][ $type ] = $labels;

		return $this;

	}

	/**
	 * Method to check if a specific post type has any post meta labels registered or not
	 *
	 * @param string $type Post type for which existence of post-meta labels are to be checked
	 *
	 * @return bool Returns TRUE if post-meta labels for the post type are registered else FALSE
	 */
	public function has_post_meta_labels( string $type ) : bool {

		return ( ! empty( $this->_config['post_meta_labels'][ $type ] ) );

	}

	/**
	 * Method to fetch post-meta labels for a specific post type
	 *
	 * @param string $type Post type for which post-meta labels are to be fetched
	 *
	 * @return array An array containing post-meta labels for the post type. Returns empty array if no post-meta labels are registered.
	 */
	public function get_post_meta_labels_for_type( string $type ) : array {

		if ( ! $this->has_post_meta_labels( $type ) ) {
			return [];
		}

		return $this->_config['post_meta_labels'][ $type ];

	}

	/**
	 * Method to specify user capabilities for a supported post type.
	 *
	 * @param string $type Post type for which capabilities are being specified
	 * @param array $capabilities An array containing user capabilities
	 * @param bool $overwrite Boolean value specifying whether the existing value in config should be overwritten or not
	 *
	 * @return \PMC\Post_Reviewer\Config
	 */
	public function add_capabilities( string $type, array $capabilities, bool $overwrite = false ) : Config {

		$capabilities = array_unique( array_filter( $capabilities ) );

		if ( empty( $type ) || empty( $capabilities ) ) {
			return $this;
		}

		if ( true !== $overwrite && ! empty( $this->_config['allowed_caps'][ $type ] ) ) {

			$capabilities = array_merge( $this->_config['allowed_caps'][ $type ], $capabilities );

		}

		$capabilities = apply_filters(
			'pmc_post_reviewer_config_add_capabilities',
			array_unique( array_filter( $capabilities ) ),
			$type,
			$overwrite
		);

		$this->_config['allowed_caps'][ $type ] = array_unique( array_filter( $capabilities ) );

		return $this;

	}

	/**
	 * Method to fetch capabilities for a specific post type
	 *
	 * @param string $type Post type for which capabilities are to be fetched
	 *
	 * @return array An array containing capabilities for the post type. Returns empty array if no capabilities apply for the post type.
	 */
	public function get_capabilities_for_type( string $type ) : array {

		// Get all the allowed caps for the post type
		$capabilities = ( ! empty( $this->_config['allowed_caps'][ $type ] ) ) ? $this->_config['allowed_caps'][ $type ] : [];

		// Get the default caps & merge them to post type caps
		$capabilities = array_unique(
			array_filter(
				array_merge( $capabilities, $this->_config['allowed_caps']['default'] )
			)
		);

		sort( $capabilities );

		return $capabilities;

	}

	/**
	 * Method to check if current user is allowed on a specific post type or not
	 *
	 * @param string $type Post type for which user permission is to be checked
	 *
	 * @return bool Returns TRUE if current user is allowed on the post type else FALSE
	 */
	public function is_current_user_allowed_on_type( string $type ) : bool {

		$capabilities = $this->get_capabilities_for_type( $type );

		if ( ! empty( $capabilities ) ) {

			for ( $i = 0; $i < count( $capabilities ); $i++ ) {

				if ( current_user_can( $capabilities[ $i ] ) ) {
					return true;
				}

			}

		}

		return false;

	}

}    // end class



//EOF
