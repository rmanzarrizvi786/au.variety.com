<?php
// One time script fix; we need to ignore these rules for entire file
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
// phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_fputcsv
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose

namespace PMC\Facebook_Instant_Articles\WP_CLI;

use Facebook\Facebook;
use Facebook\InstantArticles\Client\Client;
use WP_CLI;
use PMC_WP_CLI_Base;
use Instant_Articles_Post;

/**
 * CLI Commands for Facebook Instant Articles
 *
 * @package PMC\Facebook_Instant_Articles\WP_CLI
 *
 * @codeCoverageIgnore There is no benefit on spending the time to write code coverage for this script that only run once
 */
class Fbia extends PMC_WP_CLI_Base {

	protected $_detected_authors              = [];
	protected $_assoc_args_properties_mapping = [
		'app_id'        => 'app-id',
		'app_secret'    => 'app-secret',
		'access_token'  => 'access-token',
		'page_id'       => 'page-id',
		'dev_mode'      => 'dev-mode',
		'csv_file'      => 'csv',
		'continue_from' => 'continue-from',
	];

	public $dry_run      = false;
	public $app_id       = false;
	public $app_secret   = false;
	public $access_token = false;
	public $page_id      = false;
	public $dev_mode     = true;
	public $csv_file     = false;

