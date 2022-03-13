<?php
/*
Plugin Name: PMC Disable Comments
Plugin URI: https://github.com/vickybiswas/pmc-disable-comments
Description: PMC Disable allows you to switch defaul commenting on/off for individual post types.
Version: 1.1
Author: Vicky Biswas
Author URI: http://www.pmc.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Disable_Comments {

	use Singleton;

	private static $blocked_types;

	/**
	 * Initialization function called when object is instantiated.
	 */
	protected function __construct() {
		add_action( 'admin_init', array( $this, 'admin' ) );
		add_action( 'wp_loaded', array( $this, 'setup_filters' ) );
		self::$blocked_types = (array) get_option( 'pmc-disable-comments-toggle' );
	}

	public function setup_filters(){
		if( !is_admin() ) {
			add_action( 'template_redirect', array( $this, 'load_comment_template' ) );
		}
	}

	/**
	 * Checks if current post type is in out black list
	 */
	function load_comment_template() {
		if( !is_singular() ){
			return;
		}

		if( in_array( get_post_type(), self::$blocked_types ) ) {
			add_filter( 'comments_template', array( $this, 'dummy_comments_template' ), 20 );
			// Remove comment-reply script for themes that include it indiscriminately
			wp_deregister_script( 'comment-reply' );
			// feed_links_extra inserts a comments RSS link
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}
	/**
	 * Returns dummy comment template path
	 * @return string
	 */
	function dummy_comments_template() {
		return dirname( __FILE__ ) . '/comments-template.php';
	}

	/**
	 * Add disable comment settings and register setting in options
	 *
	 * @param void.
	 * @return void.
	 */
	public static function admin() {
		self::$blocked_types = (array) get_option( 'pmc-disable-comments-toggle' );

		add_settings_section(
			'pmc-disable-comments',
			'Disable/Enable comments for the below:',
			'',
			'discussion'
		);
		add_settings_field(
			'pmc-disable-comments-toggle',
			'Choose the Post Types for which you want to switch comments off:',
			array('PMC_Disable_Comments', 'admin_settings'),
			'discussion',
			'pmc-disable-comments'
		);
		register_setting(
			'discussion',
			'pmc-disable-comments-toggle'
		);
	}

	/**
	 * Creates a form to toggle disable comments.
	 * Based upon the option valued saved it shows checked or uncheck
	 */
	public static function admin_settings( $args ) {
		$post_types=get_post_types('','objects');
		echo '<fieldset>';
		foreach ($post_types as $post_type_slug => $post_type ) {
			if ( post_type_supports( $post_type_slug, 'comments' ) ) {
				?>
				<label for="<?php echo esc_attr("pmc-disable-comments-$post_type_slug"); ?>">
					<input type="checkbox" id="<?php echo esc_attr("pmc-disable-comments-$post_type_slug"); ?>" name="pmc-disable-comments-toggle[]" value="<?php echo esc_attr("$post_type_slug"); ?>" <?php checked(in_array($post_type_slug, self::$blocked_types), true, false); ?> /> <?php echo esc_html($post_type->labels->singular_name); ?>
					<br />
				</label>
				<?php
			}
		}
		echo '</fieldset>';
	}
}

/**
 * Load PMC_Disabled_Comments plugin
 */
PMC_Disable_Comments::get_instance();

// EOF
