<?php
/**
 * Compatibility with old namespace.
 *
 * @deprecated
 */

class_alias( 'PMC\Store_Products\Fields', 'PMC\Plugins\PMC_Store_Products\Fields' );
class_alias( 'PMC\Store_Products\Product', 'PMC\Plugins\PMC_Store_Products\Product' );
class_alias( 'PMC\Store_Products\Shortcode', 'PMC\Plugins\PMC_Store_Products\Shortcode' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class_alias( 'PMC\Store_Products\CLI', 'PMC\Plugins\PMC_Store_Products\CLI' );
}
