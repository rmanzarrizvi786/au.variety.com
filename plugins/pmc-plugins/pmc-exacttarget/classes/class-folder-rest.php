<?php
/**
 * This class is responsible to process all REST API requests for Folders ( Categories ) in Content Builder.
 *
 * Currently the plugin requires to retrieve all the folders from ET hence this class doesn't have much code.
 * The class is defined to override the $path property of parent.
 * To retrieve the list of folder, call create an object of this class and call 'get()' method.
 *
 */

namespace PMC\Exacttarget;

use FuelSdk\ET_Client;
use \PMC\Exacttarget\Rest_Error;
use \PMC\Exacttarget\Rest_Support;

class Folders_Rest extends Rest_Support {

	/**
	 * API path for the resource.
	 * Overriding parent property to use appropriate path for Folders.
	 *
	 * @var string
	 */
	protected $path = 'asset/v1/content/categories/';

}
