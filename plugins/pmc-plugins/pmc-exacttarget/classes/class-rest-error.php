<?php
/**
 * This class is responsible to process all REST GET API requests in Content Builder.
 */

namespace PMC\Exacttarget;

/**
 * Error class, used for representing rest errors, this is to keep the responses from functions similar.
 */
class Rest_Error {

	/**
	 * @var bool The status of the request, true if successful; false otherwise.
	 */
	public $status;

	/**
	 * @var string Error message.
	 */
	public $message;

	/**
	 * @var int HTTP status code.
	 */
	public $code;

	function __construct( $message, $status = false, $code = 0 ) {
		$this->status  = $status;
		$this->message = $message;
		$this->code    = $code;
	}
}
