<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Importer {

	use Singleton;

	public $import_message;

	/**
	 * Class constructor
	 * Add all the hooks and filters
	 */
	protected function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	* Action hook on admin_init
	*/
	public function admin_init() {
		if ( empty( $_GET['page'] ) || $_GET['page'] != 'ad-manager' ) {
			return;
		}
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'import-upload' ) ) {
			return;
		}

		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			$this->import_message = '<p class="error">' . $file['error'] . '</p>';

			return;
		}

		$attachment = get_attached_file( $file['id'] );
		if ( ! is_file( $attachment ) ) {
			return;
		}

		if ( ! class_exists( 'WXR_Parser' ) ) {

			$path = "/wordpress-importer/parsers.php";

			if ( true === WPCOM_IS_VIP_ENV ) {
				$path = WP_CONTENT_DIR . '/admin-plugins' . $path;
			} else {
				$path = WP_PLUGIN_DIR . $path;
			}

			require_once( $path );
		}

		if ( ! class_exists( 'WXR_Parser' ) ) {
			$this->import_message = '<p><strong>WXR_Parser is not loaded</strong></p>';

			return;
		}
		$this->import_data( $attachment );

		if ( empty( $this->import_message ) ) {

			$this->import_message = '<p><strong>Import complete!</strong></p>';
		}
	}

	/**
	 * Import the data present in the file
	 * @param $file
	 */
	protected function import_data( $file ) {

		$parser      = new WXR_Parser();
		$import_data = $parser->parse( $file );

		if ( is_wp_error( $import_data ) ) {
			$this->import_message = 'Failed to read WXR file.';
		}
		else {
			$this->process_posts( $import_data['posts'] );
		}

	}

	/**
	 * Function copied from wordpress plugin importer to suit needs of pmc ads
	 * We dont need to import terms/comments/authors etc. cleaned them up.
	 *
	 * @param $posts
	 */
	protected function process_posts( $posts ) {

		$message = "";

		foreach ( $posts as $post ) {

			if ( PMC_Ads::POST_TYPE != $post['post_type'] ) {
				$message .= "{$post['post_title']} Not a valid ad post type <br/>";
				continue;
			}

			if ( empty( $post['post_id'] ) ) {
				$message .= "Post id not set <br/>";
				continue;
			}

			if ( $post['status'] !== 'publish' ) {
				$message .= "{$post['post_id']} does not have status publish <br/>";
				continue;
			}

			$post_id = post_exists( $post['post_title'], '', $post['post_date'] );

			if ( $post_id && get_post_type( $post_id ) == $post['post_type'] ) {
				$message .= $post['post_title'] . " already exists.<br/>";
			} else {
				$post_parent = (int) $post['post_parent'];
				if ( $post_parent ) {
					// if we already know the parent, map it to the new local ID
					if ( isset( $this->processed_posts[$post_parent] ) ) {
						$message .= "post {$post['post_id']} has parent, bail <br/>";
						continue;
					} else {
						$post_parent = 0;
					}
				}

				// import as current author
				$author = (int) get_current_user_id();

				$postdata = array(
					'import_id'      => $post['post_id'],
					'post_author'    => $author,
					'post_date'      => $post['post_date'],
					'post_date_gmt'  => $post['post_date_gmt'],
					'post_content'   => $post['post_content'],
					'post_excerpt'   => $post['post_excerpt'],
					'post_title'     => $post['post_title'],
					'post_status'    => $post['status'],
					'post_name'      => $post['post_name'],
					'comment_status' => $post['comment_status'],
					'ping_status'    => $post['ping_status'],
					'guid'           => $post['guid'],
					'post_parent'    => $post_parent,
					'menu_order'     => $post['menu_order'],
					'post_type'      => $post['post_type'],
					'post_password'  => $post['post_password']
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) {
					$message .= "Failed to import {$post['post_title']} <br/>";
					continue;
				}
			}

			// add/update post meta
			if ( isset( $post['postmeta'] ) ) {

				foreach ( $post['postmeta'] as $meta ) {

					$key = apply_filters( 'import_post_meta_key', $meta['key'] );

					if ( $key ) {

						// export gets meta straight from the DB so could have a serialized string
						$value = maybe_unserialize( $meta['value'] );

						update_post_meta( $post_id, $key, $value );

						do_action( 'import_post_meta', $post_id, $key, $value );

					}
				}
			}
		}

		unset( $this->posts );

		$this->import_message = $message;
	}

}

PMC_Ads_Importer::get_instance();

//EOF
