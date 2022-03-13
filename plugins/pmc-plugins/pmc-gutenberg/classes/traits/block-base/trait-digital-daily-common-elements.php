<?php

/**
 * Elements of Digital Daily's Larva patterns that are shared across several
 * story blocks.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Traits\Block_Base;

use PMC\Digital_Daily\Full_View;
use PMC\Global_Functions\Utility;
use PMC\Larva;
use WWD\Inc\Article as Article_Utilities;

/**
 * Trait Digital_Daily_Common_Elements.
 *
 * @codeCoverageIgnore Implementing classes will be refactored into Larva
 *                     controllers after Patterns are moved to `pmc-larva`.
 */
trait Digital_Daily_Common_Elements
{
	/**
	 * Set block styles.
	 *
	 * @param int $quantity Number of supported styles.
	 */
	protected function _set_styles(int $quantity): void
	{
		$this->_styles = [];
		$style_numbers = [];

		for ($num = 1; $num <= $quantity; $num++) {
			$style_numbers[] = $num;
		}

		$filtered_style_numbers = apply_filters(
			'pmc_gutenberg_story_block_style_numbers',
			$style_numbers,
			$this->_block
		);

		// Prevent adding styles not supported by the block.
		$style_numbers = array_intersect(
			$style_numbers,
			$filtered_style_numbers
		);

		$default = $style_numbers[array_key_first($style_numbers)];

		foreach ($style_numbers as $num) {
			$this->_styles[] = [
				'name'       => 'style-' . $num,
				'label'      => $num,
				'is_default' => $default === $num,
			];
		}
	}

	/**
	 * Parse block attributes along with defaults.
	 *
	 * @param array $attributes Block attributes.
	 * @return array|null
	 */
	protected function _parse_attributes(array $attributes): ?array
	{
		$parsed = parent::_parse_attributes($attributes);

		if (null === $parsed) {
			return null;
		}

		if (isset($attributes['hasContentOverride'])) {
			$parsed['contentOverride'] = $attributes['contentOverride'] ?? '';
		}

		if (isset($attributes['imageCropClass'])) {
			$parsed['imageCropClass'] = $attributes['imageCropClass'];
		}

		if (isset($attributes['backgroundColor'])) {
			$parsed['backgroundColorClassSuffix'] =
				$attributes['backgroundColor'];
		}

		return $parsed;
	}

	/**
	 * Set variant based on style selected for this block.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	protected function _get_larva_module_with_variant(array $attrs): string
	{
		// Reset for each usage per property's documentation.
		$this->larva_module_variant = 'prototype';

		if (Full_View::is()) {
			$suffix = '-full';

			if (!str_ends_with($this->larva_module, $suffix)) {
				$this->larva_module = $this->larva_module . $suffix;
				$this->template     = 'modules/' . $this->larva_module;
			}

			return parent::_get_larva_module_with_variant($attrs);
		}

		if (empty($attrs['className'])) {
			return parent::_get_larva_module_with_variant($attrs);
		}

		$parsed = preg_match(
			'#is-style-style-(\d+)#i',
			$attrs['className'],
			$matches
		);

		if (1 !== $parsed || 1 === (int) $matches[1]) {
			return parent::_get_larva_module_with_variant($attrs);
		}

		$this->larva_module_variant = (string) $matches[1];

		return parent::_get_larva_module_with_variant($attrs);
	}

	/**
	 * Remove permalink filters that keep users in the Full View, when
	 * conditions warrant it.
	 *
	 * Certain blocks do not have a Full-View representation, so users are
	 * allowed to click to that content's regular presentation.
	 *
	 * @return bool
	 */
	protected function _conditionally_remove_full_view_filters(): bool
	{
		$filtered = false;

		if (Full_View::is()) {
			$filtered = Full_View::permalink_filters_added();

			if ($filtered) {
				Full_View::remove_permalink_filters();
			}
		}

		return $filtered;
	}

	/**
	 * Restore permalink filters that keep users in the Full View, when
	 * conditions warrant it.
	 *
	 * Certain blocks do not have a Full-View representation, so users are
	 * allowed to click to that content's regular presentation.
	 *
	 * @param bool $removed If filters were removed by invoking
	 *                      `self::_conditionally_remove_full_view_filters`.
	 */
	protected function _conditionally_restore_full_view_filters(
		bool $removed
	): void {
		if (!$removed) {
			return;
		}

		if (Full_View::is()) {
			Full_View::add_permalink_filters();
		}
	}

	/**
	 * Populate post tags for full post.
	 *
	 * @param array $data `c_article_tags` Pattern data.
	 */
	protected function _add_c_article_tags(array &$data): void
	{
		$taxonomy = apply_filters(
			'pmc_gutenberg_digital_daily_article_tags_taxonomy',
			'post_tag',
			$this->_attributes['postID'],
			$this->larva_module,
			$this->template
		);

		$terms = get_the_terms($this->_attributes['postID'], $taxonomy);

		if (empty($terms) || !is_array($terms)) {
			$data = false;
			return;
		}

		$template = array_shift($data['o_nav']['o_nav_list_items']);

		$data['o_nav']['o_nav_list_items'] = [];

		foreach ($terms as $term) {
			$link = $template;

			Larva\add_controller_data(
				Larva\Controllers\Components\C_Link::class,
				[
					'text' => $term->name,
					'url'  => get_term_link($term),
				],
				$link
			);

			$data['o_nav']['o_nav_list_items'][] = $link;
		}
	}

