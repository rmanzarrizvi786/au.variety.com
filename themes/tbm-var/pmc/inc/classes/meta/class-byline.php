<?php
/**
 * Byline
 *
 * @package pmc-core-v2
 * @since   2017.1.0
 * @see https://confluence.pmcdev.io/display/HIG/Article+Page#ArticlePage-Byline
 */

namespace PMC\Core\Inc\Meta;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Byline
 *
 * @since 2017.1.0
 * @see   \pmc\global_functions\traits\singleton
 *
 * Any method in this class can be accessed directly by calling the method and
 * passing either a post_id or an authors array.
 *
 * @example
 * <pre>
 *   PMC\Core\Inc\Meta::get_instance()->get_the_byline( {$post_id} );
 *   PMC\Core\Inc\Meta::get_instance()->format_byline( {$authors} );
 * </pre>
 */
class Byline {

	use Singleton;

	const FILTER_BYLINE    = 'pmc_core_byline_filter';
	const FILTER_COAUTHORS = 'pmc_core_coauthors_filter';

	/**
	* get_authors
	*
	* @since 2017-10-18
	*
	* @author brandoncamenisch
	* @version 2017-10-18 - PMCVIP-2905:
	* - Gets the authors from co-authors using the post_id.
	* @version 2017-10-24 - PMCVIP-2905:
	* - Removing object references and adding override instead.
	*
	* @param int $post_id, bool $override
	* @return array $authors, array $authors_or
	* @version 2017-10-18 - PMCVIP-2905:
	**/
	public function get_authors( $post_id = null, $override = false ) {
		$authors = [];

		if ( function_exists( 'get_coauthors' ) ) {
			$authors = get_coauthors( $post_id );
		}

		if ( empty( $authors ) ) {
			return;
		}

		if ( true === $override ) {
			$authors_or = [];
			$authors_or['escaped_html'] = coauthors_posts_links( null, null, null, null, false );
			$authors_or['authors'] = $authors;

			$size = \PMC::is_mobile() ? 50 : 64;
			if ( ! empty( $authors[0] ) ) {
				$authors_or['gravatar'] = coauthors_get_avatar( $authors[0], $size, 'blank', false );
			}
			return $authors_or;
		}

		return apply_filters( self::FILTER_COAUTHORS, $authors );
	}

	/**
	* get_the_byline
	*
	* @since 2017-10-18 - Outputs the author byline(s) from co-authors plugin.
	* @uses coauthors_get_avatar(), wpcom_vip_get_page_by_title(), get_the_date()
	* PMC::render_template, coauthors_posts_links(), apply_filters()
	*
	* @author brandoncamenisch
	* @version 2017-10-18 - PMCVIP-2905:
	* - Builds the author byline as per the spec described in the confluence doc.
	* @version 2017-10-24 - PMCVIP-2905:
	* - Remove closures.
	* - Ading template parts for different sections of HTML.
	*
	* @param int $post_id
	* @return string html of author byline(s) or empty string
	**/
	public function get_the_byline( $post_id = null ) {
		$byline  = '';
		$post_id = isset( $post_id ) ? $post_id : get_the_ID();
		$authors = $this->get_authors( $post_id );

		// Make sure we at least have an author
		if ( ! empty( $authors ) && is_numeric( $post_id ) ) {
			// Format and check the byline so we have something to return

			// Build the avatar html
			$byline .= \PMC::render_template( PMC_CORE_PATH . '/template-parts/meta/byline-image.php', [
				'byline_image' => apply_filters( 'pmc_core_byline_image', $this->get_author_image( $authors ) ),
			] );

			// Build the 'By {author}' html
			$byline.= \PMC::render_template( PMC_CORE_PATH . '/template-parts/meta/byline-authors.php', [
				'byline_authors' => $this->format_byline( $authors )
			] );

			// Build the dateline html
			$byline .= apply_filters(
				'pmc_core_byline_date',
				sprintf(
					' on %s',
					get_the_date( 'F j, Y', $post_id )
				)
			);

			// Build the job title html
			if ( ! empty( $authors ) && is_array( $authors ) && 1 >= count( $authors ) && ! empty( $authors[0]->_pmc_title ) ) {
				$byline.= \PMC::render_template( PMC_CORE_PATH . '/template-parts/meta/byline-title.php', [
					'byline_title' => apply_filters( 'pmc_core_byline_title', $authors[0]->_pmc_title ),
				] );
			}

			// Build the twitter html
			if ( ! empty( $authors ) && is_array( $authors ) && 1 >= count( $authors ) && ! empty( $authors[0]->_pmc_user_twitter ) ) {
				$byline .= \PMC::render_template( PMC_CORE_PATH . '/template-parts/meta/byline-twitter.php', [
					'byline_twitter' => apply_filters( 'pmc_core_byline_twitter', $authors[0] ),
				] );
			}

			// Finally return everything
			return apply_filters( self::FILTER_BYLINE, $byline );
		} elseif ( function_exists( 'coauthors_posts_links' ) ) {
			return coauthors_posts_links( null, null, 'By ', null, false );
		} else {
			return '';
		}
	}

