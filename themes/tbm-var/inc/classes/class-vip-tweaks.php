<?php
/**
 * Class containing VIP specific tweaks
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2019-05-08
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class VIP_Tweaks {

	use Singleton;

	/**
	 * Class constructor
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 *
	 */
	protected function _setup_hooks() : void {

		/*
		 * Filters
		 */
		add_filter( 'ajax_query_attachments_args', [ $this, 'offload_media_library_to_es' ] );

	}

	/**
	 * Method hooked into 'es_searchable_fields' hook to enable
	 * excerpt field in ES for media library AJAX query in post screen in wp-admin.
	 *
	 * @param array $fields list of fields.
	 *
	 * @return array
	 */
	public function get_es_searchable_fields( $fields = [] ) : array {

		if ( ! is_array( $fields ) ) {
			$fields = [];
		}

		$fields[] = 'excerpt';

		return $fields;

	}

	/**
	 * Method hooked into 'ajax_query_attachments_args' hook to enable
	 * ES for media library AJAX query in post screen in wp-admin.
	 *
	 * @param array $query list of query variables.
	 *
	 * @return array
	 */
	public function offload_media_library_to_es( array $query ) : array {

		add_filter( 'es_searchable_fields', [ $this, 'get_es_searchable_fields' ] );

		$query['es']          = true;
		$query['post_status'] = 'any'; // for some unknown reason, media posts do not have the status inherit they are flagged as draft inside ElasticSearch.

		if ( isset( $query['post_mime_type'] ) ) {
			unset( $query['post_mime_type'] );
		}

		return $query;

	}


}    //end class

//EOF
