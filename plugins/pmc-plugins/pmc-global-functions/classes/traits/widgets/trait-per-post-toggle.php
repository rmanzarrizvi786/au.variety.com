<?php
/**
 * Trait to add toggle functionality to a widget
 * at post level via a post option.
 *
 * By implementing this trait, any widget can define
 * its own post option which would provide an easy
 * toggle for any feature. The trait takes care of
 * all the boilerplate code and exposes a straightforward API
 * which can be used by the widget to initialize post option
 * and to check if current/specific post has the option enabled or not.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-08-16
 */

namespace PMC\Global_Functions\Traits\Widgets;

use \PMC\Post_Options\API;
use \ErrorException;

trait Per_Post_Toggle {

	/**
	 * @var \PMC\Post_Options\API
	 */
	protected $_post_options_api;

	/**
	 * @var array Details of the post option to be created.
	 *
	 * These are to be defined in the class which implements this trait.
	 * If the values are not set then no post option will be created.
	 */
	protected $_post_option = [
		'slug'        => '',
		'label'       => '',
		'description' => '',
	];

	/**
	 * Method to init post options mechanism
	 * by getting API object and setting up listeners to hooks
	 *
	 * This should be called in class constructor.
	 *
	 * @return void
	 */
	protected function _init_post_options() : void {

		$this->_post_options_api = API::get_instance();

		/*
		 * Actions
		 */
		add_action( 'init', [ $this, 'register_post_option' ] );

	}

	/**
	 * Method to define post option values.
	 * This is to be called by the class implementing this trait.
	 *
	 * @param string $slug
	 * @param string $label
	 * @param string $desc
	 *
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	protected function _set_post_option_values( string $slug, string $label, string $desc = '' ) : void {

		$desc  = wp_strip_all_tags( $desc );
		$label = wp_strip_all_tags( $label );
		$slug  = wp_strip_all_tags(
			sanitize_title( $slug )
		);

		if ( empty( $slug ) || empty( $label ) ) {
			throw new ErrorException(
				sprintf( '%s::%s() expects a valid slug and name', get_called_class(), __FUNCTION__ )
			);
		}

		$this->_post_option = [
			'slug'        => $slug,
			'label'       => $label,
			'description' => $desc,
		];

	}

	/**
	 * Method to check if post option values have been defined in class var or not
	 *
	 * @return bool
	 */
	protected function _is_post_option_defined() : bool {

		if (
			! empty( $this->_post_option['slug'] ) && is_string( $this->_post_option['slug'] )
			&& ! empty( $this->_post_option['label'] ) && is_string( $this->_post_option['label'] )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Method to check if current post has the post-option or not.
	 * A post ID can be passed to run the check on a specific post.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function current_post_has_option( int $post_id = 0 ) : bool {

		if ( ! $this->_is_post_option_defined() ) {
			return false;
		}

		if ( ! is_singular() ) {
			return false;
		}

		$post_id = ( 0 > $post_id ) ? 0 : $post_id;
		$post    = get_post( $post_id );

		if (
			! empty( $post ) && is_a( $post, \WP_Post::class )
			&& $this->_post_options_api->post( $post )->has_option( $this->_post_option['slug'] )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Method to register post option under global options
	 *
	 * @return void
	 */
	public function register_post_option() : void {

		if ( ! $this->_is_post_option_defined() ) {
			return;
		}

		$this->_post_options_api->register_global_options(
			[
				$this->_post_option['slug'] => [
					'label'       => $this->_post_option['label'],
					'description' => ( empty( $this->_post_option['description'] ) ) ? '' : $this->_post_option['description'],
				],
			]
		);

	}

}    //end trait

//EOF