	/**
	 * WP-CLI command to generate report meta data and store in meta table. If --meta-fields option is empty then generates all metadata for all the posts.
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : If provided then will write report to the CSV file.
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * [--sleep=<sleep>]
	 * : Set sleep in seconds to throttle the script after calling stop the insanity, default = 2 (seconds)
	 *
	 * [--max-iteration=<iteration>]
	 * : Set max iteration for stop the insanity, default = 20
	 *
	 * [--batch-size=<number>]
	 * : Batch size, default = 500
	 *
	 * [--batch-paged=<number>]
	 * : Batch starting page, default = 1
	 *
	 * [--date-after]
	 * : To fetch posts between duration of date from.
	 *
	 * [--date-before]
	 * : To fetch posts between duration of date to.
	 *
	 * ## EXAMPLES
	 *
	 *    # Generate csv and pipe to stdout stream
	 *    $ wp pmc-fbia generate-bad-author-report --csv=php://output --quiet --email-when-done=false | tee output.csv
	 *
	 * @subcommand generate-bad-author-report
	 *
	 * @param array $pos_args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function generate_bad_author_report( $pos_args = array(), $assoc_args = array() ) {

		$attachments = [];
		$this->_extract_common_args( $assoc_args );
		$this->_notify_start( 'Generating bad author report' );

		$paged = $this->batch_paged;

		// get_posts args.
		$args = array(
			'post_type'        => 'post',
			'posts_per_page'   => $this->batch_size,
			'suppress_filters' => true,  // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFiltersTrue
			'orderby'          => 'ID',
			'order'            => 'ASC',
		);

		$date_query = [];
		if ( ! empty( $assoc_args['date-after'] ) ) {
			$date_query['after'] = $assoc_args['date-after'];
		}
		if ( ! empty( $assoc_args['date-before'] ) ) {
			$date_query['before'] = $assoc_args['date-before'];
		}
		if ( ! empty( $date_query ) ) {
			$date_query['inclusive'] = true;
			$args['date_query']      = [
				$date_query,
			];
		}

		$fp      = fopen( $this->csv_file, 'w' );
		$headers = [ 'Post_Id', 'URL', 'Title', 'Author', 'Wrong_Author' ];
		fputcsv( $fp, $headers );

		$total          = 0;
		$total_detected = 0;

		do {
			$count = 0;

			$args['paged'] = $paged;

			$posts = get_posts( $args ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions

			if ( empty( $posts ) ) {
				break;
			}

			$paged++;

			$posts_count = count( $posts );
			$total      += $posts_count;

			foreach ( $posts as $post ) {
				$authors = $this->_get_bad_authors_from_post( $post );
				if ( ! empty( $authors ) ) {
					$row = [
						$post->ID,
						get_permalink( $post->ID ),
						$post->post_title,
						implode( ';', $authors['coauthor'] ),
						implode( ';', $authors['fbia'] ),
					];
					fputcsv( $fp, $row );
					$count++;
				}
			}

			if ( ! empty( $count ) ) {
				$total_detected += $count;
			}

			$this->_write_log( sprintf( 'Processed %d articles, found %d with bad author', $total, $total_detected ) );

			$this->_update_interation();

		} while ( $this->batch_size === $posts_count );

		fclose( $fp );

		if ( ! empty( $this->csv_file ) && ! preg_match( '@php://@', $this->csv_file ) ) {
			$attachments[] = $this->csv_file;
		}

		$this->_notify_done( 'Done.', $attachments );
	}

	private function _maybe_add_actions() {
		$this->_detected_authors = [];
		if ( empty( $this->_actions_added ) ) {
			add_filter(
				'instant_articles_authors',
				function ( $authors ) {
					$this->_detected_authors['fbia'] = $this->_extract_author_display_name( $authors );
					return $authors;
				},
				1
			);

			add_filter(
				'instant_articles_authors',
				function ( $authors ) {
					$this->_detected_authors['coauthor'] = $this->_extract_author_display_name( $authors );
					return $authors;
				},
				100
			);

			$this->_actions_added = true;
		}
	}

	private function _get_bad_authors_from_post( $post ) : array {
		$this->_maybe_add_actions();
		$ia_post = new Instant_Articles_Post( $post );
		$ia_post->get_the_authors();

		if ( count( $this->_detected_authors['fbia'] ) !== count( $this->_detected_authors['coauthor'] ) ) {
			return $this->_detected_authors;
		}

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->_detected_authors['fbia'] != $this->_detected_authors['coauthor'] ) {
			return $this->_detected_authors;
		}

		return [];
	}

	private function _extract_author_display_name( $authors ) {
		$result = [];

		if ( is_string( $authors ) ) {
			if ( preg_match_all( '@<address>(.*?)</address>@', $authors, $matches ) ) {
				foreach ( $matches[1] as $item ) {
					$result[] = preg_replace( '@<a.*?>(.*?)</a>@', '$1', $item );
				}
			}
		} elseif ( is_array( $authors ) ) {
			foreach ( $authors as $author ) {
				$result[] = $author->display_name;
			}
		}

		sort( $result );
		return $result;
	}

	private function _get_authors_from_fbia( $url ) {
		$field    = $this->dev_mode ? 'development_instant_article' : 'instant_article';
		$response = $this->_get_facebook_api()->get( '?id=' . $url . '&fields=' . $field );
		$ia       = $response->getGraphNode()->getField( $field );
		if ( ! empty( $ia ) ) {
			return $this->_extract_author_display_name( $ia->getField( 'html_source' ) );
		}
		return [];
	}

	private function _get_facebook_api() {
		if ( empty( $this->_fb_api ) ) {
			$this->_fb_api = new Facebook(
				[
					'app_id'                => $this->app_id,
					'app_secret'            => $this->app_secret,
					'default_access_token'  => $this->access_token,
					'default_graph_version' => 'v2.5',
				]
			);
		}
		return $this->_fb_api;
	}

	/**
	 * WP-CLI command to generate report meta data and store in meta table. If --meta-fields option is empty then generates all metadata for all the posts.
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : csv file containing the data to process
	 *
	 * --app-id=<app-id>
	 * : Facebook App ID
	 *
	 * --app-secret=<app-secret>
	 * : Facebook App Secret
	 *
	 * --page-id=<page-id>
	 * : Facebook Instant Article Page ID
	 *
	 * --access-token=<access-token>
	 * : Facebook API access token
	 *
	 * [--dev-mode=<boolean>]
	 * : Facebook Instant Article Development Mode, default = true
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * @subcommand get-fbia-author
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function get_fbia_author( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );
		$this->_notify_start( sprintf( 'Get Facebook Instant Articles, dev-mode: %s', $this->dev_mode ? 'on' : 'off' ) );

		$processed = [];
		$fp        = fopen( $this->csv_file, 'r' );
		$fo        = fopen( str_replace( '.csv', '-fbia.csv', $this->csv_file ), 'c+' );
		fseek( $fo, 0, SEEK_SET );
		$headers     = fgetcsv( $fp );
		$headers_out = fgetcsv( $fo );
		if ( empty( $headers_out ) ) {
			$headers_out   = $headers;
			$headers_out[] = 'FBIA_Author';
			fputcsv( $fo, $headers_out );
		} else {
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( ( $row = fgetcsv( $fo ) ) !== false ) {
				if ( count( $row ) === count( $headers_out ) ) {
					$data                      = array_combine( $headers_out, $row );
					$processed[ $data['URL'] ] = $data['Post_Id'];
				}
			}
		}

		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $row = fgetcsv( $fp ) ) !== false ) {
			if ( count( $row ) === count( $headers ) ) {
				$data = array_combine( $headers, $row );

				if ( ! isset( $processed[ $data['URL'] ] ) ) {
					$row[] = implode( ';', $this->_get_authors_from_fbia( $data['URL'] ) );
					$data  = array_combine( $headers_out, $row );
					fputcsv( $fo, $data );
					$this->_write_log( sprintf( '%d: %s -> %s', $data['Post_Id'], $data['Author'], $data['FBIA_Author'] ) );
				}
			}
		}
		fclose( $fp );
		fclose( $fo );

		$this->_notify_done();
	}

	/**
	 * WP-CLI command to generate report meta data and store in meta table. If --meta-fields option is empty then generates all metadata for all the posts.
	 *
	 * ## OPTIONS
	 *
	 * --csv=<file>
	 * : csv file containing the data to process
	 *
	 * [--app-id=<app-id>]
	 * : Facebook App ID
	 *
	 * [--app-secret=<app-secret>]
	 * : Facebook App Secret
	 *
	 * [--page-id=<page-id>]
	 * : Facebook Instant Article Page ID
	 *
	 * [--access-token=<access-token>]
	 * : Facebook API access token
	 *
	 * [--dev-mode=<boolean>]
	 * : Facebook Instant Article Development Mode, default = true
	 *
	 * [--log-file=<file>]
	 * : Path to the log file.
	 *
	 * [--dry-run]
	 * : Dry run
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * [--continue-from]
	 * : Continue from last post id
	 *
	 * @subcommand fix-fbia-author
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function fix_fbia_author( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );
		$this->_notify_start( sprintf( 'Fix Facebook Instant Articles, dev-mode: %s', $this->dev_mode ? 'on' : 'off' ) );
		$fp = fopen( $this->csv_file, 'r' );

		$headers = fgetcsv( $fp );

		if ( ! in_array( 'FBIA_Author', (array) $headers, true ) ) {
			$this->_error( 'Author inform missing from csv file' );
		}

		if ( ! $this->dry_run ) {
			$fbia_client = Client::create( $this->app_id, $this->app_secret, $this->access_token, $this->page_id, $this->dev_mode );
		}

		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $row = fgetcsv( $fp ) ) !== false ) {
			if ( count( $headers ) !== count( $row ) ) {
				continue;
			}
			$data = array_combine( $headers, $row );
			if ( ! empty( $data['FBIA_Author'] ) && $data['Author'] !== $data['FBIA_Author'] ) {
				if ( ! empty( $this->continue_from ) ) {
					if ( $this->continue_from !== $data['Post_Id'] ) {
						continue;
					}
					$this->continue_from = false;
				}

				$post = get_post( $data['Post_Id'] );
				if ( empty( $post ) ) {
					$this->_write_log( sprintf( '%d: not found', $data['Post_Id'] ) );
					continue;
				}

				$ia_post = new Instant_Articles_Post( $post );
				$this->_write_log( sprintf( "%d: %s\n> %s -> %s\n", $data['Post_Id'], $ia_post->get_canonical_url(), $data['FBIA_Author'], $data['Author'] ) );
				if ( ! $this->dry_run ) {
					$id = $fbia_client->importArticle( $ia_post->to_instant_article(), true, true );
					$this->_write_log( sprintf( '> fbia id %d', $id ) );
					$this->_update_interation();
				}
			}
		}

		fclose( $fp );
		$this->_notify_done();
	}

	protected function _replace_theme_path( $path = '' ) {
		return preg_replace( '@^theme://@', trailingslashit( get_stylesheet_directory() ), $path );
	}

	protected function _extract_common_args( $assoc_args ) {
		parent::_extract_common_args( $assoc_args );
		if ( ! empty( $this->csv_file ) ) {
			$this->csv_file = $this->_replace_theme_path( $this->csv_file );
		}
	}

}

WP_CLI::add_command( 'pmc-fbia', Fbia::class );

