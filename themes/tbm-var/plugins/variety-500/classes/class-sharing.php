<?php
/**
 * Sharing
 *
 * Handles Social Sharing from the pmc-social-bar plugin.
 *
 * The parent Class is found in pmc-plugins/pmc-social-bar/classes/frontend.php.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Social_Share_Bar;

/**
 * Class Sharing
 *
 * Handler for Sharing icon display.
 *
 * @since 1.0
 * @see Social_Share_Bar\Frontend
 */
class Sharing extends Social_Share_Bar\Frontend {

	/**
	 * Get Icons
	 *
	 * Fetches the social icons arrays from the settings.
	 *
	 * @since 1.0
	 * @return array An array of social sharing icons.
	 */
	public function get_icons() {
		global $post;
		return $this->get_icons_from_cache( Social_Share_Bar\Admin::PRIMARY, $post->post_type );
	}

	/**
	 * Assemble Link
	 *
	 * Creates an "a" tag using information from a Social Icon object.
	 *
	 * @since 1.0
	 * @param object $icon A Social Icon object.
	 * @param string $id   A social Icon ID string.
	 *
	 * @return string A social icon anchor element.
	 */
	public function assemble_link( $icon, $id ) {
		// Ensure we have the required data.
		if ( empty( $icon ) ||
		     ! is_object( $icon ) ||
		     ! is_string( $id ) ||
		     empty( $icon->url ) ||
		     empty( $icon->class ) ||
		     empty( $icon->name ) ) {
			return '';
		}

		$link = sprintf( '<a data-href="%1$s" href="%1$s" ', esc_url( $icon->url ) );

		// Not using esc_url() as this string is used for JS.
		if ( $icon->is_javascript() ) {
			$link = sprintf( '<a href="%1$s" ',  esc_attr( $icon->url ) );
		}

		$link .= sprintf( 'class="%1$s c-social-icon c-social-icon--pin c-social-icon__%2$s" ', esc_attr( $icon->class ) , sanitize_title( $icon->name ) );

		if ( $icon->is_popup() ) {
			$link .= 'target="_blank" ';
		}

		if ( \PMC\Social_Share_Bar\Config::WA === $id ) {
			$link .= 'data-action="share/whatsapp/share" ';
		}

		if ( ! empty( $icon->title ) ) {
			$link .= sprintf( 'title="%1$s" ', esc_attr( $icon->title ) );
		}

		$link .= '>';

		// Leaving this in expanded PHP form for readability.
		$link .= sprintf( '<span class="screen-reader-text">%1$s</span>', esc_html( $icon->name ) );
		$link .= '</a>';
		return $link;
	}
}

