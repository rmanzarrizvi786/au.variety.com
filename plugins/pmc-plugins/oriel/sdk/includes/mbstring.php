<?php

if ( ! function_exists( 'mb_chr' ) ) {

	function getEncoding( $encoding ) {
	    $internalEncoding = 'UTF-8';
		if ( null === $encoding ) {
			return $internalEncoding;
		}

		$encoding = strtoupper( $encoding );

		if ( '8BIT' === $encoding || 'BINARY' === $encoding ) {
			return 'CP850';
		}
		if ( 'UTF8' === $encoding ) {
			return 'UTF-8';
		}

		return $encoding;
	}

	function mb_ord( $s, $encoding = null ) {
		if ( 'UTF-8' !== $encoding = getEncoding( $encoding ) ) {
			$s = mb_convert_encoding( $s, 'UTF-8', $encoding );
		}

		$code = ( $s = unpack( 'C*', substr( $s, 0, 4 ) ) ) ? $s[1] : 0;
		if ( 0xF0 <= $code ) {
			return ( ( $code - 0xF0 ) << 18 ) + ( ( $s[2] - 0x80 ) << 12 ) + ( ( $s[3] - 0x80 ) << 6 ) + $s[4] - 0x80;
		}
		if ( 0xE0 <= $code ) {
			return ( ( $code - 0xE0 ) << 12 ) + ( ( $s[2] - 0x80 ) << 6 ) + $s[3] - 0x80;
		}
		if ( 0xC0 <= $code ) {
			return ( ( $code - 0xC0 ) << 6 ) + $s[2] - 0x80;
		}

		return $code;
	}
	function mb_chr( $code, $encoding = null ) {
		if ( 0x80 > $code %= 0x200000 ) {
			$s = \chr( $code );
		} elseif ( 0x800 > $code ) {
			$s = \chr( 0xC0 | $code >> 6 ) . \chr( 0x80 | $code & 0x3F );
		} elseif ( 0x10000 > $code ) {
			$s = \chr( 0xE0 | $code >> 12 ) . \chr( 0x80 | $code >> 6 & 0x3F ) . \chr( 0x80 | $code & 0x3F );
		} else {
			$s = \chr( 0xF0 | $code >> 18 ) . \chr( 0x80 | $code >> 12 & 0x3F ) . \chr( 0x80 | $code >> 6 & 0x3F ) . \chr( 0x80 | $code & 0x3F );
		}

		if ( 'UTF-8' !== $encoding = getEncoding( $encoding ) ) {
			$s = mb_convert_encoding( $s, $encoding, 'UTF-8' );
		}

		return $s;
	}
	function mb_scrub( $s, $enc = null ) {
		$enc = null === $enc ? mb_internal_encoding() : $enc;
		return mb_convert_encoding( $s, $enc, $enc );
	}
}
