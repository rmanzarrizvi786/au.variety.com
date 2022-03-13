<?php

class Google_Publisher_Provider extends PMC_Ad_Provider {

	protected $_id = 'google-publisher';
	protected $_title = 'Google Publisher';
	protected $_pmc_ads;
	protected $_auto_uid = 0;

	/**
	 * Fields to save as meta data.
	 *key
	 sitename
	 zonename
	 divid
	 width
	 * @var array
	 */
	protected $_fields = array(
		'sitename' => array(
			'title' => 'Sitename',
			'required' => true,
			'placeholder' => 'pmc (require)',
		),
		'zone' => array(
			'title' => 'Zone',
			'required' => true,
			'placeholder' => 'homepage (require)',
		),
		'div-id' => array(
			'title' => 'Div ID',
			'required' => true,
			'placeholder' => 'div-gpt-12363453o8 (require)',
		),
		'slot-type' => array(
			'title'     => 'Slot Type',
			'required'  => true,
			'options'   => array(
				'normal' => 'Normal',
				'oop'    => 'Out of page',
				'fluid'  => 'Fluid',
			),
		),
		'ad-width' => array(
			'title'       => 'Ad width: Format [300, 50], [320, 50]',
			'required'    => true,
			'placeholder' => '[300, 250] (require)',
			'validator'   => 'gpt-ad-width',
		),
		'dynamic_slot' => array(
				'title'    => 'Dynamic Ad Unit Format',
				'required' => true,
			),
	);

	/**
	 * Fields to show in the admin form
	 *
	 * @var array
	 */
	protected $_admin_templates = [
		'basic',
		'refreshable',
		'when-to-render',
		'device',
		'status',
		'time-frame',
		'floating-preroll',
		'contextual-player',
		'custom-google-publisher',
		'ad-unit-order',
		'lazy-load',
		'conditionals',
		'targetting',
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
		if ( is_admin() ) {
			$this->_fields['div-id']['default'] = $this->_fields['div-id']['placeholder'] = 'gpt-' . time();
			return;
		}

		if ( ! is_object( $this->_pmc_ads ) ) {
			$this->_pmc_ads = PMC_Ads::get_instance();
		}

		add_action( 'wp_footer', array( $this, 'wp_footer' ), 5 );

	}

	public function wp_footer() {
		$this->out_ad_slot_definitions();	//define and render ads
	}

	/**
	 * This function prints out ad slot definition and render javascript for the
	 * ads that are to be displayed on the current page.
	 *
	 * @since 2013-10-18 Amit Gupta
	 * @version 2013-10-30 Amit Gupta
	 */
	public function out_ad_slot_definitions() {
		if ( ! is_object( $this->_pmc_ads ) || empty( $this->_pmc_ads->ads ) ) {
			return;
		}

		$hostname = ! empty( $this->_config['hostname'] ) ? $this->_config['hostname'] : parse_url( get_home_url(), PHP_URL_HOST );
		$hostname = apply_filters ( 'pmc_adm_hostname', $hostname );

		PMC::render_template( PMC_ADM_DIR . '/templates/ads/google-publisher-slot-definition.php', array(
			'ads' => $this->_pmc_ads->ads,
			'provider' => $this,
			'hostname' => $hostname,
		), true );
	}

	public function prepare_ad_data( $data ) {
		$data = array_merge( $this->_config, $data );

		$data = apply_filters( "pmc_adm_gpt_prepare_ad_data", $data );

		if ( !isset( $data['css-class'] ) ) {
			$data['css-class'] = '';
		}

		$data['key'] = apply_filters( 'pmc_adm_gpt_publisher_key', $this->get_key() );

		$data['ad-widths'] = $this->parse_ad_widths( $data['ad-width'] );

		if ( ! empty( $data['location'] ) && in_array( $data['location'], array( 'gallery-interstitial', 'interstitial', 'prestitial' ), true ) ) {
			$data['is-ad-rotatable'] = 'no';
		}

		return $data;
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

		$data = $this->prepare_ad_data( $data );

		// make div-id unique to avoid any potential dup that might cause entire gpt ads not working.
		$data['div-id'] = $data['div-id'] . '-uid' . ( $this->_auto_uid++ );

		$this->_pmc_ads->ads[] = $data;

		$template_file = sprintf( '%s/templates/ads/%s.php', untrailingslashit( PMC_ADM_DIR ), $this->get_id() );

		$ad_data = [
			'ad'       => $data,
			'provider' => $this,
		];

		return PMC::render_template( $template_file, $ad_data, $echo );
	}

