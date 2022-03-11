<?php
/**
 * Hooks for registering Field Manager Fields.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

remove_action( 'fm_post_post', 'pmc_core_fields_relationships' );
remove_action( 'fm_post_issue-article', 'pmc_core_fields_relationships' );
remove_action( 'fm_post_issue', 'pmc_core_fields_relationships' );
remove_action( 'fm_post_pmc-gallery', 'pmc_core_fields_relationships' );
remove_action( 'fm_post_pmc-list-slideshow', 'pmc_core_fields_relationships' );
