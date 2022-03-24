<?php

/**
 * An abstract class to restrict taxonomy term creation
 *
 * @package pmc-taxonomy-restrictions
 */

namespace PMC\Taxonomy_Restrictions;

use \PMC\Global_Functions\Traits\Singleton;

abstract class Taxonomy_Restrictions
{

	use Singleton;

	/**
	 * Default whitelisted user roles
	 *
	 * @var array
	 */
	protected $_default_user_roles_whitelist = array('administrator', 'pmc-editorial-manager');

	/**
	 * Filtered whitelisted user roles.
	 *
	 * @var array
	 */
	protected $_user_roles_whitelist = array();

	/**
	 * Initialize
	 */
	protected function __construct()
	{

		add_filter('pre_insert_term', array($this, 'prevent_term_creation'), 10, 2);

		add_action('admin_init', array($this, 'remove_new_terms_from_post_var'));
	}

	/**
	 * Return true if theme option is enabled else false
	 *
	 * This function must override by child class
	 *
	 * @return bool
	 */
	abstract protected function _is_term_creation_restricted();

	/**
	 * Return whitelisted user roles
	 *
	 * @return array
	 */
	protected function _get_user_roles_whitelist()
	{

		if (!empty($this->_user_roles_whitelist)) {
			return $this->_user_roles_whitelist;
		}

		/**
		 * Allow to filter default whitelisted user roles
		 *
		 * @param array $_default_user_roles_whitelist Default whitelisted user roles.
		 */
		$this->_user_roles_whitelist = apply_filters(
			sprintf(
				'pmc-taxonomy-restrictions-%s-user-role-whitelist',
				str_replace('_', '-', $this::TAXONOMY)
			),
			$this->_default_user_roles_whitelist
		);

		return $this->_user_roles_whitelist;
	}

	/**
	 * Return WP_Error object to prevent creation of new term
	 *
	 * @param string $term     The term to add or update.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return string|\WP_Error
	 */
	public function prevent_term_creation($term, $taxonomy)
	{

		if ($this::TAXONOMY !== $taxonomy) {
			return $term;
		}

		if (!$this->_is_term_creation_restricted()) {
			return $term;
		}

		$user = wp_get_current_user();

		if (!empty($user->roles) && empty(array_intersect($this->_get_user_roles_whitelist(), $user->roles))) {

			return new \WP_Error('term_addition_blocked', __('You are not allowed to create new term.', 'pmc-plugins'));
		}

		return $term;
	}

	/**
	 * Remove new terms of non-hierarchical taxonomy added by restricted user roles on post edit page
	 *
	 * @return void
	 */
	public function remove_new_terms_from_post_var()
	{

		// Hierarchical taxonomy terms will create on edit post page using ajax and
		// it will restrict by prevent_term_creation() so no need to process further.
		if (is_taxonomy_hierarchical($this::TAXONOMY)) {
			return;
		}

		if (!$this->_is_term_creation_restricted()) {
			return;
		}

		$user = wp_get_current_user();

		if (!empty($user->roles) && !empty(array_intersect($this->_get_user_roles_whitelist(), $user->roles))) {
			return;
		}

		if (empty($_POST['action']) || !in_array(wp_unslash($_POST['action']), array('editpost', 'inline-save'), true)) {
			return;
		}

		if (empty($_POST['tax_input']) || empty($_POST['tax_input'][$this::TAXONOMY])) {
			return;
		}

		$terms = wp_unslash($_POST['tax_input'][$this::TAXONOMY]);

		// code from core file.
		if (!is_array($terms)) {
			$terms = explode(',', trim($terms, " \n\t\r\0\x0B,"));
		}

		$exist_terms = array();

		foreach ($terms as $term) {

			if (empty($term)) {
				continue;
			}

			if (term_exists($term, $this::TAXONOMY)) {
				$exist_terms[] = sanitize_text_field($term);
			}
		}

		$_POST['tax_input'][$this::TAXONOMY] = implode(',', $exist_terms);
	}
}
