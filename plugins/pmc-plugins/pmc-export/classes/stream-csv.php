<?php
namespace PMC\Export;
use PMC\Global_Functions\Traits\Singleton;

/**
 * This abstract class define the basic common CSV stream for various data type
 *
 * Class Stream_Csv
 * @package PMC\Export
 */
abstract class Stream_Csv extends Stream {
	const ID                  = 'csv';
	public $first_row_headers = true;

	/**
	 * This class extend the abstract class Stream, the signature must match its definition
	 * Note: Do not apply return type to function to avoid warning errors
	 * @param int $page
	 * @return string
	 */
	public function data( int $page ) {

		// We want to create a temporary buffer and use fputcsv to generate the csv data string
		// @see https://www.php.net/manual/en/wrappers.php.php
		$fh = fopen( 'php://temp', 'r+' ); // phpcs:ignore

		if ( ( 1 === $page || 0 === $page ) && $this->first_row_headers ) {
			fputcsv( $fh, $this->get_headers() ); // phpcs:ignore
		}

		foreach ( $this->get_rows( $page ) as $row ) {

			// Loop through each value to ensure the correct value is put in correct column in CSV.
			$csv_row = [];
			foreach ( $this->get_headers() as $header ) {
				$csv_row[] = $row[ $header ];
			}

			fputcsv( $fh, $csv_row ); // phpcs:ignore
		}

		rewind( $fh );

		return (string) stream_get_contents( $fh );

	}

	public abstract function get_headers() : array;
	public abstract function get_rows( int $page ) : array;

}
