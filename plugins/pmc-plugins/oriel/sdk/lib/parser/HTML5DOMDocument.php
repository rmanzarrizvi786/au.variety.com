<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright 2016, Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 *
 */
class HTML5DOMDocument extends \DOMDocument {


	/**
	 * Returns the first element matching the selector
	 *
	 * @param  string $selector CSS query selector
	 * @return \DOMElement|null The result DOMElement or null if not found
	 */
	private function internalQuerySelector( $selector ) {
		$result = $this->internalQuerySelectorAll( $selector, 1 );
		return $result->item( 0 );
	}

	/**
	 * Returns a list of document elements matching the selector
	 *
	 * @param  string   $selector       CSS query selector
	 * @param  int|null $preferredLimit Preferred maximum number of elements to return
	 * @return DOMNodeList Returns a list of DOMElements matching the criteria
	 * @throws \InvalidArgumentException
	 */
	private function internalQuerySelectorAll( $selector, $preferredLimit = null ) {
		if ( ! is_string( $selector ) ) {
			throw new \InvalidArgumentException( 'The selector argument must be of type string' );
		}

		$getElementById = function ( $id ) {
			if ( $this instanceof \DOMDocument ) {
				return $this->getElementById( $id );
			} else {
				$elements = $this->getElementsByTagName( '*' );
				foreach ( $elements as $element ) {
					if ( $element->getAttribute( 'id' ) === $id ) {
						return $element;
					}
				}
			}
			return null;
		};

		if ( $selector === '*' ) { // all
			return $this->getElementsByTagName( '*' );
		} elseif ( preg_match( '/^[a-z0-9]+$/', $selector ) === 1 ) { // tagname
			return $this->getElementsByTagName( $selector );
		} elseif ( preg_match( '/^[a-z0-9]+#.+$/', $selector ) === 1 ) { // tagname#id
			$parts   = explode( '#', $selector, 2 );
			$element = $getElementById( $parts[1] );
			if ( $element && $element->tagName === $parts[0] ) {
				$arr[] = $element;
				return new \IvoPetkov\HTML5DOMNodeList( $arr );
			}
			return new \IvoPetkov\HTML5DOMNodeList();
		} elseif ( preg_match( '/^[a-z0-9]+\..+$/', $selector ) === 1 ) { // tagname.classname
			$parts         = explode( '.', $selector, 2 );
			$result        = array();
			$selectorClass = $parts[1];
			$elements      = $this->getElementsByTagName( $parts[0] );
			foreach ( $elements as $element ) {
				$classAttribute = $element->getAttribute( 'class' );
				if ( $classAttribute === $selectorClass || strpos( $classAttribute, $selectorClass . ' ' ) === 0 || substr( $classAttribute, -( strlen( $selectorClass ) + 1 ) ) === ' ' . $selectorClass || strpos( $classAttribute, ' ' . $selectorClass . ' ' ) !== false ) {
					$result[] = $element;
					if ( $preferredLimit !== null && sizeof( $result ) >= $preferredLimit ) {
						break;
					}
				}
			}
			return new \IvoPetkov\HTML5DOMNodeList( $result );
		} elseif ( substr( $selector, 0, 1 ) === '#' ) { // #id
			$element = $getElementById( substr( $selector, 1 ) );
			$arr[]   = $element;
			return $element !== null ? new \IvoPetkov\HTML5DOMNodeList( $arr ) : new \IvoPetkov\HTML5DOMNodeList();
		} elseif ( substr( $selector, 0, 1 ) === '.' ) { // .classname
			$elements      = $this->getElementsByTagName( '*' );
			$result        = array();
			$selectorClass = substr( $selector, 1 );
			foreach ( $elements as $element ) {
				$classAttribute = $element->getAttribute( 'class' );
				if ( $classAttribute === $selectorClass || strpos( $classAttribute, $selectorClass . ' ' ) === 0 || substr( $classAttribute, -( strlen( $selectorClass ) + 1 ) ) === ' ' . $selectorClass || strpos( $classAttribute, ' ' . $selectorClass . ' ' ) !== false ) {
					$result[] = $element;
					if ( $preferredLimit !== null && sizeof( $result ) >= $preferredLimit ) {
						break;
					}
				}
			}
			return new \IvoPetkov\HTML5DOMNodeList( $result );
		}
		throw new \InvalidArgumentException( 'Unsupported selector' );
	}

