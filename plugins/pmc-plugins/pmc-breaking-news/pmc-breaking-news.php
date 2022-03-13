<?php
/**
 * Breaking News Banner plugin
 */
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * Class PMC_Breaking_News
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Breaking_News {

	use Singleton;

	const KEY = '_pmc-breaking-news';

	/**
	 * Register hooks once from class constructor.
	 */
	protected function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_action( 'wp_ajax_save-breaking-news', array( $this, 'save_breaking_news' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue a script and style in the WordPress admin.
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script( self::KEY . '-admin', plugins_url( 'js/admin-breaking-news.js', __FILE__ ) );

		wp_enqueue_style( self::KEY . '-admin-css', plugins_url( 'css/admin-breaking-news.css', __FILE__ ) );

	}

	/**
	 * Enqueue a script in the WordPress front-end.
	 */
	public function enqueue_scripts() {

		$file_ext = '.js';

		if ( \PMC::is_production() ) {
			$file_ext = '.min.js';
		}

		$key = 'pmc-async-' . self::KEY;

		wp_enqueue_script( $key, plugins_url( 'js/breaking-news' . $file_ext, __FILE__ ), [ 'jquery' ], false, true );

		$data = $this->get_data();

		wp_localize_script(
			$key,
			'pmc_breaking_news_hash',
			[
				'hash' => $data['hash'],
			]
		);
	}

	/**
	 * Register PMC Breaking News dashboard widget.
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'pmc-breaking-news', 'PMC Breaking News', array(
				$this,
				'render_breaking_news_admin',
			)
		);
	}

	/**
	 * Get breaking news information.
	 *
	 * @return array $data Return breaking news information.
	 */
	public function get_data() {

		$data = pmc_get_option( self::KEY, self::KEY );

		if ( empty( $data ) || ! is_array( $data ) ) {
			$data = array();
		}

		$defaults = array(
			'title'    => '',
			'link'     => '',
			'post_id'  => '',
			'hash'     => '',
			'image_id' => '',
			'active'   => 'off',
		);

		$data = wp_parse_args( $data, $defaults );

		return $data;

	}

	/**
	 * Save breaking news information.
	 */
	public function save_breaking_news() {

		$nonce = '';
		if ( ! empty( $_POST['nonce'] ) ) { // @codingStandardsIgnoreLine
			$nonce = $_POST['nonce'];
		}

		if ( ! wp_verify_nonce( $nonce, 'save-breaking-news' ) ) {
			wp_die( 'Invalid Nonce' );
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( 'User does not have capability to save breaking news.' );
		}

		$site_url = wp_parse_url( home_url(), PHP_URL_HOST );

		$allow_domains = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
			$site_url,
		);

		$link = '';
		if ( ! empty( $_POST['link'] ) && wpcom_vip_is_valid_domain( $_POST['link'], $allow_domains ) ) { // @codingStandardsIgnoreLine
			$link = esc_url_raw( $_POST['link'] );
		}

		$post_id = '';
		if ( ! empty( $_POST['post_id'] ) ) { // @codingStandardsIgnoreLine
			$post_id = intval( $_POST['post_id'] );

			if ( 'publish' !== get_post_status( $post_id ) ) {
				$post_id = '';
			}
		}

		$image_id = '';
		if ( ! empty( $_POST['image_id'] ) ) { // @codingStandardsIgnoreLine
			$image_id = intval( $_POST['image_id'] );
		}

		$option = array(
			'title'    => sanitize_text_field( $_POST['title'] ), // @codingStandardsIgnoreLine
			'link'     => $link,
			'post_id'  => $post_id,
			'image_id' => $image_id,
		);

		if ( 0 < $post_id ) {
			$option['hash'] = $post_id;
		} else {
			$option['hash'] = substr( wp_hash( $option['title'] . $option['link'] ), 0, 10 );
		}

		$active = '';
		if ( ! empty( $_POST['active'] ) ) { // @codingStandardsIgnoreLine
			$active = sanitize_text_field( $_POST['active'] );
		}

		$option['active'] = $active;

		pmc_update_option( self::KEY, $option, self::KEY );

		wp_die( 'success' );
	}

	/**
	 * Display breaking news banner widget in dashboard.
	 */
	public function render_breaking_news_admin() {

		$data        = $this->get_data();
		$linked_data = '';

		if ( ! empty( $data['post_id'] ) ) {

			$post_id = intval( $data['post_id'] );

			$linked_data = wp_json_encode(
				array(
					'url'   => get_permalink( $post_id ),
					'id'    => $post_id,
					'title' => get_the_title( $post_id ),
				)
			);
		}

		$args = array(
			'linked_data' => $linked_data,
			'title'       => $data['title'],
			'url'         => $data['link'],
			'image_id'    => $data['image_id'],
			'active'      => $data['active'],
		);

		/**
		 * Filter to add image option for breaking news. Default is false.
		 */
		if ( apply_filters( 'pmc_breaking_news_image_option', false ) ) {

			$args['image_option'] = true;
			$args['image_thumb']  = ( ! empty( $data['image_id'] ) ) ? wp_get_attachment_image_url( $data['image_id'], 'thumbnail' ) : '';

		} else {

			$args['image_option'] = false;
			$args['image_thumb']  = '';

		}

		echo PMC::render_template( __DIR__ . '/templates/breaking-news-widget.php', $args ); // @codingStandardsIgnoreLine
	}

	/**
	 * Format breaking news banner data to display in a template.
	 *
	 * @return array $breaking_news_banner_args Breaking news banner data.
	 */
	public function get_breaking_news_banner_template_args() {
		$data = $this->get_data();

		if ( ! empty( $data['active'] ) && 'off' === $data['active'] ) {
			return;
		}

		$image_thumb = '';

		if ( ! empty( $data['post_id'] ) ) {

			$post_id = intval( $data['post_id'] );

			if ( ! empty( $data['title'] ) ) {
				$title = $data['title'];
			} else {
				$title = get_the_title( $post_id );
			}

			$link = get_permalink( $post_id );

		} else {

			$title = $data['title'];
			$link  = $data['link'];

		}

		/**
		 * Filter to display image for breaking news. Default is false.
		 */
		if ( apply_filters( 'pmc_breaking_news_image_option', false ) ) {

			/**
			 * Filter to change image size for breaking news.
			 */
			$image_size = apply_filters( 'pmc_breaking_news_image_size', 'thumbnail' );

			if ( ! empty( $data['post_id'] ) ) {

				if ( ! empty( $data['image_id'] ) ) {
					$image_thumb = wp_get_attachment_image_url( $data['image_id'], $image_size );
				} else {
					$image_thumb = get_the_post_thumbnail_url( intval( $data['post_id'] ), $image_size );
				}

			} else {

				if ( ! empty( $data['image_id'] ) ) {
					$image_thumb = wp_get_attachment_image_url( $data['image_id'], $image_size );
				}

			}

		}

		// No title, nothing to show.
		if ( empty( $title ) ) {
			return;
		}

		if ( empty( $link ) ) {
			$link = '#';
		}

		$breaking_news_banner_args = array(
			'title'       => $title,
			'link'        => $link,
			'image_thumb' => $image_thumb,
			'hash'        => $data['hash'],
			'span_class'  => ( empty( $image_thumb ) ) ? 'display-center' : '',
		);

		return $breaking_news_banner_args;
	}

	/**
	 * Display breaking news banner.
	 */
	public function render() {

		$breaking_news_banner_args = $this->get_breaking_news_banner_template_args();

		if ( ! empty( $breaking_news_banner_args ) ) {

			$default_template = __DIR__ . '/templates/breaking-news-banner.php';

			/**
			 * Allow to override breaking news banner template.
			 */
			$news_template = apply_filters( 'pmc_breaking_news_banner_template', $default_template );

			// If filtered template not exist then use default one.
			if ( ! file_exists( $news_template ) || validate_file( $news_template ) !== 0 ) {
				$news_template = $default_template;
			}

			echo PMC::render_template( $news_template, $breaking_news_banner_args ); // @codingStandardsIgnoreLine

		}

	}

}

PMC_Breaking_News::get_instance();
