<?php
// Tell Sonar not to worry about this file; PHPUnit doesn't run for this plugin anyway.
// @codeCoverageIgnoreFile

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Field_Override {

	use Singleton;

	protected static $_post_types = array('post');
	protected static $_fields = array();

	/**
	 * Initialize admin.
	 */
	public function init() {
		self::add_field('post_title', 'Title (Hed)');
		self::add_field('post_excerpt', 'Excerpt (Dek)', null, array(
			'field_type' => 'textarea'
		));

		self::register_meta();

		wp_register_script( 'field-overrides', plugins_url( 'js/script.js', __FILE__  ), array('jquery') );

		add_action( 'custom_metadata_manager_init_metadata', array($this, 'setup_admin') );
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_script( 'field-overrides' );
		});
	}

	/**
	 * Loop through the custom fields and generate metadata fields.
	 */
	public function setup_admin() {
		self::$_post_types = apply_filters( 'pmc_field_override_post_types',self::$_post_types);
		x_add_metadata_group('overrides', self::$_post_types, array(
			'label' => 'Field Overrides'
		));

		foreach (self::$_fields as $field => $options) {
			$post_types = $options['post_types'];

			if (!$post_types) {
				$post_types = self::$_post_types;
			}

			x_add_metadata_field('override_' . $field, $post_types, $options);
		}
	}

	/**
	 * Add a custom field.
	 *
	 * @param string $name
	 * @param string $title
	 * @param string|array $post_types
	 * @param array $options
	 */
	public static function add_field($name, $title, $post_types = null, array $options = array()) {
		$options = $options + array(
			'post_types' => $post_types,
			'group' => 'overrides',
			'label' => $title
		);

		self::$_fields[$name] = $options;
	}

	/**
	 * Add default post type.
	 *
	 * @param string|array $type
	 */
	public static function add_post_type($type) {
		self::$_post_types = array_unique(array_merge(self::$_post_types, (array) $type));
	}

	/**
	 * Register meta used in this plugin.
	 *
	 * @codeCoverageIgnore meta is being registered
	 */
	public static function register_meta() {
		// Property is initialized as an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		foreach ( array_keys( self::$_fields ) as $name ) {
			register_meta(
				'post',
				'override_' . $name,
				[
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				]
			);
		}
	}

}

/**
 * Return the override, or truncate the original field.
 */
function pmc_get_field_override($post, $field, $limit, $append, $strip_html) {
	$post = get_post($post);

	if (!$post) {
		return null;
	}

	// Return override if it exists
	if ($override = get_post_meta($post->ID, 'override_' . $field, true)) {
		return $override;
	}

	// Return null if not a true post object field
	if (!isset($post->{$field})) {
		return null;
	}

	switch ($field) {
		case 'post_title':		$string = get_the_title($post); break;
		case 'post_excerpt':
			$string = apply_filters( 'get_the_excerpt', $post->post_excerpt );
			break;
		default:				$string = $post->{$field}; break;
	}

	// Return the full string if we don't want to truncate
	if (!$limit) {
		return $string;
	}

	return pmc_truncate($string, $limit, $append, $strip_html);
}

/**
 * Return the hed (title override) if it exists, else fallback and truncate the title.
 */
function pmc_get_title($post = null, $limit = null, $append = 'ellipsis', $strip_html = false) {
	return pmc_get_field_override($post, 'post_title', $limit, $append, $strip_html);
}

/**
 * Return the dek (excerpt override) if it exists, else fallback and truncate the excerpt.
 */
function pmc_get_excerpt($post = null, $limit = null, $append = 'ellipsis', $strip_html = false) {
	return pmc_get_field_override($post, 'post_excerpt', $limit, $append, $strip_html);
}

/**
 * Initialize classes.
 */
add_action( 'init', array( PMC_Field_Override::get_instance(), 'init' ) );
