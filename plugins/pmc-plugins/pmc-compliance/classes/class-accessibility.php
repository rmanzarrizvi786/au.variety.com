<?php

namespace PMC\Compliance;

use PMC\Global_Functions\Traits\Singleton;

class Accessibility {

	use Singleton;

	const PAGE_NAME = 'accessibility';

	protected function __construct() {

		add_action( 'admin_init', [ $this, 'create_page' ] );

		add_filter( 'the_content', [ $this, 'the_content' ] );
	}

	/**
	 * @param $content
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public function the_content( $content ) {

		if ( ! is_page( self::PAGE_NAME ) ) {

			return $content;
		}

		return \PMC::render_template(
			sprintf( '%s/template-parts/accessibility.php', untrailingslashit( PMC_COMPLIANCE_ROOT ) ),
			[],
			false
		);
	}

	public function create_page() {

		if ( ! current_user_can( 'edit_posts' ) || wp_doing_ajax() ) {
			return;
		}

		$is_page_already_added = \pmc_get_option( 'pmc_acc_page_added' );

		if ( '1' === $is_page_already_added ) {
			return;
		}

		$accessibility_page = get_page_by_path( self::PAGE_NAME );

		if ( ! empty( $accessibility_page->ID ) ) {
			return;
		}

		$accessibility_page_args = [
			'post_title'  => 'Accessibility',
			'post_name'   => self::PAGE_NAME,
			'post_type'   => 'page',
			'post_status' => 'publish',
		];

		$page_id = wp_insert_post( $accessibility_page_args, true );

		\pmc_add_option( 'pmc_acc_page_added', '1' );

		return $page_id;
	}

	/**
	 * Get Site related accessibility config data
	 *
	 * @param string $sitename
	 *
	 * @return string[]
	 */
	public function get_site_data( $sitename = '' ) {

		$config = [
			'alphadata'       => [
				'sitename' => 'AlphaData',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Border City Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'artnews'         => [
				'sitename' => 'Art Media',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Art Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'bgr'             => [
				'sitename' => 'BGR',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'BGR Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'blogher'         => [
				'sitename' => 'BlogHer',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'SheMedia, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'deadline'        => [
				'sitename' => 'Deadline',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Deadline Hollywood, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'dirt'            => [
				'sitename' => 'Dirt.com',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Dirt.com, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'footwearnews'    => [
				'sitename' => 'FootwearNews',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Fairchild Publishing LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'goldderby'       => [
				'sitename' => 'Gold Derby',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Gold Derby Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'indiewire'       => [
				'sitename' => 'IndieWire',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Indiewire Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'pmc'             => [
				'sitename' => 'Penske Media Corporation',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Penske Media Corporation, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'robbreport'      => [
				'sitename' => 'Robb Report',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Robb Report Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'rr1'             => [
				'sitename' => 'Robb Report',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Robb Report Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'rollingstone'    => [
				'sitename' => 'Rolling Stone',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Rolling Stone Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'shemedia'        => [
				'sitename' => 'SheMedia',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'SheMedia, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'sheknows'        => [
				'sitename' => 'SheKnows',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'SheMedia, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'soaps.com'       => [
				'sitename' => 'Soaps',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'SheMedia, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'stylecaster'     => [
				'sitename' => 'Stylecaster',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'SheMedia, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'hollywoodlife'   => [
				'sitename' => 'Hollywood Life',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Hollywoodlife.com, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'sourcingjournal' => [
				'sitename' => 'Sourcing Journal',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Sourcing Journal Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'sportico'        => [
				'sitename' => 'Sportico',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Sportico Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'spy'             => [
				'sitename' => 'Spy.com',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Spy Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'tvline'          => [
				'sitename' => 'TVLine',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'TVLine Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'variety'         => [
				'sitename' => 'Variety',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Variety Media, LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
			'wwd'             => [
				'sitename' => 'WWD',
				'email'    => 'accessibility@pmc.com',
				'address'  => 'Fairchild Publishing LLC, Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
			],
		];

		if ( ! empty( $config[ $sitename ] ) ) {
			return $config[ $sitename ];
		}
	}
}
