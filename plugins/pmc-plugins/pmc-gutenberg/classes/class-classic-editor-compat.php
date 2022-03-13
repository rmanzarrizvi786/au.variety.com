<?php
/**
 * Transitional support for the Classic Editor.
 *
 * While supporting both editors, it is sometimes necessary to disable a Core
 * Tech feature in Gutenberg while retaining it in the Classic Editor. Meta
 * boxes are a notable example, because Gutenberg saves their input last,
 * allowing a metabox input to override a Gutenberg one.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Classic_Editor_Compat.
 */
class Classic_Editor_Compat {
	use Singleton;

	/**
	 * Classic_Editor_Compat constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Early priority so these modifications are present at default priority.
		add_action( 'load-post.php', [ $this, 'hide_exact_target_metabox' ], 0 );
		add_action( 'load-post-new.php', [ $this, 'hide_exact_target_metabox' ], 0 );

		// Late priority to ensure precedence.
		add_filter( 'pmc_field_override_post_types', [ $this, 'hide_field_override_metabox' ], PHP_INT_MAX );

		// Hooks to disable functionality in some plugins for post types that are block editor-enabled.
		add_filter( 'pmc_review_block_editor_skip', [ $this, 'skip_if_block_editor' ] );

	}

	/**
	 * Hide pmc-exact-target metaboxes on Gutenberg-enabled post types,
	 * otherwise their input supersedes that of the Gutenberg sidebar.
	 */
	public function hide_exact_target_metabox(): void {
		global $typenow;

		// Determine if post-type is compatible with block editor.
		if ( false === use_block_editor_for_post_type( $typenow ) ) {
			return;
		}

		remove_action( 'admin_print_scripts-post.php', 'sailthru_add_admin_scripts_onpost' );
		remove_action( 'admin_print_scripts-post-new.php', 'sailthru_add_admin_scripts_onpost' );
		remove_action( 'add_meta_boxes', 'sailthru_post_options' );
		remove_action( 'add_meta_boxes', 'sailthru_newsletter_featured_post_module', 1 );
	}

	/**
	 * Hide the pmc-field-overrides metaboxes on Gutenberg-enabled post types,
	 * otherwise their input supersedes that of the Gutenberg sidebar.
	 *
	 * @param array $post_types Post types supporting field overrides.
	 * @return array
	 */
	public function hide_field_override_metabox( array $post_types ): array {
		return array_filter(
			$post_types,
			static function ( $type ): bool {
				return ! use_block_editor_for_post_type( $type );
			}
		);
	}

	/**
	 * Helper function to be used with {plugin}_block_editor_skip filters to
	 * skip functionality when the block editor is active. This is
	 * useful for plugins that do not yet support the block editor.
	 *
	 * @return bool Whether or not to skip the feature based
	 *              on block editor.
	 */
	public function skip_if_block_editor(): bool {
		global $typenow;

		return use_block_editor_for_post_type( $typenow );
	}

}
