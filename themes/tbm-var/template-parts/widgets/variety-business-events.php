<?php
/**
 * Business Events module.
 *
 * @package pmc-variety
 */

$event = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/live-events.prototype' );

$event['c_span']['c_span_text']                        = $data['event_name'];
$event['c_dek']['c_dek_text']                          = $data['event_description'];
$event['o_more_link_desktop']['c_link']['c_link_text'] = __( 'Register Now', 'pmc-variety' );
$event['o_more_link_desktop']['c_link']['c_link_url']  = $data['event_link'];
$event['o_more_link_mobile']['c_link']['c_link_text']  = __( 'Register Now', 'pmc-variety' );
$event['o_more_link_mobile']['c_link']['c_link_url']   = $data['event_link'];

$image_template = $event['live_events_images'][0];
$event_template = $event['live_events_taglines'][0];

$event['live_events_images']   = [];
$event['live_events_taglines'] = [];

if ( ! empty( $data['event_speakers'] ) ) {
	foreach ( $data['event_speakers'] as $speaker ) {
		$image = $image_template;
		$item  = $event_template;

		$thumbnail  = $speaker['photo'];
		$image_data = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

		if ( ! empty( $thumbnail ) && ! empty( $image ) ) {
			$image['c_lazy_image_alt_attr']        = $image_data['image_alt'];
			$image['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$image['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail );
			$image['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail );
			$image['c_lazy_image_src_url']         = $image_data['src'];
			$image['c_figcaption_caption_markup']  = $image_data['image_caption'];
			$image['c_figcaption_credit_text']     = $image_data['image_credit'];
		} else {
			$image = [];
		}

		$item['c_tagline_markup'] = sprintf( '<strong>%s:</strong> %s', $speaker['name'], $speaker['company'] );

		$event['live_events_images'][]   = $image;
		$event['live_events_taglines'][] = $item;
	}
}

$event['cxense_subscribe_widget']['cxense_id_attr'] = 'cx-module-events-300x250';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/live-events.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$event,
	true
);
