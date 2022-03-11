<?php
if ( empty( $instance['id'] ) || empty( $instance['widget_id'] ) ) {
	return;
}

$cxense_widget                          = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/cxense-widget.prototype' );
$cxense_widget['cxense_widget_id_attr'] = $instance['widget_id'];
$cxense_widget['cxense_id_attr']        = $instance['id'];

if ( ! empty( $instance['classes'] ) ) {
	$cxense_widget['cxense_widget_classes'] = $instance['classes'];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/cxense-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$cxense_widget,
	true
);
//