	/**
	* get_author_image | inc/classes/meta/class-byline.php
	*
	* @since 2017-10-18 - Gets the author image html from co-authors
	* @uses coauthors_get_avatar()
	*
	* @author brandoncamenisch
	* @version 2017-10-18 - feature/PMCVIP-2905:
	* - Moving from get_the_byline into it's own method.
	*
	* @param object $authors, int $size
	* @return string|html of author avatar
	**/
	public function get_author_image( $authors, $size = 64 ) {
		if ( is_array( $authors ) && ! empty( $authors[0] ) ) {
			$avatar = coauthors_get_avatar( $authors[0], $size, 'blank', false );
			// Check that there is an image and that it's not gravatar
			if ( false !== $avatar
				&& ! empty( $avatar )
				&& false === strpos( $avatar, 'gravatar' )
			) {
				return $avatar;
			}
		}
		return false;
	}

	/**
	* get_the_mini_byline | inc/classes/meta/class-byline.php
	*
	* @since 2017-10-18
	* @uses coauthors_posts_links(), get_the_author()
	* @see dd
	*
	* @author brandoncamenisch
	* @version 2017-10-18 - feature/PMCVIP-2905:
	* - Formats a mini byline without dates, images, social.
	*
	* @param int|string $post_id, bool $links
	* @return string|html
	**/
	public function get_the_mini_byline( $post_id, $links = false ) {
		$authors = $this->get_authors( $post_id );
		// Make sure we at least have an author
		if ( ! empty( $authors ) ) {
			return $this->format_byline( $authors );
		} elseif ( false === $links && function_exists( 'coauthors' ) ) {
			return coauthors( null, null, null, null, false );
		} elseif ( true === $links && function_exists( 'coauthors_posts_links' ) ) {
			return coauthors_posts_links( null, null, null, null, false );
		} else {
			return get_the_author();
		}
	}

	/**
	 * format_byline | inc/classes/meta/class-byline.php
	 *
	 * @since   2017-10-18 - Formats author(s) object into byline with author links.
	 *
	 * @author  brandoncamenisch
	 * @version 2017-10-18 - feature/PMCVIP-2905:
	 *
	 * @param object $authors
	 *
	 * @return string|html
	 */
	public function format_byline( $authors ) {
		if ( ! empty( $authors ) && is_array( $authors ) ) {
			$i = 1;
			$output = '';
			// Creates the links for the author meta
			foreach ( $authors as $k => $v ) {
				if ( 1 > count( $authors ) && $i === count( $authors ) ) {
					$output.= 'and ';
				}

				if ( 'guest-author' === $authors[$k]->type ) {
					$link = '<a href="%s" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">%s</span></a>';
				} else {
					$link = '<a href="%s" itemprop="contributor" itemscope itemtype="http://schema.org/Person"><span itemprop="name">%s</span></a>';
				}
				$output.= sprintf(
					$link,
					esc_url( get_author_posts_url( $authors[$k]->ID, $authors[$k]->user_nicename ) ),
					esc_html( $authors[$k]->display_name )
				);

				// Output the comma seperated authors
				if ( $i !== count( $authors ) ) {
					$output.= ', ';
				}

				$i++;
			}
			return $output;
		} else {
			return '';
		}
	}

}
