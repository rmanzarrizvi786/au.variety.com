<?php
/**
 * Utilities for working with post-author data regardless of active plugins.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\Utility;

use PMC;

/**
 * Class Author.
 */
class Author {
	/**
	 * Post ID to retrieve author data from.
	 *
	 * @var int
	 */
	protected int $_post_id;

	/**
	 * Source of author data, such as `pmc-guest-authors` or
	 * `pmc-global-functions`.
	 *
	 * @var string
	 */
	protected string $_source;

	/**
	 * Author data as retrieved from its source.
	 *
	 * @var array
	 */
	protected array $_raw_data;

	/**
	 * Author constructor.
	 *
	 * @param int $id Post ID.
	 */
	public function __construct( int $id ) {
		$this->_post_id = $id;

		$this->_get_raw_data();
	}

	/**
	 * Retrieve raw author data for the post.
	 */
	protected function _get_raw_data(): void {
		if ( function_exists( 'pmc_get_post_authors_data' ) ) {
			$authors       = pmc_get_post_authors_data( $this->_post_id );
			$this->_source = 'pmc-guest-authors';
		} else {
			// Cannot unload `pmc-guest-authors` plugin.
			// @codeCoverageIgnoreStart
			$authors       = PMC::get_post_authors( $this->_post_id );
			$this->_source = 'pmc-global-functions';
			// @codeCoverageIgnoreEnd
		}

		if ( is_array( $authors ) ) {
			$this->_raw_data = $authors;
		}
	}

	/**
	 * Retrieve formatted list of authors linked to their archive pages.
	 *
	 * @return string|null
	 */
	public function get_formatted(): ?string {
		if ( empty( $this->_raw_data ) ) {
			return null;
		}

		$data = $this->_format_raw_data_for_formatted_output( $this->_raw_data );

		switch ( count( $data ) ) {
			case 1:
				$formatted = $data[0];
				break;

			case 2:
				$formatted = sprintf(
					/* translators: 1. First author, 2. Second author. */
					__( '%1$s and %2$s', 'pmc-global-functions' ),
					$data[0],
					$data[1]
				);
				break;

			default:
				$last   = array_pop( $data );
				$others = implode(
					_x(
						', ',
						'Author list separator',
						'pmc-global-functions'
					),
					$data
				);

				$formatted = sprintf(
					/* translators: 1. Comma-separated author list, 2. Last author. */
					_x(
						'%1$s, and %2$s',
						'Author list final author',
						'pmc-global-functions'
					),
					$others,
					$last
				);
				break;
		}

		return $formatted;
	}

	/**
	 * Format raw author data for formatted output.
	 *
	 * @param array $data Raw author data.
	 * @return array
	 */
	protected function _format_raw_data_for_formatted_output( array $data ): array {
		switch ( $this->_source ) {
			case 'pmc-guest-authors':
				foreach ( $data as &$author ) {
					$author = sprintf(
						'<a href="%1$s">%2$s</a>',
						$author['url'],
						$author['display_name']
					);
				}
				unset( $author );
				break;

			case 'pmc-global-functions':
				foreach ( $data as &$author ) {
					$author = sprintf(
						'<a href="%1$s">%2$s</a>',
						get_author_posts_url(
							(int) $author['ID'],
							$author['user_nicename']
						),
						$author['display_name']
					);
				}
				unset( $author );
				break;

			default:
				$data = [];
				break;
		}

		return $data;
	}
}