	/**
	 * Populate author data if selected for display.
	 *
	 * @param array $data Pattern data.
	 * @return void
	 */
	protected function _conditionally_add_c_author(array &$data): void
	{
		if ($this->_attributes['hasDisplayedByline']) {
			$this->_add_c_author($data['c_author']);
		} else {
			$data['c_author'] = false;
		}
	}

	/**
	 * Populate author data for full post.
	 *
	 * @param $data `c_author` Pattern data.
	 */
	protected function _add_c_author(&$data): void
	{
		Article_Utilities::get_instance()->populate_pattern_data_for_author(
			$data,
			$this->_attributes['postID']
		);

		$data['author_details'] = false;
	}

	/**
	 * Populate `c_button` with story's permalink.
	 *
	 * @param array $data `c_button` Pattern data.
	 */
	protected function _add_c_button(array &$data): void
	{
		$data['c_button_url'] = get_permalink($this->_attributes['postID']);
	}

	/**
	 * Populate post excerpt (dek).
	 *
	 * @param array $data `c_dek` Pattern data.
	 */
	protected function _add_c_dek(array &$data): void
	{
		Larva\add_controller_data(
			Larva\Controllers\Components\C_Dek::class,
			[
				'excerpt' => $this->_attributes['excerpt'],
				'post_id' => $this->_attributes['postID'],
			],
			$data
		);
	}

	/**
	 * Populate post content in `c_paragraph`.
	 *
	 * TODO: This is named incorrectly in the pattern; it's a module, not a
	 *       component.
	 *
	 * @param array $data  `paragraph` module Pattern data.
	 * @param int   $which Number of paragraph to return.
	 */
	protected function _add_c_paragraph(array &$data, int $which): void
	{
		if (isset($this->_attributes['contentOverride'])) {
			/**
			 * If override is modified and contains unbalanced content, it may
			 * not be noticeable in Gutenberg, but can produce broken front-end
			 * output.
			 */
			$data['paragraph_markup'] = force_balance_tags(
				$this->_attributes['contentOverride']
			);

			return;
		}

		global $post;

		// `setup_postdata()` doesn't set this, it happens in `the_post()`.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = get_post($this->_attributes['postID']);

		// If processing a Gutenberg post, we need rendered blocks.
		setup_postdata($this->_attributes['postID']);
		ob_start();
		the_content();
		$content = ob_get_clean();
		wp_reset_postdata();

		$found = preg_match_all(
			'#<\s*p[^>]*>\s*(.*?)\s*<\s*\/\s*p\s*>#i',
			$content,
			$paragraphs
		);

		if ($found >= $which) {
			$data['paragraph_markup'] = $paragraphs[1][$which - 1];
		} else {
			$data['paragraph_markup'] = '';
		}
	}

	/**
	 * Populate post's primary term in `c_tag_span`.
	 *
	 * @param array $data
	 */
	protected function _add_c_tag_span(array &$data): void
	{
		$term = Utility\Post::get_primary_term($this->_attributes['postID']);

		if (null === $term) {
			$data = false;
		} else {
			Larva\add_controller_data(
				Larva\Controllers\Components\C_Span::class,
				[
					'text' => $term->name,
					'url'  => get_term_link($term),
				],
				$data
			);
		}
	}

	/**
	 * Populate post timestamp for full post.
	 *
	 * @param array $data `c_timestamp` Pattern data.
	 */
	protected function _add_c_timestamp(array &$data): void
	{
		Larva\add_controller_data(
			Larva\Controllers\Components\C_Timestamp::class,
			[
				'post_id' => $this->_attributes['postID'],
			],
			// Nested, this is not a bug.
			$data['c_timestamp']
		);
	}

	/**
	 * Populate post title.
	 *
	 * @param array $data `c_title` Pattern data.
	 */
	protected function _add_c_title(array &$data): void
	{
		Larva\add_controller_data(
			Larva\Controllers\Components\C_Title::class,
			[
				'post_id' => $this->_attributes['postID'],
				'title'   => $this->_attributes['title'],
			],
			$data
		);
	}

	/**
	 * Populate post object's relative permalink.
	 *
	 * @param string $permalink Permalink for analytics purposes.
	 */
	protected function _set_permalink_attr(string &$permalink): void
	{
		$permalinks_are_filtered = Full_View::permalink_filters_added();

		if ($permalinks_are_filtered) {
			Full_View::remove_permalink_filters();
		}

		$permalink = get_permalink($this->_attributes['postID']);

		if ($permalinks_are_filtered) {
			Full_View::add_permalink_filters();
		}
	}

	/**
	 * Populate post object's title.
	 *
	 * @param string $title Title for analytics purposes.
	 */
	protected function _set_title_attr(string &$title): void
	{
		$title = get_the_title($this->_attributes['postID']);
	}

	/**
	 * Set data for sharing icons.
	 *
	 * @param array $data Block's pattern data.
	 */
	protected function _populate_sharing_buttons(array &$data): void
	{
		$sharing_data = [
			'post_id' => $this->_attributes['postID'],
		];

		$sharing = Larva\Controllers\Modules\Social_Share::get_instance()
			->init(
				$sharing_data
			);

		$data['m_social_share']          = $sharing
			->populate_pattern_data(
				$data['m_social_share'],
				$sharing_data
			);
		$data['m_social_share_tool_tip'] = $sharing
			->populate_pattern_data(
				$data['m_social_share_tool_tip'],
				$sharing_data
			);
	}
}
