<?php

namespace PMC\Social_Share_Bar;

use \PMC;

class Icon {

	private $_ID;

	public $name;
	public $title;
	public $class;
	public $url;
	public $popup = true;
	public $popup_title;
	public $position;
	public $javascript;

	/**
	 * Constructor.
	 *
	 * @param string $id
	 * @param array $icon_property
	 *
	 * @throws \ErrorException
	 */
	public function __construct( $id, $icon_property ) {

		if ( empty( $id ) || ! is_string( $id ) ) {
			throw new \ErrorException( esc_html__( 'Need to specify a unique ID for icon that is a string', 'pmc-social-share-bar' ) );
		}

		if ( empty( $icon_property ) || ! is_array( $icon_property ) ) {
			throw new \ErrorException( esc_html__( 'Need to specify a icons properties for icon that is an array of properties', 'pmc-social-share-bar' ) );
		}

		$this->_ID = $id;

		foreach ( $icon_property as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

	}

	public function get_icon_id() {
		return $this->_ID;
	}

	public function modify( $properties ) {
		if ( empty( $properties ) || ! is_array( $properties ) ) {
			throw new \ErrorException( esc_html__( 'Need to specify a icons properties for icon that is an array of properties', 'pmc-social-share-bar' ) );
		}

		foreach ( $properties as $key => $value ) {
			$this->$key = $value;
		}

		return $this;
	}

	public function set_share_url( $social_network, $permalink, $title ) {

		if ( empty( $social_network ) || empty( $permalink ) || empty( $title ) ) {
			return false;
		}

		// Strip out HTML tags
		$title = strip_tags( html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) );

		switch ( $social_network ) {

			case Config::FB :
				$app_id     = apply_filters( 'pmc_social_share_bar_facebook_app_id', '' );
				$query_args = array(
					'u'     => $permalink,
					'title'   => $title,
					'sdk'     => 'joey',
					'display' => 'popup',
					'ref'     => 'plugin',
					'src'     => 'share_button',
				);
				if ( ! empty( $app_id ) ) {
					$query_args['app_id'] = $app_id;
				}
				$this->url = add_query_arg( $query_args, $this->url );
				$this->url = apply_filters( 'pmc_social_share_facebook_url', $this->url );
				break;

			case Config::TW :
				$query_args = array(
					'url'  => $permalink,
					'text' => rawurlencode( $title ),
					'via'  => PMC_TWITTER_SITE_USERNAME,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_twitter_url', $this->url );
				break;

			case Config::TB :
				$query_args = array(
					'shareSource'  => 'legacy',
					'canonicalUrl' => '',
					'url'          => $permalink,
					'posttype'     => 'link',
					'title'        => $title,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_tumblr_url', $this->url );
				break;

			case Config::PN :
				$query_args = array(
					'url'         => $permalink,
					'description' => $title,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_pinit_url', $this->url );
				break;

			case Config::RD :
				$query_args = array(
					'url'   => $permalink,
					'title' => $title,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_reddit_url', $this->url );
				break;

			case Config::LN :
				$query_args = array(
					'mini'    => true,
					'url'     => $permalink,
					'title'   => $title,
					'summary' => '',
					'source'  => PMC_TWITTER_SITE_USERNAME,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_linked_url', $this->url );
				break;

			case Config::WA :
				$query_args = array(
					'text' => $title . ' - ' . $permalink,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_whatsapp_url', $this->url );
				break;

			case Config::EM :
				$query_args = array(
					'subject' => PMC_TWITTER_SITE_USERNAME . ' : ' . $title,
					'body'    => $permalink . ' - ' . $title,
				);
				$this->url  = add_query_arg( $query_args, $this->url );
				$this->url  = apply_filters( 'pmc_social_share_email_url', $this->url );
				break;

			case Config::CM :
				$this->url = get_comments_link();
				$this->url = apply_filters( 'pmc_social_share_comment_url', $this->url );
				break;

			case Config::PT :
				$this->url = apply_filters( 'pmc_social_share_print_url', $this->url );
				break;

			default:
				break;

		}

		return $this;
	}

	/**
	 * Get all the properties of the icon
	 *
	 * @return array
	 *
	 */
	public function get_properties( $key = '' ) {

		if ( ! empty( $key ) && property_exists( $this, $key ) ) {
			return $this->$key;
		}

		return array(
			'name'        => $this->name,
			'title'       => $this->title,
			'class'       => $this->class,
			'url'         => $this->url,
			'popup'       => $this->popup,
			'javascript'  => $this->javascript,
			'popup_title' => $this->popup_title,
		);

	}

	/**
	 * Should the icon open a popup onclick
	 *
	 * @return bool
	 */
	public function is_popup() {
		return $this->popup;
	}

	/**
	 * Does the icon have a javascript for onclick
	 *
	 * @return bool
	 */
	public function is_javascript() {
		return $this->javascript;
	}

	/**
	 * Is this a comment box
	 *
	 * @return bool
	 */
	public function is_comment() {
		return ( Config::CM === $this->_ID );
	}

	/**
	 * Is this a comment box return the comment count
	 *
	 * @return bool
	 */
	public function get_comment_count() {
		if ( Config::CM === $this->_ID ){
			$comments = get_comment_count( get_the_ID() );
			return $comments['approved'];
		}
		return false;
	}

	/**
	 * get the position of the icon in the list
	 *
	 * @return bool
	 */
	public function get_position() {
		return $this->position;
	}

} // end class

//EOF
