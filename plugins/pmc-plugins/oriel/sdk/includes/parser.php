<?php

namespace Oriel;


class Parser {

	/**
	 * @var string Regular expression to find all html tags.
	 */
	private static $findAllTags = '/<([^\s>]+)([^>]*)>/i';


	public static function insertScriptAndObfuscate( $key, $html, $script, $settings, $loaderSettings ) {
		// Unless a `<head>` is detected first, then we aren't encoding images,
		// because we might be dealing with a partial HTML response for which we
		// are not inserting Oriel's script
		$hasHead = false;
		// Unless we are inside `<body>` we aren't obfuscating images, since it
		// means that the `<img>` tag we're noticing are in `<head>` or something
		$insideBody = false;
		// Keeping track of seen `<script>` tags to avoid obfuscating images
		// that are built via JavaScript
		$insideScript = 0;
		//Used for filtering only the images that should be obfuscated.
		$imageFilter = new CustomFilter( $settings->image_filter );
		// Used for keeping track of seen `<picture>` tags to avoid obfuscating `<source>`
		// tags that are inside `<video>` or `<audio>` tags.
		$insidePictureTag = false;

		$loaderSettings['ko1'] = $key;
		$loaderSettings['ll'] = $settings->lazy_load_images;
		$preparedScript = $loader = Crypto::inject_settings_in_loader( json_encode( $loaderSettings, JSON_NUMERIC_CHECK ), $script );

		return preg_replace_callback(
			self::$findAllTags,
			function ( $matches ) use ( $settings, $preparedScript, $key, &$insideBody, &$insideScript, &$insidePictureTag, &$hasHead, $imageFilter ) {
				$wholeTag = $matches[0];
				$tagName = $matches[1];
				$properties = $matches[2];
				$findAttributeToObfuscate = '/(\s+)(' . join( '|', $settings->attributes_to_obfuscate ) . ")\s*=\s*(['\"])([^\"']+)\\3/i";

				switch ( mb_strtolower( $tagName ) ) {
					case 'head':
						$hasHead = true;
						return $wholeTag . "\n    <script>" . $preparedScript . '</script>';
					case 'img':
						// if the `obfuscate_image_sources` feature is not active, then obfuscate the
						// image only if it is inside a `picture` tag.
						if ( ! $settings->obfuscate_image_sources && ! $insidePictureTag ) {
							return $wholeTag;
						}
						// do not obfuscate images from scripts
						if ( ! $hasHead || ! $insideBody || 0 !== $insideScript ) {
							return $wholeTag;
						}
						// do not obfuscate images inside picture tags if the picture tag should be ignored.
						if ( $insidePictureTag && ! $settings->obfuscate_picture_sources ) {
							return $wholeTag;
						}
						// if the filter does not match the image, obfuscate it only if it is inside a
						// picture tag that should be obfuscated.
						if ( ! $imageFilter->matches( $wholeTag ) ) {

							if ( $insidePictureTag ) {
								return self::obfuscateTagSrc(
									$key, $wholeTag, $settings->image_source_placeholder,
									$findAttributeToObfuscate
								);
							}

							return $wholeTag;
						}

						return self::obfuscateTagSrc(
							$key, $wholeTag, $settings->image_source_placeholder,
							$findAttributeToObfuscate
						);
					case 'body':
						if ( 0 === $insideScript ) {
							$insideBody = true;
						}

						if ( isset( $settings->noscript_beacon_url ) ) {
							return $wholeTag . "\n<noscript><img src='" . $settings->noscript_beacon_url . "'/></noscript>";
						}

						return $wholeTag;

					case 'picture':
						$insidePictureTag = true;

						return $wholeTag;

					case '/picture':
						$insidePictureTag = false;

						return $wholeTag;

					case 'source':
						// do not obfuscate pictures if the `obfuscate_picture_sources` feature is deactivated.
						if ( ! $settings->obfuscate_picture_sources ) {
							return $wholeTag;
						}

						// do not obfuscate picture sources from scripts
						if ( ! $hasHead || ! $insideBody || 0 !== $insideScript ) {
							return $wholeTag;
						}

						// do not `source` tags if they are not inside a `picture` tag
						if ( ! $insidePictureTag ) {
							return $wholeTag;
						}

						return self::obfuscateTagSrc(
							$key, $wholeTag, $settings->image_source_placeholder,
							$findAttributeToObfuscate
						);

					case 'script':
						$contents = isset( $properties ) ? strtolower( trim( $properties ) ) : '';
						$insideScript += strpos( $contents, 'src' ) === false && ! ( substr( $contents, -strlen( '/>' ) ) === '/>' ) ? 1 : 0;
						return $wholeTag;

					case '/script':
						$insideScript = $insideScript > 0 ? $insideScript - 1 : 0;
						return $wholeTag;

					default:
						return $wholeTag;
				}
			}, $html
		);
	}

	private static function obfuscateTagSrc( $key, $tag, $imageSourcePlaceholder, $filter ) {
		return preg_replace_callback(
			$filter, function ( $matches ) use ( $key, $imageSourcePlaceholder ) {
				$spaces = $matches[1];
				$propertyName = strtolower( trim( $matches[2] ) );
				$quote = $matches[3];
				$url = $key . $matches[4];
				$encoded = Crypto::obfuscateTextByModulo( $key, $url );

				if ( 'src' === $propertyName || 'srcset' === $propertyName ) {
					return $spaces . $propertyName . '=' . $quote . $imageSourcePlaceholder . $quote . ' ' .
					'data-5' . substr( $propertyName, 1 ) . '=' . $quote . $encoded . $quote;
				}

				return $spaces . $propertyName . '=' . $quote . $encoded . $quote;
			}, $tag
		);
	}
}


class CustomFilter {


	private $filterExpression;

	function __construct( $filterExpression ) {
		if ( isset( $filterExpression ) && trim( $filterExpression ) !== '' ) {
			$this->filterExpression = '/' . $filterExpression . '/i';
		}
	}

	public function matches( $toMatch ) {
		if ( ! isset( $this->filterExpression ) ) {
			return true;
		}

		if ( preg_match( $this->filterExpression, $toMatch ) ) {
			return true;
		}

		return false;
	}
}
