<?php

/**
 * Variety Sponsored Content
 *
 * Based on 'Variety Sponsored Content' from
 * the pmc-variety-2014 theme.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Sponsored_Content;

use iG\Metabox\Checkbox_Field;
use iG\Metabox\Metabox;
use PMC\Global_Functions\Traits\Singleton;

class Sponsored_Content
{

	use Singleton;

	const METABOX_ID = 'vy-sponsored-content-meta-box';
	const FIELD_ID   = 'vy-sponsored-content';

	protected $_context = 'none';

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	protected function _setup_hooks()
	{

		if (!is_admin()) {
			return;
		}
		/**
		 * Actions
		 */
		add_action('init', [$this, 'init_setup'], 11); // Change priority so that all public post types are returned.

	}

	/**
	 * Delayed setup so we can fetch values (if any)
	 */
	public function init_setup()
	{

		$this->_setup_metabox();
	}

	protected function _setup_metabox()
	{

		$metabox = new Metabox(self::METABOX_ID);

		$metabox->set_title(__('Partner Content', 'pmc-variety'))
			->set_context('side')
			->set_priority('low')
			->set_css_class(self::METABOX_ID)
			->set_post_types($this->_get_post_types())
			->add_field(
				Checkbox_Field::create(
					self::FIELD_ID,
					__('Display Partner Content Flag', 'pmc-variety')
				)->set_description(__('If checked, a Partner Content flag will appear within the page.', 'pmc-variety'))->set_css_class(self::FIELD_ID)->set_value('on')
			)->add();
	}

	/**
	 * Post types that support Sponsored Content.
	 * 
	 * @return array $post_types that support Sponsored Content.
	 */
	protected function _get_post_types()
	{

		return get_post_types(['public' => true]);
	}

	/**
	 * Get sponsored badge text.
	 *
	 * @param int    $post_id Post ID, default will be current global post id.
	 * @param string $text Text of badge. default will be 'SPONSORED'
	 *
	 * @return string
	 */
	public function get_sponsored_flag_text($post_id = 0, $text = ''): string
	{

		if (empty($post_id)) {
			//post ID not passed, lets try & grab it
			$post_id = get_the_ID();
		}

		if (empty($post_id)) {
			//One more attempt at finding the ID
			$post_id = get_queried_object_id();
		}

		if (empty($post_id) || !is_numeric($post_id)) {
			// Reset context to avoid confusion
			$this->reset_context();
			return '';
		}

		$text = (!empty($text)) ? $text : __('Partner Content', 'pmc-variety');

		$is_sponsored = get_post_meta($post_id, self::FIELD_ID, true);

		return ('on' === $is_sponsored) ? $text : '';
	}

	/**
	 * Change the context so we can apply css classes depending where the flag is going to appear
	 * resets the context after running to keep things tidy
	 *
	 * @param string $context
	 */
	public function with_context($context = 'none')
	{
		$this->_context = $context;
		return $this;
	}

	public function reset_context()
	{
		$this->_context = 'none';
	}
}

// EOF
