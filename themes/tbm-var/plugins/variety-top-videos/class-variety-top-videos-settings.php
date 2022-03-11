<?php

/**
 * Define Variety_Top_Videos_Settings class while extending Singleton
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Top_Videos_Settings {

	use Singleton;

	const OPTION_SETTINGS = 'variety_top_video_settings';
	const MENU_LABEL = 'Videos Playlist';

	protected $_capability = 'manage_options';

	/**
	 * Class constructor.
	 *
	 * Initialize variety_top_videos_capability_override at the admin init
	 */
	protected function __construct() {

		if (!is_admin()) {
			return;
		}

		//allow user capability override
		$this->_capability = apply_filters('variety_top_videos_capability_override', $this->_capability);

		add_action('admin_init', array($this, 'action_admin_init'));
		add_action('admin_menu', array($this, 'action_admin_menu'));
	}

	public function action_admin_init() {
		if ( ! current_user_can( $this->_capability ) ) {
			return;
		}
		$this->register_settings();
	}

	/**
	 * Ads an option page for the plugin
	 *
	 */
	public function action_admin_menu() {
		if ( ! is_admin() || ! current_user_can( $this->_capability ) ) {
			return;
		}
		add_options_page(self::MENU_LABEL, self::MENU_LABEL, $this->_capability, self::OPTION_SETTINGS, array($this, 'admin_page'));
	}

	protected function register_settings() {
		$settings = [
			[
				'id'      => 'active_channels',
				'title'   => __( 'Visible on Video Archive (Landing Page)', 'pmc-variety' ),
				'options' => [
					'hint' => __( '(comma delimited: formerly "Active Playlists.")', 'pmc-variety' ),
					'name' => 'active_channels',
					'type' => 'input',
				],
			],
			[
				'id'      => 'active_contenders_channels',
				'title'   => __( 'Visible on Contenders Playlist Page', 'pmc-variety' ),
				'options' => [
					'hint' => __( '(comma delimited: formerly "Active Playlists.")', 'pmc-variety' ),
					'name' => 'active_contenders_channels',
					'type' => 'input',
				],
			],
		];

		$section = self::OPTION_SETTINGS;
		$page = self::OPTION_SETTINGS;
		$option_name = $option_group = self::OPTION_SETTINGS;
		register_setting(
			$option_group,
			$option_name,
			array(
				'sanitize_callback' => array($this, 'sanitize_admin_options'),
			)
		);
		add_settings_section($section, false, '__return_false', $section);

		foreach ($settings as $item) {
			add_settings_field($item['id'], $item['title'], array($this, 'render_form_field'), $page, $section, (!empty($item['options']) ? $item['options'] : false));
		}

	}

	private $_options = false;

	public function options( $key = false, $default = false ) {
		if ( false === $this->_options ) {
			$this->_options = get_option( self::OPTION_SETTINGS );
		}
		if ( ! is_array( $this->_options ) ) {
			$this->_options = [
				'available_channels'         => [
					'news'       => __( 'News', 'pmc-variety' ),
					'trailers'   => __( 'Trailers', 'pmc-variety' ),
					'interviews' => __( 'Interviews', 'pmc-variety' ),
					'events'     => __( 'Events', 'pmc-variety' ),
				],
				'active_channels'            => [
					'news'       => __( 'News', 'pmc-variety' ),
					'trailers'   => __( 'Trailers', 'pmc-variety' ),
					'interviews' => __( 'Interviews', 'pmc-variety' ),
					'events'     => __( 'Events', 'pmc-variety' ),
				],
				'active_contenders_channels' => [
					'news'       => __( 'News', 'pmc-variety' ),
					'trailers'   => __( 'Trailers', 'pmc-variety' ),
					'interviews' => __( 'Interviews', 'pmc-variety' ),
					'events'     => __( 'Events', 'pmc-variety' ),
				],
				'featured_video'     => '',
			];
		}
		if ( false !== $key ) {
			return isset($this->_options[ $key ]) ? $this->_options[ $key ] : $default;
		}

		return $this->_options;
	}

	public static function get_option($key = false, $default = false) {
		$instance = self::get_instance();

		return $instance->options($key, $default);
	}

	protected function parse_channels($string) {
		$channels = array();
		$tokens = explode(',', $string);
		if (!empty($tokens)) {
			foreach ($tokens as $item) {
				$item = trim($item);
				if (!empty($item)) {
					$channels[ sanitize_title($item) ] = ucwords(sanitize_text_field($item));
				}
			}
		}

		return $channels;
	}

	protected function channels_to_string($channels, $delimiter = ',') {
		$string = '';
		if (!empty($channels) && is_array($channels)) {
			foreach ($channels as $value) {
				$string .= (!empty($string) ? $delimiter : '') . $value;
			}
		}

		return $string;
	}


	public function sanitize_admin_options( $options ) {

		$active_channels            = [];
		$active_contenders_channels = [];
		foreach ( $options as $key => $value ) {
			switch ( $key ) {
				case 'active_channels':
					if ( ! is_array( $value ) ) {
						$active_channels = $this->parse_channels( $value );
					} else {
						// value already in array, this mean value isn't coming from form submit directly
						$active_channels = $value;
					}
					break;
			}
			switch ( $key ) {
				case 'active_contenders_channels':
					if ( ! is_array( $value ) ) {
						$active_contenders_channels = $this->parse_channels( $value );
					} else {
						// value already in array, this mean value isn't coming from form submit directly
						$active_contenders_channels = $value;
					}
					break;
			}
		}

		return array(
			'active_channels'            => $active_channels,
			'active_contenders_channels' => $active_contenders_channels,
		);
	}

	public function render_form_field($args = null) {
		if (empty($args['name']) || empty($args['type'])) return;
		$id = !empty($args['id']) ? $args['id'] : $args['name'];
		$name = self::OPTION_SETTINGS . "[{$args['name']}]";
		$value = $this->options($args['name']);
		if ('featured_video' !== $args['name']) {
			$selected = $this->channels_to_string($value);
		} else {
			$selected = $value;
		}
		switch ($args['type']) {
			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ($args['data'] as $key => $value) {
					printf( '<option %1$s value="%2$s">%3$s</option>', selected( $selected, $key, false ), esc_attr( $key ), esc_html( $value ) );
				}
				echo '</select>';
				break;
			case 'input':
				printf( '<input id="%1$s" name="%2$s" value="%3$s" class="widefat">', esc_attr( $id ), esc_attr( $name ), esc_attr( $selected ) );
				break;
		}
		if (!empty($args['hint'])) {
			printf( '<p class="description">%1$s</p>', esc_html( $args['hint'] ) );
		}
	}

	public function admin_page() {
		$menu_label = self::MENU_LABEL;
		$options_setings = self::OPTION_SETTINGS;

		/**
		* @since 2017-09-01 Milind More CDWE-499
		*/
		echo \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-top-videos/templates/top-videos-admin.php',
			array(
				'menu_label'      => $menu_label,
				'options_setings' => $options_setings,
			)
		);

	}
}

//EOF
