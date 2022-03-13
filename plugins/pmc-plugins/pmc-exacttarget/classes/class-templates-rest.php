<?php
/**
 * This class is responsible to process all REST API requests for Folders ( Categories ) in Content Builder.
 */

namespace PMC\Exacttarget;

use PMC\Exacttarget\Rest_Support;
use PMC\Exacttarget\Rest_Request;
use \PMC\Exacttarget\Rest_Error;


class Templates_Rest extends Rest_Support {

	const ASSET_TYPE_NAME = 'Template';

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
	 * Gets templates based on $query.
	 * Refer to https://developer.salesforce.com/docs/atlas.en-us.noversion.mc-apis.meta/mc-apis/assetAdvancedQuery.htm for more details on parameters.
	 *
	 * @param array $query Query arguments, refer to the reference array below.
	 * array(
	 *    'query' => array(
	 *         'property' => 'Property to use for matching e.g. 'id', 'name' etc.',
	 *         'operator' => 'Operator to use for matching the value in property. e.g. 'eq', 'neq' etc, refer to static::ALLOWED_GET_OPERATORS',
	 *         'value'    => 'Value to use for matching',
	 *     ),
	 *     'fields' => array(
	 *         'name',
	 *         'content',
	 *     ),
	 * );
	 *
	 * @param array $path_params This param is not used for our purposes, we're overriding parent function
	 * so added to ensure function signature matches with parent's function we have to add this param.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public function get( $query = [], $query_params = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		$template_query                         = self::DEFAULT_QUERY_ARGS;
		$template_query['query']['leftOperand'] = $query['query'];

		// Add default sorting by name, this is important, for some reason API returns incosistent data in pagination if sort param is missing!
		if ( ! array_key_exists( 'sort', $query ) ) {

			$query['sort'][] = array(
				'property'  => 'name',
				'direction' => 'ASC',
			);
		}

		unset( $query['query'] );

		return parent::post( array_merge( $template_query, $query ), [ 'query' ] );
	}
}
