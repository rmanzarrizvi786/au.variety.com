<?php

class DoubleClick_Provider extends PMC_Ad_Provider {

	const KEYWORDS_LIMIT = 10;
	protected $_id = 'double-click';
	protected $_title = 'DoubleClick';
	public static $tile = 0;

	/**
	 * Fields to save as meta data.
	 *
	 * @var array
	 */
	protected $_fields = array(
		'type' => array(
			'title' => 'Type',
			'options' => array(
				'adi' => 'Iframe',
				'adj' => 'Javascript'
			)
		),
		'pos' => array(
			'title' => 'Position',
			'required' => false
		),
		'sitename' => array(
			'title' => 'Sitename',
			'required' => false
		),
		'zone' => array(
			'title'    => 'Zone',
			'required' => false
		),
	);

	/**
	 * Fields to show in the admin form
	 *
	 * @var array
	 */
	protected $_templates = [];

	/**
	 * Return all the templates for this provider required from /pmc-adm/templates/provider-admin/*.php
	 * @return array
	 */
	public function get_admin_templates() {
		return $this->_templates;
	}

	/**
	 * Get automatic host name for the site.
	 * @return mixed
	 */
	function get_hostname() {
		if ( !empty( $this->hostname ) ) {
			return $this->hostname;
		} else {
			$this->set_hostname_sitename();

			return $this->hostname;
		}
	}

	/**
	 * Get automatic sitename for the site.
	 * @return mixed
	 */
	function get_sitename() {
		if ( !empty( $this->sitename ) ) {
			return $this->sitename;
		} else {
			$this->set_hostname_sitename();

			return $this->sitename;
		}
	}

	public function format_params($params) {
		$output = array();

		foreach ($params as $key => $value) {
			if (is_array($value)) {
				$value = implode(';' . $key . '=', $value);
			}

			$output[] = $key . '=' . $value;
		}

		return implode(';', $output).';';
	}

	public function get_params() {
		$params              = parent::get_params();
		$params['mtfInline'] = 'TRUE';
		$params['mtfIFPath'] = '/wp-content/themes/vip/pmc-plugins/partner/doubleclick/';
		$params['host']      = $this->_config['hostname'];
		$params['tile']      = self::$tile;
		$params['kw']        = $this->get_keywords( self::KEYWORDS_LIMIT );
		return $params;
	}

	/**
	 * Include any 3rd-party scripts.
	 */
	public function include_assets() {

	}

	protected function prepare_ad_data( $data ) {

		$data = array_merge( $this->_config, $data );

		if ( !isset( $data['sitename'] ) || empty( $data['sitename'] ) ) {
			$data['sitename'] = $this->get_sitename();
		}

		if ( !isset( $data['hostname'] ) || empty( $data['hostname'] ) ) {
			$data['hostname'] = $this->get_hostname();
			$this->_config['hostname'] = $this->get_hostname();
		}

		if ( !isset( $data['zone'] ) || empty( $data['zone'] ) ) {
			$data['zone'] = $this->get_zone();
		}

		if ( !isset( $data['css-class'] ) ) {
			$data['css-class'] = '';
		}

		$data['key'] = $this->get_key();

		return $data;

	}

	function set_hostname_sitename() {

		$host = parse_url( home_url(), PHP_URL_HOST );

		// Set the sitename (and sometimes zone and other stuff)
		switch ( PMC_SITE_NAME ) {
			case 'hollywoodlife':
				$sitename = 'Hollywoodlife';
				if ( 'pmchollywoodlife.wordpress.com' === $host ) {
					$host = 'hollywoodlife.com';
				}
				break;

			case 'deadline':
				// Force host to be deadline.com on test environments
				$host     = 'deadline.com';
				$sitename = 'DHD';
				break;

			case 'bgr':
				$sitename = 'bgr';
				if ( 'boygeniusreport.wordpress.com' === $host ) {
					$host = 'bgr.com';
				}
				break;

			case 'tvline':
				$sitename = 'tvline';
				if ( 'pmctvline2.wordpress.com' === $host ) {
					$host = 'tvline.com';
				}
				break;

			case 'movieline':
				$sitename = 'Movieline';
				if ( 'pmcmovieline.wordpress.com' === $host ) {
					$host = 'movieline.com';
				}
				break;

			case 'awardsline':
				$sitename = 'Awardsline';
				if ( 'pmcawardsline.wordpress.com' === $host ) {
					$host = 'awardsline.com';
				}
				break;

			default:
				$sitename = PMC_SITE_NAME;
				break;

		}

		$this->sitename = $sitename;
		$this->hostname = $host;
	}

	/**
	 * To Render or return an ad markup.
	 *
	 * @param array $data Ads Data.
	 * @param bool $echo should echo or not.
	 *
	 * @return string Ad template
	 *
	 * @throws Exception
	 */
	public function render_ad( array $data, $echo = false ) {
		self::$tile++;

		$data         = $this->prepare_ad_data( $data );
		$params       = $this->get_params();
		$params['sz'] = $data['width'] . 'x' . $data['height'];

		if ( isset( $data['pos'] ) && ! empty( $data['pos'] ) ) {
			$params['pos'] = $data['pos'];
		}

		$template_file = sprintf( '%s/templates/ads/%s.php', untrailingslashit( PMC_ADM_DIR ), $this->get_id() );

		$ad_data = [
			'ad'       => $data,
			'params'   => $params,
			'provider' => $this,
		];

		return PMC::render_template( $template_file, $ad_data, $echo );
	}

}

//EOF
