<?php

/*

Impelement ajax action: variety_digital_subscriber
	cmd: verify-credential | verify-session | remove-session | get-credential

*/

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Digital_Subscriber
{

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{
		add_action('init', array($this, 'do_init'));
	}

	public function do_init()
	{
		add_action('wp_enqueue_scripts', array($this, 'do_enqueue_scripts'));
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function do_enqueue_scripts()
	{
		if (is_page('digital-subscriber-access') || is_page('access-digital')) {
			$url_prefix = get_stylesheet_directory_uri();
			wp_register_script('variety_digital_scripts', $url_prefix . '/plugins/variety-digital-subscriber/js/variety-digital.js', array('jquery', 'pmc-core-jq-cookie'));
			wp_localize_script('variety_digital_scripts', 'variety_digital', [
				'ereader_url' => 'https://read.variety.com/launch.aspx',
			]);
			wp_enqueue_script('variety_digital_scripts');
		}
	}
}

//EOF