	/**
	 * Indicates whether an HTML code is loaded
	 *
	 * @var boolean
	 */
	private $loaded = false;

	/**
	 * Creates a new \IvoPetkov\HTML5DOMDocument object
	 *
	 * @param string $version  The version number of the document as part of the XML declaration.
	 * @param string $encoding The encoding of the document as part of the XML declaration.
	 */
	public function __construct( $version = null, $encoding = null ) {
		parent::__construct( $version, $encoding );
		$this->registerNodeClass( 'DOMElement', '\IvoPetkov\HTML5DOMElement' );
	}

	/**
	 * Load HTML from a string and adds missing doctype, html and body tags
	 *
	 * @param  string $source  The HTML code
	 * @param  int    $options Additional Libxml parameters
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public function loadHTML( $source, $options = 0 ) {
		// Enables libxml errors handling
		$internalErrorsOptionValue = libxml_use_internal_errors();
		if ( $internalErrorsOptionValue === false ) {
			libxml_use_internal_errors( true );
		}
		$source = trim( $source );

		// Add body tag if missing
		if ( isset( $source{0} ) && preg_match( '/\<!DOCTYPE.*?\>/', $source ) === 0 && preg_match( '/\<html.*?\>/', $source ) === 0 && preg_match( '/\<body.*?\>/', $source ) === 0 && preg_match( '/\<head.*?\>/', $source ) === 0 ) {
			$source = '<body>' . $source . '</body>';
		}

		if ( strtoupper( substr( $source, 0, 9 ) ) !== '<!DOCTYPE' ) {
			$source = '<!DOCTYPE html>' . $source;
		}

		// Adds temporary head tag
		$removeCharsetTag = false;
		$charsetTag       = '<meta data-html5-dom-document-internal-attribute="charset-meta" http-equiv="content-type" content="text/html; charset=utf-8" />';
		$matches          = array();
		preg_match( '/\<head.*?\>/', $source, $matches );
		$removeHeadTag = true;
		$removeHtmlTag = true;
		if ( isset( $matches[0] ) ) { // has head tag
			$removeHeadTag    = false;
			$removeHtmlTag    = false;
			$insertPosition   = strpos( $source, $matches[0] ) + strlen( $matches[0] );
			$source           = substr( $source, 0, $insertPosition ) . $charsetTag . substr( $source, $insertPosition );
			$removeCharsetTag = true;
		} else {
			$matches = array();
			preg_match( '/\<html.*?\>/', $source, $matches );
			if ( isset( $matches[0] ) ) { // has html tag
				$removeHtmlTag    = false;
				$source           = str_replace( $matches[0], $matches[0] . $charsetTag, $source );
				$removeCharsetTag = true;
			} else {
				$insertPosition   = strpos( $source, '>' ) + 1;
				$source           = substr( $source, 0, $insertPosition ) . $charsetTag . substr( $source, $insertPosition );
				$removeCharsetTag = true;
			}
		}

		// Preserve html entities
		$source = preg_replace( '/&([a-zA-Z]*);/', 'html5-dom-document-internal-entity1-$1-end', $source );
		$source = preg_replace( '/&#([0-9]*);/', 'html5-dom-document-internal-entity2-$1-end', $source );

		$result = parent::loadHTML( '<?xml encoding="utf-8" ?>' . $source );
		if ( $internalErrorsOptionValue === false ) {
			libxml_use_internal_errors( false );
		}
		if ( $result === false ) {
			return false;
		}
		$this->encoding = 'utf-8';
		foreach ( $this->childNodes as $item ) {
			if ( $item->nodeType === XML_PI_NODE ) {
				$this->removeChild( $item );
				break;
			}
		}
		if ( $removeCharsetTag ) {
			$metaTagElement = $this->getElementsByTagName( 'meta' )->item( 0 );
			if ( $metaTagElement !== null ) {
				if ( $metaTagElement->getAttribute( 'data-html5-dom-document-internal-attribute' ) === 'charset-meta' ) {
					$metaTagElement->parentNode->removeChild( $metaTagElement );
				}
				if ( $removeHeadTag ) {
					$headElement = $this->getElementsByTagName( 'head' )->item( 0 );
					if ( $headElement !== null && $headElement->childNodes->length === 0 ) {
						$headElement->parentNode->removeChild( $headElement );
					}
				}
				if ( $removeHtmlTag ) {
					$htmlElement = $this->getElementsByTagName( 'html' )->item( 0 );
					if ( $htmlElement !== null && $htmlElement->childNodes->length === 0 ) {
						$htmlElement->parentNode->removeChild( $htmlElement );
					}
				}
			}
		}

		// Update dom if there are multiple head tags
		$headElements = $this->getElementsByTagName( 'head' );
		if ( $headElements->length > 1 ) {
			$firstHeadElement = $headElements->item( 0 );
			while ( $headElements->length > 1 ) {
				$nextHeadElement              = $headElements->item( 1 );
				$nextHeadElementChildren      = $nextHeadElement->childNodes;
				$nextHeadElementChildrenCount = $nextHeadElementChildren->length;
				for ( $i = 0; $i < $nextHeadElementChildrenCount; $i++ ) {
					$firstHeadElement->appendChild( $nextHeadElementChildren->item( 0 ) );
				}
				$nextHeadElement->parentNode->removeChild( $nextHeadElement );
			}
		}

		// Update dom if there are multiple body tags
		$bodyElements = $this->getElementsByTagName( 'body' );
		if ( $bodyElements->length > 1 ) {
			$firstBodyElement = $bodyElements->item( 0 );
			while ( $bodyElements->length > 1 ) {
				$nextBodyElement              = $bodyElements->item( 1 );
				$nextBodyElementChildren      = $nextBodyElement->childNodes;
				$nextBodyElementChildrenCount = $nextBodyElementChildren->length;
				for ( $i = 0; $i < $nextBodyElementChildrenCount; $i++ ) {
					$firstBodyElement->appendChild( $nextBodyElementChildren->item( 0 ) );
				}
				$nextBodyElement->parentNode->removeChild( $nextBodyElement );
			}
		}

		$this->removeDuplicateTags();

		$this->loaded = true;
		return true;
	}


	/**
	 * Adds the HTML tag to the document if missing
	 *
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	private function addHtmlElementIfMissing() {
		if ( $this->getElementsByTagName( 'html' )->item( 0 ) === null ) {
			$this->appendChild( new \DOMElement( 'html' ) );
			return true;
		}
		return false;
	}

	/**
	 * Adds the HEAD tag to the document if missing
	 *
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	private function addHeadElementIfMissing() {
		if ( $this->getElementsByTagName( 'head' )->item( 0 ) === null ) {
			$htmlElement = $this->getElementsByTagName( 'html' )->item( 0 );
			$headElement = new \DOMElement( 'head' );
			if ( $htmlElement->childNodes->length === 0 ) {
				$htmlElement->appendChild( $headElement );
			} else {
				$htmlElement->insertBefore( $headElement, $htmlElement->firstChild );
			}
			return true;
		}
		return false;
	}

	/**
	 * Adds the BODY tag to the document if missing
	 *
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	private function addBodyElementIfMissing() {
		if ( $this->getElementsByTagName( 'body' )->item( 0 ) === null ) {
			$this->getElementsByTagName( 'html' )->item( 0 )->appendChild( new \DOMElement( 'body' ) );
			return true;
		}
		return false;
	}

	/**
	 * Dumps the internal document into a string using HTML formatting
	 *
	 * @param  \DOMNode $node Optional parameter to output a subset of the document.
	 * @return string The document (ot node) HTML code as string
	 */
	public function saveHTML( \DOMNode $node = null ) {
		if ( ! $this->loaded ) {
			return '<!DOCTYPE html>';
		}
		$bodyElement = $this->getElementsByTagName( 'body' )->item( 0 );
		if ( $bodyElement !== null ) { // This preserves the whitespace between the HTML tags
			$bodyElements      = $bodyElement->getElementsByTagName( '*' );
			$bodyElementsCount = $bodyElements->length;
			$tempNodes         = array();
			$tempTextNode      = $this->createTextNode( 'html5-dom-document-internal-content' );
			for ( $i = 0; $i < $bodyElementsCount; $i++ ) {
				$element      = $bodyElements->item( $i );
				$newTextNode1 = clone($tempTextNode);
				$newTextNode2 = clone($tempTextNode);
				$parentNode   = $element->parentNode;
				$parentNode->insertBefore( $newTextNode1, $element );
				if ( ( $nextSibling = $element->nextSibling ) !== null ) {
					$parentNode->insertBefore( $newTextNode2, $nextSibling );
				} else {
					$parentNode->appendChild( $newTextNode2 );
				}
				$tempNodes[] = array( $parentNode, $newTextNode1, $newTextNode2 );
			}
		}

		$removeHtmlElement = false;
		$removeHeadElement = false;
		$headElement       = $this->getElementsByTagName( 'head' )->item( 0 );
		if ( $headElement === null ) {
			if ( $this->addHtmlElementIfMissing() ) {
				$removeHtmlElement = true;
			}
			if ( $this->addHeadElementIfMissing() ) {
				$removeHeadElement = true;
			}
			$headElement = $this->getElementsByTagName( 'head' )->item( 0 );
		}
		$meta = $this->createElement( 'meta' );
		$meta->setAttribute( 'data-html5-dom-document-internal-attribute', 'charset-meta' );
		$meta->setAttribute( 'http-equiv', 'content-type' );
		$meta->setAttribute( 'content', 'text/html; charset=utf-8' );
		$headElement->appendChild( $meta );

		$html = parent::saveHTML( $node );

		if ( $bodyElement !== null ) {
			foreach ( $tempNodes as $tempNodesData ) {
				$tempNodesData[0]->removeChild( $tempNodesData[1] );
				$tempNodesData[0]->removeChild( $tempNodesData[2] );
			}
			unset( $tempNodes );
		}

		if ( $removeHeadElement ) {
			$headElement->parentNode->removeChild( $headElement );
		} else {
			$meta->parentNode->removeChild( $meta );
		}

		if ( strpos( $html, 'html5-dom-document-internal-entity' ) !== false ) {
			$html = preg_replace( '/html5-dom-document-internal-entity1-(.*?)-end/', '&$1;', $html );
			$html = preg_replace( '/html5-dom-document-internal-entity2-(.*?)-end/', '&#$1;', $html );
		}

		$codeToRemove = array(
			'html5-dom-document-internal-content',
			'<meta data-html5-dom-document-internal-attribute="charset-meta" http-equiv="content-type" content="text/html; charset=utf-8">',
			'</area>',
			'</base>',
			'</br>',
			'</col>',
			'</command>',
			'</embed>',
			'</hr>',
			'</img>',
			'</input>',
			'</keygen>',
			'</link>',
			'</meta>',
			'</param>',
			'</source>',
			'</track>',
			'</wbr>',
		);
		if ( $removeHeadElement ) {
			$codeToRemove[] = '<head></head>';
		}
		if ( $removeHtmlElement ) {
			$codeToRemove[] = '<html></html>';
		}
		$html = str_replace( $codeToRemove, '', $html );

		// Remove the whitespace between the doctype and html tag
		$html = preg_replace( '/\>\s\<html/', '><html', $html, 1 );
		return trim( $html );
	}

