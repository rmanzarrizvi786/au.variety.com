<?php

class PMC_Ads_WP_CLI extends PMC_WP_CLI_Base {
	/**
	 * @synopsis [--dry-run] [--batch-size=<number>] [--sleep=<second>] [--max-iteration=<number>] [--log-file=<file>]
	 * @subcommand migrate-oop
	 */
	public function migrate_oop( $args = array(), $assoc_args = array() ) {

		WP_CLI::line( 'Migrating OOP Ads' );

		if ( $this->dry_run ) {
			$this->_write_log( 'Dry run' );
		}

		// process any default flag override
		$this->_extract_common_args( $assoc_args );

		$total_affected = 0;
		$offset = 0;
		$args = array(
				'post_type'        => PMC_Ads::POST_TYPE,
				'suppress_filters' => true,
				'posts_per_page'   => $this->batch_size,
				'offset'           => $offset,
			);
		do {

			$args['offset'] = $offset;
			// increase the offset before we forget to prevent endless loop
			$offset += $this->batch_size;

			$posts = get_posts( $args );

			if ( $posts && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) {
					$ad = json_decode( $post->post_content, true );
					if ( !$ad ) {
						$this->_write_log( 'Invalid ads: '. $post->ID );
					} else {
						if ( ! empty( $ad['out-of-page'] ) && strtolower( $ad['out-of-page'] ) == 'yes' ) {
							$total_affected += 1;
							$this->_write_log( sprintf('oop ad found: %1$d %2$s %3$s %4$sx%5$s', $post->ID, $ad['location'], $ad['title'], $ad['width'], $ad['height'] ) );
							unset( $ad['out-of-page'] );
							$oop_ad = $ad;
							$ad['slot-type'] = '';
							$oop_ad['slot-type'] = 'oop';
							$oop_ad['title'] = 'oop: ' . $oop_ad['title'];
							if ( $this->dry_run ) {
								$this->_write_log( sprintf("Would update %d: %s\n", $post->ID, json_encode( $ad ) ) );
								$this->_write_log( sprintf("Would create '%s' %s\n", $oop_ad['title'], json_encode( $oop_ad ) ) );
							} else {
								$this->_write_log( sprintf("Update %d: %s\n", $post->ID, json_encode( $ad ) ) );
								wp_update_post( array( 'ID' => $post->ID, 'post_content' => json_encode( $ad ) ) );
								update_post_meta( $post->ID, '_ad_data', $ad );

								$this->_write_log( sprintf("Create: %s\n", json_encode( $oop_ad ) ) );
								$new_post = array(
										'post_content_filtered' => $post->post_content_filtered,
										'post_content'          => json_encode($oop_ad),
										'post_excerpt'          => $post->post_excerpt,
										'menu_order'            => $post->menu_order,
										'post_title'            => $oop_ad['title'],
										'post_status'           => 'publish',
										'post_type'             => PMC_Ads::POST_TYPE,
									);

								$new_post_id = wp_insert_post( $new_post, true );
								if ( is_wp_error( $new_post_id ) ) {
									$this->_write_log( $new_post_id->get_error_message() );
								} else {
									update_post_meta( $new_post_id, '_ad_title', $oop_ad['title'] );
									update_post_meta( $new_post_id, '_ad_location', $oop_ad['location'] );
									update_post_meta( $new_post_id, '_ad_data', $oop_ad );
								}

								// @see PMC_WP_CLI_Base::_update_iteration
								// call sleep & stop_the_insanity when needed
								$this->_update_iteration();
							}
						}
					}
				}
			} else {
				break;
			}
		} while( $posts && count( $posts ) == $this->batch_size );

		$this->_write_log( sprintf("\nTotal affected: %d\nDone.", $total_affected ) );
	}
}

WP_CLI::add_command( 'pmc-ads', 'PMC_Ads_WP_CLI' );
