<?php

/**
 * Class which adds an Input field type password using PMC_Crypto
 *
 * This CAP field adds an input type password, when you save the value going to be encrypted
 * using PMC_Crypto
 */

// @codeCoverageIgnoreStart Ignoring coverage for this because this cannot be reasonably tested at present.
if ( ! class_exists( '\CheezCapTextOption', false ) ) {
	return;
}
// @codeCoverageIgnoreEnd

class PMC_CheezCapSecureInput extends CheezCapTextOption {

	const DEFAULT_ENCRYPTION_KEY = '0f9ca14170b13b1599d9629d073787d5';
	const CIPHER                 = 'AES-128-CBC';
	const KEY_LENGTH             = 16;

	private $_aes_key;

	function __construct( $_name, $_desc, $_id, $_std = '', $_use_text_area = false, $_validation_cb = false, $_aes_key = null ) {
		parent::__construct( $_name, $_desc, $_id, $_std, $_use_text_area, $_validation_cb );
		$this->_aes_key = is_null( $_aes_key ) ? self::DEFAULT_ENCRYPTION_KEY : $_aes_key;
	}

	/**
	 * Write out the HTML field which is displayed in the group in wp-admin
	 */
	function write_html() {

		$std_text = $this->std;

		$std_text_option = get_option( $this->id );

		if ( ! empty( $std_text_option ) ) {
			$std_text = $std_text_option;
		}

		?>
		<tr valign="top">
			<th scope="row"><label for="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_html( $this->name . ':' ); ?></label>
			</th>
			<?php
			$comment_width = 2;
			if ( $this->useTextArea ) :
				$comment_width = 1;
				?>
			<td rowspan="2">
				<textarea style="width:100%;height:100%;" name="<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>"><?php echo esc_textarea( $std_text ); ?></textarea>
				<?php else : ?>
			<td>
				<input name="<?php echo esc_attr( $this->id ); ?>" id="<?php echo esc_attr( $this->id ); ?>" type="password" value="<?php echo esc_attr( $std_text ); ?>" size="40" />
				<?php endif; ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="<?php echo absint( $comment_width ); ?>">
				<label for="<?php echo esc_attr( $this->id ); ?>">
					<small><?php echo esc_html( $this->desc ); ?></small>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<hr />
			</td>
		</tr>
		<?php
	}

	/**
	 * Encrypt and save the value
	 *
	 * @param $value
	 *
	 * @throws Exception
	 */
	function save( $value ) {

		if ( 0 === strlen( $value ) ) {
			parent::save( '' );

			return;
		}

		// First check if value hasn't changed and isn't encrypted
		$original_value = get_option( $this->id );

		if ( ! empty( $original_value ) && $original_value === $value &&
			! is_null( json_decode( base64_decode( $original_value ), true ) ) ) {
			return;
		}

		$value = $this->sanitize( $value );

		$iv = random_bytes( openssl_cipher_iv_length( self::CIPHER ) );

		$ciphertext = openssl_encrypt( $value, self::CIPHER, $this->_aes_key, 0, $iv );

		$iv = base64_encode( $iv );

		// Create mac to detect when ciphertext gets altered
		$mac = $this->hash( $iv, $ciphertext );

		$json = wp_json_encode( compact( 'iv', 'ciphertext', 'mac' ), JSON_UNESCAPED_SLASHES );

		parent::save( base64_encode( $json ) );
	}

	/**
	 * Get decrypted value - return raw value if decryption fails
	 */
	function get() {

		$value = get_option( $this->id );

		if ( empty( $value ) ) {
			return $this->std;
		}

		$payload = json_decode( base64_decode( $value ), true );

		if ( is_null( $payload ) || ! $this->valid_payload( $payload ) || ! $this->valid_mac( $payload ) ) {
			return $value;
		}

		$iv = base64_decode( $payload['iv'] );

		$decrypted = openssl_decrypt( $payload['ciphertext'], self::CIPHER, $this->_aes_key, 0, $iv );

		return ( false === $decrypted ) ? $value : $this->sanitize( $decrypted );
	}

	/**
	 * Create a MAC for the given value.
	 *
	 * @param string $iv
	 * @param mixed  $value
	 *
	 * @return string
	 */
	protected function hash( $iv, $value ) {
		return hash_hmac( 'sha256', $iv . $value, $this->_aes_key );
	}

	/**
	 * Verify that the encryption payload is valid.
	 *
	 * @param mixed $payload
	 *
	 * @return bool
	 */
	protected function valid_payload( $payload ) {
		return is_array( $payload ) && isset( $payload['iv'], $payload['ciphertext'], $payload['mac'] ) &&
			strlen( base64_decode( $payload['iv'], true ) ) === self::KEY_LENGTH;
	}

	/**
	 * Determine if the MAC for the given payload is valid.
	 *
	 * @param array $payload
	 *
	 * @return bool
	 */
	protected function valid_mac( array $payload ) {
		$calculated = $this->calculate_mac( $payload, $bytes = random_bytes( self::KEY_LENGTH ) );

		return hash_equals(
			hash_hmac( 'sha256', $payload['mac'], $bytes, true ), $calculated
		);
	}

	/**
	 * Calculate the hash of the given payload.
	 *
	 * @param array  $payload
	 * @param string $bytes
	 *
	 * @return string
	 */
	protected function calculate_mac( $payload, $bytes ) {
		return hash_hmac( 'sha256', $this->hash( $payload['iv'], $payload['ciphertext'] ), $bytes, true );
	}
}
