<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_Gallery class
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Gallery;
use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Term;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;

/**
 * Runway_Gallery endpoint class.
 */
class VY_Gallery extends Gallery {

	use Usable_Definitions;

	/**
	 * Article model.
	 *
	 * @var Article_Object
	 */
	protected $article_object;


	/**
	 * Get entitlements.
	 *
	 * @return array
	 */
	protected function get_entitlements(): array {
		return $this->article_object->entitlements();
	}

	/**
	 * Get post vertical term.
	 *
	 * @return array|\stdClass
	 */
	protected function get_vertical(): array {
		return $this->article_object->category( 'vertical' );
	}

	/**
	 * Updating schema to add the ad size obejct.
	 *
	 * @return array
	 */
	public function get_response_schema(): array {
		$schema = parent::get_response_schema();

		$schema['properties']['vertical'] = $this->add_definition( new Term() );

		return $schema;
	}
}
