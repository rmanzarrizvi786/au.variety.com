<?php
/**
 * Base class for PMC Post Options plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Post_Options;

use PMC\Global_Functions\Traits\Singleton;

abstract class Base {

	use Singleton;

	const NAME        = '_post-options';
	const PLUGIN_ID   = 'post-options';
	const PLUGIN_NAME = 'PMC Post Options';

	/**
	 * @var string Contains the capability a user must have to change settings
	 */
	protected $_capability = 'manage_options';


	abstract protected function _setup_hooks();

}	//end class


//EOF
