<?php

$variant = ! empty( $variant ) ? $variant : 'prototype';

$article_title = \PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-title.' . $variant );

$sub_heading = get_post_meta( get_the_ID(), '_variety-sub-heading', true );


$article_title['article_title_markup']        = get_the_title();
$article_title['c_tagline']['c_tagline_text'] = $sub_heading;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-title.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$article_title,
	true
);
