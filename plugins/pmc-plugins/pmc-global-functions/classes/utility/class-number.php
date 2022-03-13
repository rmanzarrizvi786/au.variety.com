<?php
/**
 * Class to work with numbers
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-11-04
 */

namespace PMC\Global_Functions\Utility;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

class Number {

	use Singleton;

	const ORDINAL_UPPER_LIMIT = 59;

	protected $_ones = [
		'eth',
		'first',
		'second',
		'third',
		'fourth',
		'fifth',
		'sixth',
		'seventh',
		'eighth',
		'ninth',
		'tenth',
		'eleventh',
		'twelfth',
		'thirteenth',
		'fourteenth',
		'fifteenth',
		'sixteenth',
		'seventeenth',
		'eighteenth',
		'nineteenth',
		'twentieth',
	];

	protected $_tens = [
		'',
		'',
		'twenty',
		'thirty',
		'forty',
		'fifty',
	];

	/**
	 * Method to convert an integer into ordinal number.
	 * Eg. 1 to first, 5 to fifth, 21 to twenty-first, etc.
	 *
	 * @param int $num
	 *
	 * @return string
	 */
	public function get_ordinal( int $num = 1, string $seperator = '-' ) : string {

		$ordinal_number = '';

		if ( 1 > $num || self::ORDINAL_UPPER_LIMIT < $num ) {
			return $ordinal_number;
		}

		if ( 20 >= $num && ! empty( $this->_ones[ $num ] ) ) {
			return $this->_ones[ $num ];
		}

		$ones = (int) substr( (string) $num, -1, 1 );
		$tens = (int) substr( (string) $num, -2, 1 );

		$ordinal_number = sprintf(
			'%1$s%3$s%2$s',
			$this->_tens[ $tens ],
			$this->_ones[ $ones ],
			$seperator
		);

		$ordinal_number = str_replace(
			sprintf( 'y%seth', $seperator ),
			'ieth',
			$ordinal_number
		);

		return $ordinal_number;

	}

	/**
	 * Method to get ordinal number equivalent for an integer to be displayed as a label.
	 *
	 * @param int $num
	 *
	 * @return string
	 */
	public function get_ordinal_as_label( int $num = 1 ) : string {

		$ordinal_number = $this->get_ordinal( $num, ' ' );

		return ucwords( $ordinal_number );

	}

}    //end class

//EOF
