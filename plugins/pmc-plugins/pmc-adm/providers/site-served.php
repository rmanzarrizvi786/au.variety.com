<?php

/*
 * Site Served Ad provider
 * @since 2017-10-15 Archana Mandhare PMCRS-460
 */

class Site_Served_Provider extends PMC_Ad_Provider {

	protected $_id    = 'site-served';
	protected $_title = 'Site Served';

	/**
	 * Fields to save as meta data.
	 *
	 * @var array
	 */
	protected $_fields = [
		'ad-promo'         => [
			'title'    => 'Ad Promo ID',
			'required' => true,
		],
		'ad-campaign-name' => [
			'title'    => 'Ad Campaign Name',
			'required' => true,
		],
		'ad-creative'      => [
			'title'    => 'Ad Creative',
			'required' => true,
		],
		'ad-url'           => [
			'title'    => 'Ad URL',
			'required' => true,
		],
		'ad-image'         => [
			'title'    => 'Ad Image',
			'required' => true,
		],
	];

	/**
	 * List out the templates to show in the admin form. These will be rendered in the same order as flex column wrap.
	 * The admin templates are in /pmc-adm/templates/provider-admin/*.php
	 *
	 * @var array
	 */
	protected $_admin_templates = [
		'basic',
		'device',
		'custom-site-served',
		'status',
		'time-frame',
		'conditionals',
	];

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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_media_uploader' ] );
	}

	/**
	 * Load media uploader scripts
	 */
	public function enqueue_media_uploader() {
		if ( is_admin() ) {
			wp_enqueue_media();
		}
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

		return PMC::render_template( $template_file, [ 'ad' => $data ], $echo );
	}

}

//EOF
