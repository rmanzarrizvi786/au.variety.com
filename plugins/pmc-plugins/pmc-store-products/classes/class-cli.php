<?php

namespace PMC\Store_Products;

use WP_CLI;
use WP_CLI\Iterators;
use WP_CLI\Utils;

/**
 * Interface for managing PMC Store Products.
 */
class CLI extends \PMC_WP_CLI_Base {

	/**
	 * Patterns used to process content.
	 *
	 * @var array
	 */
	protected $patterns;

	/**
	 * Number of updated posts.
	 *
	 * @var integer
	 */
	protected $updated = 0;

	/**
	 * Migrate existing HTML to PMC Store product format.
	 *
	 * ## OPTIONS
	 *
	 * [<post-id>...]
	 * : One or more post IDs to migrate.
	 *
	 * [--all]
	 * : Migrate all posts.
	 *
	 * [--dry-run]
	 * : Mock the conversion process without writing to the database.
	 *
	 * @subcommand convert-existing
	 */
	public function convert_existing( $args, $assoc_args ) {
		global $wpdb;

		if ( ( empty( $args ) && ! self::_get_flag_value( $assoc_args, 'all' ) )
			|| ( ! empty( $args ) && self::_get_flag_value( $assoc_args, 'all' ) ) ) {
			WP_CLI::error( 'Please specify one or more post ids, or use --all.' );
		}

		define( 'WP_IMPORTING', true );
		$this->start_bulk_operation();

		/**
		 * Conversion patterns to use in the migration process.
		 *
		 * Defined by the theme via this filter.
		 *
		 * @param array $patterns No default conversion patterns.
		 */
		$this->patterns = apply_filters( 'pmc_store_products_conversion_patterns', array() );

		if ( empty( $this->patterns ) ) {
			WP_CLI::error( 'Please define conversion patterns via filter before proceeding.' );
		}

		$data = ! empty( $args ) ? $args : new Iterators\Query( "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ('publish', 'pending') AND post_type IN ('post', 'pmc_review', 'pmc-gallery' )" );

		$i = 0;
		foreach ( $data as $post_id ) {
			$i++;
			if ( is_object( $post_id ) ) {
				$post_id = $post_id->ID;
			}
			$this->process_post_contents( $post_id, 'post_content', $assoc_args );
			if ( $i && 0 === $i % 200 ) {
				$this->stop_the_insanity();
			}
		}

		$this->end_bulk_operation();
		WP_CLI::success( "Converted {$this->updated} posts." );
	}

	/**
	 * Process the contents of a post (or attachment)
	 *
	 * @param integer $post_id    Post ID.
	 * @param string  $field      Post field to process.
	 * @param array   $assoc_args Arguments used for invocation.
	 */
	protected function process_post_contents( $post_id, $field, $assoc_args ) {
		$post = get_post( $post_id );
		if ( empty( $post_id ) || empty( $post ) ) {
			WP_CLI::log( "Invalid post {$post_id}, skipping." );
			return;
		}

		$gallery_items = get_post_meta( $post_id, 'pmc-gallery', true );
		if ( ! empty( $gallery_items ) ) {
			WP_CLI::log( "Processing gallery items for {$post_id}..." );
			foreach ( $gallery_items as $gallery_item ) {
				$this->process_post_contents( $gallery_item, 'post_excerpt', $assoc_args );
			}
		}
		WP_CLI::log( "Processing {$post->post_type} {$post_id}..." );

		$content          = $post->$field;
		$original_content = $content;
		foreach ( $this->patterns as $pattern => $callback ) {
			$callback_handler = function( $matches ) use ( $callback ) {
				$original_match = $matches[0];
				$matches[0]     = $callback( $matches );
				if ( $original_match !== $matches[0] ) {
					WP_CLI::log( ' -> Original: ' . $original_match );
					WP_CLI::log( ' -> Replace: ' . $matches[0] );
				}
				return $matches[0];
			};
			$content          = preg_replace_callback( $pattern, $callback_handler, $content );
		}

		if ( $original_content === $content ) {
			WP_CLI::log( "No transformations made on {$post->post_type} {$post_id}." );
			return;
		}

		$action = 'Dry-updated';
		if ( ! self::_get_flag_value( $assoc_args, 'dry-run' ) ) {
			wp_update_post(
				array(
					'ID'   => $post_id,
					$field => $content,
				)
			);
			$action = 'Updated';
		}

		$this->updated++;
		WP_CLI::log( "{$action} {$post->post_type} {$post_id} to use PMC Store Product shortcode." );
	}

}
