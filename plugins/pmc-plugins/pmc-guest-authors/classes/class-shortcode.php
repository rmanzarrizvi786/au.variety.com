<?php
namespace PMC\Guest_Authors;

use \PMC\Global_Functions\Traits\Singleton;

class Shortcode {

	use Singleton;

	/**
	 * Shortcode constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		add_shortcode( 'pmc_guest_authors', [ $this, 'guest_authors' ] );

	}

	/**
	 * Render author info/html based on id/slug.
	 * Initial requirement was from PPT-3150
	 * usage [pmc_guest_authors author="id/slug" nolinks="true"]
	 *
	 * @param array $atts Shortcode attribute like author="id/slug" nolinks="true".
	 *
	 * @return string Author blurb content and details.
	 *
	 * @throws \Exception Throw exception if template not found.
	 */
	public function guest_authors( array $atts = [] ) : string {

		$retval       = '';
		$default_atts = [
			'author'  => '',
			'nolinks' => false,
		];

		if ( ! empty( $atts['nolinks'] ) ) {
			$atts['nolinks'] = filter_var( $atts['nolinks'], FILTER_VALIDATE_BOOLEAN );
		}

		$atts = shortcode_atts( $default_atts, $atts );

		if ( empty( $atts['author'] ) ) {
			return $retval;
		}

		// Check if author id is provide or slug.
		if ( is_numeric( $atts['author'] ) ) {
			$guest_author = \PMC_Guest_Authors::get_instance()->get_author_data_by( 'id', intval( $atts['author'] ) );
		} else {
			$guest_author = \PMC_Guest_Authors::get_instance()->get_author_data_by( 'user_login', sanitize_text_field( $atts['author'] ) );
		}

		if ( empty( $guest_author ) ) {
			return $retval;
		}

		$author_name = ( ! empty( $guest_author['display_name'] ) ) ? $guest_author['display_name'] : $guest_author['first_name'] . ' ' . $guest_author['last_name'];

		$params = [
			'guest_author' => $guest_author,
			'author_name'  => $author_name,
			'nolinks'      => ( 'true' === strtolower( $atts['nolinks'] ) || true === $atts['nolinks'] ),
		];

		// No default template until Larva in pmc-plugins is sorted out. Theme supplies template.
		$template_path = apply_filters( 'pmc_guest_authors_template', '' );

		if ( ! empty( $template_path ) && \PMC::is_file_path_valid( $template_path ) ) {
			$retval = \PMC::render_template(
				$template_path,
				$params,
				false
			);

		}

		return $retval;

	}

}

// EOF
