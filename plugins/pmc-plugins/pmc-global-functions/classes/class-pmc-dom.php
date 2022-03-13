<?php

class PMC_DOM {
	private function __construct() {
	}

	/**
	 * @param string $content    A string containing the html text where paragraphs are marked as <p>..</p>
	 * @param array  $args
	 *
	 * @return mixed This function return a string with paragraphs data injected into content
	 */
	public static function inject_paragraph_content( $content, $args = array() ) {

		$default_args = array(
			'append'                                 => true,
			// Whether to append this content if there are not enough paragraphs
			'minimum_characters'                     => false,
			// Minumum number of characters before a new paragraph is injected, so that we don't break layout or formatting
			'autop'                                  => true,
			'should_append_after_tag'                => false,
			'should_apply_pmc_dom_insertions_filter' => true,
		);

		$args = wp_parse_args( $args, $default_args );

		if ( $args['autop'] ) {
			$display_content = wpautop( $content );
		} else {
			$display_content = $content;
		}
		$clean_content   = strip_tags( $content );

		// workaround for autoembed
		$display_content = preg_replace( '|^<p>(https?://[^\s"]+)</p>$|im','\1', $display_content );

		try {

			$doc           = new DOMDocument();
			$doc->encoding = 'UTF-8';

			//  Suppress error and warning
			$internal_errors = libxml_use_internal_errors( true );
			$result          = $doc->loadHTML( '<?xml encoding="UTF-8">' . $display_content );

			// restore to previous state
			libxml_use_internal_errors( $internal_errors );

			// Error bail
			if ( $result === false ) {
				return $content;
			}

			//get body
			$body = $doc->getElementsByTagName( 'body' )->item( 0 );

			// return the original content if we have a malformed body
			if ( ! ( is_object( $body ) && method_exists( $body, 'appendChild' ) ) ) {
				return $content;
			}
			//get paragraphs
			$xpath   = new DOMXPath( $doc );
			$query   = 'body/p'; // query all P elements which are direct descendant
			$p_nodes = $xpath->query( $query );

			$context         = is_feed() ? 'feed' : ( is_single() ? 'single' : 'river' );

			if ( ! empty( $args['paragraphs'] ) && is_array( $args['paragraphs'] ) ) {
				$paragraphs = $args['paragraphs'];
			} else {
				$paragraphs = array(); // array  $paragraphs An indexed paragraph position
			}
			//array ( 1 => array( 'text 1', 'text 2' ), 2 => array( 'text 2')...) where contents are to be inject into $contents

			if ( true === $args['should_apply_pmc_dom_insertions_filter'] ) {
				$paragraphs = apply_filters( 'pmc_dom_insertions', $paragraphs, $p_nodes->length, $context );
			}

			$paragraphs = array_filter(
				$paragraphs, function ( $item ) {

					if ( is_array( $item ) ) {
						return array_filter( $item );
					}

					if ( ! empty( $item ) ) {
						return true;
					}
				}
			);

			//No insertions specified
			if ( empty( $paragraphs ) ) {
				return $content;
			}

			$extras  = array();
			$pos_add = 0;
			$offset  = 0;


			if ( $args['minimum_characters'] ) {

				$min_chars = intval( $args['minimum_characters'] );

				/** Loop through each p node, get its text and search its position on $clean_content.
				 *  The offset will be first occurrence of node text + strlen(node text) since we are appending the html content.
				 * Once the offset is found grab the positioning of the p after which content needs to be appended.
				 */
				for ( $i = 0; $i < $p_nodes->length; ++$i ) {
					$p_node = $p_nodes->item( $i );
					$text   = $p_node->textContent;

					//if empty text then no need to get its offset
					if ( empty( $text ) ) {
						continue;
					}
					$offset = mb_strpos( $clean_content, $text, $offset ) + strlen( $text );

					if ( $offset >= $min_chars ) {
						$pos_add = $i;
						break;
					}
				}
			}

			foreach ( $paragraphs as $pos => $value ) {

				if ( empty( $value ) ) {
					continue;
				}

				if ( ! is_array( $value ) ) {
					continue;
				}

				$html_to_insert = implode( '', $value );

				// -1 here coz the $p_nodes->item is 0 based index, while the args passed as 1 based index.
				$new_pos = $pos_add + $pos - 1;

				// We need next node reference to append paragraph using insertBefore().
				if ( true === $args['should_append_after_tag'] ) {
					$new_pos++;
				}

				if ( $p_nodes->length >= $new_pos && $p_nodes->item( $new_pos ) ) {

					$content_to_inject = $doc->createDocumentFragment();
					$content_to_inject->appendXML( $html_to_insert );

					// Only inject the content if there is content to inject.
					if ( ! empty( $content_to_inject->textContent ) ) {

						if ( true === $args['should_append_after_tag'] ) {
							$body->insertBefore( $content_to_inject, $p_nodes->item( $new_pos ) );
						} else {
							$p_nodes->item( $new_pos )->appendChild( $content_to_inject );
						}

					}

				} else {
					if ( $args['append'] ) {
						$extras[] = $html_to_insert;
					}
				}

			}

			$display_content = self::domnode_get_innerhtml( $body ) . implode( '', $extras );

			return $display_content;

		} catch ( Exception $e ) {

			return $content;

		}
	}

	/**
	 * Retrieve the inner HTML of an individual DOMNode
	 * From http://php.net/manual/en/book.dom.php#105815
	 *
	 */
	public static function domnode_get_innerhtml( $el ) {
		if ( empty( $el ) ) {
			return $el;
		}
		$doc = new DOMDocument();
		$doc->appendChild( $doc->importNode( $el, true ) );
		$html = trim( $doc->saveHTML() );
		$tag  = $el->nodeName;

		return preg_replace( '@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html );
	}

	/**
	 * Can be used to initialize a DOM document consistently.
	 *
	 * @param $content
	 * @return bool|DOMDocument
	 */
	public static function load_dom_content( $content ){
		$doc           = new \DOMDocument();
		$doc->encoding = 'UTF-8';

		//  Suppress error and warning
		$internal_errors = libxml_use_internal_errors( true );
		$result          = $doc->loadHTML( '<?xml encoding="UTF-8">' . $content );

		// restore to previous state
		libxml_use_internal_errors( $internal_errors );

		// Error bail
		if ( $result === false ) {
			return false;
		}

		return $doc;
	}

	/**
	 * Gets link tags from body
	 *
	 * @param DOMDocument $doc
	 * @return bool|DOMNodeList
	 */
	public static function get_dom_links( DOMDocument $doc ){

		//get body
		$body = $doc->getElementsByTagName( 'body' )->item( 0 );

		// Bail on bad body
		if ( ! ( is_object( $body ) && method_exists( $body, 'appendChild' ) ) ) {
			return false;
		}

		//get links in paragraphs
		$xpath   = new \DOMXPath( $doc );
		$query   = 'body//a';
		$a_nodes = $xpath->query( $query );

		if( $a_nodes->length <= 0 ){
			return false;
		}

		return $a_nodes;


	}

	/**
	 * @param $tag
	 * @param $content
	 * @return DOMElement
	 */
	public static function create_dom_element( $tag, $content ){

		$doc           = new \DOMDocument();
		$doc->encoding = 'UTF-8';

		$new_a_node = $doc->createElement( $tag, $content);

		return $new_a_node;
	}


}
