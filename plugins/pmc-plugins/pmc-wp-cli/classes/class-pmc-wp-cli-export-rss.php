<?php
/**
 * Export xml rss
 *
 *
 */


WP_CLI::add_command('pmc-export-rss', 'PMC_WP_CLI_Export_Rss');

class PMC_WP_CLI_Export_Rss extends PMC_WP_CLI_Base {
	/**
	 * @synopsis <rss-file> [--batch-size=<number>] [--limit=<number>]
	 * @subcommand simple
	 */
	public function simple( $args = array(), $assoc_args = array() ) {
		// process any default flag override
		$this->_extract_common_args( $assoc_args );

		if ( !empty( $assoc_args['limit'] ) ) {
			$limit = intval( $assoc_args['limit'] );
		}

		$blog_name = get_bloginfo( 'name', 'display' );
		$home_url = home_url();

		if ( !empty( $assoc_args['blog-name'] ) ) {
			$blog_name = $assoc_args['blog-name'];
		}
		if ( !empty( $assoc_args['home-url'] ) ) {
			$home_url = $assoc_args['home-url'];
		}

		if ( empty( $limit ) ) {
			$limit = 1000;
		}

		$file = $args[0];

		$args = array(
			'post_type'        => 'post',
			'posts_per_page'   => $this->batch_size,
			'suppress_filters' => true,
		);
		$offset = 0;
		$total = 0;
		$batch_size = $this->batch_size;
		file_put_contents( $file,
				sprintf('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"><channel><title>%s</title><link>%s</link>',
						PMC_Custom_Feed_Helper::esc_xml( $blog_name ),
						PMC_Custom_Feed_Helper::esc_xml( $home_url )
					) );
		do {
			$args['offset'] = $offset;
			$offset += $batch_size;
			if( $posts = get_posts( $args ) ) {
				$total += count( $posts );
				foreach( $posts as $post ) {
					file_put_contents( $file,
							sprintf('<item><title>%s</title><link>%s</link></item>',
									PMC_Custom_Feed_Helper::esc_xml( $post->post_title ),
									PMC_Custom_Feed_Helper::esc_xml( get_permalink( $post ) )
								), FILE_APPEND );

				}
			}
			$total += $batch_size;
			if ( $total >= $limit ) {
				break;
			}
			if ( $total + $batch_size >= $limit ) {
				$batch_size = $limit - $total;
				$args['posts_per_page'] = $batch_size;
			}
		} while ( !empty( $posts) && count( $posts ) == $this->batch_size && $total < $limit );

		file_put_contents( $file, '</channel></rss>', FILE_APPEND );

	}
}
