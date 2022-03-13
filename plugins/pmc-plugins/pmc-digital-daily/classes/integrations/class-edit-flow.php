<?php
/**
 * Leverage Edit Flow to support articles published only for use in a Digital
 * Daily issue.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily\Integrations;

use PMC\Global_Functions\Traits\Singleton;
use WP_Post;
use WP_Term;

/**
 * Class Edit_Flow;
 */
class Edit_Flow {
	use Singleton;

	/**
	 * Slug of custom status indicating post is published for use in a Digital
	 * Daily issue.
	 */
	public const EF_STATUS_SLUG = 'dd-published';

	/**
	 * Edit Flow custom-status module name.
	 */
	protected const EF_MODULE_NAME = 'custom_status';

	/**
	 * Arguments needed to add the custom status.
	 *
	 * @var array
	 */
	protected array $_ef_status_args;

	/**
	 * Edit_Flow_Integration constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'admin_init', [ $this, 'prepare_integration' ] );
		add_filter(
			'pmc_gutenberg_story_block_engine_can_render_post',
			[ $this, 'allow_rendering_of_posts_with_custom_status' ],
			10,
			3
		);
		add_filter(
			'pmc_digital_daily_table_of_contents_can_include_item',
			[ $this, 'include_posts_with_custom_status_in_toc' ],
			10,
			2
		);
		add_filter(
			'pmc_digital_daily_full_view_post_status_should_use_pretty_permalink',
			[ $this, 'allow_pretty_permalink_for_custom_status' ],
			10,
			2
		);
	}

	/**
	 * Set up integration when conditions allow it.
	 */
	public function prepare_integration(): void {
		if ( wp_doing_ajax() ) {
			return;
		}

		// Cannot unload function.
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'EditFlow' ) ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		$module_data = EditFlow()->get_module_by(
			'name',
			static::EF_MODULE_NAME
		);

		if (
			false === $module_data
			|| (
				isset( $module_data->options->enabled )
				&& 'on' !== $module_data->options->enabled
			)
		) {
			return;
		}

		$this->_ef_status_args = [
			'slug'        => static::EF_STATUS_SLUG,
			'name'        => 'DD Published',
			'description' => __(
				'Post is published for use in a Digital Daily issue.',
				'pmc-digital-daily'
			),
		];

		$this->_register_status();
	}

	/**
	 * Conditionally register custom post status.
	 */
	protected function _register_status(): void {
		$module = EditFlow()->{static::EF_MODULE_NAME};

		$status_term = get_term_by(
			'name',
			$this->_ef_status_args['name'],
			$module::taxonomy_key
		);

		if ( $status_term instanceof WP_Term ) {
			return;
		}

		$module->add_custom_status(
			$this->_ef_status_args['name'],
			$this->_ef_status_args
		);
	}

	/**
	 * Allow posts with our custom status to appear in a Digital Daily issue.
	 *
	 * @param bool   $can_render   Whether or not post status is allowed.
	 * @param int    $post_id      ID of post being rendered by Story Block
	 *                             Engine.
	 * @param string $block_suffix Name of block being rendered by Story Block
	 *                             Engine.
	 * @return bool
	 */
	public function allow_rendering_of_posts_with_custom_status(
		bool $can_render,
		int $post_id,
		string $block_suffix
	): bool {
		if ( 'story-digital-daily' !== $block_suffix ) {
			return $can_render;
		}

		if (
			! $can_render
			&& static::EF_STATUS_SLUG === get_post_status( $post_id )
		) {
			return true;
		}

		return $can_render;
	}

	/**
	 * Allow posts having our custom status to appear in the Table of Contents.
	 *
	 * @param bool  $can_include Whether or not post shows in Table of Contents.
	 * @param array $post_data   Data parsed for building Table of Contents.
	 * @return bool
	 */
	public function include_posts_with_custom_status_in_toc(
		bool $can_include,
		array $post_data
	): bool {
		if (
			! $can_include
			&& static::EF_STATUS_SLUG === get_post_status( $post_data['ID'] )
		) {
			return true;
		}

		return $can_include;
	}

	/**
	 * Allow posts with our custom status to use pretty permalinks when rendered
	 * in a Digital Daily. As these posts don't have the `publish` status, they
	 * would otherwise use query-string permalinks.
	 *
	 * @param bool        $allowed    Whether or not post status is supported.
	 * @param WP_Post|int $post_or_id Post ID or object.
	 * @return bool
	 */
	public function allow_pretty_permalink_for_custom_status(
		bool $allowed,
		$post_or_id
	): bool {
		if (
			! $allowed
			&& static::EF_STATUS_SLUG === get_post_status( $post_or_id )
		) {
			return true;
		}

		return $allowed;
	}
}
