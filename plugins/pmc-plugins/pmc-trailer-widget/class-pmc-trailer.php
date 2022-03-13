<?php
/**
 * youtube videos
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Trailer {

	use Singleton;

	protected function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	public function register_post_type() {
		register_post_type( 'pmc-trailer', array(
			'labels' => array(
				'name' => 'Trailers',
				'singular_name' => 'Trailer',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Trailer',
				'edit_item' => 'Edit Trailer',
				'new_item' => 'New Trailer',
				'view_item' => 'View Trailer',
				'search_items' => 'Search Trailers',
				'not_found' => 'No Trailers found.',
				'not_found_in_trash' => 'No Trailers found in Trash.',
				'all_items' => 'Trailers'
			),
			'show_ui' => true,
			'show_in_menu' => 'edit.php?post_type=pmc-quotes',
			'show_in_admin_bar' => false,
			'supports' => array( 'title' ),
			'register_meta_box_cb' => array( $this, 'meta_box_cb' ),
			'rewrite' => false,
		));
	}

	public function meta_box_cb( $post ) {
		add_meta_box( 'pmc-trailer-link', 'YouTube Video URL', array( $this, 'meta_box' ), $post->post_type, 'normal' );
	}

	public function meta_box( $post ) {
		wp_nonce_field( 'pmc-trailer-nonce', '_pmc_trailer_nonce_name' );
	?>
		<label for="_pmc_trailer_link" class="screen-reader-text">YouTube URL</label>
		<input type="text" id="_pmc_trailer_link" name="_pmc_trailer_link" class="widefat" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pmc_trailer_link', true ) ); ?>" />
	<?php
	}

	public function save_post( $post_id ) {
		if ( !empty( $_POST['_pmc_trailer_nonce_name'] ) && wp_verify_nonce( $_POST['_pmc_trailer_nonce_name'], 'pmc-trailer-nonce' ) ) {
			if ( empty( $_POST['_pmc_trailer_link'] ) || !preg_match( "#v=([\w|-]+)#", $_POST['_pmc_trailer_link'], $matches ) ) {
				delete_post_meta( $post_id, '_pmc_trailer_link' );
				delete_post_meta( $post_id, '_pmc_trailer_data' );
			} else {
				update_post_meta( $post_id, '_pmc_trailer_link', esc_url( $_POST['_pmc_trailer_link'] ) );
				// process data
				$data = wpcom_vip_file_get_contents( 'http://gdata.youtube.com/feeds/api/videos/' . $matches[1] . '?v=2&alt=json' );
				if ( $data = json_decode( $data ) ) {
					// probably need to improve checking here...
					$base_data = array(
						'link' => $data->entry->link[0]->href,
						'thumbnail' => $data->entry->{'media$group'}->{'media$thumbnail'}[2]->url,
						'desc' => $data->entry->{'media$group'}->{'media$description'}->{'$t'},
						'duration' => $data->entry->{'media$group'}->{'yt$duration'}->seconds,
					);
					update_post_meta( $post_id, '_pmc_trailer_data', $base_data );
				} else {
					delete_post_meta( $post_id, '_pmc_trailer_data' );
				}
			}
		}
	}
}

/**
 * create a top level menu item to sidebar contain post types
 */
function pmc_trailer_admin_menu() {
	if( !post_type_exists( 'pmc-quotes' ) ){
		add_menu_page( 'Mini Modules', 'Mini Modules', 'edit_posts', 'edit.php?post_type=pmc-trailer', '', plugins_url( 'images/mini-modules.png' , __FILE__ ), 40 );
	}
}

add_action( 'admin_menu', 'pmc_trailer_admin_menu', 5 );

//EOF
