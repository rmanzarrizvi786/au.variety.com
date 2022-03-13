var pmc_krux_gallery_view_triggered = pmc_krux_gallery_view_triggered || [];

/**
 * Fire Krux pixel once every 5th slide
 * @Jira: PPT-4256
 */
jQuery( document ).on( 'pmc-gallery-image-rendered', function (event) {
    var curr_img_pos = event.current == 0 ? 1 : event.current;
    if ( 1 == curr_img_pos || 0 == ( curr_img_pos % 5) || pmc_krux_gallery_view_triggered.length == 0 ) {
        if ( pmc_krux_gallery_view_triggered.indexOf( curr_img_pos ) < 0 ) {
            if ( typeof krux_event_pixels != 'undefined' && typeof krux_event_pixels.gallery_slide_view != 'undefined' ) {
                /* krux_event_pixels.gallery_slide_view is already escaped in php using esc_url_raw() */
                ( new Image() ).src = krux_event_pixels.gallery_slide_view;
				pmc_krux_gallery_view_triggered.push( curr_img_pos );
            }
        }
    }
});

