<?php

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;

class JW_Player extends Block_Base implements With_Render_Callback {

	public function __construct() {
		$this->_block          = 'jw-player';
		$this->_has_stylesheet = true;

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'load-post.php', [ $this, 'remove_legacy_admin_items' ] );
		add_action( 'load-post-new.php', [ $this, 'remove_legacy_admin_items' ] );
	}

	/**
	 * Suppress JW Player's legacy metabox when post type supports our block.
	 */
	public function remove_legacy_admin_items(): void {
		global $pagenow, $typenow;

		if (
			empty( $typenow )
			|| ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true )
			|| ! use_block_editor_for_post_type( $typenow )
		) {
			return;
		}

		$hooks = [
			[
				'admin_enqueue_scripts',
				'jwplayer_admin_enqueue_scripts',
			],
			[
				'admin_head-post.php',
				'jwplayer_admin_head',
			],
			[
				'admin_head-post-new.php',
				'jwplayer_admin_head',
			],
		];

		foreach ( $hooks as $hook ) {
			[ $tag, $function ] = $hook;

			remove_action( $tag, $function );
		}

		/**
		 * `admin_menu` is fired too early to check context, so we can't remove
		 * `jwplayer_media_add_video_box` from that action.
		 *
		 * For the same reason, we can't use `pre_option_jwplayer_show_widget`.
		 */
		remove_meta_box( 'jwplayer-video-box', $typenow, 'side' );
	}

	/**
	 * Render block output as shortcode to leverage existing rendering logic.
	 *
	 * Declaration must be compatible with interface.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param array    $attrs   Block attributes.
	 * @param string   $content Block content.
	 * @param WP_Block $block   Block object.
	 * @return string
	 */
	public function render_callback(
		array $attrs,
		string $content,
		WP_Block $block
	): string {
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		if (
			! isset( $attrs['videoId'] )
			|| ! $this->_id_is_valid( $attrs['videoId'] )
		) {
			return '';
		}

		$shortcode = $attrs['videoId'];

		if (
			isset( $attrs['playerId'] )
			&& $this->_id_is_valid( $attrs['playerId'] )
		) {
			$shortcode .= '-' . $attrs['playerId'];
		}

		return sprintf(
			'[jwplayer %1$s]',
			$shortcode
		);
	}

	/**
	 * Validate a video, playlist, or player ID using same regex pattern applied
	 * by JW Player's shortcode.
	 *
	 * @param string $id ID to check.
	 * @return bool
	 */
	protected function _id_is_valid( string $id ): bool {
		return 1 === preg_match(
			'#[a-z0-9]{8}#i',
			preg_quote(
				$id,
				'#'
			)
		);
	}
}
