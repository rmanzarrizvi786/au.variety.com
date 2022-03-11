<?php

if ( empty( $instance ) || empty( $instance['url'] ) ) {
	return;
}

$iheart_widget = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/iheart-widget.prototype' );

$iheart_widget['iheart_widget_header_title_text']    = $instance['title'];
$iheart_widget['iheart_widget_header_subtitle_text'] = $instance['subtitle'];
$iheart_widget['iheart_widget_footer_text']          = __( 'A Variety and iHeartRadio Podcast', 'pmc-variety' );

$iheart_widget['iheart_widget_iframe_url']         = $instance['url'];
$iheart_widget['iheart_widget_iframe_height_attr'] = $instance['iframe_height'] ?? 170;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/iheart-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$iheart_widget,
	true
);
