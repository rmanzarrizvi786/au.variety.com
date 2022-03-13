<?php
/**
 * PMC Admin-specific functions.
 *
 * Generic helper functions that are only available within the admin.
 *
 * @version Corey Gilmore 2013-08-07 Add PMC_Admin_Notice class
 * @license PMC Proprietary.  All rights reserved
 *
 */


/**
 * Customizable function for displaying admin notices, warnings and errors.
 * Usage: PMC_Admin_Notice::add_admin_notice( $notice_text, $options );
 * See PMC_Admin_Notice::_get_default_options for details on $options.
 *
 * @todo Add option for a "modal" notice that's visible sitewide until any user dismisses it.
 * @todo Dismissing a dismissible, but non-snoozable notice should store an option in user attributes (or a transient?)
 *
 * @version Corey Gilmore 2013-08-07 Initial version
 * @author Corey Gilmore
 *
 */

class PMC_Admin_Notice {
	static $did_add_js = false;
	/**
	 * Add an admin notice
	 *
	 * @param string|array $class One or more classes to add to the class list.
	 * @param string|array $class One or more classes to add to the class list.
	 *
	 * @uses action::admin_notices
	 * @return string The name of the cookie used, if the message is dismissible.
	 *
	 */
	public static function add_admin_notice( $text, $opts = array() ) {
		$opts = static::_parse_and_sanitize_options( $opts );

		if( empty( $opts['notice_classes'] ) )
			$opts['notice_classes'] = array( 'updated' );

		$opts['notice_classes'][] = "pmc-admin-notice";

		$cookie_name = static::_register_notice( $text, $opts );
		return $cookie_name;
	}

	protected static function _get_default_options() {
		$default_opts = array(
			'dismissible'      => false,
			'required_cap'     => array(), // array of user capabilities required to see the message. Leave empty to show to all users.
			'snooze_time'      => 0, // time in seconds that the message can be hidden for. Set to 0 to permanantly dismiss the message.
			'notice_classes'   => array(), // must be an array
			'html_is_escaped'  => false, // set to true if passing HTML that has been properly sanitized.
			'priority'         => 10,
			'admin_message'    => false, // will be included in the output of site admins as an HTML comment
			'_cookie_name'     => false, // This should virtually never be set this - it's primarily useful when you only want a single 'type' of message shown
		);

		return $default_opts;
	}

	protected static function _parse_and_sanitize_options( $opts ) {
		$default_opts = static::_get_default_options();
		$opts = wp_parse_args( $opts, $default_opts );

		// Force certain items to be arrays
		if( !is_array( $opts['notice_classes'] ) )
			$opts['notice_classes'] = array();

		if( !is_array( $opts['required_cap'] ) )
			$opts['required_cap'] = array();

		$opts['priority'] = intval( $opts['priority'] );
		$opts['snooze_time'] = floatval( $opts['snooze_time'] );

		if( $opts['snooze_time'] >= 1 )
			$opts['dismissible'] = true;

		return $opts;
	}

	/**
	 * Add an admin notice
	 *
	 * @uses action::admin_notices
	 * @return false|string The name of the cookie used, if the message is dismissible. False if the message will not be shown (privs).
	 */
	protected static function _register_notice( $text, $opts ) {
		$cookie_name = true;
		$show_notice = true;

		if( !empty( $opts['required_cap'] ) && is_array( $opts['required_cap'] ) ) {
			$show_notice = false; // default to false until we have a matching capability
			foreach( $opts['required_cap'] as $capability ) {
				if( current_user_can( $capability ) ) {
					$show_notice = true;
					break;
				}
			}
		}
		if( $opts['dismissible'] ) {
			$cookie_name = static::_generate_cookie_name( $text, $opts );

			// don't show the notice if our cookie is set
			if( !empty( $_COOKIE[$cookie_name] ) )
				$show_notice = false;
		}

		if( $show_notice ) {
			$callback = static::_generate_notice_callback( $text, $opts, $cookie_name );
			add_action( 'admin_notices', $callback, intval( $opts['priority'] ) );
			if( !static::$did_add_js )
				static::_add_footer_js();

			return $cookie_name;
		}

		return false;
	}

	/**
	 * Generate a reasonably unique cookie name based on the text of the notice and the passed options.
	 *
	 */
	protected static function _generate_cookie_name( $text, $opts ) {
		if( !empty($opts['_cookie_name']) ) {
			$cookie_name = (string)$opts['_cookie_name'];
		} else {
			unset( $opts['_cookie_name'] );
			$cookie_name = 'pmc_notice_' . hash( "crc32b", $text . serialize( $opts ) );
		}

		return esc_attr($cookie_name);
	}

