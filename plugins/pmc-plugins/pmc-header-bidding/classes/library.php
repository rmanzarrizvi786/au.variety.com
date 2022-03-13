<?php

namespace PMC\Header_Bidding;

use PMC\Global_Functions\Traits\Singleton;

/**
 * PMC's Header Bidding w/ Prebid.js.
 *
 * See README.md for information and instructions.
 */
class Library {

	use Singleton;

	const PMC_HEADER_BIDDER_SCRIPT_NAME        = 'pmc-header-bidder';
	const PMC_HEADER_BIDDER_PREBID_SCRIPT_NAME = 'pmc-prebid';
	const PMC_HEADER_BIDDER_SCRIPT_OBJECT      = 'pmc_header_bidder_script_object';
	const PMC_HEADER_BIDDER_TIMEOUT            = 'pmc_header_bidder_timeout';
	const PMC_HB_GALLERY_TIMEOUT               = 'pmc_hb_gallery_timeout';
	const PMC_HEADER_BIDDING_ENABLED           = 'pmc_header_bidding_enabled';
	const PMC_HEADER_BIDDING_ON_GALLERY        = 'pmc_header_bidding_on_gallery';
	const PMC_HEADER_BIDDER_PREFIX             = 'pmc_header_bidder_';
	const PMC_HEADER_BIDDER_CHEEZ_GROUP        = 'pmc_header_bidder_group';
	const PMC_HEADER_BIDDING_FB_WRAPPER        = 'pmc_header_bidding_fb_wrapper';

	/**
	 * Final object to be passed into prebid js configuration.
	 * This var is localized into an object for front end consumption.
	 *
	 * @var object $prebid_object
	 */
	var $prebid_object = array();

