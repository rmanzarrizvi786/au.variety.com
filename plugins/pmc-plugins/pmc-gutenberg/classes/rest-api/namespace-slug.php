<?php
/**
 * Set consistent namespace slug without repetition.
 *
 * @package pmc-gutenberg
 */
namespace PMC\Gutenberg\REST_API;

/**
 * Trait Namespace_Slug.
 */
trait Namespace_Slug {
	/**
	 * Return endpoint's slug for use within the `pmc` namespace. Slug will be
	 * prefixed with `pmc/` and have version appended automatically.
	 *
	 * Often, this will be the name of the implementing plugin, with the leading
	 * `pmc-` removed; for example, `pmc-carousel` endpoints would set this to
	 * `carousel`.
	 *
	 * @return string
	 */
	protected function _get_namespace_slug(): string {
		return 'gutenberg';
	}
}