	/**
	 * Returns the first document element matching the selector
	 *
	 * @param  string $selector CSS query selector
	 * @return \DOMElement|null The result DOMElement or null if not found
	 */
	public function querySelector( $selector ) {
		return $this->internalQuerySelector( $selector );
	}

	/**
	 * Returns a list of document elements matching the selector
	 *
	 * @param  string $selector CSS query selector
	 * @return DOMNodeList Returns a list of DOMElements matching the criteria
	 * @throws \InvalidArgumentException
	 */
	public function querySelectorAll( $selector ) {
		return $this->internalQuerySelectorAll( $selector );
	}

	/**
	 * Creates an element that will be replaced by the new body in insertHTML
	 *
	 * @param  string $name The name of the insert target
	 * @return \DOMElement A new DOMElement that must be set in the place where the new body will be inserted
	 * @throws \InvalidArgumentException
	 */
	public function createInsertTarget( $name ) {
		if ( ! is_string( $name ) ) {
			throw new \InvalidArgumentException( 'The name argument must be of type string' );
		}
		if ( ! $this->loaded ) {
			$this->loadHTML( '' );
		}
		$element = $this->createElement( 'html5-dom-document-insert-target' );
		$element->setAttribute( 'name', $name );
		return $element;
	}

	/**
	 * Inserts a HTML document into the current document. The elements from the head and the body will be moved to their proper locations.
	 *
	 * @param  string $source The HTML code to be inserted
	 * @param  string $target Body target position. Available values: afterBodyBegin, beforeBodyEnd or insertTarget name.
	 * @throws \InvalidArgumentException
	 */
	public function insertHTML( $source, $target = 'beforeBodyEnd' ) {
		if ( ! is_string( $source ) ) {
			throw new \InvalidArgumentException( 'The source argument must be of type string' );
		}
		if ( ! is_string( $target ) ) {
			throw new \InvalidArgumentException( 'The target argument must be of type string' );
		}
		if ( ! $this->loaded ) {
			$this->loadHTML( '' );
		}
		$domDocument = new HTML5DOMDocument();
		$domDocument->loadHTML( $source );

		$currentDomDocument = &$this;
		$getNewChild        = function ( $child ) use ( $currentDomDocument ) {
			if ( $child instanceof \DOMElement ) {
				$id = $child->getAttribute( 'id' );
				if ( $id !== '' && $currentDomDocument->getElementById( $id ) !== null ) {
					return null;
				}
			}
			return $currentDomDocument->importNode( $child, true );
		};

		$copyAttributes = function ( $sourceNode, $targetNode ) {
			$attributesCount = $sourceNode->attributes->length;
			for ( $i = 0; $i < $attributesCount; $i++ ) {
				$attribute = $sourceNode->attributes->item( $i );
				$targetNode->setAttribute( $attribute->name, $attribute->value );
			}
		};

		$removeDuplicateTags = false;

		$htmlElement = $domDocument->getElementsByTagName( 'html' )->item( 0 );
		if ( $htmlElement !== null ) {
			$currentDomHTMLElement = $this->getElementsByTagName( 'html' )->item( 0 );
			if ( $currentDomHTMLElement === null ) {
				$this->addHtmlElementIfMissing();
				$currentDomHTMLElement = $this->getElementsByTagName( 'html' )->item( 0 );
			}
			$copyAttributes( $htmlElement, $currentDomHTMLElement );
		}

		$headElement = $domDocument->getElementsByTagName( 'head' )->item( 0 );
		if ( $headElement !== null ) {
			$currentDomHeadElement = $this->getElementsByTagName( 'head' )->item( 0 );
			if ( $currentDomHeadElement === null ) {
				$this->addHtmlElementIfMissing();
				$this->addHeadElementIfMissing();
				$currentDomHeadElement = $this->getElementsByTagName( 'head' )->item( 0 );
			}
			$headElementChildren      = $headElement->childNodes;
			$headElementChildrenCount = $headElementChildren->length;
			for ( $i = 0; $i < $headElementChildrenCount; $i++ ) {
				$newNode = $getNewChild( $headElementChildren->item( $i ) );
				if ( $newNode !== null ) {
					$currentDomHeadElement->appendChild( $newNode );
				}
			}
			$copyAttributes( $headElement, $currentDomHeadElement );
			$removeDuplicateTags = true;
		}

		$bodyElement              = $domDocument->getElementsByTagName( 'body' )->item( 0 );
		$bodyElementChildrenCount = 0;
		if ( $bodyElement !== null ) {
			$currentDomBodyElement = $this->getElementsByTagName( 'body' )->item( 0 );
			if ( $currentDomBodyElement === null ) {
				$this->addHtmlElementIfMissing();
				$this->addBodyElementIfMissing();
				$currentDomBodyElement = $this->getElementsByTagName( 'body' )->item( 0 );
			}
			$bodyElementChildren      = $bodyElement->childNodes;
			$bodyElementChildrenCount = $bodyElementChildren->length;
			$copyAttributes( $bodyElement, $currentDomBodyElement );
		}
		if ( $target === 'afterBodyBegin' ) {
			for ( $i = $bodyElementChildrenCount - 1; $i >= 0; $i-- ) {
				$newNode = $getNewChild( $bodyElementChildren->item( $i ) );
				if ( $newNode !== null ) {
					if ( $currentDomBodyElement->firstChild === null ) {
						$currentDomBodyElement->appendChild( $newNode );
					} else {
						$currentDomBodyElement->insertBefore( $newNode, $currentDomBodyElement->firstChild );
					}
				}
			}
		} elseif ( $target === 'beforeBodyEnd' ) {
			for ( $i = 0; $i < $bodyElementChildrenCount; $i++ ) {
				$newNode = $getNewChild( $bodyElementChildren->item( $i ) );
				if ( $newNode !== null ) {
					$currentDomBodyElement->appendChild( $newNode );
				}
			}
		} else {
			$targetElements      = $this->getElementsByTagName( 'html5-dom-document-insert-target' );
			$targetElementsCount = $targetElements->length;
			for ( $j = 0; $j < $targetElementsCount; $j++ ) {
				$targetElement = $targetElements->item( $j );
				if ( $targetElement->getAttribute( 'name' ) === $target ) {
					for ( $i = 0; $i < $bodyElementChildrenCount; $i++ ) {
						$newNode = $getNewChild( $bodyElementChildren->item( $i ) );
						if ( $newNode !== null ) {
							$targetElement->parentNode->insertBefore( $newNode, $targetElement );
						}
					}
				}
				$targetElement->parentNode->removeChild( $targetElement );
				break;
			}
		}
		if ( $bodyElementChildrenCount > 0 ) {
			$removeDuplicateTags = true;
		}

		if ( $removeDuplicateTags ) {
			$this->removeDuplicateTags();
		}
	}

