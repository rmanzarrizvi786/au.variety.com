<?php
/**
 * Base class for PMC Genre plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;

use \PMC\Global_Functions\Traits\Singleton;


abstract class Base {

	use Singleton;

	const NAME        = 'genre';
	const PLUGIN_ID   = 'pmc-genre';
	const PLUGIN_NAME = 'PMC Genre Taxonomy';

	/**
	 * @var string Contains the capability a user must have to change settings
	 */
	protected $_capability = 'manage_options';


	abstract protected function _setup_hooks();

}	//end class


//EOF