	/**
	 * Generate the callback function for displaying the admin notice
	 *
	 */
	protected static function _generate_notice_callback( $text, $opts, $cookie_name ) {
		$classname = get_called_class();

		$callback = function() use ( $text, $opts, $classname, $cookie_name ) {
			$one_month_in_seconds = 2629743;
			$snooze_time = (float)$opts['snooze_time'];
			$dismiss_time = 3 * $one_month_in_seconds;

			// beginning of HTML output
			echo '<div id="' . esc_attr( $cookie_name ) . '" class="' . esc_attr( join( ' ', $classname::get_notice_class( $opts['notice_classes'] ) ) ) . '">';
			if( $opts['html_is_escaped'] === true ) { // this value defaults to false
				echo $text;
			} else {
				echo '<p>' . esc_html( $text ) . '</p>';
			}

			if( !empty($opts['admin_message']) && is_string($opts['admin_message']) && current_user_can('manage_options') ) {
				$admin_message = $opts['admin_message'];

				// the message is output using esc_html inside of an HTML comment, but to be safe (safer) we will 'break' any HTMl comments that might be in the text
				$admin_message = str_replace( array('<!--', '-->', '<!>'), array('<#!--', '--#>', '<#!>'), $admin_message ); // ref: http://htmlhelp.com/reference/wilbur/misc/comment.html
				echo "\n<!--\n" . esc_html($admin_message) . "\n-->\n";
			}


			if( $opts['dismissible'] && $snooze_time >= 1 ) {
				printf( '<p><a class="pmc-notice-action pmc-notice-hide" data-cookie_name="%s" data-cookie_expire="%s" href="javascript:void(0);">%s</a></p>', esc_attr( $cookie_name ), $snooze_time, esc_html( __('Hide', 'pmc-admin-notice') ) );
			} elseif( $opts['dismissible'] && $snooze_time < 1 ) {
				printf( '<p><a class="pmc-notice-action pmc-notice-dismiss" data-cookie_name="%s" data-cookie_expire="%s" href="javascript:void(0);">%s</a></p>', esc_attr( $cookie_name ), $dismiss_time, esc_html( __('Dismiss', 'pmc-admin-notice') ) );
			}
			echo '</div>';
			// end of HTML output
		};

		return $callback;
	}

	/**
	 * Display the classes for the admin notice.
	 *
	 * @param string|array $class One or more classes to add to the class list.
	 *
	 * @see body_class()
	 *
	 */
	public static function notice_class( $class = '' ) {
	        // Separates classes with a single space, sanitizes each class with esc_attr and collates classes for notice element
	        echo 'class="' . join( ' ', static::get_notice_class( $class ) ) . '"';
	}

	/**
	 * Returns the classes for the admin notice as an array
	 *
	 *
	 * @param string|array $class One or more classes to add to the class list
	 * @return array Array of classes, sanitized with esc_attr
	 *
	 * @see esc_attr()
	 * @see get_body_class()
	 *
	 */
	public static function get_notice_class( $class = '' ) {
		$classes = array();

		if ( !empty( $class ) ) {
			if ( !is_array( $class ) )
				$class = preg_split( '#\s+#', $class );
			$classes = array_merge( $classes, $class );
		}

		$classes = array_map( 'esc_attr', $classes );

		return $classes;
	}

	protected static function _add_footer_js() {
		if( static::$did_add_js )
			return;

		add_action('admin_print_footer_scripts', array( get_called_class(), 'print_footer_js' ) );

		static::$did_add_js = true;
	}

	public static function print_footer_js() {
		?>
		<script>
		(function($,window){

			var pmc_notice_dismiss = function() {
				var cookie_name = jQuery(this).data('cookie_name');
				var cookie_expire = jQuery(this).data('cookie_expire');
				if( !cookie_name.length ) {
					return;
				}
				var expire_msec = parseInt( cookie_expire, 10 ) * 1000;
				document.cookie = cookie_name + "=1; expires=" + new Date( new Date().getTime() + expire_msec ).toUTCString() + "; path=/";
				jQuery(this).parents('.pmc-admin-notice').fadeOut();
			};

			jQuery( document ).ready( function() {
				jQuery('a.pmc-notice-action').on( 'click', pmc_notice_dismiss );
			});


		})(jQuery,this);
		</script>
		<?php
	}

} // End PMC_Admin_Notice


//EOF