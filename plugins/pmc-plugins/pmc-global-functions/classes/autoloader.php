<?php
/**
 * Autoloader for PHP classes inside PMC Plugins
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2015-05-12
 */


namespace PMC\Global_Functions;


class Autoloader {

	public static function load_resource( $resource = '' ) {

		$namespace_root = 'PMC\\';

		$resource = trim( $resource, '\\' );

		if ( empty( $resource ) || strpos( $resource, '\\' ) === false || strpos( $resource, $namespace_root ) !== 0 ) {
			//not our namespace, bail out
			return;
		}

		$path = explode(
					'\\',
					str_replace( '_', '-', $resource )
				);

		$plugin_name = untrailingslashit(
							strtolower(
									implode(
											'-',
											array_slice( $path, 0, 2 )
									)
							)
						);

		$class_path = strtolower(
							implode(
									'/',
									array_slice( $path, 2 )
							)
						);

		$resource_path = sprintf( '%s/%s/classes/%s.php', untrailingslashit( dirname( PMC_GLOBAL_FUNCTIONS_PATH ) ), $plugin_name, $class_path );

		if ( file_exists( $resource_path ) && validate_file( $resource_path ) === 0 ) {
			require_once $resource_path;
		} else {

			$file_prefix = '';

			if ( strpos( $resource_path, 'traits' ) > 0 ) {
				$file_prefix = 'trait';
			} elseif ( strpos( $resource_path, 'interfaces' ) > 0 ) {
				$file_prefix = 'interface';
			} elseif ( strpos( $resource_path, 'classes' ) > 0 ) {  // this has to be the last
				$file_prefix = 'class';
			}

			if ( ! empty( $file_prefix ) ) {

				$resource_parts = explode( '/', $resource_path );

				$resource_parts[ count( $resource_parts ) - 1 ] = sprintf(
					'%s-%s',
					strtolower( $file_prefix ),
					$resource_parts[ count( $resource_parts ) - 1 ]
				);

				$resource_path = implode( '/', $resource_parts );

			}

			if ( file_exists( $resource_path ) && validate_file( $resource_path ) === 0 ) {
				require_once $resource_path;
			}

		}

	}

}


/**
 * Register autoloader
 */
spl_autoload_register( __NAMESPACE__ . '\Autoloader::load_resource' );


//EOF