	/**
	 * Initialize the class.
	 */
	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
		add_filter( 'pmc_header_bidder_active', array( $this, 'is_bidding_enabled' ) );
		add_action( 'wp', array( $this, 'action_wp' ) );
	}

	/**
	 * Setup and enqueue header bidding
	 */
	public function action_wp() {
		if ( $this->is_bidding_enabled() ) {

			// Populate our internal prebid object
			$this->prebid_object = array(
				self::PMC_HEADER_BIDDER_TIMEOUT     => get_option( 'cap_' . self::PMC_HEADER_BIDDER_TIMEOUT ),
				self::PMC_HEADER_BIDDING_ON_GALLERY => get_option( 'cap_' . self::PMC_HEADER_BIDDING_ON_GALLERY ),
				self::PMC_HB_GALLERY_TIMEOUT        => intval( get_option( 'cap_' . self::PMC_HB_GALLERY_TIMEOUT ) ),
			);

			// Add bidding information into each ad rendered on the page
			add_filter( 'pmc_adm_google_publisher_ad_item_bids', array( $this, 'filter_pmc_adm_google_publisher_ad_item_bids' ), 10, 2 );
			add_filter( 'pmc_header_bidding_outstream_media_types', array( $this, 'outstream_media_types' ), 10, 2 );

			// load the prebid.js before gpt.js is loaded in pmc-adm loader-v3.js.
			add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ), 9 );
			add_filter( 'script_loader_tag', array( $this, 'filter_script_loader_tag' ), 10, 2 );
		}

	}

	/**
	 * Enqueue scripts.
	 */
	public function action_wp_enqueue_scripts() {

		$script_extension = '.js';
		$prebid_fb_wrapper = get_option( 'cap_' . self::PMC_HEADER_BIDDING_FB_WRAPPER );

		if ( \PMC::is_production() ) {
			$script_extension = '.min.js';
		}

		$prebid_script_url = PMC_HEADER_BIDDING_URL . 'assets/js/prebid' . $script_extension;
		$prebid_script_version = 'v1.24.0';

		if ( 'yes' === $prebid_fb_wrapper ) {
			$prebid_script_url = PMC_HEADER_BIDDING_URL . 'assets/js/fb-prebid' . $script_extension;
			$prebid_script_version = 'v0.16.0';
		}

		wp_enqueue_script(
			self::PMC_HEADER_BIDDER_PREBID_SCRIPT_NAME,
			$prebid_script_url,
			array( 'jquery' ),
			$prebid_script_version
		);

		// Register the script.
		wp_register_script(
			self::PMC_HEADER_BIDDER_SCRIPT_NAME,
			PMC_HEADER_BIDDING_URL . 'assets/js/header-bidding' . $script_extension,
			array( self::PMC_HEADER_BIDDER_PREBID_SCRIPT_NAME ),
			'1.1'
		);

		// Localize the script.
		wp_localize_script(
			self::PMC_HEADER_BIDDER_SCRIPT_NAME,
			self::PMC_HEADER_BIDDER_SCRIPT_OBJECT,
			$this->prebid_object
		);

		wp_enqueue_script( self::PMC_HEADER_BIDDER_SCRIPT_NAME );
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		$cheezcap_options = array(
			new \CheezCapDropdownOption(
				__( 'PMC Header Bidding', 'pmc-header-bidding' ),
				__( 'When set to YES, the header bidding is enabled across the site', 'pmc-header-bidding' ),
				self::PMC_HEADER_BIDDING_ENABLED,
				array( 'no', 'yes' ),
				0, // first option => No.
				array( __( 'No', 'pmc-header-bidding' ), __( 'Yes', 'pmc-header-bidding' ) )
			),
			new \CheezCapTextOption(
				__( 'Header bidder timeout', 'pmc-header-bidding' ),
				__( 'Enter the timeout in MS for prebid to halt bidding', 'pmc-header-bidding' ),
				self::PMC_HEADER_BIDDER_TIMEOUT,
				'1000'
			),
			new \CheezCapDropdownOption(
				__( 'Enable Header Bidding on Gallery pages', 'pmc-header-bidding' ),
				__( 'When set to YES, the header bidding is enabled on gallery pages', 'pmc-header-bidding' ),
				self::PMC_HEADER_BIDDING_ON_GALLERY,
				array( 'no', 'yes' ),
				0, // first option => No.
				array( __( 'No', 'pmc-header-bidding' ), __( 'Yes', 'pmc-header-bidding' ) )
			),
			new \CheezCapDropdownOption(
				__( 'Enable FB Header Bidding wrapper(alpha)', 'pmc-header-bidding' ),
				__( 'When set to YES, prebidjs wrapper is switched to FB prebidjs wrapper', 'pmc-header-bidding' ),
				self::PMC_HEADER_BIDDING_FB_WRAPPER,
				array( 'no', 'yes' ),
				0, // first option => No.
				array( __( 'No', 'pmc-header-bidding' ), __( 'Yes', 'pmc-header-bidding' ) )
			),
			new \CheezCapTextOption(
				__( 'Gallery - Header bidder timeout', 'pmc-header-bidding' ),
				__( 'Enter the timeout in MS for prebid to halt bidding on gallery pages', 'pmc-header-bidding' ),
				self::PMC_HB_GALLERY_TIMEOUT,
				'500'
			),

		);

		// Build a CheezCap enable/disable toggle for each vendor
		// registered in each LOB.
		$vendors_param_data = $this->get_bidding_vendors();

		if ( ! empty( $vendors_param_data ) && is_array( $vendors_param_data ) ) {
			foreach ( $vendors_param_data as $vendor_name => $vendor_config ) {
				$uc_vendor_name = ucfirst( $vendor_name );
				$cheezcap_options[] = new \CheezCapDropdownOption(
					$uc_vendor_name,
					// translators: %s is vendor name.
					sprintf( __( 'Set to Yes to activate %s Bidding', 'pmc-header-bidding' ), $uc_vendor_name ),
					self::PMC_HEADER_BIDDER_PREFIX . $vendor_name,
					array( 'no', 'yes' ),
					0, // first option => No.
					array( __( 'No', 'pmc-header-bidding' ), __( 'Yes', 'pmc-header-bidding' ) )
				);
			}
		}

		$cheezcap_groups[] = new \CheezCapGroup( __( 'Header Bidder', 'pmc-header-bidding' ), self::PMC_HEADER_BIDDER_CHEEZ_GROUP, $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Is header bidder enabled?
	 *
	 * @return bool True on success, false on failure.
	 */
	public function is_bidding_enabled() {

		$prebid_enabled    = strtolower( get_option( 'cap_' . self::PMC_HEADER_BIDDING_ENABLED ) );
		$prebid_on_gallery = get_option( 'cap_' . self::PMC_HEADER_BIDDING_ON_GALLERY );
		$page_type         = get_query_var( 'post_type', 'post' );

		if ( 'yes' === $prebid_enabled ) {

			if ( class_exists( '\PMC_Gallery_Defaults' ) && defined( '\PMC_Gallery_Defaults::name' ) ) {
				if ( \PMC_Gallery_Defaults::name === $page_type && 'yes' !== $prebid_on_gallery ) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Get vendors and the params they require, defined per lob.
	 */
	public function get_bidding_vendors() {
		return apply_filters( 'pmc_header_bidder_filter_vendors', array() );
	}

	/**
	 * Get vendors ad data mapping, defined per lob.
	 */
	public function get_vendors_ad_data_mapping() {
		return apply_filters( 'pmc_header_bidder_filter_bidder_params', array() );
	}

	/**
	 * Helper to determine if a vendor is enabled or not.
	 *
	 * @param string $vendor_name The vendor's name.
	 *
	 * @return bool True when the vendor is enabled.
	 *              False when the vendor is not enabled.
	 */
	public function is_vendor_enabled( $vendor_name = '' ) {

		if ( empty( $vendor_name ) ) {
			return false;
		}

		$vendor_enabled = get_option( 'cap_' . self::PMC_HEADER_BIDDER_PREFIX . $vendor_name );

		if ( 'yes' === $vendor_enabled ) {
			return true;
		}

		return false;
	}

	/**
	 * Filters the default ad list and adds our options to the output so that it can be used by prebid to create our
	 * configuration details for bids. This essentially builds the adUnit object of prebid
	 *
	 * @param bool|array $bids The bids for the current ad unit.
	 *                         False when there are no bids for the ad unit.
	 *
	 * @param array      $ad   Ad unit.
	 *
	 * @return bool|array $bids An array of bidding data when there are bids for the ad.
	 *                          False when there are no bids for the given ad.
	 */
	public function filter_pmc_adm_google_publisher_ad_item_bids( $bids = false, $ad = array() ) {

		// If oop ad then skip adding bidders and set as false.
		if ( isset( $ad['slot-type'] ) && 'oop' === $ad['slot-type'] ) {
			return $bids;
		}

		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return $bids;
		}

		$bidding_data = $this->extract_bidder_configs( $ad );

		if ( $bidding_data ) {
			if ( ! empty( $ad['div-id'] ) ) {
				$bidding_data['code'] = $ad['div-id'];
			}
			if ( ! empty( $ad['ad-widths'] ) ) {
				if ( ! empty( $bidding_data['mediaTypes'] ) ) {
					$bidding_data['sizes'] = [ [ 640, 480 ] ];
				} else {
					$bidding_data['sizes'] = $ad['ad-widths'];

					//Adding mediaTypes config for all banner ads
					$bidding_data['mediaTypes'] = [
						'banner' => [ 'sizes' => $bidding_data['sizes'] ],
					];
				}
			}
			$bids = $bidding_data;
		}

		return $bids;
	}

	/**
	 * Filter function to add async attribute.
	 *
	 * @param string $tag Enqueued script tag.
	 * @param string $handle Enqueued script handle.
	 *
	 * @return mixed
	 */
	public function filter_script_loader_tag( $tag, $handle ) {

		if ( false !== strpos( $handle, 'pmc-prebid-js' ) ) {
			return str_replace( '<script ', '<script async ', $tag );
		}

		return $tag;
	}

	/**
	 * Build the bidder params per ad unit rendered on the page.
	 *
	 * @param array $ad The current Ad Manager Ad Unit being iterated over.
	 *
	 * @return array|false An array of bidders and bidder params for the ad.
	 *                     False on failure.
	 */
	public function extract_bidder_configs( $ad = array() ) {

		$vendors_param_data = $this->get_bidding_vendors();

		// Bail if there is no data for any vendors
		if ( empty( $vendors_param_data ) || ! is_array( $vendors_param_data ) ) {
			return false;
		}

		$bidding_data = $this->get_vendors_ad_data_mapping();

		// Bidding data may not be needed for each vendor
		// pass it on if it's there, but don't bail if it's missing.

		$ad_bidding_data = array();

		// Loop though each vendor's bidding data and assemble the
		// bidding data which gets bundled with the ad.

		$outstream_ad_config = apply_filters( 'pmc_header_bidding_outstream_ads_config', array() );
		$ad_location         = ( ! empty( $ad['location'] ) ) ? $ad['location'] : '';

		foreach ( $vendors_param_data as $vendor_name => $vendor_param_data ) {

			// Bail if the vendor is not enabled
			if ( ! $this->is_vendor_enabled( $vendor_name ) ) {
				continue;
			}

			// Bail if there are no defined-params in the vendor data
			if ( empty( $vendor_param_data ) || ! is_array( $vendor_param_data ) ) {
				continue;
			}

			// Bidding data may not be needed for each vendor
			// pass it on if it's there, but don't bail if it's missing.
			$vendor_bidding_data = array();
			if ( ! empty( $bidding_data[ $vendor_name ] ) ) {
				$vendor_bidding_data = $bidding_data[ $vendor_name ];
			}

			// Begin assembling the vendor's bidding data for this ad
			//IF outsream video is enabled then allow eligible vendors to bid

			if ( ( ! \PMC::is_mobile() ) && ! empty( $outstream_ad_config['ad_locations'] ) && ! empty( $outstream_ad_config['ad_locations'][ $ad_location ] ) ) {
				$eligible_outsream_vendors = $outstream_ad_config['ad_locations'][ $ad_location ];

				if ( ! in_array( $vendor_name, $eligible_outsream_vendors, true ) ) {
					continue;
				}
			}
			$vendor_ad_bidding_data = array(
				'bidder' => $vendor_name,
			);

			// Loop through each of the vendor's params and build the value for each.
			foreach ( $vendor_param_data as $param_name => $param_value ) {

				// Bail if the param name isn't set/not an associative array
				if ( empty( $param_name ) || ! is_string( $param_name ) ) {
					continue;
				}

				/**
				 * Build the vendor's ad bidding param data.
				 *
				 * http://prebid.org/dev-docs/bidders.html
				 * pmc_header_bidder_filter_vendors
				 *
				 * e.g.
				 * pmc_header_bidder_filter_appnexus_params
				 * pmc_header_bidder_filter_pubmatic_params
				 * ...
				 *
				 * @param string $param_value         The value of the vendor paramater.
				 * @param string $param_name          The name of the vendor paramater.
				 * @param string $vendor_name         The name of the vendor.
				 * @param array  $vendor_bidding_data The vendor's bidding data.
				 * @param array  $ad                  The current ad.
				 */
				$param_value = apply_filters( "pmc_header_bidder_filter_{$vendor_name}_params", $param_value, $param_name, $vendor_bidding_data, $ad );

				if ( empty( $param_value ) ) {
					continue;
				}

				$vendor_ad_bidding_data['params'][ $param_name ] = $param_value;
			}

			//Setting alias name if any by calling filter hook
			$vendor_ad_bidding_data['bidder'] = apply_filters( "pmc_header_bidder_filter_{$vendor_name}_alias_name", $vendor_name );

			// Assemble the final bidding data for each vendor
			if ( ! empty( $vendor_ad_bidding_data['bidder'] ) && ! empty( $vendor_ad_bidding_data['params'] ) ) {
				//outstream vendor params
				if ( $this->is_outstream_ad_unit( $ad_location, $outstream_ad_config ) ) {
					$vendor_ad_bidding_data['params'] = apply_filters(
						sprintf( 'pmc_header_bidding_%s_outstream_params', sanitize_title_with_dashes( $vendor_name ) ),
						$vendor_ad_bidding_data['params']
					);
				}
				$ad_bidding_data['bids'][] = $vendor_ad_bidding_data;
			}
		}

		//outstream ad unit params
		if ( $this->is_outstream_ad_unit( $ad_location, $outstream_ad_config ) ) {
			$ad_bidding_data = apply_filters( 'pmc_header_bidding_outstream_media_types', $ad_bidding_data );
		}
		if ( ! empty( $ad_bidding_data ) &&
				 ! empty( $ad_bidding_data['bids'] &&
				 is_array( $ad_bidding_data['bids'] ) ) ) {

			return $ad_bidding_data;
		}

		return false;
	}

	/**
	 * @param array $ad_bidding_data
	 *
	 * @return mixed
	 */
	public function outstream_media_types( $ad_bidding_data = array() ) {

		$ad_bidding_data['mediaTypes'] = [
			'video' => [
				'context' => 'outstream',
			],
		];
		return $ad_bidding_data;
	}

	/**
	 * Check if Ad unit is outstream ad unit or not.
	 *
	 * @param $ad_location
	 * @param $outstream_ad_config
	 *
	 * @return bool
	 */
	public function is_outstream_ad_unit( $ad_location, $outstream_ad_config ) {
		if ( ( ! \PMC::is_mobile() ) && ! empty( $outstream_ad_config['ad_locations'] ) && ! empty( $outstream_ad_config['ad_locations'][ $ad_location ] ) ) {
			return true;
		} else {
			return false;
		}
	}
}


// EOF
