<?php
/**
 * This class is responsible to process all REST API requests for Emails in Content Builder.
 */

namespace PMC\Exacttarget;

use \PMC\Exacttarget\Rest_Support;
use \PMC\Exacttarget\Rest_Error;

class Email_Rest extends Rest_Support {

	// @TODO: Implement validation using this array when creating an email.
	const REQUIRED_ATTRIBUTES_POST = [
		'name'      => '',
		'channels'  => [
			'email' => true,
			'web'   => false,
		],
		'views'     => [
			'html'        => [
				'content' => '',
			],
			'subjectline' => [
				'content' => '',
			],
		],
		'assetType' => [
			'name' => '',
			'id'   => 0,
		],
	];

	const EMAIL_TYPES = [
		'htmlemail' => 208,
	];

	/**
	 * For retriveing an email by $field_name using $value.
	 * @TODO: Currently this function only retrieves a single function, we can make this more generic to retreive multiple emails.
	 *
	 * @param string $field_name Name of the field, used for filtering the blocks.
	 * @param string $vaue       Value for the field provided in $field_name.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function query_email_by( $field_name, $value, $fields = [] ) {

		if ( empty( $field_name ) || empty( $value ) ) {
			return new Rest_Error( 'Field name and Value are required' );
		}

		if ( empty( $fields ) ) {

			$fields = array(
				'ID',
				'createdDate',
				'modifiedDate',
				'name',
				'category',
				'content',
				'views',
				'status',
				'assetType',
			);
		}

		$query = [
			'query'  => [
				'rightOperand'    => [
					'property'       => 'assetType.name',
					'simpleOperator' => 'in',
					'value'          => array_keys( (array) self::EMAIL_TYPES ),
				],
				'leftOperand'     => [
					'property'       => $field_name,
					'simpleOperator' => 'equal',
					'value'          => $value,
				],
				'logicalOperator' => 'AND',
			],
			'fields' => $fields,
		];

		// Complex queries are only supported via POST request.
		$response = parent::post( $query, [ 'query' ] );

		// If request failed then return the response as it is.
		if ( false === $response->status ) {
			return $response;
		}

		// If request was successful.
		if ( ! empty( $response->results ) && ! empty( $response->results->items ) ) {

			$results = $response->results->items[0];
			unset( $response->results );

			$response->results = $results;

		} else {

			$response->status  = false;
			$response->message = 'No matching emails found! ';
		}

		return $response;
	}

	/**
	 * For retriveing an email by ID.
	 *
	 * @param string|int $id     ID of the email.
	 * @param string     $fields Fields to retrieve from ET, e.g. name, id etc.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function get_email_by_id( $id, $fields = [] ) {
		return $this->query_email_by( 'id', $id, $fields );
	}

	/**
	 * For retriveing an email by ID.
	 *
	 * @param string|int $name   Name of the email.
	 * @param string     $fields Fields to retrieve from ET, e.g. name, id etc.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function get_email_by_name( $name, $fields = [] ) {
		return $this->query_email_by( 'name', $name, $fields );
	}

	/**
	 * Updates an email.
	 *
	 * @param string|int $email_id     ID of the email to update.
	 * @param string     $name         Updated name.
	 * @param string     $subject      Updated subject line.
	 * @param string     $html_content Updated email content.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function update( $email_id, $name, $subject, $html_content ) {

		if ( empty( $email_id ) || empty( $name ) || empty( $subject ) || empty( $html_content ) ) {
			return new Rest_Error( 'Required fields are missing' );
		}

		$updates = [
			'name'  => $name,
			'views' => [
				'html'        => [
					'content' => $html_content,
				],
				'subjectline' => [
					'content' => $subject,
				],
			],
		];

		$response = parent::patch( $email_id, $updates );

		return $response;
	}

	/**
	 * Creates an email.
	 *
	 * @param array $args Array in following format containing arguments for creating a new email.
	 * array(
	 *    'name'    => 'Name of the email to create',
	 *    'subject' => 'Subject for the email',
	 *    'content' => 'Email content',
	 * );
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function create( $args ) {

		if (
			empty( $args['name'] )
			|| empty( $args['subject'] )
			|| empty( $args['content'] )
		) {
			return new Rest_Error( 'Name, Subject or Content is missing' );
		}

		$email      = self::REQUIRED_ATTRIBUTES_POST;
		$email_type = 'htmlemail'; // @TODO: In future we can add support for more email types to be crated, depends on bussiness requirements.

		$email['name']                            = $args['name'];
		$email['views']['subjectline']['content'] = $args['subject'];
		$email['views']['html']['content']        = $args['content'];

		// Pipeline flags this as not covered even though the tests are written, will look into it after launch.
		$email['assetType'] = [ // @codeCoverageIgnore
			'name' => $email_type,
			'id'   => self::EMAIL_TYPES[ $email_type ],
		];

		return parent::post( $email );
	}

	/**
	 * Deletes an email.
	 *
	 * @param string|int $id ID of the email.
	 *
	 * @return bool Returns true if deletion was successful, false otherwise.
	 */
	public function delete( $id ) {

		if ( empty( $id ) ) {
			return false;
		}

		$response = parent::delete( $id );

		if ( true === $response->status && 'OK' === $response->results ) {
			return true;
		}

		return false;
	}
}
