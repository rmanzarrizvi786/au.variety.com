<?php
/**
 * Commands for interacting with Jetpack.
 *
 * VIP blocks Jetpack's CLI, necessitating custom commands.
 *
 * @package pmc-wp-cli
 */

namespace PMC\WP_CLI\Commands;

use Jetpack;
use PMC_WP_CLI;
use WP_CLI;
use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\get_flag_value;

/**
 * Get details about Jetpack.
 */
class Jetpack_CLI extends PMC_WP_CLI {
	public const COMMAND_NAME = 'pmc-jetpack';

	/**
	 * List active Jetpack modules.
	 *
	 * ## OPTIONS
	 *
	 * [--format]
	 * : Output format, as supported by WP-CLI's `format_items()` utility.
	 *
	 * ---
	 * default: table
	 * options:
	 *  - table
	 *  - json
	 *  - csv
	 *  - yaml
	 *  - count
	 *
	 * ## EXAMPLES
	 *     wp pmc-jetpack list-active
	 *     wp pmc-jetpack list-active --format=json
	 *
	 * @subcommand list-active
	 *
	 * @codeCoverageIgnore Neither `format_items()` nor `get_flag_value()` are
	 *                     available in our unit-test mocks. Additionally,
	 *                     underlying methods are covered, which takes care of
	 *                     the command's important features. To test this
	 *                     method, we would need to include WP-CLI code in our
	 *                     unit-test plugin.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function list_active( array $args, array $assoc_args ): void {
		if ( ! class_exists( Jetpack::class, false ) ) {
			WP_CLI::line(
				__(
					'Jetpack is not available.',
					'pmc-wp-cli'
				)
			);
			return;
		}

		$format = get_flag_value( $assoc_args, 'format', 'table' );

		if ( 'ids' === $format ) {
			WP_CLI::error(
				__(
					'`ids` is not a supported format.',
					'pmc-wp-cli'
				)
			);
		}

		format_items(
			$format,
			$this->_get_active_modules(),
			[
				'Name',
				'Slug',
				'Description',
			]
		);
	}

	/**
	 * Retrieve list of active Jetpack modules.
	 *
	 * @return array
	 */
	protected function _get_active_modules(): array {
		$active_modules = Jetpack::get_active_modules();
		$module_data    = [];

		if ( empty( $active_modules ) || ! is_array( $active_modules ) ) {
			return $module_data;
		}

		foreach ( $active_modules as $module_slug ) {
			$data = Jetpack::get_module( $module_slug );

			$module_data[] = [
				'Name'        => $data['name'],
				'Slug'        => $module_slug,
				'Description' => $data['description'],
			];
		}

		return $module_data;
	}
}
