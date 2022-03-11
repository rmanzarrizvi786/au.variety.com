<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$awards = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-awards.prototype' );

// Awards.
$main_template     = $awards['awards_curated']['o_tease_primary'];
$side_template     = $awards['awards_curated']['o_tease_list']['o_tease_list_items'][0];
$alt_side_template = $awards['awards_curated']['o_tease_list']['o_tease_list_items'][1];

$awards['awards_curated']['o_tease_primary']                           = [];
$awards['awards_curated']['o_tease_list']['o_tease_list_items']        = [];
$awards['awards_curated']['o_tease_list_bottom']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['awards'] ) ) {
	$count = 1;

	foreach ( $data['articles']['awards'] as $_post ) {
		if ( 1 === $count ) {
			$main_template['c_span']      = false;
			$main_template['c_link']      = false;
			$main_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $main_template );
		} elseif ( 1 === $count % 2 ) {
			$list_template['c_span']      = false;
			$list_template['c_link']      = false;
			$list_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $alt_side_template );
		} else {
			$side_template['c_span']      = false;
			$side_template['c_link']      = false;
			$side_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $side_template );
		}

		$item = $populate->get();

		if ( 1 === $count ) {
			if ( ! empty( $item['c_dek']['c_dek_text'] ) ) {
				$item['c_dek']['c_dek_text'] = wp_strip_all_tags( $item['c_dek']['c_dek_text'] );
			}
			$awards['awards_curated']['o_tease_primary'] = $item;
		} elseif ( $count > 3 ) {
			$awards['awards_curated']['o_tease_list_bottom']['o_tease_list_items'][] = $item;
		} else {
			$awards['awards_curated']['o_tease_list']['o_tease_list_items'][] = $item;
		}

		$count++;
	}
}

$awards['awards_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More Awards', 'pmc-variety' );
$awards['awards_curated']['o_more_link']['c_link']['c_link_url']  = '';

if ( ! empty( $data['awards_more_text'] ) ) {
	$awards['awards_curated']['o_more_link']['c_link']['c_link_text'] = $data['awards_more_text'];
}

if ( ! empty( $data['awards_more_link'] ) ) {
	$awards['awards_curated']['o_more_link']['c_link']['c_link_url'] = $data['awards_more_link'];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-awards.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$awards,
	true
);
