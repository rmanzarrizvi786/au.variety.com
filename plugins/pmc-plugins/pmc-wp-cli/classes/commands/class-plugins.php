<?php
/**
 * Commands for managing PMC's plugins.
 *
 * @package pmc-wp-cli
 */

namespace PMC\WP_CLI\Commands;

use PMC_WP_CLI;
use WP_CLI;
use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\get_flag_value;

/**
 * Get details about the pmc-plugins used on this site.
 */
class Plugins extends PMC_WP_CLI {
	public const COMMAND_NAME = 'pmc-plugins';

	/**
	 * List plugins loaded via `wpcom_vip_load_plugin()` or `pmc_load_plugin()`.
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
	 *     wp pmc-plugins list
	 *     wp pmc-plugins list --format=json
	 *
	 * @subcommand list
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
	public function list( array $args, array $assoc_args ): void {
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
			$this->_get_data(
				$this->_get_loaded()
			),
			[
				'Name',
				'Path',
				'Description',
			]
		);
	}

	/**
	 * Get list of loaded plugins, omitting those in the `plugins` directory as
	 * they are available through `wp plugins list`.
	 *
	 * @return array
	 */
	protected function _get_loaded(): array {
		$loaded = array_unique( (array) wpcom_vip_get_loaded_plugins() );

		$loaded = array_filter(
			$loaded,
			static function ( string $path ): bool {
				return 0 !== strpos( $path, 'plugins/' );
			}
		);

		return $loaded;
	}

	/**
	 * Get plugin data from each loaded plugin's header.
	 *
	 * @param array $paths
	 * @return array
	 */
	protected function _get_data( array $paths ): array {
		$data = [];

		foreach ( $paths as $path ) {
			$datum = get_plugin_data(
				WP_PLUGIN_DIR . '/' . $path,
				false,
				false
			);

			if ( empty( $datum['Name'] ) ) {
				$datum['Name'] = $path;
			}

			$datum['Path'] = $path;

			$data[ $path ] = $datum;
		}

		ksort( $data );

		return $data;
	}
}
