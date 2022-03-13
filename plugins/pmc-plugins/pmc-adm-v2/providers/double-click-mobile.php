<?php

class DoubleClick_Mobile_Provider extends DoubleClick_Provider {

	protected $_id = 'double-click-mobile';
	protected $_title = 'DoubleClick Mobile';

	function set_hostname_sitename() {
		$this->set_hostname_sitename_zone();
	}

	public function get_fields() {
		unset( $this->_fields['type'] );

		return $this->_fields;
	}

	/**
	 * Fields to show in the admin form
	 *
	 * @var array
	 */
	protected $_admin_templates = [];

	/**
	 * Return all the templates for this provider required from /pmc-adm/templates/provider-admin/*.php
	 * @return array
	 */
	public function get_admin_templates() {
		return $this->_admin_templates;
	}

	function set_hostname_sitename_zone() {

		if ( is_home() ) {
			$zone = 'homepage';
		} elseif ( is_single() ) {
			$zone = 'article';
		} else {
			$zone = '';
		}

		// Turn the domain into the mobile sitename.  Only replaces the domain after the
		// last ".", which in most cases is the TLD.  Will break on country code domains.
		// ex: "https://tvline.com" -> "tvline.mob"
		$host   = parse_url( home_url(), PHP_URL_HOST );
		$sitename = substr_replace( $host, '.mob', strrpos( $host, '.' ) );

		if ( defined( 'PMC_SITE_NAME' ) ) {
			switch ( PMC_SITE_NAME ) {
				case 'hollywoodlife':
					if ( !is_home() ) {
						$zone = 'ros';
					}
					break;
				case 'deadline':
					$sitename = 'dhd.deadline.mob';
					$zone     = 'TV/';
					break;
				case 'variety':
					$sitename = 'Variety_Mobile';
					if ( !is_home() ) {
						$zone = 'ros';
					}
					break;
			}
		}

		$this->sitename = $sitename;
		$this->hostname = $host;
		$this->zone     = $zone;
	}

	public function get_zone() {

		if ( empty( $this->zone ) ) {
			$this->set_hostname_sitename_zone();
		}

		return $this->zone;
	}

	public function get_params() {
		$params              = parent::get_params();
		$params['host']      = $this->_config['hostname'];
		$params['tile']      = self::$tile;
		$params['kw']        = $this->get_keywords();

		unset( $params['mtfInline'] );
		unset( $params['mtfIFPath'] );
		return $params;
	}

}
//EOF
