<?php
/**
 * A pseudo Command Bus where all different WP CLI commands are registered.
 * This can be converted into a proper command bus once a DI container is added for us in our code.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-07-12
 */

namespace PMC\WP_CLI;

use \PMC\Global_Functions\Traits\Singleton;
use \WP_CLI;
use PMC\WP_CLI\Meta;
use PMC\WP_CLI\Shutter_Stock;
use PMC\WP_CLI\Associated_Press;
use \PMC\WP_CLI\PMC_Export_Comments;

/**
 * Ignoring this class for code coverage as this is only a manifest which registers
 * individual command classes with WP_CLI. Each class registered here has its own
 * unit tests.
 *
 * @codeCoverageIgnore
 */
class Command_Bus {

	use Singleton;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_register();
	}

	/**
	 * Method which registers commands with wp-cli
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _register() : void {

		// General terms related CLI commands
		WP_CLI::add_command( 'pmc-terms', \PMC\WP_CLI\Terms::class );
		WP_CLI::add_command( 'pmc-redirect-manager', \PMC\WP_CLI\Redirect_Manager::class );
		WP_CLI::add_command( 'pmc-purge-article', \PMC\WP_CLI\Purge_Article::class );

		$classes = [
			Meta::class,
			Shutter_Stock::class,
			OEmbed_Cache::class,
			Commands\Jetpack_CLI::class,
			Commands\Manage_Attachments::class,
			Commands\Plugins::class,
			Commands\Post::class,
			Commands\Search_Replace::class,
			Associated_Press::class,
			PMC_Export_Comments::class,
		];

		foreach( $classes as $class ) {
			WP_CLI::add_command( $class::COMMAND_NAME, $class );
		}

	}

}    //end class

//EOF
