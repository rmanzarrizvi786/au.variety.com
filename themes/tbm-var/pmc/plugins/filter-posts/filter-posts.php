<?php

/**
 * This plugin is written to allow post type filtering on home page.
 * Configurable to support variables condition and post types.
 * Default to filter on post type post on home page.
 *
 * @author: Hau Vong (hvong@pmc.com)
 * @since 2017-07-28
 *
 */

require_once __DIR__ . '/classes/class-filters.php';
\PMC\Core\Plugins\Filter_Posts\Filters::get_instance();
