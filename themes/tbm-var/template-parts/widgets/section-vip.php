<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$json = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-vip.prototype' );

// VIP.
$hero_template     = $json['vip_curated']['o_tease_primary'];
$list_template     = $json['vip_curated']['o_tease_list']['o_tease_list_items'][0];
$alt_list_template = $json['vip_curated']['o_tease_list']['o_tease_list_items'][1];

$json['vip_curated']['o_tease_primary']                           = [];
$json['vip_curated']['o_tease_list']['o_tease_list_items']        = [];
$json['vip_curated']['o_tease_list_bottom']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['vip'] ) ) {
	$count = 1;

	foreach ( $data['articles']['vip'] as $_post ) {
		if ( 1 === $count ) {
			$hero_template['c_span']      = false;
			$hero_template['c_link']      = false;
			$hero_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $hero_template );
		} elseif ( 1 === $count % 2 ) {
			$list_template['c_span']      = false;
			$list_template['c_link']      = false;
			$list_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $alt_list_template );
		} else {
			$list_template['c_span']      = false;
			$list_template['c_link']      = false;
			$list_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $list_template );
		}

		$item = $populate->get();

		if ( 1 === $count ) {
			if ( ! empty( $item['c_dek']['c_dek_text'] ) ) {
				$item['c_dek']['c_dek_text'] = wp_strip_all_tags( $item['c_dek']['c_dek_text'] );
			}
			$json['vip_curated']['o_tease_primary'] = $item;
		} elseif ( $count > 3 ) {
			$json['vip_curated']['o_tease_list_bottom']['o_tease_list_items'][] = $item;
		} else {
			$json['vip_curated']['o_tease_list']['o_tease_list_items'][] = $item;
		}

		$count++;
	}
}

$json['vip_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More VIP', 'pmc-variety' );
$json['vip_curated']['o_more_link']['c_link']['c_link_url']  = \Variety\Plugins\Variety_VIP\VIP::vip_url();

if ( ! empty( $data['vip_more_text'] ) ) {
	$json['vip_curated']['o_more_link']['c_link']['c_link_text'] = $data['vip_more_text'];
}

if ( ! empty( $data['vip_more_link'] ) ) {
	$json['vip_curated']['o_more_link']['c_link']['c_link_url'] = $data['vip_more_link'];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-vip.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$json,
	true
);
