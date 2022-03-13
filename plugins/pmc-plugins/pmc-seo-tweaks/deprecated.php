<?php
/**
 * Deprecated Global Functions.
 * Need to update themes/plugins using these functions and then we can remove them.
 */

/**
 * @deprecated 2.0 Use \PMC\SEO_Tweaks\Helpers::canonical()
 *
 * @param bool $echo
 * @param bool $unpaged
 * @param int  $post_id
 *
 * @return bool|false|string|void|WP_Error
 */
function pmc_canonical( $echo = true, $unpaged = false, $post_id = 0 ) {
	return PMC\SEO_Tweaks\Helpers::canonical( $echo, $unpaged, $post_id );
}

/**
 * @deprecated 2.0 Use \PMC\SEO_Tweaks\Helpers::amt_metatags()
 *
 * @param $meta_tags
 *
 * @return mixed|string
 */
function pmc_amt_metatags( $meta_tags ) {
	return PMC\SEO_Tweaks\Helpers::amt_metatags( $meta_tags );
}