	public function parse_ad_widths( $data ) {
		$widths = array();
		foreach ( explode( '],[', str_replace( ' ', '', $data ) ) as $pair ) {
			$widths[] =  array_map( 'intval', explode( ',', trim( $pair, '[]' ) ) );
		}
		return $widths;
	}

	/**
	 * Preparing ad settings object for the page
	 *
	 * @param $ads
	 *
	 * @return array|bool
	 */
	public function prepare_ad_settings( $ads ) {
		global $post;

		if ( empty( $ads ) ) {
			return false;
		}

		$ad_targetings = array();
		$ad_list = array();
		foreach( $ads as $ad ) {
			$ad_item = array(
				'targeting' => array(),
				);
			if ( !empty( $ad['targeting_data'] ) ) {
				foreach ( $ad['targeting_data'] as $data ) {
					if ( 'pos' === $data['key'] ) {
						$ad_item['targeting']['pos'][] = $data['value'];
					} else{
						$ad_targetings[ $data['key'] ] = $data['value'];
					}
				}
			}

			$ad_item['targeting']['refresh'] = '0';

			$ad_item['slot']         = apply_filters( 'pmc_adm_google_publisher_slot', sprintf( '/%s/%s/%s', $ad['key'], $ad['sitename'], $ad['zone'] ), $ad );
			$ad_item['id']           = $ad['div-id'];
			$ad_item['width']        = $ad['ad-widths'];
			$ad_item['adunit-order'] = ! empty( $ad['adunit-order'] ) ? $ad['adunit-order'] : 10;

			/**
			 * Filters the bidders for each advertisement.
			 *
			 * @param bool|array $bids The bids for the current ad unit.
			 *                         False when there are no bids for the ad unit.
			 *
			 * @param array      $ad   Ad unit.
			 */
			$ad_item['bidders']         = apply_filters( 'pmc_adm_google_publisher_ad_item_bids', false, $ad );
			$ad_item['is_lazy_load']    = ( isset( $ad['is_lazy_load'] ) ) ? $ad['is_lazy_load'] : false;
			$ad_item['is_ad_rotatable'] = ( isset( $ad['is-ad-rotatable'] ) ) ? $ad['is-ad-rotatable'] : false;
			$global_ad_refresh_time     = PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_global_ad_refresh_time_limit' );
			$ad_item['ad_refresh_time'] = ( ! empty( $ad['ad-refresh-time'] ) ) ? $ad['ad-refresh-time'] : $global_ad_refresh_time;
			$ad_item['ad_refresh_time'] = intval( $ad_item['ad_refresh_time'] ) * 1000; //converting to milliseconds

			$group = ! empty( $ad['ad-group'] ) ? $ad['ad-group'] : 'default';
			switch ( $ad['location'] ) {
				case 'gallery-interstitial':
					$group = 'interrupt-ads-gallery';
					break;
				case 'interstitial':
				case 'prestitial':
					$group = 'interrupt-ads';
					break;
			}

			if ( !empty( $ad['slot-type'] ) ) {
				switch( $ad['slot-type'] ) {
					case 'oop':
						$ad_item['oop'] = true;
						unset( $ad_item['width'] );
						break;
					case 'fluid':
						$ad_item['fluid'] = true;
						unset( $ad_item['width'] );
						$ad_item['width'] = 'fluid';
						break;
				}
			}
			$ad_list[$group][] = $ad_item;

			if ( ! empty( $ad['out-of-page'] ) && strtolower( $ad['out-of-page'] ) === 'yes' ) {
				$ad_item = (array) $ad_item;
				$ad_item['oop'] = true;
				$ad_item['id'] = $ad['div-id'].'-oop';
				unset( $ad_item['width'] );
				$ad_list[$group][] = $ad_item;
			}
		} // foreach ads

		$hostname = ! empty( $this->_config['hostname'] ) ? $this->_config['hostname'] : parse_url( get_home_url(), PHP_URL_HOST );
		$hostname = apply_filters ( 'pmc_adm_hostname', $hostname );

		if ( !empty( $hostname ) ) {
			if ( isset( $ad_targetings['host'] ) ) {
				if ( is_string( $ad_targetings['host'] )  && $hostname !== $ad_targetings['host'] ) {
					$ad_targetings['host'] = array( $hostname );
				}else{
					$ad_targetings['host'] = (array) $ad_targetings['host'];
				}

			} else {
				$ad_targetings['host'] = $hostname;
			}
		} // if

		if ( empty( $ad_targetings['kw'] ) ) {
			$ad_targetings['kw'] = $this->get_keywords();
		} // if

		$ad_targetings['pm'] = '';

		if ( empty( $ad_targetings['topic'] ) ) {
			$ad_targetings['topic'] = $this->get_topics();
		} // if

		// Add targeting for videos in the post content
		$ad_targetings['featured-video'] = 'no';
		$ad_targetings['content-video'] = 'no';

		if ( is_singular() ) {

			// PMCRS-284
			// Send 'featured-video = yes|no' when a featured video is present for the post.
			if ( class_exists( 'PMC_Featured_Video_Override' ) ) {
				if ( PMC_Featured_Video_Override::is_jwplayer_or_youtube_video( $post->ID ) ) {
					$ad_targetings['featured-video'] = 'yes';
				}
			}

			// PMCRS-284
			// Send a 'content-video = yes|no' when a YouTube video is present in the post content
			$embeds = get_media_embedded_in_content( apply_filters( 'the_content', $post->post_content ) );

			if ( ! empty( $embeds ) && is_array( $embeds ) ) {
				foreach ( $embeds as $embed ) {
					if ( false !== strpos( $embed, 'youtube' ) ) {
						$ad_targetings['content-video'] = 'yes';
						break;
					}
				}
			}

			$ad_targetings['ci'] = sprintf( '%s-%s', esc_html( get_bloginfo( 'name' ) ), $post->ID );

		}

		$ad_targetings = apply_filters( 'pmc_adm_google_publisher_ad_targeting', $ad_targetings );

		// fix associated array value
		foreach ( $ad_targetings as $key => $value ) {
			if ( is_array( $value ) ) {
				// make sure value is not associated array so json_encode can be encode properly as array
				$ad_targetings[ $key ] = array_values( $value );
			}
		}

		$ad_list        = $this->reorder_adlist( $ad_list );

		//These site vast tags will be added from theme
		$ad_vast_tags = apply_filters( 'pmc_adm_google_publisher_ad_vast_tags', [] );

		return array(
				'ad_targetings'     => $ad_targetings,
				'ad_list'           => $ad_list,
				'ad_vast_tags'      => $ad_vast_tags,
			);
	} // function

	/**
	 * Re arrange ad list so based on 'adunit-order'
	 * @param array $ad_list list of ads that are configured for the current page.
	 *
	 * @return array List of ads with re-ordered
	 */
	public function reorder_adlist( $ad_list = array() ) {
		if ( ! empty( $ad_list ) && is_array( $ad_list ) && ! empty( $ad_list['default'] ) && is_array( $ad_list['default'] ) ) {

			usort( $ad_list['default'], function( $a, $b ) {
				return intval( $a['adunit-order'] ) - intval( $b['adunit-order'] );
			} );
		}
		return $ad_list;
	}

}

//EOF
