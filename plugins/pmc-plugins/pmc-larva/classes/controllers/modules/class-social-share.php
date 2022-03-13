<?php
/**
 * Larva Social Share module controller.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use PMC\Social_Share_Bar;

/**
 * Social_Share class.
 */
class Social_Share extends Base {
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $pattern_shortpath = 'modules/social-share';

	/**
	 * The default options structure for the module. This structure
	 * serves as a kind of "contract" for any data that is sent to
	 * the Larva module specified for the class. This "contract" is
	 * enforced before passing rendering the template with data.
	 *
	 * @return array Object to ultimately be passed to the pattern.
	 */
	public function get_default_options(): array {
		return [
			'data'    => [
				'post_id' => 0,
			],
			'variant' => 'prototype',
		];
	}

	/**
	 * Manually map provided data to the pattern JSON object.
	 *
	 * @param array $pattern The Larva pattern JSON object to plugin data into.
	 * @param array $data Actual data to override placeholder data.
	 *
	 * @return array Object to ultimately be passed to render_template.
	 */
	public function populate_pattern_data( array $pattern, array $data ): array {
		// Cannot unload class.
		// @codeCoverageIgnoreStart
		if ( ! class_exists( Social_Share_Bar\Frontend::class, false ) ) {
			return $this->_populate_for_empty_sharing_config( $pattern );
		}
		// @codeCoverageIgnoreEnd

		$post            = get_post( $data['post_id'] );
		$primary_icons   = Social_Share_Bar\Frontend::get_instance()
			->get_icons_from_cache(
				Social_Share_Bar\Admin::PRIMARY,
				$post->post_type,
				$post->ID
			);
		$secondary_icons = Social_Share_Bar\Frontend::get_instance()
			->get_icons_from_cache(
				Social_Share_Bar\Admin::SECONDARY,
				$post->post_type,
				$post->ID
			);

		$prototype                         =
			$pattern['social_share_primary'][0];
		$pattern['social_share_primary']   = [];
		$pattern['social_share_secondary'] = [];

		if ( is_array( $primary_icons ) && ! empty( $primary_icons ) ) {
			foreach ( $primary_icons as $icon ) {
				$pattern['social_share_primary'][] = $this->_populate_icon(
					$prototype,
					$icon
				);
			}
		}

		if ( is_array( $secondary_icons ) && ! empty( $secondary_icons ) ) {
			foreach ( $secondary_icons as $icon ) {
				$pattern['social_share_secondary'][] = $this->_populate_icon(
					$prototype,
					$icon
				);
			}
		}

		if (
			empty( $pattern['social_share_primary'] )
			&& empty( $pattern['social_share_secondary'] )
		) {
			return $this->_populate_for_empty_sharing_config( $pattern );
		}

		return $pattern;
	}

	/**
	 * Set pattern data to hide sharing module if no services are configured.
	 *
	 * @param array $pattern Larva pattern data.
	 * @return array
	 */
	protected function _populate_for_empty_sharing_config(
		array $pattern
	): array {
		$pattern['social_share_classes']  .= ' lrv-a-hidden';
		$pattern['social_share_primary']   = [];
		$pattern['social_share_secondary'] = [];

		return $pattern;
	}

	/**
	 * Apply icon's configuration to the `c-icon` prototype for the button.
	 *
	 * @param array                  $prototype `c-icon` prototype.
	 * @param Social_Share_Bar\Icon  $icon      Icon configuration.
	 * @return array
	 */
	protected function _populate_icon(
		array $prototype,
		Social_Share_Bar\Icon $icon
	): array {
		$prototype['c_icon_name']        = $icon->get_icon_id();
		$prototype['c_icon_rel_name']    = $prototype['c_icon_name'];
		$prototype['c_icon_url']         = $icon->get_properties( 'url' );
		$prototype['c_icon_target_attr'] = '_blank';

		$prototype['c_icon_link_screen_reader_text'] = sprintf(
			/* translators: 1. Share icon name. */
			__(
				'Share this article on %1$s',
				'pmc-larva'
			),
			ucfirst(
				$icon->get_properties(
					'name'
				)
			)
		);

		// Add JS trigger to print icon classes instead of URL.
		if ( 'print' === $icon->get_icon_id() ) {
			$prototype['c_icon_link_screen_reader_text'] = 'Print this article';
			$prototype['c_icon_classes']                .=
				' js-PrintTrigger';
		}

		return $prototype;
	}
}
