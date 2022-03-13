<?php

namespace PMC\Social_Share_Bar;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Config {

	use Singleton;

	const FB = 'facebook';
	const TW = 'twitter';
	const TB = 'tumblr';
	const PN = 'pinit';
	const RD = 'reddit';
	const LN = 'linkedin';
	const WA = 'whatsapp';
	const EM = 'email';
	const PT = 'print';
	const CM = 'comment';

	public $share_icons_object = array();

	protected $_share_icons = array();

	/**
	 * Config constructor.
	 */
	protected function __construct() {

		$this->_init();
	}

	protected function _init() {

		$this->_share_icons = array(
			self::FB => array(
				'name'        => __( 'Facebook', 'pmc-social-share-bar' ),
				'title'       => __( 'Share on Facebook', 'pmc-social-share-bar' ),
				'class'       => 'btn-facebook',
				'url'         => 'https://www.facebook.com/sharer.php',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on Facebook', 'pmc-social-share-bar' ),
			),
			self::TW => array(
				'name'        => __( 'Twitter', 'pmc-social-share-bar' ),
				'title'       => __( 'Tweet', 'pmc-social-share-bar' ),
				'class'       => 'btn-twitter',
				'url'         => 'https://twitter.com/intent/tweet',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on Twitter', 'pmc-social-share-bar' ),
			),
			self::TB => array(
				'name'        => __( 'Tumblr', 'pmc-social-share-bar' ),
				'title'       => __( 'Post to Tumblr', 'pmc-social-share-bar' ),
				'class'       => 'btn-tumblr',
				'url'         => 'https://www.tumblr.com/widgets/share/tool/preview',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on Tumblr', 'pmc-social-share-bar' ),
			),
			self::PN => array(
				'name'        => __( 'Pin It', 'pmc-social-share-bar' ),
				'title'       => __( 'Pin it', 'pmc-social-share-bar' ),
				'class'       => 'btn-pinterest',
				'url'         => 'https://pinterest.com/pin/create/link/',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on Pinterest', 'pmc-social-share-bar' ),
			),
			self::RD => array(
				'name'        => __( 'Reddit', 'pmc-social-share-bar' ),
				'title'       => __( 'Submit to Reddit', 'pmc-social-share-bar' ),
				'class'       => 'btn-reddit',
				'url'         => 'https://www.reddit.com/submit',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on Reddit', 'pmc-social-share-bar' ),
			),
			self::LN => array(
				'name'        => __( 'LinkedIn', 'pmc-social-share-bar' ),
				'title'       => __( 'Share on LinkedIn', 'pmc-social-share-bar' ),
				'class'       => 'btn-linkedin',
				'url'         => 'https://www.linkedin.com/shareArticle',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Share on LinkedIn', 'pmc-social-share-bar' ),
			),
			self::WA => array(
				'name'        => __( 'WhatsApp', 'pmc-social-share-bar' ),
				'title'       => __( 'Share on WhatsApp', 'pmc-social-share-bar' ),
				'class'       => 'btn-whatsapp',
				'url'         => 'whatsapp://send',
				'popup'       => false,
				'javascript'  => false,
				'popup_title' => __( 'Share on Whats App', 'pmc-social-share-bar' ),
			),
			self::EM => array(
				'name'        => __( 'Email', 'pmc-social-share-bar' ),
				'title'       => __( 'Email', 'pmc-social-share-bar' ),
				'class'       => 'btn-email',
				'url'         => 'mailto:',
				'popup'       => true,
				'javascript'  => false,
				'popup_title' => __( 'Send an Email', 'pmc-social-share-bar' ),
			),
			self::PT => array(
				'name'        => __( 'Print', 'pmc-social-share-bar' ),
				'title'       => __( 'Print This Page', 'pmc-social-share-bar' ),
				'class'       => 'btn-print',
				'url'         => 'javascript:window.print()',
				'popup'       => false,
				'javascript'  => true,
				'popup_title' => __( 'Print the Article', 'pmc-social-share-bar' ),
			),
			self::CM => array(
				'name'        => __( 'Talk', 'pmc-social-share-bar' ),
				'title'       => __( 'Talk', 'pmc-social-share-bar' ),
				'class'       => 'btn-comment',
				'url'         => '',
				'popup'       => false,
				'javascript'  => false,
				'popup_title' => __( 'Post a Comment', 'pmc-social-share-bar' ),
			),
		);

		// Set up the default Share Icons from the Config file.
		$this->setup_icons();
	}

	public function get_share_list() {
		return $this->_share_icons;
	}


	/**
	 * Set up each icon as object and add to array
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare - PMCVIP-815
	 *
	 */
	public function setup_icons() {

		$share_icons = $this->get_share_list();

		foreach ( $share_icons as $id => $share_icon ) {
			$this->share_icons_object[ $id ] = new Icon( $id, $share_icon );
		}

	}

	/**
	 *
	 * Register a new share icon
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 * @param string $id Required the key name of share icon
	 * @param array $params Array list of parameters for the share icons
	 *
	 * @return $this object
	 *
	 */
	public function register( $id, $params = array() ) {

		if ( empty( $id ) || empty( $params ) ) {
			return $this;
		}

		$this->share_icons_object[ $id ] = new Icon( $id, $params );

		return $this;

	}

	/**
	 * Remove a share icon from array
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 * @param string $id Required the key id of share icon
	 *
	 * @return $this object
	 */
	public function de_register( $id ) {

		if ( empty( $id ) ) {
			return $this;
		}

		if ( array_key_exists( $id, $this->share_icons_object ) ) {
			unset( $this->share_icons_object[ $id ] );
		}

		return $this;

	}

	/**
	 *
	 * Get the icon details based on the id passed.
	 * If nothing is passed get list of all share icons the class has registered
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 * @param string $id - the key of the share icons that also signifies social network name
	 *
	 * @return array
	 */
	public function get_social_share_icons( $id = '' ) {

		if ( empty( $id ) ) {
			$share_icons = array();
			foreach ( $this->share_icons_object as $icon ) {
				$share_icons[ $icon->get_icon_id() ] = $icon->get_properties();
			};

			return $share_icons;
		}
		if ( ! empty ( $this->share_icons_object[ $id ] ) ) {
			$icon               = $this->share_icons_object[ $id ];
			$share_icons[ $id ] = $icon->get_properties();

			return $share_icons;
		}

		return false;
	}

	/**
	 *
	 * Get the icon details based on the name passed.
	 * If nothing is passed get list of all share icons the class has registered
	 *
	 * @since 2016-02-11
	 * @version 2016-02-11 Archana Mandhare - PMCVIP-815
	 *
	 * @param string $name - the key of the share icons that also signifies social network name
	 *
	 * @return array | Icon object
	 */
	public function get_social_share_icons_object( $name = '' ) {

		if ( empty( $name ) ) {
			return $this->share_icons_object;
		}
		if ( ! empty ( $this->share_icons_object[ $name ] ) ) {
			return $this->share_icons_object[ $name ];
		}

		return false;
	}

}
