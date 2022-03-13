<?php
/**
 * Larva convenience functions.
 *
 * @package pmc-larva
 */

namespace PMC\Larva;

/**
 * Invoke a Larva controller to inject data into a pattern.
 *
 * @param string $class Name of controller class to invoke. Should use `::class`
 *                      notation rather than passing an explicit string.
 * @param array  $args  Controller arguments.
 * @param array  $data  Larva pattern data node to modify via controller.
 */
function add_controller_data(
	string $class,
	array $args,
	array &$data
): void {
	( new $class( $args ) )->add_data( $data );
}

/**
 * Generate a unique ID for a given post ID, for use in HTML IDs.
 *
 * For example, the `c_title` component has a `c_title_id_attr` attribute that
 * can be used for deep linking with the assistance of this function.
 *
 * @param int $post_id
 * @return string
 */
function get_id_attribute_for_post_id( int $post_id ): string {
	return sprintf(
		'%1$s-%2$s-%3$d',
		'c-title',
		get_post_field(
			'post_name',
			$post_id
		),
		$post_id
	);
}
