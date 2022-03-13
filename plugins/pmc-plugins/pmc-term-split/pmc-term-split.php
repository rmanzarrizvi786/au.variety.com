<?php
/**
 * @ticket PPT-4753, PPT-4736
 * This plugin is written to fix WP 4.2 shared taxonomy term splitting
 *
 * @ref: https://wordpressvip.zendesk.com/requests/41079
 * @ref: https://make.wordpress.org/core/2015/02/16/taxonomy-term-splitting-in-4-2-a-developer-guide/
 * @ref: https://developer.wordpress.org/plugins/taxonomy/working-with-split-terms-in-wp-4-2/
 *
 * @since 2015-06-01 Hau Vong
 *
 * The approach to fix term split is to add a filter on each plugin code where the term need fix
 * this plugin will use those filters hook to make the appropriate changes without affect the
 * underline plugin code. All pmc-plugins that are affected will have code fix placed in pmc-term-split/[plugin-name].php
 * We need to isolate the code so we can turn on/off without touch the plugin source
 * We add a custom function to save the history of split term and not rely on wp_get_split_term as wp core function use
 * a single array and have potential race condition and not memory safe if terms split history are large.
 * @see class PMC_Term_Split
 *
 * For term that save in post meta or wp options, a filter should be added to allow value to be override.
 * Example, the primary category plugin save category_id as post_meta 'primary_category':
 * We first add a filter to allow primary_category override after get_post_meta, eg.
 *   $primary_category = get_post_meta( $post_id, 'primary_category', true );
 *   $primary_category = apply_filters( 'get_post_primary_category', $primary_category, $post_id );
 *
 * From another class file (add to theme if for individual lob, add to pmc-term-split/plugins if plugin belong to pmc-plugins)
 * we add following code to do the fix:

	add_filter('get_post_primary_category', function($term_id,$post_id){
		if ( $new_term_id = PMC_Term_Split::get_instance()->get_term_id( $term_id, 'category' ) ) {
		$term_id = $new_term_id;
			update_post_meta( $post_id, 'primary_category', $new_term_id, $term_id );
		}
		return $term_id;
	},10,2);

	add_action('pmc_cli_split_shared_term', function($old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy){

		$meta_key = 'primary_category';
		$args = array(
			'fields' => 'ids',
			'meta_key' => $meta_key,
			'meta_value' => $old_term_id,
		) ;

		$first_item = false;
		do {
			$post_ids = get_posts( $args );
			if ( ! empty( $post_ids ) ) {
				$post_id = reset( $post_ids );
				if ( $first_item && $first_item == $post_id ) {
					// Endless loop detected, let's bail out
					break;
				}
				$first_item = $post_id;
				foreach ( $post_ids as $post_id ) {
					update_post_meta( $post_id, $meta_key, $new_term_id, $old_term_id );
				}
			}

		} while ( !empty( $post_ids ) );

	},10,4);

 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// add WP-CLI command support
if ( defined('WP_CLI') && WP_CLI ) {
	require_once __DIR__ . '/class-pmc-term-split-cli.php';
}

require_once __DIR__ . '/class-pmc-term-split.php';
require_once __DIR__ . '/plugins/pmc-primary-taxonomy.php';
require_once __DIR__ . '/plugins/pmc-seo-tweaks.php';
require_once __DIR__ . '/plugins/sailthru.php';

/**
 * Disable global terms on WordPress.com.  Affecting term split if not disabled
 */

if ( ! defined('PMC_IS_VIP_GO_SITE') || ! PMC_IS_VIP_GO_SITE ) {
	if ( function_exists( 'wpcom_vip_disable_global_terms' ) ) {
		wpcom_vip_disable_global_terms();
	}
}

// EOF