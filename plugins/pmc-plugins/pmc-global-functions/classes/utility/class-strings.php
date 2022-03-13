<?php
/**
 * Class to work with strings
 *
 * @author Kelin Chauhan <kelin.chauhan@rtcamp.com>
 *
 * @since  2020-09-16
 */

namespace PMC\Global_Functions\Utility;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

class Strings {

	use Singleton;

	/**
	 * Function for converting a string of unknown encoding to a utf-8 string.
	 * Code is referenced from http://php.net/manual/en/function.iconv.php
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function ascii_to_utf8( $text ) : string {

		$limit = 1000; // maximum number of characters to process at once.



		if ( is_string( $text ) ) {
			// Includes combinations of characters that present as a single glyph
			$len     = mb_strlen( $text );
			$strings = array();
			if ( $len >= $limit ) {

				for ( $start = 0; $start < $len; $start += $limit ) {
					$strings[] = mb_substr( $text, $start, $limit );
				}

			} else {

				$strings = array( $text );
			}

			// Process the strings in chunks of $limit.
			foreach ( $strings as $index => $string ) {
				$strings[ $index ] = preg_replace_callback( '/\X/u', array( $this, '_char_to_utf8' ), $string );
			}

			$text = implode( '', $strings );

		}



		return $text;
	} // function

	/**
	 * Call back for `preg_replace_callback` to convert a single chart to  utf-8 char.
	 *
	 * @param array $text Array containing a single character.
	 */
	private function _char_to_utf8( $text ) {
		if ( is_array( $text ) && 1 === count( $text ) && is_string( $text[0] ) ) {
			/**
			 * BR-1096: this was sending question marks as replacement text in newsletters
			 * instead of substituting expected fallbacks.
			 * Created in PASE-612
			 */

			// IGNORE characters that can't be TRANSLITerated to UTF
			$text = iconv( mb_detect_encoding( $text[0] ), 'UTF-8//TRANSLIT', $text[0] );

			// The documentation says that iconv() returns false on failure but it sometimes can return ''.
			if ( '' === $text || ! is_string( $text ) ) {

				// Test case is written for this scenario however for some reason it doesn't cover during pipeline.
				$text = ''; // @codeCoverageIgnore

			} elseif ( preg_match( '/\w/', $text ) ) {      // If the text contains any letters

				$text = preg_replace( '/\W+/', '', $text ); // ...then remove all non-letters
			}

		} else {  // $text was not a string

			$text = '';
		}

		return $text;
	}

}    //end class

//EOF
