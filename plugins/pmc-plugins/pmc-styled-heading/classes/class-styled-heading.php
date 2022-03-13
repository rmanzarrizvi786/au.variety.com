<?php
/**
 * Styled_Heading class.
 *
 * @package pmc-styled-heading
 * @since 2018-5-15
 */

namespace PMC\Styled_Heading;

use Exception;
use Fieldmanager_Group;
use Fieldmanager_Select;
use Fieldmanager_TextField;
use Fieldmanager_Colorpicker;

class Styled_Heading {

	/**
	 * Unique prefix for this plugin's filters.
	 *
	 * @var string
	 */
	const FILTER_PREFIX = 'pmc_styled_heading_';

	/**
	 * IDs for styled headings already registerd.
	 *
	 * @var array
	 */
	public static $registered_ids = [];

	/**
	 * Registers an instance.
	 *
	 * @param string $name A name for the styled heading.
	 * @param string $id   A unique identifier for the field group.
	 */
	public static function register_styled_heading( $name = '', $id = '', $show_callback = null ) {

		if ( in_array( $id, (array) static::$registered_ids, true ) ) {
			throw new Exception( esc_html( sprintf(
				// translators: Class name, function name, and the name of the registered styled heading.
				__( '%1$s::%2$s() requires a unique name. "%3$s" is already registered.', 'pmc-styled-heading' ),
				__CLASS__,
				__FUNCTION__,
				$name
			) ) );
		}

		static::$registered_ids[] = $id;

		new Fields( $name, $id, $show_callback );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_style' ) );
		add_filter( 'safe_style_css', array( __CLASS__, 'allow_display_css' ) );

	}

	/**
	 * Provides HTML for the component.
	 *
	 * @param string $id The styled heading ID.
	 * @param int|null $post_id A \WP_Post ID.
	 * @return string Rendered HTML or an empty string.
	 */
	public static function get_styled_heading( $id, $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( empty( $post_id ) ) {
			return '';
		}

		$fields = Fields::get_fields( $id, $post_id );

		if ( empty( $fields ) || empty( $fields['text_lines'] ) || empty( $fields['text_lines']['text_line'] ) ) {
			return '';
		}

		$html = \PMC::render_template(
			PMC_STYLED_HEADING_PATH . 'templates/styled-heading.php',
			compact( 'fields' )
		);

		/**
		 * Filters a styled heading's HTML.
		 *
		 * @param string The HTML string.
		 * @param string The styled heading ID.
		 */
		return apply_filters( self::FILTER_PREFIX . 'styled_heading', $html, $id );

	}

	/**
	 * Adds `display` to allowed CSS properties.
	 *
	 * @param array $styles Allowed CSS properties.
	 * @return array Filtered CSS properties.
	 */
	public static function allow_display_css( $styles ) {

		if ( ! is_array( $styles ) ) {
			$styles = [];
		}

		$styles[] = 'display';
		return $styles;

	}

	/**
	 * Enqueue's the plugin's admin stylesheet.
	 */
	public static function enqueue_admin_style() {

		wp_enqueue_style( 'pmc-styled-heading', PMC_STYLED_HEADING_URL . 'assets/css/pmc-styled-heading.css' );

	}

	/**
	 * Echoes an inline style attribute.
	 *
	 * @param array $data An associative array possibly containing some CSS property-value pairs.
	 */
	public static function inline_style( $data = [] ) {

		$style = array_reduce(
			array_keys( (array) $data ),
			function( $style, $key ) use ( $data ) {

				if ( empty( $data[ $key ] ) ) {
					return $style;
				}

				switch ( $key ) {
					case 'display':
					case 'color':
					case 'font-weight':
					case 'background-color':
					case 'border-color':
					case 'text-align':
					case 'margin':
						return "$style$key:{$data[ $key ]};";

					case 'font-size':
					case 'letter-spacing':
					case 'border-width':
					case 'max-width':
					case 'padding-top':
					case 'padding-right':
					case 'padding-bottom':
					case 'padding-left':
					case 'line-height':
						return "$style$key:{$data[ $key ]}px;";

					default:
						return $style;
				}
			},
			''
		);

		if ( empty( $style ) ) {
			return;
		}

		// Set border to solid if border-color and border-width are both set.
		if ( ! empty( $data['border-color'] ) && ! empty( (int) $data['border-width'] ) ) {
			$style = "$style;border-style:solid;";
		}

		$style = esc_attr( $style );
		echo wp_kses_post( " style=\"$style\"" );
	}

}
