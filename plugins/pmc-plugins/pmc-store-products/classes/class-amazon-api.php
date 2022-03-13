<?php

namespace PMC\Store_Products;

class Amazon_Api {

	public $url = null;

	private $_access_key        = null;
	private $_secret_key        = null;
	private $_associate_tag     = null;
	private $_region_name       = 'us-east-1';
	private $_service_name      = 'ProductAdvertisingAPI';
	private $_aws_headers       = [];
	private $_payload           = '';
	private $_host              = 'webservices.amazon.com';
	private $_path              = '/paapi5/getitems';
	private $_http_method       = 'POST';
	private $_hmac_algorithm    = 'AWS4-HMAC-SHA256';
	private $_aws4_request      = 'aws4_request';
	private $_str_signed_header = null;
	private $_x_amz_date        = null;
	private $_current_date      = null;
	private $_operator          = null;

	/**
	 * Amazon_Api constructor.
	 *
	 * @param array $asin
	 * @param string $associate_tag
	 * @param array  $resources
	 * @param string $operator
	 */
	public function __construct( array $asin, string $associate_tag, array $resources, string $operator = 'GetItems' ) {

		$this->_access_key    = PMC_SP_AMAZON_API_ACCESS_KEY;
		$this->_secret_key    = PMC_SP_AMAZON_API_ACCESS_SECRET;
		$this->_associate_tag = $associate_tag;
		$this->_x_amz_date    = $this->_get_time_stamp();
		$this->_current_date  = $this->_get_date();
		$this->_operator      = $operator;
		$this->url            = sprintf(
			'https://%s/%s',
			untrailingslashit( $this->_host ),
			\PMC::unleadingslashit( $this->_path )
		);

		$this->_set_payload( $asin, $resources );

	}

	/**
	 * Create payload to pass to API request.
	 *
	 * @param array $asin
	 * @param array  $resources
	 */
	protected function _set_payload( array $asin, array $resources ) : void {

		// Remove empty values.
		$asin = array_unique( array_values( array_filter( $asin ) ) );

		$payload = [
			'PartnerTag'            => $this->_associate_tag,
			'PartnerType'           => 'Associates',
			'LanguagesOfPreference' => [ 'en_US' ],
			'Marketplace'           => 'www.amazon.com',
			'Resources'             => $resources,
		];

		if ( 'GetVariations' === $this->_operator ) {
			$payload['ASIN'] = reset( $asin );
		} else {
			$payload['ItemIds']    = $asin;
			$payload['ItemIdType'] = 'ASIN';
		}

		ksort( $payload );

		$this->_payload = wp_json_encode( $payload );

	}

	/**
	 * Prepare canonical request string.
	 *
	 * @return string
	 */
	private function _prepare_canonical_request() : string {

		$canonical_url  = [
			$this->_http_method,
			$this->_path,
			'',
		];
		$signed_headers = [];

		foreach ( $this->_aws_headers as $key => $value ) {
			$signed_headers[] = strtolower( $key ) . ';';
			$canonical_url[]  = strtolower( $key ) . ':' . $value;
		}

		$canonical_url[]          = '';
		$this->_str_signed_header = substr( implode( '', $signed_headers ), 0, -1 );
		$canonical_url[]          = $this->_str_signed_header;
		$canonical_url[]          = $this->_generate_hex( $this->_payload );

		return implode( PHP_EOL, $canonical_url );

	}

	/**
	 * Prepare string for signature.
	 *
	 * @param string $canonical_url
	 *
	 * @return string
	 */
	private function _prepare_string_to_sign( string $canonical_url ) : string {

		$string_to_sign = [];

		/* Add algorithm designation, followed by a newline character. */
		$string_to_sign[] = $this->_hmac_algorithm;

		/* Append the request date value, followed by a newline character. */
		$string_to_sign[] = $this->_x_amz_date;

		/* Append the credential scope value, followed by a newline character. */
		$string_to_sign[] = sprintf(
			'%s/%s/%s/%s',
			$this->_current_date,
			$this->_region_name,
			$this->_service_name,
			$this->_aws4_request
		);

		/* Append the hash of the canonical request */
		$string_to_sign[] = $this->_generate_hex( $canonical_url );

		return implode( PHP_EOL, $string_to_sign );

	}

