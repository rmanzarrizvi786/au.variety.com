<?php
/**
 * Handler for Editorial Critics Picks Badge
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-08-11
 *
 * @package pmc-variety-2017
 */

namespace Variety\Inc\Badges;

class Critics_Pick extends Badge {

	/**
	 * Taxonomy slug for badge term.
	 *
	 * @var string
	 */
	const TAXONOMY_SLUG = 'editorial';

	/**
	 * Slug of Term that is used as badge.
	 *
	 * @var string
	 */
	const TERM_SLUG = 'critics_pick';

	/**
	 * Slug of Term that is used as badge.
	 *
	 * @var string
	 */
	const TERM_NAME = "Critic's Pick";

}
