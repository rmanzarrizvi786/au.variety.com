<?php
/**
 * This class is responsible to process all REST API requests for Content Blocks in Content Builder.
 */

namespace PMC\Exacttarget;

use \PMC\Exacttarget\Rest_Support;
use \PMC\Exacttarget\Rest_Error;

class Content_Block_Rest extends Rest_Support {

	// @TODO: Implement validation using this array when creating a block.
	const REQUIRED_ATTRIBUTES_POST = [
		'name'      => '',
		'content'   => '',
		'category'  => [
			'id'       => 0,
			'name'     => '',
			'parentId' => 0,
		],
		'assetType' => [
			'name' => '',
			'id'   => 0,
		],
	];

	// @TODO: Content Builder supports multiple types of code block, we can improve this class to support more types in future.
	const ASSET_TYPE_NAME = 'codesnippetblock';

	const ASSET_TYPE_ID = 220;

	const DEFAULT_QUERY_ARGS = [
		'query' => [
			'rightOperand'    => [
				'property'       => 'assetType.name',
				'simpleOperator' => 'equal',
				'value'          => self::ASSET_TYPE_NAME,
			],
			'logicalOperator' => 'AND',
		],
	];

	/**
	 * For retriveing a block by $field_name using $value.
	 * @TODO: Can make this function generic to retreive more than one block.
	 *
	 * @param string $field_name Name of the field, used for filtering the blocks.
	 * @param string $vaue       Value for the field provided in $field_name.
	 * @param string $fields     Fields to retrieve from ET, e.g. name, id etc.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function query_block_by( $field_name, $value, $fields = [] ) {

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
				'status',
				'assetType',
			);
		}

		$query                         = self::DEFAULT_QUERY_ARGS;
		$query['fields']               = $fields;
		$query['query']['leftOperand'] = [
			'property'       => $field_name,
			'simpleOperator' => 'equal',
			'value'          => $value,
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
			$response->message = 'No matching Content Blocks found!';
		}

		return $response;
	}

	/**
	 * For retriveing a block by ID.
	 *
	 * @param string|int $id     ID of the block.
	 * @param array      $fields Fields to retrieve in the result.
	 *
	 * @codeCoverageIgnore Covered in test cases written for $this->query_block_by()
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function get_block_by_id( $id, $fields = [] ) {
		return $this->query_block_by( 'id', $id, $fields );
	}

	/**
	 * For retriveing a block by name.
	 *
	 * @param string|int $id     Name of the block.
	 * @param array      $fields Fields to retrieve in the result.
	 *
	 * @codeCoverageIgnore Covered in test cases written for $this->query_block_by()
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function get_block_by_name( $name, $fields = [] ) {
		return $this->query_block_by( 'name', $name, $fields );
	}

	/**
	 * For updating a block's content.
	 *
	 * @param string|int $block_id ID of the block.
	 * @param array      $content  Updated content.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function update( $block_id, $content ) {

		if ( empty( $block_id ) || empty( $content ) ) {
			return new Rest_Error( 'Required fields are missing' );
		}

		$updates = [
			'content' => $content,
		];

		$response = parent::patch( $block_id, $updates );

		return $response;
	}

	/**
	 * For creating a block.
	 *
	 * @param array $args Args for creating the block, array should be of the format,
	 * array(
	 *    'name',    // Name of the block, ( Required ).
	 *    'content', // Content of the block, ( Required ).
	 *    'category' // ID of the folder, ( Optional ) default folder is Content Builder.
	 * )
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function create( $args ) {

		if (
			empty( $args['name'] )
			|| empty( $args['content'] )
		) {
			return new Rest_Error( 'Name or Content is missing' );
		}

		$category_id = ( ! empty( $args['category_id'] ) ) ? $args['category_id'] : 0;

		$block = [
			'name'      => $args['name'],
			'content'   => $args['content'],
			'category'  => [ 'id' => $category_id ],
			'assetType' => [
				'name' => self::ASSET_TYPE_NAME,
				'id'   => self::ASSET_TYPE_ID,
			],
		];

		return parent::post( $block );
	}

	/**
	 * For deleting a block.
	 *
	 * @param string|int $id ID of the block.
	 *
	 * @return bool True if block was deleted false otherwise.
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