	/**
	 * Calculate the signature for API request.
	 *
	 * @param string $string_to_sign
	 *
	 * @return string
	 */
	private function _calculate_signature( string $string_to_sign ) : string {

		/* Derive signing key */
		$signature_key = $this->_get_signature_key( $this->_secret_key, $this->_current_date, $this->_region_name, $this->_service_name );

		/* Calculate the signature. */
		$signature = hash_hmac( 'sha256', $string_to_sign, $signature_key, true );

		/* Encode signature (byte[]) to Hex */
		return strtolower( bin2hex( $signature ) );

	}

	/**
	 * Returns payload for body of request.
	 *
	 * @return string
	 */
	public function get_payload() : string {

		return $this->_payload;

	}

	/**
	 * Returns headers for request.
	 *
	 * @return array
	 */
	public function get_headers() : array {

		$this->_aws_headers['Content-Encoding'] = 'amz-1.0';
		$this->_aws_headers['Content-Type']     = 'application/json';
		$this->_aws_headers['Host']             = $this->_host;
		$this->_aws_headers['X-Amz-Date']       = $this->_x_amz_date;
		$this->_aws_headers['X-Amz-Target']     = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $this->_operator;

		/* Sort headers */
		ksort( $this->_aws_headers );

		/* Create a Canonical Request for Signature Version 4. */
		$canonical_url = $this->_prepare_canonical_request();

		/* Create a String to Sign for Signature Version 4. */
		$string_to_sign = $this->_prepare_string_to_sign( $canonical_url );

		/* Calculate the AWS Signature Version 4. */
		$signature = $this->_calculate_signature( $string_to_sign );

		if ( ! empty( $signature ) ) {
			$this->_aws_headers['Authorization'] = $this->_build_authorization_string( $signature );
		}

		return $this->_aws_headers;

	}

	/**
	 * Builds string for request authorization.
	 *
	 * @param string $str_signature
	 *
	 * @return string
	 */
	private function _build_authorization_string( string $str_signature ) : string {

		return sprintf(
			'%s Credential=%s/%s/%s/%s/%s,SignedHeaders=%s,Signature=%s',
			$this->_hmac_algorithm,
			$this->_access_key,
			$this->_current_date,
			$this->_region_name,
			$this->_service_name,
			$this->_aws4_request,
			$this->_str_signed_header,
			$str_signature
		);

	}

	/**
	 * Generates a hash.
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	private function _generate_hex( string $data ) : string {

		return hash( 'sha256', $data );

	}

	/**
	 * Gets signature key.
	 *
	 * @param string $key
	 * @param string $date
	 * @param string $region
	 * @param string $service
	 *
	 * @return string
	 */
	private function _get_signature_key( string $key, string $date, string $region, string $service ) : string {

		$k_secret  = 'AWS4' . $key;
		$k_date    = hash_hmac( 'sha256', $date, $k_secret, true );
		$k_region  = hash_hmac( 'sha256', $region, $k_date, true );
		$k_service = hash_hmac( 'sha256', $service, $k_region, true );

		return hash_hmac( 'sha256', $this->_aws4_request, $k_service, true );

	}

	/**
	 * Get time stamp.
	 *
	 * @return string
	 */
	private function _get_time_stamp() : string {

		return gmdate( 'Ymd\THis\Z' );

	}

	/**
	 * Get current date string.
	 *
	 * @return string
	 */
	private function _get_date() : string {

		return gmdate( 'Ymd' );

	}

}

//EOF
