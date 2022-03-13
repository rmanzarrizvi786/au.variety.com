<?php
/**
 * Manipulate Elasticsearch integration.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily\Integrations;

use const PMC\Digital_Daily\POST_TYPE_SPECIAL_EDITION_ARTICLE;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Elasticsearch.
 */
class Elasticsearch {
	use Singleton;

	/**
	 * Elasticsearch constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks(): void {
		add_filter( 'ep_indexable_post_types', [ $this, 'add_post_types' ] );

		add_filter(
			'ep_indexable_post_status',
			[ $this, 'add_post_statuses' ]
		);
	}

	/**
	 * Allow special-edition articles to be searchable in story blocks.
	 *
	 * @param array $types Post types indexed in Elasticsearch.
	 * @return array
	 */
	public function add_post_types( array $types ): array {
		$types[] = POST_TYPE_SPECIAL_EDITION_ARTICLE;

		return $types;
	}

	/**
	 * Allow articles published only for inclusion in DD to be searchable in
	 * story blocks.
	 *
	 * @param array $statuses Post statuses indexed in Elasticsearch.
	 * @return array
	 */
	public function add_post_statuses( array $statuses ): array {
		$statuses[] = Edit_Flow::EF_STATUS_SLUG;

		return $statuses;
	}
}
