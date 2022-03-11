<?php

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/buy-now.prototype' );

$guid   = '';
$target = ( ! empty( $target ) ) ? $target : '_blank';
$url    = $link;

if ( ! empty( $product ) && is_object( $product ) ) {
	$text = $title;
	$url  = ( ! empty( $url ) ) ? $url : $product->url;
	$guid = $product->guid;
}

$data['guid_attr']            = $guid;
$data['link_url']             = $url;
$data['target_attr']          = $target;
$data['buy_now_product_text'] = $text;
$data['orig_price_text']      = $orig_price;
$data['price_text']           = $price;
$data['product']              = [
	'title' => $text,
	'price' => $price,
];

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/buy-now.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
