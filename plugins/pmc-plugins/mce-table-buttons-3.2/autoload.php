<?php
/**
 * Class and Trait autoloader for this plugin
 *
 */

/*
 * Register resource autoloader
 */
spl_autoload_register( 'mce_table_buttons_autoloader' );

/**
 * The function that makes on-demand autoloading of files for this plugin
 * possible. It is registered with spl_autoload_register() and must not be
 * called directly.
 *
 * @param string $resource Fully qualified name of the resource that is to be loaded
 * @return void
 */
function mce_table_buttons_autoloader( $resource = '' ) {
	$namespace_root = 'MCE_Table_Buttons';

	$resource = trim( $resource, '\\' );

	if ( empty( $resource ) || strpos( $resource, '\\' ) === false || strpos( $resource, $namespace_root ) !== 0 ) {
		//not our namespace, bail out
		return;
	}

	$path = str_replace(
		'_',
		'-',
		implode(
			'/',
			array_slice(    //remove the namespace root and grab the actual resource
				explode( '\\', $resource ),
				1
			)
		)
	);

	$path = 'class-' . strtolower( $path );
	$path = sprintf( '%s/classes/%s.php', untrailingslashit( MCE_TABLE_BUTTONS_ROOT ), $path );

	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

//EOF
