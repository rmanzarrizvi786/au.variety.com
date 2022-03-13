<?php
/**
 * This file contains the Endpoints\Schema_Definitions\Related_Articles class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Schema_Definitions;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Post_Card;

/**
 * Related_Articles schema.
 */
class Related_Articles implements Definition {

	use Usable_Definitions;

	/**
	 * Get the schema slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'related-articles';
	}

	/**
	 * Get the item schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'type'  => 'array',
			'items' => $this->add_definition( new Post_Card() ),
		];
	}
}
