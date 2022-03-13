<?php
/**
 * Controller for o-author object.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Objects;

use PMC\Larva\Controllers\Base;
use PMC\Larva\Controllers\Components;

/**
 * Class O_Author.
 */
class O_Author extends Base {
	/**
	 * Add controller data to an element of Larva data.
	 *
	 * @param array $data Larva component/object data structure.
	 */
	public function add_data( array &$data ): void {
		// TODO: should use c-tagline-author instead, so we can support multiple authors.
		$author = get_userdata(
			get_post_field(
				'post_author',
				$this->_args['post_id']
			)
		);

		(
			new Components\C_Span(
				[
					'text' => $author->display_name,
					'url'  => get_author_posts_url(
						$author->ID,
						$author->user_nicename,
					),
				]
			)
		)->add_data( $data['c_span'] );

		(
			new Components\C_Timestamp(
				[
					'post_id' => $this->_args['post_id'],
				]
			)
		)->add_data( $data['c_timestamp'] );
	}
}
