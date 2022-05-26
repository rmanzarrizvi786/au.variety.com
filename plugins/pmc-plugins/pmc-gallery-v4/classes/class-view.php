<?php

/**
 * PMC Gallery View.
 *
 * @package pmc-gallery-v4
 *
 * @since 2019-02-28 Sayed Taqui
 */

namespace PMC\Gallery;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Global_Functions\Utility\Device;
use PMC_Ads;

class View extends View_Legacy
{

	use Singleton;

	/**
	 * Supported gallery types, as returned by self::get_current_gallery_type().
	 *
	 * Used for invalidations, see self::rebuild_gallery_by_id().
	 */
	protected const GALLERY_TYPES = [
		false,
		'horizontal',
		'vertical',
		'runway',
	];

	/**
	 * Gallery data.
	 *
	 * @var array
	 */
	protected static $_gallery = array();

	/**
	 * Gallery configuration.
	 *
	 * @var array
	 */
	protected static $_gallery_config = array();

	/**
	 * Gallery display setting.
	 *
	 * @var string
	 */
	protected static $_gallery_display = '';

	/**
	 * Has linked gallery.
	 *
	 * @var null
	 */
	protected $_has_linked_gallery = null;

	/**
	 * Has gallery.
	 *
	 * @var null
	 */
	protected $_has_gallery = null;

	/**
	 * Use to save the page view events that we remove so we can override and add hash value ro page url
	 *
	 * @var array
	 */
	protected $_pageview_event_names = array();

	/**
	 * Adjacent post term id.
	 */
	private $_term_id_adjacent_post;

