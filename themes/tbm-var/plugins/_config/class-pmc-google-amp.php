<?php

/**
 * Configuration for pmc-google-amp plugin
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-08-29 - CDWE-489
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Variety_VIP\Content;

class PMC_Google_AMP
{

	use Singleton;

	/**
	 * Construct method of current class.
	 *
	 *
	 */
	protected function __construct()
	{

		// Because 'is_amp_endpoint()' is only available after `parse_query` action.
		add_action('wp', [$this, 'setup_hooks'], 15);
	}

	/**
	 * Conditional method to check if current URL is AMP URL or not
	 *
	 * @return bool Returns TRUE if current URL is AMP URL else FALSE
	 */
	public function is_amp()
	{

		return \PMC::is_amp();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @since 2017-08-29 - Dhaval Parekh - CDWE-489 - Migrate to new theme.
	 *
	 *
	 *
	 * @return void
	 */
	public function setup_hooks()
	{

		if (!$this->is_amp()) {
			return;
		}

		/**
		 * Filters
		 */
		add_filter('pmc-google-amp-styles', array($this, 'setup_styling'));
		add_filter('pmc_google_amp_ga_event_tracking', array($this, 'ga_event_tracking'));
		add_filter('post_thumbnail_size', array($this, 'post_thumbnail_size'));
		add_filter('amp_social_share_template', array($this, 'get_social_share_template'), 10, 2);
		add_filter('pmc_google_amp_related_article_thumbnail_size', array($this, 'get_related_article_thumbnail_size'));
		add_filter('pmc_google_amp_get_breadcrumbs', array($this, 'get_breadcrumbs'));
		add_filter('pmc_google_amp_ix_ad_slot_ids', [$this, 'set_ix_amp_ad_slot_ids']);
		add_filter('pmc_google_amp_skimlinks_site_id', array($this, 'amp_skimlinks_publisher_code'));
		add_filter('pmc_google_amp_gallery_button_link', [$this, 'amp_gallery_button_link'], 10, 3);
		add_filter('amp_post_template_meta_parts', '__return_empty_array');
		add_action('pmc_amp_before_post_title', [$this, 'render_before_title'], 11);
		add_action('pmc_amp_after_post_title', [$this, 'render_after_title']);

		/**
		 * Use 9 priority because pmc-google-amp plugin use 10
		 * To add "Launch Gallery" and other stuffs
		 */
		add_filter('the_content', array($this, 'append_sub_heading'), 9);

		/**
		 * Actions.
		 */
		add_action('amp_post_template_footer', [$this, 'add_bombora_amp_pixel_tracking']);
	}

	/**
	 * CSS Style that will load in head section.
	 *
	 * @param string $css
	 *
	 * @return string
	 * @throws \Exception
	 *
	 *
	 */
	public function setup_styling($css = '')
	{
		$logo_img_url = sprintf(
			'%s/assets/src/svg/brand-logo.svg',
			untrailingslashit(get_stylesheet_directory_uri())
		);

		if (Content::is_vip_page()) {
			$logo_img_url = sprintf(
				'%s/assets/src/svg/brand-logo.svg',
				untrailingslashit(get_stylesheet_directory_uri())
			);
		}

		return PMC::render_template(
			sprintf('%s/plugins/templates/pmc-google-amp/single-post/css.php', CHILD_THEME_PATH),
			[
				'amp_stylesheet_path' => sprintf('%s/assets/build/css', untrailingslashit(CHILD_THEME_PATH)),
				'logo_img_url'        => $logo_img_url,
				'font_dir_url'        => sprintf(
					'%s/assets/public',
					untrailingslashit(get_stylesheet_directory_uri())
				),
			]
		);
	}

	/**
	 * To add additional event in google analytics for amp pages.
	 *
	 * @hook   pmc_google_amp_ga_event_tracking
	 *
	 * @param  array $events List of events.
	 *
	 * @return string List of events.
	 */
	public function ga_event_tracking($events)
	{

		$events = (!empty($events) && is_array($events)) ? $events : array();

		$events[] = array(
			'label'    => 'gallery_simple',
			'selector' => '.gallery-B .data a',
			'category' => 'amp',
		);

		// If related article div element is last than.
		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-fn-content .pmc-related-link:last-of-type a',
			'label'    => 'related-links_text_bottom',
		);

		// If related article div element is in middle than.
		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-fn-content .pmc-related-link:not(:last-of-type) a',
			'label'    => 'related-links_text_mid',
		);

		// For Related link with thumbnail.
		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-fn-content div.related-story-container:last-of-type a .related-story-image',
			'label'    => 'related-links_thumbnail_bottom',
		);

		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-fn-content div.related-story-container:not(:last-of-type) a .related-story-image',
			'label'    => 'related-links_thumbnail_mid',
		);

		return $events;
	}

	/**
	 * To change featured image size for AMP page.
	 *
	 * @since   2017-07-08 - Dhaval Parekh - CDWE-372
	 * @version 2017-08-29 - Dhaval Parekh - CDWE-589 - Migrated to new theme
	 *
	 * @param   string $size Size of feature image to be show.
	 *
	 * @return  string Size of feature image to be show.
	 */
	public function post_thumbnail_size($size)
	{
		return 'landscape-large';
	}

	/**
	 * To changes template of social sharing in AMP page.
	 *
	 * @param  string $template_path Template path.
	 * @param  string $location Location where is being render.
	 *
	 * @return boolean
	 */
	public function get_social_share_template($template_path, $location)
	{

		if ('top' === strtolower($location)) {
			return false;
		}

		return $template_path;
	}

	/**
	 * Related article link thumbnail size for AMP page.
	 *
	 * @param  string $size One of registered image size.
	 *
	 * @return string One of registered image size.
	 */
	public function get_related_article_thumbnail_size($size = '')
	{
		return 'landscape-small';
	}

	/**
	 * Get breadcrumbs for AMP page.
	 *
	 * @hook   pmc_google_amp_get_breadcrumbs
	 *
	 * @param  array $breadcrumbs List of breadcrumbs.
	 *
	 * @return array
	 */
	public function get_breadcrumbs($breadcrumbs)
	{

		if (empty($breadcrumbs) || !is_array($breadcrumbs)) {
			$breadcrumbs = array();
		}

		$_breadcrumbs = \PMC\Core\Inc\Theme::get_instance()->get_breadcrumb();

		$breadcrumbs[] = array(
			'label' => __('Home', 'pmc-variety'),
			'href'  => home_url('/'),
		);

		if (!empty($_breadcrumbs) && is_array($_breadcrumbs)) {

			foreach ($_breadcrumbs as $crumb) {
				$breadcrumbs[] = array(
					'label' => $crumb->name,
					'href'  => get_term_link($crumb),
				);
			}
		}

		return $breadcrumbs;
	}

	/**
	 * To append Sub heading before content and after Launch gallery button
	 *
	 * @param string $content Post content.
	 *
	 * @return string Post Content.
	 */
	public function append_sub_heading($content = '')
	{

		$sub_heading = get_post_meta(get_the_ID(), '_variety-sub-heading', true);

		if (!empty($sub_heading)) {
			$sub_heading = sprintf('<h3 class="sub-heading">%s</h3>', esc_html($sub_heading));
		} else {
			$sub_heading = '';
		}

		return $sub_heading . $content;
	}

	/**
	 * Set Array of IX amp slot ids.
	 *
	 * @param array $ix_slot_ids Array of IX amp slot ids.
	 *
	 * @return array
	 */
	public function set_ix_amp_ad_slot_ids($ix_slot_ids)
	{

		return [
			'amp-header'        => '331228',
			'amp-bottom'        => '331231',
			'amp-adhesion'      => '331232',
			'amp-mid-article'   => '331229',
			'amp-mid-article-1' => '331230',
			'amp-mid-article-x' => '331230',
		];
	}

	/**
	 * Add Bombora amp page view tracking pixel codes.
	 */
	public function add_bombora_amp_pixel_tracking()
	{
		printf('<amp-pixel src="%s" layout="nodisplay"></amp-pixel>', esc_url('https://ml314.com/utsync.ashx?eid=65499&et=0&cid=amp'));
	}

	/**
	 * Set skimlinks publisher code for amp
	 *
	 * @return string skimlinks publisher code
	 */
	public function amp_skimlinks_publisher_code($publisher_code)
	{

		return '87443X1540253';
	}

	/**
	 * Function prevent 'pmc_google_amp_gallery_button_link' to return amp link for
	 * 'dirt' cross post type.
	 *
	 * @hook  pmc_google_amp_gallery_button_link
	 *
	 * @param string $amp_url
	 * @param int    $gallery_id
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function amp_gallery_button_link(string $amp_url, int $gallery_id, int $post_id)
	{

		$dirt_url = get_post_meta($post_id, 'dirt_permalink', true);

		if (empty($dirt_url)) {
			// Return not a cross post from Dirt.com
			return $amp_url;
		}

		// Is a cross post from Dirt.com
		$linked_gallery = get_post_meta($post_id, 'pmc-gallery-linked-gallery', true);

		if (empty($linked_gallery)) {
			return $amp_url;
		}

		$linked_gallery = json_decode($linked_gallery, true);

		if (!empty($linked_gallery['url'])) {
			$amp_url = $linked_gallery['url'];
		}

		return $amp_url;
	}

	/**
	 * Render elements before title.
	 *
	 *
	 */
	public function render_before_title()
	{
		PMC::render_template(
			sprintf('%s/templates/pmc-google-amp/single-post/meta-time.php', untrailingslashit(dirname(__DIR__))),
			[],
			true
		);
	}

	/**
	 * Render elements after title.
	 *
	 *
	 */
	public function render_after_title()
	{
		PMC::render_template(
			sprintf('%s/templates/pmc-google-amp/single-post/excerpt.php', untrailingslashit(dirname(__DIR__))),
			[],
			true
		);

		PMC::render_template(
			sprintf('%s/templates/pmc-google-amp/single-post/meta-author.php', untrailingslashit(dirname(__DIR__))),
			[],
			true
		);
	}
}
