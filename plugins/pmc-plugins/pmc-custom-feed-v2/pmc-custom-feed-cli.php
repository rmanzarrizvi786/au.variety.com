<?php

WP_CLI::add_command( 'pmc_custom_feed', 'PMC_Custom_Feed_WP_CLI' );

class PMC_Custom_Feed_WP_CLI extends WP_CLI_Command {

	/**
	 * @synopsis [--dry_run=<number>]
	 * @subcommand move-custom-feeds
	 */
	public function move_custom_feeds( $args, $assoc_args ) {

		$dry_run = 1;

		WP_CLI::line( 'Starting...' );

		if ( isset( $assoc_args['dry_run'] ) ) {
			$dry_run = intval( $assoc_args['dry_run'] );
		}

		if ( 2 == $dry_run ) {
			$dry_run = false;
		} else {
			$dry_run = true;
		}

		WP_CLI::line( 'Dry_run{ 2 will swap }:' . $dry_run );

		$max_limit = 100;
		$i         = 0;

		$custom_feed_options = get_option( "pmc_custom_feed_option" );

		$meta_mapper = array(
			'feed_item_count'   => 'count',
			'feed_template'     => 'template',
			'feed_taxonomy'     => 'taxonomy',
			'feed_image_size'   => 'image_size',
			'feed_query_string' => 'query_string',
			'feed_html'         => 'html',
			'feed_related'      => 'related',
			'feed_post_type'    => 'post_type',
			'feed_token'        => 'token',
			'feed_slug'         => 'slug'
		);

		$user = get_user_by( "login", "pmcamit" );

		foreach ( $custom_feed_options as $slug => $feed ) {

			if ( $i++ > $max_limit ) {
				break;
			}

			$post_information = array(
				'post_status'      => 'publish',
				'post_title'       => $slug,
				'post_type'        => PMC_Custom_Feed::post_type_name,
				'meta_query'       => array(
					array(
						'key'   => PMC_Custom_Feed::meta_custom_name . 'slug',
						'value' => $slug,
					)
				),
			);

			$post_available = get_posts( $post_information );
			$post_id        = "";
			$status         = " created";

			if ( !empty( $post_available ) && isset( $post_available[0]->ID ) ) {
				$post_information['ID'] = $post_available[0]->ID;
				$status                 = " updated";
			} else {
				$post_information["post_name"] = $slug;
			}

			$post_information['post_author'] = $user->ID;

			if ( !$dry_run ) {
				unset( $post_information['meta_query'] );
				$post_id = wp_insert_post( $post_information, true );

			}

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::line( "Feed {$slug} Could not be created - Error:" . $post_id->get_error_message() );
				continue;
			} else {
				WP_CLI::line( "Feed {$slug} {$status} post_id: " . $post_id );
			}

			if ( !empty( $post_id ) ):
				foreach ( $meta_mapper as $value => $key ) {
					$meta_key   = PMC_Custom_Feed::meta_custom_name . $key;
					$meta_value = $feed[$value];

					if ( "feed_related" == $value && "1" == $meta_value ) {
						$meta_value = "on";
					}

					if ( "default" == $meta_value || "all posts" == strtolower( $meta_value ) ) {
						$meta_value = "";
					}

					if ( 'default' == $slug && 'feed_slug' == $value ) {
						$meta_value = 'default';
					}

					if ( !$dry_run ) {
						update_post_meta( $post_id, $meta_key, $meta_value );
					} else {
						WP_CLI::line( "{$i}. Feed {$slug} post_id {$post_id} {$meta_key} {$meta_value}  additional data {$status} " );
					}

				}
				WP_CLI::line( "{$i}. Feed {$slug} post_id {$post_id}  additional data {$status} " );
			endif;


			wp_cache_delete( PMC_Custom_Feed::cache_key, PMC_Custom_Feed::cache_key . "grp" );

		}

		WP_CLI::success( "All done" );

	}
}

//EOF