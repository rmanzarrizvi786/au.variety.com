<?php

/**
 * Class PMC_Inject_Content
 * Add functionality to inject content inside post_content.
 * Activate via: PMC_Inject_Content::get_instance()->register_post_type( array( 'post' ) )
 * Use filter hook pmc_inject_content_paragraphs to setup content to be inject into paragraphs
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Inject_Content {

	use Singleton;

	private $_post_type           = [];
	private $_paragraphs          = [];
	private $_auto_content_inject = true;

	protected function __construct() {
		add_action( 'wp', array( $this, 'action_wp' ) );
	}

	public function auto_inject( bool $auto_inject ) {
		$this->_auto_content_inject = $auto_inject;

		return $this->_auto_content_inject;
	}

	public function action_wp() {
		if ( ! ( $post = get_post() ) ) {
			return;
		}

		if ( ! $this->_auto_content_inject ) {
			return;
		}

		if ( ! is_single( $post) || ! in_array( get_post_type(), $this->_post_type, true ) ) {
			return;
		}

		$context = is_feed() ? 'feed' : ( is_single() ? 'single' : 'river' );

		// for compatibility, plugin need to be rewrite to use shortcode for injection to support total paragraphs count
		$total_paragraphs = 10;

		$content = wpautop( $post->post_content );

		$this->register_paragraphs( apply_filters( 'pmc_inject_content_paragraphs', array(), $total_paragraphs, $context ) );

		// We need to use priority 7 to inject marker into the paragraph prior to autoembed (priority 8)
		add_filter( 'the_content', array( $this, 'filter_the_content_7' ), 7 );
		// we then need to translate the marker into injected contents to prevent wpautop (priority 10) from messing up the html code
		add_filter( 'the_content', array( $this, 'filter_the_content_11' ), 11 );
	}

	/**
	 * @param array $post_type The array of post type to activate
	 * @return $this Object
	 */
	public function register_post_type( $post_type ) {
		if ( !empty( $post_type ) ) {
			if ( !is_array( $post_type ) ) {
				$post_type = array( $post_type );
			}
			$this->_post_type = array_filter( array_unique( array_merge( $this->_post_type, array_values( $post_type ) ) ) );
		}
		return $this;
	}

	public function register_paragraphs( array $paragraphs ) {
		foreach ( $paragraphs as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			if ( ! isset( $this->_paragraphs[ $key ] ) ) {
				$this->_paragraphs[ $key ] = array();
			}
			$this->_paragraphs[ $key ] = array_merge( $this->_paragraphs[ $key ], $value );
		}
	}

	/**
	 *
	 * Apply PMC_DOM::inject_paragraph_content if content meet criteria
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_the_content_7( $content ) {
		global $post;
		if ( ! is_single( $post->ID ) || ! in_array( get_post_type( $post->ID ), $this->_post_type, true ) ) {
			return $content;
		}

		$paragraphs = array();
		foreach ( array_keys( $this->_paragraphs ) as $pos ) {
			$paragraphs[ $pos ][] = "\n\n<!--//pmc-insert-{$pos}//-->";
		}

		$args = array(
				'paragraphs' => $paragraphs,
				'append'     => false,
				'autop'      => true,
			);
		$args = apply_filters( 'pmc_inject_content_options', $args );
		$content = PMC_DOM::inject_paragraph_content( $content, $args );

		return $content;
	}

	/**
	 * Filter to replace paragraph markers with inject contents
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_the_content_11( $content ) {
		global $post;
		if ( ! is_single( $post->ID ) || ! in_array( get_post_type( $post->ID ), $this->_post_type, true ) ) {
			return $content;
		}

		if ( empty( $this->_paragraphs ) || false === mb_strpos( $content, '<!--//pmc-insert-' ) ) {
			return $content;
		}

		$wrap_in_paragraph = apply_filters( 'pmc_inject_wrap_paragraph', false );

		foreach ( $this->_paragraphs as $pos => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( '', $value );
			}

			if ( true === $wrap_in_paragraph ) {
				$content = str_replace( sprintf( '<p><!--//pmc-insert-%s//--></p>', $pos ), $value, $content );
			} else {
				$content = str_replace( sprintf( '<!--//pmc-insert-%s//-->', $pos ), $value, $content );
			}
		}

		return $content;
	}

}

// EOF
