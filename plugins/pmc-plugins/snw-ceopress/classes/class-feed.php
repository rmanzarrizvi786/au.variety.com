<?php
/**
 * Feed class will create the page to load the table
 */

namespace SNW\CEO_Press;

use \PMC\Global_Functions\Traits\Singleton;

class Feed {

	use Singleton;

	/**
	 * Constructor will create the menu item
	 */
	protected function __construct() {
		add_action( 'admin_menu', [ $this, 'ceo_add_pages' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'style_table' ] );
	}

	/**
	 * Menu item will allow us to load the page to display the table
	 */
	public function ceo_add_pages() {

		add_submenu_page(
			'tools.php',
			esc_html__( 'CEO Feed', 'snw-ceopress' ),
			esc_html__( 'CEO Feed', 'snw-ceopress' ),
			CEOPress::USER_CAPABILITY,
			'ceo-feed',
			[ $this, 'list_table_page' ]
		);

	}

	/**
	 * Display the list table page
	 *
	 * @return void
	 */
	public function list_table_page() {

		$feed_table = new Feed_Table();
		$feed_table->prepare_items();

		echo '<div class="wrap"><h2>' . esc_html__( 'CEO Content', 'snw-ceopress' ) . '</h2>';
		$feed_table->display();
		echo '</div>';

	}

	/**
	 * To enqueue style.
	 *
	 * @return void
	 */
	public function style_table() {

		$page = \PMC::filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$dir  = untrailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );

		if ( ! empty( $page ) && 'ceo-feed' === $page ) {
			wp_enqueue_script( 'ceo_feed', sprintf( '%s/assets/js/ceo_feed.js', $dir ), [ 'jquery' ], false, true );
			wp_enqueue_style( 'feed_css', sprintf( '%s/assets/css/ceo_feed.css', $dir ) );
		}

	}

}

//EOF