	/**
	 * Initialize.
	 */
	protected function _init()
	{

		parent::__construct();

		add_filter('pmc_google_analytics_track_pageview', array($this, 'filter_pmc_google_analytics_track_pageview'));

		// We want to load standard gallery scripts at first priority for faster first paint.
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets_for_galleries'), 1);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets_for_inline_gallery'));

		// Run this late so that themes have a change to register their own default	sizes
		add_action('init', array($this, 'register_gallery_image_sizes'), 99);

		add_filter('post_gallery', array($this, 'gallery_shortcode'), 100, 2);

		add_action('init', array($this, 'action_init'));

		// To remove the responsive ad skins from the gallery pages except vertical gallery.
		add_filter('pmc_adm_dfp_skin_enabled', array($this, 'remove_responsive_ad_skins'));

		// Need to hook this at a higher priority to ensure other hooked functions don't override this function's outcome.
		add_filter('pmc_seo_tweaks_googlebot_news_override', array($this, 'maybe_exclude_googlebot_news_tag'), 20);

		add_action('wp_head', array($this, 'add_next_prev_links'));

		add_filter('pmc_canonical_url', array($this, 'filter_canonical_url'), 20);

		add_filter('the_content', array($this, 'add_vertical_gallery'));

		add_filter('pmc_google_analytics_get_custom_dimensions', array($this, 'update_dimensions_for_gallery_slides'));

		add_filter('body_class', array($this, 'add_gallery_body_class'));

		add_action('wp_footer', array($this, 'add_gallery_modal'));

		add_filter('pmc-tags-filter-tags', array($this, 'filter_tag_options'));

		add_filter('devicepx_enabled', '__return_false');

		add_action('wp_head', array($this, 'add_style_to_hide_content'), 1);

		add_filter('pmc_cxense_page_location', array($this, 'filter_pmc_cxense_page_location'));
	}

	/**
	 * Get gallery configuration.
	 *
	 * @return array
	 */
	public function get_gallery_config()
	{
		if (!empty(self::$_gallery_config)) {
			return self::$_gallery_config;
		}

		// Options.
		$options               = self::is_runway_gallery() ? Settings::get_instance()->get_runway_gallery_options() : Settings::get_instance()->get_options();
		$interstitial_ad_after = (!empty($options['interstitial_refresh_clicks'])) ? $options['interstitial_refresh_clicks'] : 5;
		$enable_interstitial   = Settings::get_instance()->no_ads_on_this_post() ? false : $options['enable_interstitial'];
		$gallery_type          = self::get_current_gallery_type();

		$gallery_fetch_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce('wp_rest'),
				'post_id'  => get_the_ID(),
			),
			get_rest_url(null, 'pmc-gallery/v4/get-related-gallery-list')
		);

		$gallery = self::fetch_gallery();

		self::$_gallery_config = apply_filters(
			'pmc_gallery_v4_config',
			[
				'gallery'                     => $gallery,
				'galleryCount'                => count($gallery),
				'galleryId'                   => get_the_ID(),
				'type'                        => $gallery_type,
				'galleryTitle'                => get_the_title(),
				'logo'                        => [],
				'i10n'                        => [
					'backToArticle'      => esc_html__('Back to Article', 'pmc-gallery-v4'),
					'backToAllGalleries' => esc_html__('Back to All Galleries', 'pmc-gallery-v4'),
					'backToReview'       => esc_html__('Back to Review', 'pmc-gallery-v4'),
					'backToAllReviews'   => esc_html__('Back to All Reviews', 'pmc-gallery-v4'),
					'thumbnail'          => esc_html__('Thumbnails', 'pmc-gallery-v4'),
					'nextSlide'          => esc_html__('Next Slide', 'pmc-gallery-v4'),
					'prevSlide'          => esc_html__('Previous Slide', 'pmc-gallery-v4'),
					'skipAd'             => esc_html__('Skip Ad', 'pmc-gallery-v4'),
					'skipIn'             => esc_html__('Skip In', 'pmc-gallery-v4'),
					'of'                 => esc_html__('of', 'pmc-gallery-v4'),
					'missingSomething'   => __('You\'re missing something!', 'pmc-gallery-v4'),
					'subscribeNow'       => esc_html__('Subscribe Now', 'pmc-gallery-v4'),
					'next'               => esc_html__('Next', 'pmc-gallery-v4'),
					'nextGallery'        => esc_html__('Next Gallery', 'pmc-gallery-v4'),
					'closeThisMessage'   => esc_html__('Close this message', 'pmc-gallery-v4'),
					'closeModal'         => esc_html__('Close Modal', 'pmc-gallery-v4'),
					'closeGallery'       => esc_html__('Close Gallery', 'pmc-gallery-v4'),
					'startSlideShow'     => esc_html__('Start Slideshow', 'pmc-gallery-v4'),
					'lightBox'           => esc_html__('Lightbox', 'pmc-gallery-v4'),
					'scrollUp'           => esc_html__('Scroll Up', 'pmc-gallery-v4'),
					'scrollDown'         => esc_html__('Scroll Down', 'pmc-gallery-v4'),
					'look'               => esc_html__('Look', 'pmc-gallery-v4'),
					'readMore'           => esc_html__('Read More', 'pmc-gallery-v4'),
					'showLess'           => esc_html__('Show Less', 'pmc-gallery-v4'),
					'vertical'           => [
						'photo' => esc_html__('Photo', 'pmc-gallery-v4'),
					],
				],

				//  Ads related settings
				'ads'                         => [],
				'adsProvider'                 => 'boomerang',
				'railBottomAdRefreshInterval' => $options['rail_bottom_ad_refresh_clicks'],
				'adhesionAdRefreshInterval'   => $options['adhesion_ad_refresh_clicks'],
				'adAfter'                     => $options['ad_refresh_clicks'],
				'enableInterstitial'          => $enable_interstitial,
				'interstitialAdAfter'         => intval($interstitial_ad_after),

				'socialIconsUseMenu'          => true,
				'socialIcons'                 => [
					'facebook'  => [],
					'twitter'   => [],
					'pinterest' => [],
					'tumblr'    => [],
				],
				'twitterUserName'             => defined('PMC_TWITTER_SITE_USERNAME') ? PMC_TWITTER_SITE_USERNAME : '',
				'timestamp'                   => [
					'date'     => get_the_date('F d, Y, g:ia'),
					'datetime' => get_the_date('Y-m-d\TH:i:sP'),
				],
				'showThumbnails'              => true,
				'siteTitle'                   => sanitize_text_field(get_bloginfo('name')),
				'siteUrl'                     => trailingslashit(wp_make_link_relative(get_site_url())),
				'pagePermalink'               => trailingslashit(get_permalink()),
				'zoom'                        => $options['enable_zoom'],
				'pinit'                       => $options['enable_pinit'],
				'sponsored'                   => '',
				'sponsoredStyle'              => [],
				'runwayMenu'                  => [],
				'mobileCloseButton'           => '', // PMCP-1298: Added specially for HL.
				'galleryFetchUrl'             => trailingslashit(wp_make_link_relative($gallery_fetch_url)),
				'styles'                      => [
					'header-height'                     => '79px',
					'theme-color'                       => '#d32531',
					'vertical-headline-font-weight'     => 700,
					'vertical-caption-font-weight'      => 300,
					'vertical-subtitle-font-weight'     => 500,
					'vertical-player-font-weight'       => 500,
					'vertical-headline-font-family'     => 'inherit',
					'vertical-caption-font-family'      => 'inherit',
					'vertical-subtitle-font-family'     => 'inherit',
					'vertical-player-font-family'       => 'inherit',
					'vertical-max-image-width'          => 'inherit',
					'horizontal-intro-card-font-family' => 'inherit',
					'horizontal-header-title-style'     => [],
				],
				'forceSameEnding'             => $options['force_same_ending'],
				'subscriptionsLink'           => '',
				'isMobile'                    => \PMC::is_mobile(),
			]
		);

		if ('vertical' !== $gallery_type) {
			$up_next_gallery         = $this->get_adjacent_gallery();
			$up_next_gallery_post_id = (!empty($up_next_gallery['post']) && $up_next_gallery['post'] instanceof \WP_Post) ? $up_next_gallery['post']->ID : '';
			$next_gallery_type       = $this->get_next_gallery_type($up_next_gallery);
			$next_gallery_title      = get_the_title($up_next_gallery_post_id);
			$next_gallery_link       = ($up_next_gallery_post_id) ? wp_make_link_relative(get_permalink($up_next_gallery_post_id)) : '';

			self::$_gallery_config['ads']['rightRailGallery'] = Plugin::get_instance()->get_ads('right-rail-gallery');

			if ($enable_interstitial) {
				self::$_gallery_config['ads']['galleryInterstitial'] = Plugin::get_instance()->get_ads('gallery-interstitial');
			}

			self::$_gallery_config['introCard'] = $this->get_intro_card();

			self::$_gallery_config['nextGallery'] = array(
				'ID'        => $up_next_gallery_post_id,
				'title'     => html_entity_decode($next_gallery_title),
				'link'      => wp_make_link_relative($next_gallery_link),
				'type'      => $next_gallery_type,
				'thumbnail' => wp_make_link_relative($this->get_gallery_first_thumbnail($up_next_gallery_post_id, 'pmc-gallery-s-portrait')),
			);

			self::$_gallery_config['closeButtonLink'] = trailingslashit(wp_make_link_relative($this->get_close_button_link()));
		}

		return self::$_gallery_config;
	}

	/**
	 * Enqueue styles, scripts and script data
	 */
	public function enqueue_assets_for_galleries()
	{
		if (!is_singular(Defaults::NAME)) {
			return;
		}

		$gallery_config = $this->get_gallery_config();
		$gallery_type   = self::get_current_gallery_type();
		$styles         = $this->get_gallery_styles($gallery_config['styles']);

		if (in_array($gallery_type, array('vertical', 'runway'), true)) {
			$handle = Defaults::NAME . '-' . $gallery_type;
		} else {
			$handle = Defaults::NAME;
		}

		wp_enqueue_script($handle);
		wp_enqueue_style($handle);

		if ($styles) {
			wp_add_inline_style($handle, $styles);
		}

		wp_localize_script($handle, 'pmcGalleryExports', $gallery_config);
	}

	/**
	 * Enqueue scripts for inline gallery.
	 */
	public function enqueue_assets_for_inline_gallery()
	{

		if ($this->has_gallery_shortcode() && $this->is_featured_article()) {

			wp_enqueue_script(Defaults::NAME . '-inline');
			wp_enqueue_style(Defaults::NAME . '-inline');
		}
	}

	/**
	 * Action init.
	 */
	public function action_init()
	{

		/**
		 * Add this filter to allow feed to fetch gallery data without making call to function directly.
		 * Legacy filter applied in some themes.
		 */
		add_filter('pmc_fetch_gallery', array($this, 'filter_pmc_fetch_gallery'), 10, 2);
		add_filter('pmc_gallery_linked_post', array($this, 'filter_pmc_gallery_linked_post'), 10, 2);
	}

	/**
	 * Fetch gallery data.
	 *
	 * @param array $gallery_data Gallery data.
	 * @param int   $id           Gallery ID.
	 *
	 * @return array
	 */
	public function filter_pmc_fetch_gallery($gallery_data = array(), $id = 0)
	{
		if (Defaults::NAME === get_post_type($id)) {
			$gallery_data = self::fetch_gallery($id);
		}

		return $gallery_data;
	}

	/**
	 * Fetch linked post ID.
	 *
	 * @param int $linked_post_id Post ID to which the gallery is linked.
	 * @param int $gallery_id     Gallery ID.
	 *
	 * @return int|bool Post ID or false if $gallery_id is not assigned to any post.
	 */
	public function filter_pmc_gallery_linked_post($linked_post_id, $gallery_id)
	{

		$gallery_id = absint($gallery_id);

		if (0 !== $gallery_id && Defaults::NAME === get_post_type($gallery_id)) {
			$linked_post_id = absint(self::get_linked_post_id($gallery_id));
		}

		return $linked_post_id;
	}

	/**
	 * Reset $_gallery.
	 */
	public static function reset_gallery_var()
	{
		self::$_gallery = array();
	}

	/**
	 * Register image sizes for galleries.
	 */
	public function register_gallery_image_sizes()
	{

		/**
		 * Image size configuration.
		 *
		 * 4:3 aspect ratio is being used for landscape and 2:3 for all portrait images.
		 */
		$sizes = array(
			/**
			 * Used In: Standard gallery thumbnails of left column
			 *
			 * Default WP thumbnail will be used for landscape.
			 *
			 * Aspect ratio: 2:3
			 */
			'pmc-gallery-thumbnail-portrait' => array(
				'width'  => 150,
				'height' => 225,
			),
			/**
			 * Used In: Runway gallery right rail thumbnails.
			 *
			 * Aspect Ratio: 2:3
			 */
			'pmc-gallery-runway-thumbnail'   => array(
				'width'  => 110,
				'height' => 165,
			),
			/**
			 * Used In: Runway gallery light-box thumbnails.
			 *
			 * Aspect Ratio: 2:3
			 */
			'pmc-gallery-runway-s'           => array(
				'width'  => 285,
				'height' => 428,
			),
			/**
			 * Used In: Standard gallery light-box thumbnails
			 *
			 * Photon cropping x and y params will be used for cropping portrait image for standard gallery,
			 * because different sizes for landscape and portrait would not look good.
			 *
			 * Aspect ratio: 4:3
			 */
			'pmc-gallery-s'                  => array(
				'width'  => 320,
				'height' => 240,
			),
			/**
			 * Used In: Vertical gallery in small screen when viewport size is below 414px.
			 *
			 * Aspect Ratio: 2:3
			 */
			'pmc-gallery-s-portrait'         => array(
				'width'  => 320,
				'height' => 480,
			),
			/**
			 * Used In: Standard and runway galleries when viewport size is below 800
			 *
			 * The width is 800 because the galleries become full width at this viewport size.
			 *
			 * Aspect ratio: 4:3
			 */
			'pmc-gallery-m'                  => array(
				'width'  => 640,
				'height' => 480,
			),
			/**
			 * Used In: Standard and runway galleries when viewport size is below 800
			 *
			 * The width is 800 because the galleries become full width at this viewport size.
			 *
			 * Aspect Ratio: 2:3
			 */
			'pmc-gallery-m-portrait'         => array(
				'width'  => 640,
				'height' => 960,
			),
			/**
			 * Used In: Standard and runway galleries when viewport size is below 1024
			 *
			 * The width is according to the maximum image area in this viewport size of standard gallery.
			 *
			 * Aspect ratio: 4:3
			 */
			'pmc-gallery-l'                  => array(
				'width'  => 800,
				'height' => 600,
			),
			/**
			 * Used In: Standard and runway galleries when viewport size is below 1024
			 *
			 * The width 680 is according to the maximum image area in this viewport size of standard gallery.
			 *
			 * Aspect ratio: 2:3
			 */
			'pmc-gallery-l-portrait'         => array(
				'width'  => 800,
				'height' => 1200,
			),
			/**
			 * Used In: Standard and runway gallery slider image.
			 *
			 * The width is according to the max width of the image in this viewport size of standard gallery.
			 *
			 * Aspect ratio: 4:3
			 */
			'pmc-gallery-xl'                 => array(
				'width'  => 1024,
				'height' => 768,
			),
			/**
			 * Used In: Standard and runway gallery slider image.
			 *
			 * The width is according to the max width of the image in this viewport size of standard gallery.
			 *
			 * Aspect ratio: 2:3
			 */
			'pmc-gallery-xl-portrait'        => array(
				'width'  => 1024,
				'height' => 1536,
			),
			/**
			 * Used In: All gallery's zoomed image when image is landscape.
			 *
			 * Full width image is not used to avoid cases when the uploaded image is way too large.
			 */
			'pmc-gallery-xxl'                => array(
				'width'  => 1280,
				'height' => 1024,
			),
			/**
			 * Used In: All gallery's zoomed image when its portrait.
			 *
			 * Full width image is not used to avoid cases when the uploaded image is way too large.
			 */
			'pmc-gallery-xxl-portrait'       => array(
				'width'  => 1280,
				'height' => 1920,
			),
		);

		$registered_sizes = \PMC\Image\get_intermediate_image_sizes();

		foreach ($sizes as $name => $size) {
			if (!in_array($name, (array) $registered_sizes, true)) {
				add_image_size($name, $size['width'], $size['height'], false);
			}
		}
	}

	/**
	 * Get image sizes.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @return array
	 */
	public static function get_image_sizes($attachment_id)
	{
		$image_sizes = array();

		$sizes_config = array(
			'pmc-gallery-xxl' => array(
				'landscape' => 'pmc-gallery-xxl',
				'portrait'  => 'pmc-gallery-xxl-portrait',
			),
			'pmc-gallery-xl'  => array(
				'landscape' => 'pmc-gallery-xl',
				'portrait'  => 'pmc-gallery-xl-portrait',
			),
			'pmc-gallery-l'   => array(
				'landscape' => 'pmc-gallery-l',
				'portrait'  => 'pmc-gallery-l-portrait',
			),
			'pmc-gallery-m'   => array(
				'landscape' => 'pmc-gallery-m',
				'portrait'  => 'pmc-gallery-m-portrait',
			),
			'pmc-gallery-s'   => array(
				'landscape' => 'pmc-gallery-s',
				'portrait'  => 'pmc-gallery-s-portrait',
			),
			'thumbnail'       => array(
				'landscape' => 'thumbnail',
				'portrait'  => 'pmc-gallery-thumbnail-portrait',
			),
		);

		if (self::is_runway_gallery()) {
			$sizes_config['pmc-gallery-s'] = array(
				'landscape' => 'pmc-gallery-runway-s',
				'portrait'  => 'pmc-gallery-runway-s',
			);
			$sizes_config['thumbnail']     = array(
				'landscape' => 'pmc-gallery-runway-thumbnail',
				'portrait'  => 'pmc-gallery-runway-thumbnail',
			);
		}

		foreach ($sizes_config as $size_name => $size_config) {
			$image_sizes[$size_name] = self::get_image_size($attachment_id, $size_config);
		}

		return $image_sizes;
	}

	/**
	 * Get single image size.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $size_config   Image size config.
	 *
	 * @return array
	 */
	public static function get_image_size($attachment_id, $size_config)
	{
		$image_meta = wp_get_attachment_metadata($attachment_id);

		if (empty($image_meta)) {
			return [];
		}

		if (!is_array($size_config) || empty($size_config['portrait']) || empty($size_config['landscape'])) {
			return [];
		}

		$is_portrait = false;

		if (
			isset($image_meta['height'])
			&& isset($image_meta['width'])
			&& intval($image_meta['height'])
			&& intval($image_meta['width'])
		) {
			$is_portrait = (($image_meta['height'] / $image_meta['width']) > 1);
		}

		$size = ($is_portrait) ? $size_config['portrait'] : $size_config['landscape'];

		if ('pmc-gallery-s-portrait' === $size && self::is_standard_gallery()) {
			$image_size = self::get_standard_gallery_small_thumbnail_image_size($attachment_id, $image_meta['width']);

			if (!empty($image_size)) {
				return $image_size;
			}
		}

		$image = wp_get_attachment_image_src($attachment_id, $size);

		return array(
			'src'    => (is_array($image) && !empty($image[0])) ? $image[0] : '',
			'width'  => (is_array($image) && !empty($image[1])) ? $image[1] : '',
			'height' => (is_array($image) && !empty($image[2])) ? $image[2] : '',
		);
	}

	/**
	 * Get standard gallery small thumbnail image size.
	 *
	 * @param int $attachment_id      Attachment ID
	 * @param int $actual_image_width Actual image width.
	 *
	 * @return array
	 */
	public static function get_standard_gallery_small_thumbnail_image_size($attachment_id, $actual_image_width)
	{

		if (!intval($attachment_id)) {
			return [];
		}

		$attachment_url       = wp_get_attachment_url($attachment_id);
		$desired_image_width  = 414;
		$desired_image_height = 512;

		$image_src = $attachment_url;

		// @codeCoverageIgnoreStart
		if (function_exists('jetpack_photon_url')) {
			$image_src = jetpack_photon_url(
				$attachment_url,
				[
					'w' => $desired_image_width,
					'h' => $desired_image_height,
				]
			);
		}
		// @codeCoverageIgnoreEnd

		return [
			'src'    => $image_src,
			'width'  => $desired_image_width,
			'height' => $desired_image_height,
		];
	}

	/**
	 * If has linked gallery.
	 *
	 * @return bool|null
	 */
	public function has_linked_gallery()
	{
		if (is_null($this->_has_linked_gallery)) {
			$this->_has_linked_gallery = false;

			/**
			 * Being very verbose with "has preview" tests to make it clear what's being tested and what the hierarchy is.
			 * There's only one test now, but it's possible for it to grow to more tests like $this->has_gallery()
			 */
			if (is_singular()) {
				if (!$this->_has_linked_gallery) {
					$this->_has_linked_gallery = (bool) get_post_meta(get_queried_object_id(), 'pmc-gallery-linked-gallery', true);
				}
			}
		}

		return $this->_has_linked_gallery;
	}

	/**
	 * Checks if has gallery or gallery short-code ( inline gallery ).
	 *
	 * @todo This is a legacy method and is confusing, remove after refactoring.
	 *
	 * @deprecated
	 *
	 * @return bool|null
	 */
	public function has_gallery()
	{
		if (is_null($this->_has_gallery)) {
			$this->_has_gallery = false;

			// Being very verbose with "has gallery" tests to make it clear what's being tested and what the hierarchy is.
			if (is_singular()) {
				if (!$this->_has_gallery) {
					$this->_has_gallery = (Defaults::NAME === get_post_type());
				}

				if (!$this->_has_gallery) {
					$this->_has_gallery = $this->has_gallery_shortcode();
				}
			}
		}

		return $this->_has_gallery;
	}

	/**
	 * Check if gallery has short-code.
	 *
	 * @return bool
	 */
	public function has_gallery_shortcode()
	{
		global $post;

		return (is_singular() && !empty($post) && (false !== strpos($post->post_content, '[gallery')));
	}

	/**
	 * Add body classes for gallery.
	 *
	 * @param array $classes Body classes.
	 *
	 * @return array
	 */
	public function add_gallery_body_class($classes)
	{
		$classes[] = sprintf('pmc-gallery__%s', self::get_current_gallery_type());

		return $classes;
	}

	/**
	 * Get gallery display.
	 *
	 * @return string
	 */
	public static function get_gallery_display()
	{
		if (self::$_gallery_display) {
			return self::$_gallery_display;
		}

		$_gallery_display = get_post_meta(self::$id ?? get_the_ID(), 'pmc_gallery_options_display', true);
		$_gallery_display = (!empty($_gallery_display)) ? $_gallery_display : 'horizontal';

		self::$_gallery_display = (in_array($_gallery_display, array('horizontal', 'vertical', 'runway'), true)) ? $_gallery_display : '';

		return self::$_gallery_display;
	}

	/**
	 * Get current gallery type.
	 *
	 * @return string|boolean
	 */
	public static function get_current_gallery_type()
	{
		if (!is_singular(Defaults::NAME) && !is_admin()) {
			return false;
		}

		// Themes can override if they want to show only one gallery type.
		$gallery_display = apply_filters('pmc_gallery_v4_gallery_display', false);

		if ($gallery_display) {
			return $gallery_display;
		}

		$gallery_display = self::get_gallery_display();

		if ('vertical' === $gallery_display) {
			$gallery_type = 'vertical';
		} elseif ('runway' === $gallery_display) {
			$gallery_type = 'runway';
		} elseif ('horizontal' === $gallery_display && \PMC::is_mobile()) {
			$gallery_type = 'vertical';
		} else {
			$gallery_type = 'horizontal';
		}

		$terms = get_the_terms(get_the_ID(), 'gallery-type');

		if (!empty($terms) && !is_wp_error($terms) && !empty($terms[0]->slug)) {

			if (in_array($terms[0]->slug, array('collection', 'details'), true)) {
				$gallery_type = 'runway';
			}
		}

		return $gallery_type;
	}


	/**
	 * Check if it is a vertical gallery
	 * which will be used in theme for managing header footer and site wrapper div.
	 *
	 * @return bool
	 */
	public static function is_vertical_gallery()
	{
		return ('vertical' === self::get_current_gallery_type());
	}

	/**
	 * Check if it is a standard gallery
	 *
	 * @return bool
	 */
	public static function is_standard_gallery()
	{
		return ('horizontal' === self::get_current_gallery_type());
	}

	/**
	 * Check if it is a runway gallery.
	 *
	 * @return bool
	 */
	public static function is_runway_gallery()
	{
		return ('runway' === self::get_current_gallery_type());
	}

	/**
	 * Get gallery custom css.
	 *
	 * @param array $styles Styles.
	 *
	 * @return string|boolean
	 */
	public function get_gallery_styles(array $styles = [])
	{

		if (empty($styles)) {
			return false;
		}

		$properties = [];

		/*
		 * CSS vars allowed on frontend.
		 *
		 * Key is the CSS var and the value is the callback used to sanitize the var value before output.
		 * If no valid callback is specified then it will default to sanitize_text_field()
		 */
		$css_var_allow_list = [
			'header-height'                      => [$this, 'esc_css_size_value'],
			'theme-color'                        => 'sanitize_text_field',
			'vertical-album-image-margin-top'    => 'sanitize_text_field',
			'vertical-album-image-margin-right'  => 'sanitize_text_field',
			'vertical-album-image-margin-bottom' => 'sanitize_text_field',
			'vertical-album-image-margin-left'   => 'sanitize_text_field',
			'vertical-headline-font-weight'      => 'intval',
			'vertical-caption-font-weight'       => 'intval',
			'vertical-subtitle-font-weight'      => 'intval',
			'vertical-player-font-weight'        => 'intval',
			'vertical-headline-font-family'      => 'sanitize_text_field',
			'vertical-caption-font-family'       => 'sanitize_text_field',
			'vertical-subtitle-font-family'      => 'sanitize_text_field',
			'vertical-player-font-family'        => 'sanitize_text_field',
			'vertical-max-image-width'           => 'sanitize_text_field',
			'horizontal-intro-card-font-family'  => 'sanitize_text_field',
		];

		foreach ($css_var_allow_list as $var => $callback) {

			if (empty($styles[$var])) {
				continue;
			}

			$callback = (!empty($callback) && is_callable($callback)) ? $callback : 'sanitize_text_field';
			$value    = call_user_func_array($callback, [$styles[$var]]);

			if (!empty($value) || 0 === intval($value)) {
				$properties[] = sprintf('--gallery-%1$s: %2$s;', $var, $value);
			}
		}

		return sprintf(
			':root{ %s }',
			implode(' ', $properties)
		);
	}

	/**
	 * Escape css size value.
	 *
	 * @param {string} $value Value.
	 *
	 * @return string
	 */
	public function esc_css_size_value($value)
	{
		if (!$value) {
			return '';
		}

		if (preg_match('/^\d+(px|rem|em)$/', $value)) {
			return $value;
		}

		return '';
	}

	/**
	 * Get close button link.
	 * https://wordpressvip.zendesk.com/hc/en-us/requests/91823
	 *
	 * @return false|string
	 */
	public function get_close_button_link()
	{
		$referrer          = wp_get_raw_referer();
		$archive_link      = get_post_type_archive_link(Defaults::NAME);
		$close_button_link = $archive_link;

		if ($referrer) {
			$linked_post_id       = get_post_meta(get_the_ID(), 'pmc-gallery-linked-post_id', true);
			$back_to_article_link = $linked_post_id ? get_permalink($linked_post_id) : '';

			if ($referrer !== $archive_link && $back_to_article_link) {
				$close_button_link = $back_to_article_link;
			}
		}

		// Assure that archive links don't 404.
		if (strpos($close_button_link, '%') !== false) {
			$close_button_link = home_url();
		}

		/**
		 * Allow close link URL to be overridden.
		 *
		 * @param string       $close_link     URL used by gallery's close
		 *                                     button.
		 * @param string|false $referrer       Referrer parsed to determine
		 *                                     return URL.
		 * @param string       $archive_link   URL of gallery archive.
		 * @param int|null     $linked_post_id ID of post that gallery is linked
		 *                                     to.
		 * @return string
		 */
		$close_button_link = apply_filters(
			'pmc_gallery_close_button_link',
			$close_button_link,
			$referrer,
			$archive_link,
			$linked_post_id ?? null
		);

		return $close_button_link;
	}

	/**
	 * Get up next gallery type.
	 *
	 * @param array $next_gallery Up next gallery array.
	 *
	 * @return string type.
	 */
	public function get_next_gallery_type($next_gallery)
	{
		$type = 'no-next-gallery-found';

		if (!$next_gallery || empty($next_gallery['post']) || empty($next_gallery['type'])) {
			return $type;
		}

		if ('post_tag' === $next_gallery['type']) {
			$type = 'tag-next-gallery';
		} elseif ('category' === $next_gallery['type']) {
			$type = 'category-next-gallery';
		} else {
			$type = 'next-gallery'; // @todo Confirm if this type can be added, themes use 'no-next-gallery-found' here which doesn't make sense, see footwearnews_get_upnext_gallery() for example.
		}

		return $type;
	}

	/**
	 * Load Gallery
	 *
	 * @codeCoverageIgnore
	 *
	 * @todo: Preview Enum Define
	 * @todo Legacy code, may need to revisit this method or remove it during cleanup.
	 *
	 * @param null|int $gallery        Gallery
	 * @param null     $linked_gallery No Preview, 0 Preview with first image provided by gallery 1 Preview with first image provided by theme, 0 Treated as 1
	 *
	 * @deprecated
	 *
	 * @return object
	 */
	public function load_gallery($gallery = null, $linked_gallery = null)
	{
		// sanitize $linked_gallery
		if (!is_null($linked_gallery)) {
			$linked_gallery = (int) $linked_gallery;
			if ($linked_gallery < 0) {
				$linked_gallery = 1;
			}
		}

		$prev = '';
		$next = '';

		self::$linked_gallery = $linked_gallery;

		$data = self::fetch_gallery($gallery);

		if (empty($data) || !is_array($data)) {
			return $this;
		}

		if (!$this->has_gallery() && !$this->has_linked_gallery() && is_null($linked_gallery)) {
			return $this;
		}

		/**
		 * AE: by default set all LOB's to non continuous. LOBs can opt in to the continuous
		 * gallery functionality.
		 */
		$continuous = apply_filters('pmc-gallery-continuous', false); // @codingStandardsIgnoreLine - Can not change filter name now, as being used in themes.

		// get prev n next galleries
		if (get_post_type() === Defaults::NAME && $continuous) {
			$prev_post = apply_filters('pmc-gallery-get-next-post', null); // @codingStandardsIgnoreLine - Can not change filter name now, as being used in themes.
			if (empty($prev_post)) {
				$prev_post = get_adjacent_post(false, '', false);
			}

			if ($prev_post) {
				$prev                           = self::fetch_gallery($prev_post->ID);
				$this->_js_obj['gallery_start'] = 1;
			}

			$next_post = apply_filters('pmc-gallery-get-previous-post', null); // @codingStandardsIgnoreLine - Can not change filter name now, as being used in themes.

			if (empty($next_post)) {
				$next_post = get_adjacent_post();
			}

			if ($next_post) {
				$next                           = self::fetch_gallery($next_post->ID);
				$this->_js_obj['gallery_start'] = 0;
			}
		}
		$this->_js_obj['gallery_count'] = count($data);

		if ('' === $prev && '' === $next) {
			/**
			 * We need to start at 0 since we have no prev/next set of data
			 * Swipe will trigger circular swipe properly.
			 */
			$this->_js_obj['gallery_start'] = 0;
			$this->_number_of_images        = count($data);
		} else {

			/**
			 * Fetch other galleries
			 * AE: if in fact this post has a gallery next to it and it isn't the last gallery we want to merge
			 * the next gallery to the current gallery so that when we render swipe.js can go through all the photos.
			 */
			if (is_array($next)) {

				/*
				 * Merge whole next gallery into $data as we'd need to pick
				 * more than one thumb from next gallery based on how much
				 * padding we're adding. All that is decided later on at time
				 * of render. Adding extra items in an array here & then discarding
				 * it down the line is not much of an overhead since we already
				 * have fetched whole of next gallery.
				 *
				 * @since 2016-08-03 Amit Gupta
				 */
				$data = array_merge($data, $next);
			}

			/**
			 * AE: There are 2 scenario here:
			 * Scenario one: you have a previous gallery and you have a next gallery in this case we want to add the last item of the previous gallery to the first position of the current gallery. that way if you hit the back button and you are on the first photo you should be able to see the last photo of the previous gallery.
			 * Scenario two: you have a previous Gallery, but there is no next gallery. so if you hit the forward button and you are the end of the gallery you should go to the first photo of the previous Gallery. in a circular motion. and if you are at the first photo of the last gallery and hit the back button you should still be able to go to the last photo of the previous gallery like described in scenario one. I sure do hope this makes sense to anyone reading this.
			 * scenario one
			 */
			if (is_array($prev)) {
				// prepend last item from previous gallery
				array_unshift($data, end($prev));
				$this->_js_obj['gallery_start'] = 1; // current first item start at 1 since prepend a new item above
			}

			// Scenario two
			if (is_array($prev) && '' === $next) {
				reset($prev);
				array_push($data, current($prev));
			}

			$this->_number_of_images = count($data);
		}

		// Respecting _escaped_fragment_
		$escaped_fragment  = filter_input(INPUT_GET, '_escaped_fragment_', FILTER_SANITIZE_NUMBER_INT);
		$_escaped_fragment = $escaped_fragment ? intval($escaped_fragment) : 0;

		if ($_escaped_fragment > 0 && $_escaped_fragment <= $this->_js_obj['gallery_count']) {
			$this->_js_obj['gallery_first'] = ($_escaped_fragment - 1);
		}

		// Create object and return
		$this->_data = $data;

		return $this;
	}

	/**
	 * Fetch Gallery
	 *
	 * @param mixed             $gallery     May be a string or int with a single gallery ID, an
	 *                                       ordered array of WP_Post objects, an unordered
	 *                                       array of post arrays, or an unordered array of post
	 *                                       IDs
	 * @param bool              $invalidated Flag if cache should be invalidated.
	 * @param string|false|null $type        Gallery display type, used mainly for invalidations.
	 *
	 * @todo Almost the same code from pmc-gallery-v3, may need to revisit.
	 *
	 * @return array|null
	 */
	public static function fetch_gallery($gallery = null, $invalidated = false, $type = null)
	{
		global $post;
		$id           = 0;
		$gallery_data = false;

		// we only return the cached data if and only if existing id match or we're retrieve the default gallery
		if (!$invalidated && !empty(self::$_gallery) && (null === $gallery || self::$id === $gallery)) {
			return self::$_gallery;
		}

		// Nothing passed use post id.
		if (null === $gallery) {
			if (isset($post->ID)) {
				$gallery = $post->ID;
			} else {
				return null;
			}
		}

		/**
		 * Get gallery Id.
		 *
		 * @todo $this->load_gallery() calls this method multiple times
		 * for different galleries (e.g., previous & next gallery).     So in some
		 * circumstances this internal pointer gets set to an ID that's *not*
		 * the current gallery.     This looks like a bug.
		 *
		 * @todo The var name of self::$id indicates it expects a single ID, but
		 * $gallery may be a whole bunch of different things. This looks like a bug.
		 */
		self::$id = $gallery;

		// Gallery ID Passed.
		if (is_int($gallery)) {

			if (0 === $gallery) {
				return null;
			}

			// Saving post id for use later on.
			$id = $gallery;

			// If gallery has meta.
			$meta    = get_post_meta($gallery, Defaults::NAME, true);
			$gallery = $meta;
		} elseif (is_string($gallery)) {

			/**
			 * Convert gallery id string into array.
			 *
			 * @todo $gallery may be equal to $GLOBALS['post']->ID, which may be
			 * a string.  That means this condition may be met when we really
			 * wanted the previous condition.  This looks like a bug.
			 */
			$gallery = explode(',', $gallery);
		} elseif (!is_array($gallery)) {
			return null;
		}

		$cache = Plugin::get_instance()->create_cache_instance(
			self::_get_gallery_cache_key(
				self::$id,
				$gallery,
				$type
			),
			Defaults::NAME . '-fetch'
		);

		$cache->updates_with(
			static function () use ($gallery, $gallery_data, $id, $invalidated) {
				return self::_fetch_gallery_uncached(
					$gallery,
					$gallery_data,
					$id,
					$invalidated
				);
			}
		);

		// We can only invalidate discrete gallery IDs, otherwise we use the cache to guard against stampedes.
		if (is_int(self::$id)) {
			$cache->expires_in(DAY_IN_SECONDS);
		} else {
			$cache->expires_in(MINUTE_IN_SECONDS);
		}

		if (true === $invalidated) {
			$cache->invalidate();
		}

		// For backward compatible
		self::$_gallery = $cache->get();

		/* if (is_array(self::$_gallery)) {
			self::process_ads(self::$_gallery);
		} */

		return self::$_gallery;
	}

	/**
	 * Helper function to process the ads configuration for gallery data.
	 * The data might be coming from cached store and the ads settings need
	 * to process late to avoid setting from cache might be affected by gallery caching implementation
	 *
	 * @param array $gallery_data
	 * @return void
	 */
	public static function process_ads(array &$gallery_data): void
	{
		return;
		$provider = PMC_Ads::get_instance()->get_provider('boomerang');
		array_walk(
			$gallery_data,
			function (&$item) use ($provider) {
				if (!empty($item['ads'])) {
					$result = [];
					if (!empty($provider)) {
						// Try to find the first valid ads from a list of ad locations
						// Add for backward compatible where old version of gallery might be using a different ad location naming
						foreach ((array) $item['ads'] as $ad_location) {
							$result = Plugin::get_instance()->get_ads($ad_location);
							if (!empty($result)) {
								break;
							}
						}
					}
					$item['ads'] = $result;
				}
			}
		);
	}

	/**
	 * Build cache key for given gallery attributes.
	 *
	 * @param mixed             $id           Gallery ID or array of gallery items.
	 * @param mixed             $gallery      Gallery constituents.
	 * @param string|false|null $gallery_type Gallery display type, used mainly for invalidations.
	 * @return string
	 */
	protected static function _get_gallery_cache_key($id, $gallery, $gallery_type = null): string
	{
		if (null === $gallery_type) {
			$gallery_type = self::get_current_gallery_type();
		}
		if (false === $gallery_type) {
			$gallery_type = '0';
		}

		$enable_pinterest_description = cheezcap_get_option(
			self::is_runway_gallery()
				? 'pmc_gallery_runway_enable_pinterest_description'
				: 'pmc_gallery_enable_pinterest_description'
		);

		// Using JSON to avoid any type complications.
		return sprintf(
			'%1$s %2$s %3$s %4$d %5$d %6$s %7$d %8$d %9$d',
			wp_json_encode($id),
			wp_json_encode($gallery),
			$gallery_type,
			absint(cheezcap_get_option('pmc_vertical_ad_frequency')),
			absint(cheezcap_get_option('pmc_vertical_ad_limit_count')),
			$enable_pinterest_description,
			Device::get_instance()->is_desktop(),
			Device::get_instance()->is_tablet(),
			Device::get_instance()->is_mobile()
		);
	}

	/**
	 * Retrieve raw gallery data.
	 *
	 * @param mixed $gallery      Gallery ID or array of gallery items.
	 * @param mixed $gallery_data Gallery constituents.
	 * @param mixed $id           Gallery ID or string of gallery items.
	 * @param bool  $invalidated  Was cache invalidation was requested.
	 * @return mixed
	 */
	protected static function _fetch_gallery_uncached($gallery, $gallery_data, $id, $invalidated)
	{

		if (false === $gallery_data && is_array($gallery)) {
			$gallery_data       = [];
			$pos                = 0;
			$gallery_link       = get_permalink($id);
			$gallery_attachment = Attachment_Detail::get_instance();
			$ad_count           = 1;

			// Ready gallery data for response.
			foreach ($gallery as $variant_id => $id) {
				$variant_id = intval($variant_id);
				$id         = intval($id);

				// Get variant data.
				$variant = get_post($variant_id);

				// Get attachment detail which is use as default data.
				$attachment = get_post($id);

				// if no attachment found with give id then skip it.
				if (!$attachment) {
					// @codeCoverageIgnoreStart
					continue;
					// @codeCoverageIgnoreEnd
				}

				$gallery_custom_data = [];
				if ($variant && Attachment_Detail::NAME === $variant->post_type) {
					$variant_meta = $gallery_attachment->get_variant_meta($variant_id);
					if (!empty($variant_meta) && is_array($variant_meta)) {
						$gallery_custom_data = $variant_meta;
					}
				}

				// Get post attachment meta.
				$attachment_meta = get_post_meta($id);
				$attachment_meta = (is_array($attachment_meta)) ? $attachment_meta : array();

				$image_source_url = !empty($attachment_meta['image_source_url'][0]) ? $attachment_meta['image_source_url'][0] : '';

				if (empty($image_source_url)) {
					$image_source_url = !empty($attachment_meta['sk_image_source_url'][0]) ? $attachment_meta['sk_image_source_url'][0] : '';
				}

				$data_to_fill = [
					'title'            => $attachment->post_title,
					'description'      => $attachment->post_content,
					'caption'          => $attachment->post_excerpt,
					'alt'              => !empty($attachment_meta['_wp_attachment_image_alt'][0]) ? $attachment_meta['_wp_attachment_image_alt'][0] : '',
					'image_credit'     => !empty($attachment_meta['_image_credit'][0]) ? $attachment_meta['_image_credit'][0] : '',
					'image_source_url' => $image_source_url,
				];

				foreach ($data_to_fill as $key => $value) {
					if (empty($gallery_custom_data[$key])) {
						$gallery_custom_data[$key] = $value;
					}
				}

				$alt_text = (!empty($gallery_custom_data['alt'])) ? $gallery_custom_data['alt'] : '';

				$pos++;

				$pinit_url = self::get_pinit_url_for_slide($attachment->ID, $variant_id, $pos);

				$original_image_meta = wp_get_attachment_image_src($attachment->ID, 'full');

				$original_width  = $original_image_meta[1];
				$original_height = $original_image_meta[2];

				if (self::is_vertical_gallery()) {
					// Cap image height while maintaining aspect ratio
					$new_full_height = min($original_height, 575);
					$new_full_width  = intval(round($new_full_height * ($original_width / max((int) $original_height, 1))));
				}

				// BR-1097 and VIP Ticket 123127: Remove and add filter on the_excerpt from
				// WordPress.com Compatibility plugin that is causing some valid URLs to be
				// stripped out of [buy-now] shortcode in gallery caption.
				$filter_removed = remove_filter('the_excerpt', 'wpcom_make_content_clickable', 120);

				$next_gallery_data = [
					'ID'               => $id,
					'image'            => $attachment->guid,
					'date'             => $variant->post_date,
					'modified'         => $variant->post_modified,
					'alt'              => sanitize_text_field($alt_text),
					'title'            => apply_filters('the_title', $gallery_custom_data['title']),
					'slug'             => $attachment->post_name,
					'description'      => sanitize_text_field($gallery_custom_data['description']),
					'image_credit'     => sanitize_text_field($gallery_custom_data['image_credit']),
					'image_source_url' => sanitize_text_field($gallery_custom_data['image_source_url']),
					'caption'          => apply_filters('the_excerpt', $gallery_custom_data['caption']),
					'position'         => $pos,
					'url'              => $gallery_link,
					'sizes'            => self::get_image_sizes($attachment->ID),
					'fullWidth'        => isset($new_full_width) ? $new_full_width : $original_width,
					'fullHeight'       => isset($new_full_height) ? $new_full_height : $original_height,
					'pinterestUrl'     => esc_url_raw($pinit_url),
					'mime_type'        => $attachment->post_mime_type,
				];

				// BR-1097 and VIP Ticket 123127: Add the_excerpt filter back from
				// WordPress Compatibility plugin.
				// @codeCoverageIgnoreStart
				if (!empty($filter_removed)) {
					add_filter('the_excerpt', 'wpcom_make_content_clickable', 120);
				}
				// @codeCoverageIgnoreEnd

				// We need to keep a copy of captions without shortcodes rendered
				// Used in: PMC\Gallery\PMC_Store_Products::pmc_store_products_displayed_content
				$next_gallery_data['caption_no_shortcode'] = $next_gallery_data['caption'];
				$next_gallery_data['caption']              = do_shortcode($next_gallery_data['caption']);

				$is_last_slide = (count($gallery) === $pos);

				if (self::is_vertical_gallery()) {
					if (!$is_last_slide) {

						// NOTE: This data added here will get cached 24h per cache policy fetch_gallery
						// Any changes in cheezcap might take 24h to be affected
						$ad_location             = '';
						$ad_frequency            = absint(cheezcap_get_option('pmc_vertical_ad_frequency'));
						$ad_frequency            = $ad_frequency ? $ad_frequency : 1;
						$vertical_ad_limit_count = absint(cheezcap_get_option('pmc_vertical_ad_limit_count'));

						if ((0 === ($pos + ($ad_frequency - 1)) % $ad_frequency) && 1 !== $pos) {
							if (empty($vertical_ad_limit_count) || ($ad_count++ < $vertical_ad_limit_count)) {
								$ad_location = 'in-gallery-x';
							}
						} elseif (1 === $pos) {
							$ad_location = 'in-gallery-1';
						}

						// IMPORTANT: Add ad's location only,
						// we do not want the ad setting to cache with gallery data for 24h
						if (!empty($ad_location)) {
							$next_gallery_data['ads'] = [$ad_location];
						}
					} else {
						$next_gallery_data['ads'] = [];
					}
				}

				$enable_pinterest_description_key = (self::is_runway_gallery()) ? 'pmc_gallery_runway_enable_pinterest_description' : 'pmc_gallery_enable_pinterest_description';

				if ('yes' === cheezcap_get_option($enable_pinterest_description_key)) {
					// @codeCoverageIgnoreStart
					$next_gallery_data['pinterest_description'] = sanitize_text_field($gallery_custom_data['pinterest_description']);
					// @codeCoverageIgnoreEnd
				}
				$gallery_data[] = $next_gallery_data;
			}
		}

		self::$_gallery = apply_filters('pmc_gallery_data', $gallery_data, $gallery, $invalidated);

		return self::$_gallery;
	}

	/**
	 * Rebuild cached output for a given gallery ID.
	 *
	 * @param int $id
	 */
	public static function rebuild_gallery_by_id(int $id): void
	{
		$types = apply_filters(
			'pmc_gallery_v4_gallery_display_types_to_rebuild',
			self::GALLERY_TYPES
		);

		foreach ($types as $type) {
			self::fetch_gallery($id, true, $type);
		}
	}

	/**
	 * Get gallery first thumbnail image.
	 *
	 * @param int    $gallery_id Gallery id.
	 * @param string $size       Image size.
	 *
	 * @return array
	 */
	public function get_gallery_first_thumbnail($gallery_id, $size)
	{
		$thumbnail = array();

		if (!self::is_runway_gallery()) {
			return $thumbnail;
		}

		$attachments = get_post_meta($gallery_id, Defaults::NAME, true);

		if (empty($attachments) || !is_array($attachments)) {
			return $thumbnail;
		}

		foreach ($attachments as $variant_id => $id) {
			$attachment = get_post($id);

			if (!empty($attachment)) {
				break;
			}
		}

		if (empty($attachment)) {
			return $thumbnail;
		}

		$image = wp_get_attachment_image_src($attachment->ID, $size);

		$thumbnail['ID']     = $attachment->ID;
		$thumbnail['src']    = (is_array($image) && !empty($image[0])) ? $image[0] : '';
		$thumbnail['width']  = (is_array($image) && !empty($image[1])) ? $image[1] : '';
		$thumbnail['height'] = (is_array($image) && !empty($image[2])) ? $image[2] : '';
		$thumbnail['alt']    = wp_strip_all_tags(get_post_meta($attachment->ID, '_wp_attachment_image_alt', true));

		return $thumbnail;
	}

	/**
	 * Is featured article.
	 *
	 * @return boolean
	 */
	public function is_featured_article()
	{
		$featured_article_term = get_term_by('name', 'Featured Article', '_post-options');

		return apply_filters('pmc_gallery_v4_is_featured_article', (!empty($featured_article_term)));
	}

	/**
	 * Callback for the post_gallery filter in featured articles.
	 *
	 * @param string $content Gallery content from previous filters. Not used here.
	 * @param array  $attr    Attributes passed to the gallery shortcode.
	 *
	 * @return string Shortcode output.
	 */
	public function gallery_shortcode($content = '', $attr = array())
	{

		// If empty string or null is passed WordPress will take the default gallery.
		$inline_gallery = '<i></i>';

		if (!$this->is_featured_article()) {

			if (empty($attr['id'])) {
				return $inline_gallery; // Will not output anything.
			}

			$gallery_id = $attr['id'];

			$inline_gallery = sprintf('<a href="%s">%s</a>', esc_url(get_permalink($gallery_id)), esc_html__('View Gallery', 'pmc-gallery-v4'));

			return $inline_gallery;
		}

		$attr = shortcode_atts(
			array(
				'ids'     => '',
				'orderby' => 'post__in',
			),
			(array) $attr
		);

		if (empty($attr['ids'])) {
			return $inline_gallery;
		}

		$attachment_query = new \WP_Query(
			[
				'posts_per_page'         => 50,
				'post_type'              => 'attachment',
				'post__in'               => explode(',', $attr['ids']),
				'orderby'                => $attr['orderby'],
				'post_status'            => 'any',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			]
		);

		$attachments = $attachment_query->posts;

		if (empty($attachments)) {
			return $inline_gallery;
		}

		$inline_gallery_template_file = __DIR__ . '/../template-parts/inline-gallery.php';

		if (file_exists($inline_gallery_template_file)) {
			ob_start();
			include $inline_gallery_template_file;
			$inline_gallery = ob_get_clean();
		}

		return $inline_gallery;
	}

	/**
	 * Remove google analytics pageview tracking on page load.
	 * This we are doing so that proper page url is attributed in analytics.
	 * During this time the hash of image is not present, so we remove it to be fired later on.
	 *
	 * @param $push
	 *
	 * @return array
	 */
	public function filter_pmc_google_analytics_track_pageview($push)
	{

		if (empty($push) || !is_array($push)) {
			return array();
		}

		// remove default page view tracking if current post has gallery
		// page view will be tracked via footer_scripts
		if (is_single() && $this->has_gallery()) {

			for ($i = 0; $i < count($push); $i++) {
				if (preg_match('/^(?:[^\.]+\.)?_trackPageview$/', $push[$i][0]) || preg_match('/^(?:[^\.]+\.)?pageview$/', $push[$i][0])) {
					// save the page view event name for use at footer_scripts
					$this->_pageview_event_names[] = $push[$i][0];
					unset($push[$i]);
				}
			}

			$push = array_values($push);
		}

		return $push;
	}

	/**
	 * Trick Cxense into thinking deep-link canonical URLs are actually the gallery base URL.
	 *
	 * @param $page_location
	 *
	 * @return string
	 */
	public function filter_pmc_cxense_page_location($page_location)
	{

		if (is_singular(Defaults::NAME)) {
			$page_location = trailingslashit(get_permalink());
		}

		return $page_location;
	}

	/**
	 * Get linked gallery data.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array|null
	 */
	public static function get_linked_gallery_data($post_id)
	{

		if (empty($post_id)) {
			return null;
		}

		$linked_data = get_post_meta($post_id, Defaults::NAME . '-linked-gallery', true);
		if (!empty($linked_data)) {
			$linked_data = json_decode($linked_data, true);

			return $linked_data;
		}

		return null;
	}

	/**
	 * Helper function to return the linked post_id
	 *
	 * @param int $gallery_id
	 *
	 * @return mixed The linked post ID or false if not found
	 */
	public static function get_linked_post_id($gallery_id)
	{
		// retrieve the linked post id from gallery post meta

		$post_id = get_post_meta($gallery_id, Defaults::NAME . '-linked-post_id', true);

		if ($post_id) {
			// we need to double check to make sure the post actually have the same linked data, linked gallery might be removed
			$linked_gallery = self::get_linked_gallery_data($post_id);

			if (!empty($linked_gallery)) {
				if ($linked_gallery['id'] === $gallery_id) {
					return $post_id;
				}
			}
		}

		return false;
	}

	/**
	 * Return next gallery, works in loop only
	 *          1. next gallery based on `pmc_gallery_next_gallery` filter provided
	 *          2. next gallery based on top tag on count attached to current gallery post
	 *          3. next gallery based on top category on count attached to current gallery post
	 *          4. Just return adjacent gallery if above 3 fails
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @param array $args
	 *
	 * @return array|bool
	 */
	public function get_adjacent_gallery($args = [])
	{

		$post_id = get_the_ID();

		if (!is_int($post_id)) {
			return false;
		}

		/**
		 * Id custom-next-gallery pass through query string.
		 * then do not perform adjacent gallery.
		 * Because after that it will handle via REST APIs.
		 *
		 * Scenario:
		 * At first this function fail to provide next gallery,
		 * And REST API took control for next gallery,
		 * But after getting next gallery from REST API,
		 * This adjacent function will return same galleries that already viewed by user.
		 * Because, REST API provide gallery with same Tag/category that current gallery have
		 * but published before current gallery, while this function only provide
		 * recent gallery with same Tag/Category.
		 */
		$is_custom_next_gallery = \PMC::filter_input(INPUT_GET, 'custom-next-gallery', FILTER_DEFAULT);

		if (!empty($is_custom_next_gallery)) {
			return false;
		}

		if (Defaults::NAME !== get_post_type($post_id)) {
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		$automatic_select = get_post_meta($post_id, 'pmc-gallery-automatic-select-galleries', true);

		if (empty($automatic_select)) {

			/**
			 * Check if we have next gallery data available.
			 * If we have then that will be out next gallery.
			 */
			$next_gallery_meta = get_post_meta($post_id, 'pmc-gallery-next-gallery', true);
			$next_gallery_id   = (!empty($next_gallery_meta['id']) && 0 < intval($next_gallery_meta['id'])) ? intval($next_gallery_meta['id']) : 0;
			$next_gallery      = (!empty($next_gallery_id)) ? get_post($next_gallery_id) : false;

			if (!empty($next_gallery) && is_a($next_gallery, 'WP_Post')) {
				return [
					'post' => $next_gallery,
					'type' => 'related_gallery',
				];
			}
		}

		// Short circuit the function and return your own
		$filtered_post = apply_filters('pmc_gallery_adjacent_gallery', null, $post_id, $args);

		if (null !== $filtered_post) {
			// @codeCoverageIgnoreStart
			return $filtered_post;
			// @codeCoverageIgnoreEnd
		}

		// Previous = true here means post newer & false = older post hence gonna flip it
		$args         = wp_parse_args($args, ['prev' => true]);
		$args['prev'] = !$args['prev'];

		$top_tag_post = $this->_get_adjacent_post($post_id, 'post_tag', $args['prev']);

		if (!empty($top_tag_post)) {
			return [
				'post' => $top_tag_post,
				'type' => 'post_tag',
			];
		}

		$top_category_post = $this->_get_adjacent_post($post_id, 'category', $args['prev']);

		if (!empty($top_category_post)) {
			return [
				'post' => $top_category_post,
				'type' => 'category',
			];
		}

		$up_next_post = get_adjacent_post(false, false, $args['prev']);

		if (!empty($up_next_post)) {
			return [
				'post' => $up_next_post,
				'type' => 'adjacent_only',
			];
		}

		return false;
	}

	/**
	 * Return adjacent post based on taxonomy term
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @codeCoverageIgnore
	 *
	 * @param      $post_id
	 * @param      $taxonomy
	 * @param bool     $prev
	 *
	 * @return bool|null|string|\WP_Post
	 */
	private function _get_adjacent_post($post_id, $taxonomy, $prev = true)
	{

		$terms = get_the_terms($post_id, $taxonomy);

		if (empty($terms[0]->count)) {
			return false;
		}

		// Even I don't like anonymous function, but for this, I am taking a excuse, feel free to yell at me
		usort(
			$terms,
			function ($a, $b) {
				return $b->count - $a->count;
			}
		);

		if (!empty($terms[0]->term_id)) {

			$this->_term_id_adjacent_post = $terms[0]->term_id;

			add_filter('wpcom_vip_limit_adjacent_post_term_id', array($this, 'filter_next_post_term_id'));

			// Select previous galleries.. there will always be previous ones,
			// if we only selected next posts the user would likely get to the end
			// and have no more posts to see.
			$up_next_post = get_adjacent_post(true, false, $prev, $taxonomy);

			$this->_term_id_adjacent_post = null;

			remove_filter('wpcom_vip_limit_adjacent_post_term_id', array($this, 'filter_next_post_term_id'));

			if (!empty($up_next_post)) {
				return $up_next_post;
			}
		}

		return false;
	}

	/**
	 * Just a filter to restrict term id for adjacent post
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @param $term_id_to_search
	 *
	 * @return mixed
	 */
	public function filter_next_post_term_id($term_id_to_search)
	{

		if (!empty($this->_term_id_adjacent_post)) {
			$term_id_to_search = $this->_term_id_adjacent_post;
		}

		return $term_id_to_search;
	}

	/**
	 * A filter to remove responsive ad skins from all gallery pages of all brands.
	 *
	 * @param bool Default value of should skins be enabled.
	 *
	 * @since 2018-06-11 Kelin Chauhan READS-1196
	 *
	 * @return bool
	 */
	public function remove_responsive_ad_skins($enabled)
	{

		if (self::is_standard_gallery() || self::is_runway_gallery()) {
			return false;
		}

		return $enabled;
	}

	/**
	 * A filter to override googlebot_news meta tag for pmc-gallery posts
	 *
	 * @param $gn_exclude bool
	 *
	 * @since   2018-07-31 Jignesh Nakrani READS-1378
	 * @version 2020-10-01, kelin.chauhan@rtcamp.com, SADE-570
	 *
	 * @return bool
	 */
	public function maybe_exclude_googlebot_news_tag($gn_exclude)
	{

		if (is_singular(Defaults::NAME)) {

			// By default remove meta tag for all gallery pages.
			$gn_exclude = false;

			// Respect value of 'exclude-from-google-news' global post option.
			if (class_exists('\PMC\Post_Options\Base', false) && taxonomy_exists(\PMC\Post_Options\Base::NAME)) {

				$queried_object = get_queried_object();
				$gn_exclude     = has_term('exclude-from-google-news', \PMC\Post_Options\Base::NAME, $queried_object);
			}
		}

		return $gn_exclude;
	}

	/**
	 * Get current slide slug.
	 * Used by add_next_prev_links() to retrieve the slug for nav arrows.
	 * Used by filter_canonical_url() to filter the canonical url.
	 *
	 * @return mixed
	 */
	public function get_current_slug()
	{
		global $wp;

		if (!is_singular(Defaults::NAME)) {
			return false;
		}

		$current_permalink = trailingslashit(get_permalink());
		$current_url       = trailingslashit(home_url($wp->request));
		$current_slug      = str_replace($current_permalink, '', $current_url);

		return $current_slug;
	}

	/**
	 * Add next prev links.
	 *
	 * @return void
	 */
	public function add_next_prev_links()
	{
		if (!is_singular(Defaults::NAME)) {
			return;
		}

		$current_slug      = untrailingslashit($this->get_current_slug());
		$current_permalink = trailingslashit(get_permalink());
		$gallery           = self::fetch_gallery();

		if (empty($gallery) || !is_array($gallery)) {
			return;
		}

		$current_slide_index = 0;
		$last_slide_index    = count($gallery) - 1;

		foreach ($gallery as $key => $slide) {
			if (!empty($slide['slug']) && $current_slug === $slide['slug']) {
				// @codeCoverageIgnoreStart
				$current_slide_index = $key;
				// @codeCoverageIgnoreEnd
				break;
			}
		}

		$next_slide_index = ($current_slide_index < $last_slide_index) ? $current_slide_index + 1 : false;
		$prev_slide_index = ($current_slide_index > 0) ? $current_slide_index - 1 : false;

		/**
		 * In vertical gallery initially slug is empty because the page is not on any slide yet
		 * therefore the next slide should be the first one.
		 */
		if (self::is_vertical_gallery() && '' === $current_slug) {
			$next_slide_index = 0;
		}

		$next_slide_slug     = (false !== $next_slide_index && !empty($gallery[$next_slide_index]['slug'])) ? $gallery[$next_slide_index]['slug'] : false;
		$previous_slide_slug = (false !== $prev_slide_index && !empty($gallery[$prev_slide_index]['slug'])) ? $gallery[$prev_slide_index]['slug'] : false;

		if (false !== $previous_slide_slug) {
			// @codeCoverageIgnoreStart
			printf('<link rel="prev" href="%s">', esc_url(trailingslashit($current_permalink . $previous_slide_slug)));
			// @codeCoverageIgnoreEnd
		}

		if (false !== $next_slide_slug) {
			printf('<link rel="next" href="%s">', esc_url(trailingslashit($current_permalink . $next_slide_slug)));
		}
	}

	/**
	 * Get pinit url for slide
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $variant_id    Variant ID.
	 * @param int $slide_number  Slide number.
	 *
	 * @return string|bool
	 */
	public static function get_pinit_url_for_slide($attachment_id, $variant_id, $slide_number)
	{

		if ((empty($attachment_id)) || (empty($variant_id)) || (empty($slide_number) || !is_numeric($slide_number))) {
			return false;
		}

		$pinit_url = 'https://www.pinterest.com/pin/create/button/';

		$description = get_post_meta($variant_id, 'pinterest_description', true);

		if (empty($description)) {
			$description = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		}

		$pinit_url_args = [
			'url'         => sprintf('%s/%s/', untrailingslashit(get_permalink()), $slide_number),
			'media'       => wp_get_attachment_image_url($attachment_id, 'gallery-slide-show'),
			'description' => $description,
		];

		$pinit_url_args = array_map('rawurlencode', (array) $pinit_url_args);

		$pinit_url = add_query_arg($pinit_url_args, $pinit_url);

		return $pinit_url;
	}

	/**
	 * Render gallery.
	 *
	 * @return void
	 */
	public function render_gallery()
	{
		if (self::is_vertical_gallery()) {
			echo $this->get_vertical_gallery_shell(); // XSS okay.
		} elseif (self::is_runway_gallery()) {
			echo '<div id="pmc-gallery-runway"></div>';
		} elseif ('horizontal' === self::get_current_gallery_type()) {
			echo '<div id="pmc-gallery">';
			$this->get_react_app_shell_for_standard_gallery();
			echo '</div>';
		}
	}

	/**
	 * Get vertical gallery app shell.
	 *
	 * @return string
	 */
	public function get_vertical_gallery_shell()
	{
		$shell  = '<div id="pmc-gallery-vertical">';
		$shell .= View::get_instance()->create_react_app_shell_placeholder('c-gallery-vertical-loader');
		$shell .= '</div>';

		return $shell;
	}

	/**
	 * Get app shell for showing before react script is loaded.
	 *
	 * @param string parent id.
	 * @param string wrapper class.
	 *
	 * @return string
	 */
	public function create_react_app_shell_placeholder($wrapper_class)
	{
		$items = array(
			'figure',
			'text',
			'text-small',
			'content',
		);

		$app_shell = sprintf('<div class="%s u-gallery-app-shell-loader">', esc_attr($wrapper_class));

		foreach ($items as $item) {
			if ('content' === $item) {
				for ($i = 1; $i <= 4; $i++) {
					$app_shell .= sprintf('<div class="u-gallery-app-shell__%s u-gallery-app-shell__%s-%s u-gallery-app-shell u-gallery-react-placeholder-shimmer" ></div>', esc_attr($item), esc_attr($item), intval($i));
				}
			} else {
				$app_shell .= sprintf('<div class="u-gallery-app-shell__%s u-gallery-app-shell u-gallery-react-placeholder-shimmer" ></div>', esc_attr($item));
			}
		}

		$app_shell .= '</div>';

		return $app_shell;
	}

	/**
	 * Create react app shell for standard gallery.
	 *
	 * @codeCoverageIgnore
	 */
	public function get_react_app_shell_for_standard_gallery()
	{
		$template = PMC_GALLERY_PLUGIN_DIR . '/template-parts/standard-gallery-shell.php';

		if (file_exists($template)) {
			include_once $template;
		}
	}

	/**
	 * Add vertical gallery to the content.
	 *
	 * @param {string} $content Content.
	 *
	 * @return string
	 */
	public function add_vertical_gallery($content)
	{
		if (self::is_vertical_gallery()) {
			return $content . ' ' . $this->get_vertical_gallery_shell();
		}

		return $content;
	}

	/**
	 * Get the primary term in a given taxonomy for a given (or the current) post.
	 *
	 * Terms are ordered in this site, and this function grabs the first term in the
	 * list, consider that the "primary" term in that taxonomy.
	 *
	 * @param  string $taxonomy Taxonomy for which to get the primary term.
	 * @param  int    $post_id  Optional. Post ID. If absent, uses current post.
	 *
	 * @return boolean|\WP_Term WP_Term on success, false on failure.
	 */
	public function get_the_primary_term($taxonomy, $post_id = null)
	{
		if (!$post_id) {
			$post_id = get_the_ID();
		}

		$cache_key = "pmc_primary_{$taxonomy}_{$post_id}";
		$cache     = Plugin::get_instance()->create_cache_instance($cache_key);

		return $cache->expires_in(HOUR_IN_SECONDS)
			->updates_with(
				array($this, 'the_primary_term'),
				array(
					'taxonomy' => $taxonomy,
					'post_id'  => $post_id,
				)
			)
			->get();
	}

	/**
	 * Get the primary term in a given taxonomy for a given (or the current) post.
	 *
	 * Terms are ordered in this site, and this function grabs the first term in the
	 * list, consider that the "primary" term in that taxonomy.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $taxonomy Taxonomy for which to get the primary term.
	 * @param  int    $post_id  Optional. Post ID. If absent, uses current post.
	 *
	 * @return boolean|\WP_Term WP_Term on success, false on failure.
	 */
	public function the_primary_term($taxonomy, $post_id = null)
	{
		// This has to use `wp_get_object_terms()` because we order them
		$terms = wp_get_object_terms($post_id, $taxonomy, array('orderby' => 'term_order'));

		if (!empty($terms) && !is_wp_error($terms)) {
			$primary_term = reset($terms);
			$primary_term = $primary_term->term_id;
		} else {
			$primary_term = 'none'; // If there are no terms, still cache that so we don't db lookup each time
		}

		return 'none' === $primary_term ? false : get_term($primary_term, $taxonomy);
	}

	/**
	 * Get intro card.
	 *
	 * @return array intro card.
	 */
	public function get_intro_card()
	{
		$intro_card  = array();
		$title       = get_post_meta(get_the_ID(), 'gallery_intro_card_details_title', true);
		$description = get_post_meta(get_the_ID(), 'gallery_intro_card_details_description', true);

		if (empty($title) && empty($description)) {
			return $intro_card;
		}

		$intro_card['title']   = sanitize_text_field($title);
		$intro_card['content'] = $description;
		$intro_card['excerpt'] = force_balance_tags(html_entity_decode(wp_trim_words(htmlentities($description), 30, '...')));

		$vertical = $this->get_the_primary_term('vertical');

		if (false !== $vertical && $vertical instanceof \WP_Term) {
			$intro_card['vertical'] = array(
				'link' => get_term_link($vertical),
				'name' => $vertical->name,
			);
		}

		$intro_card['date'] = get_the_date();

		return $intro_card;
	}

	/**
	 * Update dimension value for tracking gallery slides with their ids.
	 *
	 * @param array $dimensions list of dimensions.
	 *
	 * @return array
	 */
	public function update_dimensions_for_gallery_slides($dimensions)
	{

		$gallery_post_id = get_queried_object_id();

		if (!is_singular(Defaults::NAME) || !intval($gallery_post_id)) {
			return $dimensions;
		}

		if (empty($dimensions) || !is_array($dimensions)) {
			$dimensions = array();
		}

		$gallery_type = self::get_current_gallery_type();
		$page_type    = sprintf('single-pmc-gallery-%s', $gallery_type);

		$dimensions['page-type']     = $page_type;
		$dimensions['page-subtype']  = sprintf('%s_item', $page_type);
		$dimensions['id']            = $gallery_post_id;
		$dimensions['child-post-id'] = ''; // Will be added in JS on slug change.

		return $dimensions;
	}

	/**
	 * Get gallery inline image.
	 *
	 * @param int  $attachment_id Attachment id.
	 * @param bool $pre_load Pre load image by adding value in src.
	 *
	 * @return void
	 */
	public static function get_gallery_inline_image($attachment_id, $pre_load = false)
	{
		$image = wp_get_attachment_image_src($attachment_id, 'ratio-3x2');

		if (empty($image[0])) {
			return;
		}

		$image_alt = wp_strip_all_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
		$src       = $pre_load ? $image[0] : '';

		printf(
			'<img src="%s" width="%d" height="%d" data-lazy="%s" class="c-gallery-inline__image" alt="%s" />',
			esc_url($src),
			esc_attr($image[1]),
			esc_attr($image[2]),
			esc_url($image[0]),
			esc_attr($image_alt)
		);
	}

	/**
	 * Add portal modal for galleries.
	 * Used for gallery in react when the DOM node is required to be in footer.
	 */
	public function add_gallery_modal()
	{
		echo '<div id="pmc-gallery-modal"></div>';
	}

	/**
	 * Filter tag options for enabling pin it.
	 *
	 * @param array $options Options.
	 *
	 * @return array
	 */
	public function filter_tag_options($options)
	{
		if (!is_singular(Defaults::NAME)) {
			return $options;
		}

		if (isset($options['pinit'])) {

			$options['pinit']['enabled']   = true;
			$options['pinit']['positions'] = ['bottom'];
		}

		return $options;
	}

	/**
	 * Filter the rel="canonical" href on pmc gallery.
	 *
	 * @since 2019-02-07 Sayed Taqui
	 *
	 * @ticket PMCP-1226
	 *
	 * @param string $rel_canonical Existing value.
	 *
	 * @return string
	 */
	public function filter_canonical_url($rel_canonical)
	{

		if (!is_singular(Defaults::NAME)) {
			return $rel_canonical;
		}

		global $wp;

		$current_slug = trailingslashit($this->get_current_slug());
		$gallery      = self::fetch_gallery();

		if (empty($gallery) || empty($gallery[0])) {
			return $rel_canonical;
		}

		$first_slide_slug = (!empty($gallery[0]['slug'])) ? trailingslashit($gallery[0]['slug']) : '';

		if ('' !== $first_slide_slug && $first_slide_slug === $current_slug) {
			return trailingslashit(get_permalink());
		}

		return trailingslashit(home_url($wp->request));
	}

	/**
	 * Ads style to the head.
	 * Style is used in the head to hide body content at early as possible.
	 * The content will be made visible from the gallery stylesheet to ensure there is no FOUC
	 *
	 * @ticket PMCP-1301 - Sayed Taqui
	 */
	public function add_style_to_hide_content()
	{
		if (self::is_standard_gallery() || self::is_runway_gallery()) {
			echo '<style>.pmc-gallery__horizontal,.pmc-gallery__runway{display:none}</style>';
		}
	}

	/**
	 * Renders out an inline gallery.
	 * An inline gallery consists of a gallery title, the main gallery image, the first image title
	 * and the count of the gallery in the format XofY. An inline Gallery allows the user click the image
	 * and then move on to the gallery page.
	 *
	 * @param $arguments -- array of arguments for rendering. expected values ['thumbnail_size','return_link', 'button_text']
	 *
	 * @return mixed|void
	 *
	 * @codeCoverageIgnore - Gallery v3 code should be deprecated or refactored
	 *
	 */
	public function render_inline_gallery($arguments = [])
	{

		if (empty(self::$id)) {
			return;
		}

		$thumbnail_size = empty($arguments['thumbnail_size']) ? 'pmc-gallery-thumb' : $arguments['thumbnail_size'];

		$button_text    = !empty($arguments['button_text']) ? $arguments['button_text'] : 'Launch Gallery';
		$title_override = get_post_meta(get_the_ID(), \PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override', true);

		if (!empty($title_override)) {
			$title = $title_override;
		} else {
			$title = get_the_title(self::$id);
		}

		$title = !empty($title) ? force_balance_tags(strip_tags($title, '<b>,<i>,<strong>,<em>')) : '';

		$gallery_link = empty($arguments['return_link']) ? $this->get_the_permalink(true) : $this->get_the_permalink(false) . $arguments['return_link'];

?>
		<div class="view-gallery">
			<a href="<?php echo esc_url($gallery_link); ?>">
				<i class="pmc-icon-gallery"></i>
				<div class="gallery-title">
					<?php echo wp_kses_post($title); ?>
				</div>
				<?php
				if (has_post_thumbnail(self::$id) || !empty($this->_data[0]['ID'])) {
					echo '<div class="inline-gallery-image">';
					if (has_post_thumbnail(self::$id)) {
						echo get_the_post_thumbnail(self::$id, $thumbnail_size);
					} else {
						$original_image_meta = wp_get_attachment_image_src($this->_data[0]['ID'], $thumbnail_size);
						echo '<img src="' . esc_url($original_image_meta[0]) . '" width="' . absint($original_image_meta[1]) . '" height="' . absint($original_image_meta[2]) . '">'; // Gallery v3 legacy code should be deprecated
					}
					echo '</div>';
				?>
					<span class="inline-gallery-nav">
						<span class="inline-gallery-launch-gallery-text">
							<?php echo esc_html($button_text); ?></span>
						<i class="fa fa-5x fa-angle-right"></i>
					</span>
				<?php
				}
				?>
			</a>
		</div>
<?php
	}
}

// EOF