	/**
	 * Removes duplicate nodes.
	 *  - The first title element will remain if multiple
	 *  - Meta tags checked by name or property attributes
	 *  - Only the first element with a specified id will remain if multiple with the same id are set
	 */
	private function removeDuplicateTags() {
		$headElement = $this->getElementsByTagName( 'head' )->item( 0 );
		if ( $headElement !== null ) {
			$titleTags = $headElement->getElementsByTagName( 'title' );
			while ( $titleTags->length > 1 ) {
				$node = $titleTags->item( 0 );
				$node->parentNode->removeChild( $node );
			}
			$metaTags = $headElement->getElementsByTagName( 'meta' );
			if ( $metaTags->length > 0 ) {
				$list    = array();
				$idsList = array();
				for ( $i = 0; $i < $metaTags->length; $i++ ) {
					$metaTag = $metaTags->item( $i );
					$id      = $metaTag->getAttribute( 'name' );
					if ( isset( $id{0} ) ) {
						$id = 'name:' . $id;
					} else {
						$id = $metaTag->getAttribute( 'property' );
						if ( isset( $id{0} ) ) {
							$id = 'property:' . $id;
						} else {
							$id = $metaTag->getAttribute( 'charset' );
							if ( isset( $id{0} ) ) {
								$id = 'charset';
							}
						}
					}
					if ( ! isset( $idsList[ $id ] ) ) {
						$idsList[ $id ] = 0;
					}
					$idsList[ $id ] ++;
					$list[] = array( $metaTag, $id );
				}
				foreach ( $idsList as $id => $count ) {
					if ( $count > 1 && $id !== '' ) {
						foreach ( $list as $i => $item ) {
							if ( $item[1] === $id ) {
								$node = $item[0];
								$node->parentNode->removeChild( $node );
								unset( $list[ $i ] );
								$count--;
							}
							if ( $count === 1 ) {
								break;
							}
						}
					}
				}
			}
		}
	}

}
