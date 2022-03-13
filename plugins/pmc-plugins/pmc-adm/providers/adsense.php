<?php

class Adsense_Provider extends PMC_Ad_Provider {

	protected $_id = 'adsense';
	protected $_title = 'Google AdSense';

	/**
	 * Fields to save as meta data.
	 *
	 * @var array
	 */
	protected $_fields = array(
		'publisher_id' => 'Publisher ID',
		'tag_id' => 'Tag ID'
	);

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

	/**
	 * Include any 3rd-party scripts.
	 */
	public function include_assets() {

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

		$template_file = sprintf( '%s/templates/ads/%s.php', untrailingslashit( PMC_ADM_DIR ), $this->get_id() );

		return PMC::render_template( $template_file, $data, $echo );
	}

}

//EOF
