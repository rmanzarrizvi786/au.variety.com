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
 * @property-read string $innerHTML The HTML code inside the element
 * @property-read string $outerHTML The HTML code for the element including the code inside
 */
class HTML5DOMElement extends \DOMElement {


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
	 * Returns the value for the property specified
	 *
	 * @param  string $name The name of the property
	 * @return string The value of the property specified
	 * @throws \Exception
	 */
	public function __get( $name ) {
		if ( $name === 'innerHTML' ) {
			$html     = $this->ownerDocument->saveHTML( $this );
			$nodeName = $this->nodeName;
			return preg_replace( '@^<' . $nodeName . '[^>]*>|</' . $nodeName . '>$@', '', $html );
		} elseif ( $name === 'outerHTML' ) {
			return $this->ownerDocument->saveHTML( $this );
		}
		throw new \Exception( 'Undefined property: HTML5DOMElement::$' . $name );
	}

	/**
	 * Sets the value for the property specified
	 *
	 * @param  string $name
	 * @param  string $value
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function __set( $name, $value ) {
		if ( ! is_string( $value ) ) {
			throw new \InvalidArgumentException( 'The value argument must be of type string' );
		}
		if ( $name === 'innerHTML' ) {
			while ( $this->hasChildNodes() ) {
				$this->removeChild( $this->firstChild );
			}
			$tmpDoc = new \IvoPetkov\HTML5DOMDocument();
			$tmpDoc->loadHTML( '<body>' . $value . '</body>' );
			foreach ( $tmpDoc->getElementsByTagName( 'body' )->item( 0 )->childNodes as $node ) {
				$node = $this->ownerDocument->importNode( $node, true );
				$this->appendChild( $node );
			}
			return;
		} elseif ( $name === 'outerHTML' ) {
			$tmpDoc = new \IvoPetkov\HTML5DOMDocument();
			$tmpDoc->loadHTML( '<body>' . $value . '</body>' );
			foreach ( $tmpDoc->getElementsByTagName( 'body' )->item( 0 )->childNodes as $node ) {
				$node = $this->ownerDocument->importNode( $node, true );
				$this->parentNode->insertBefore( $node, $this );
			}
			$this->parentNode->removeChild( $this );
			return;
		}
		throw new \Exception( 'Undefined property: HTML5DOMElement::$' . $name );
	}

	/**
	 * Updates the result value before returning it
	 *
	 * @param  string $value
	 * @return string The updated value
	 */
	private function updateResult( $value ) {
		if ( strpos( $value, 'html5-dom-document-internal-entity' ) !== false ) {
			$matches = array();
			preg_match_all( '/html5-dom-document-internal-entity1-(.*?)-end/', $value, $matches );
			foreach ( $matches[0] as $i => $match ) {
				$value = str_replace( $match, html_entity_decode( '&' . $matches[1][ $i ] . ';' ), $value );
			}
			$matches = array();
			preg_match_all( '/html5-dom-document-internal-entity2-(.*?)-end/', $value, $matches );
			foreach ( $matches[0] as $i => $match ) {
				$value = str_replace( $match, html_entity_decode( '&#' . $matches[1][ $i ] . ';' ), $value );
			}
		}
		return $value;
	}

	/**
	 * Returns the value for the attribute name specified
	 *
	 * @param  string $name The attribute name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getAttribute( $name ) {
		if ( ! is_string( $name ) ) {
			throw new \InvalidArgumentException( 'The name argument must be of type string' );
		}
		return $this->updateResult( parent::getAttribute( $name ) );
	}

	/**
	 * Returns an array containing all attributes
	 *
	 * @return array An associative array containing all attributes
	 */
	public function getAttributes() {
		$attributesCount = $this->attributes->length;
		$attributes      = array();
		for ( $i = 0; $i < $attributesCount; $i++ ) {
			$attribute                      = $this->attributes->item( $i );
			$attributes[ $attribute->name ] = $this->updateResult( $attribute->value );
		}
		return $attributes;
	}

	/**
	 * Returns the element outerHTML
	 *
	 * @return string The element outerHTML
	 */
	public function __toString() {
		return $this->outerHTML;
	}

	/**
	 * Returns the first child element matching the selector
	 *
	 * @param  string $selector CSS query selector
	 * @return \DOMElement|null The result DOMElement or null if not found
	 */
	public function querySelector( $selector ) {
		return $this->internalQuerySelector( $selector );
	}

	/**
	 * Returns a list of children elements matching the selector
	 *
	 * @param  string $selector CSS query selector
	 * @return DOMNodeList Returns a list of DOMElements matching the criteria
	 * @throws \InvalidArgumentException
	 */
	public function querySelectorAll( $selector ) {
		return $this->internalQuerySelectorAll( $selector );
	}

}
