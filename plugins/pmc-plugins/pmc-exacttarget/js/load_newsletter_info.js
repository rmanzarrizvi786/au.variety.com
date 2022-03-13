jQuery(document).ready(function () {
    var data = {action:'sailthru_load_recurring_campaigns'};
    if (jQuery('#post_ID').val()) {
        data.post_id = jQuery('#post_ID').val();
    }
    jQuery.get(ajaxurl, data, function (response) {

        if( !response )
            return;

        if (response.hasOwnProperty('repeats') && null != response.repeats) {
            jQuery.each(response.repeats, function (i, repeat) {
                var checked = repeat.selected ? 'checked="true"' : '';
                jQuery('#sailthru_newsletter_featured_post_module .inside').append(jQuery(
                    '<input type="checkbox" value="' + repeat.repeat_id + '" name="st_recurring_newsletters[]" ' + checked + '/>\n\
            <label>' + repeat.name + '</label><br style="clear:both"/>'
                ));
            })
        }
        if (response.hasOwnProperty('fastnewsletters') && null != response.fastnewsletters) {
            jQuery.each(response.fastnewsletters, function (name, fastnewsletter) {
                var label = jQuery('label[for="fastnewsletter-' + name + '"]')
                if (fastnewsletter.subs !== null) {
                    label.html(label.html() + ' [' + fastnewsletter.subs + ' Subscribers]');
                }
            });
        }

    }, 'json');

	function pmc_sailthru_sanitize_tag_name(tag) {
		tag = pmc.sanitize_title( tag );
		return tag;
	}

    function pmc_sailthru_toggle_fast_newsletter( obj, show ){
        var text_parent = jQuery(obj).parent().text();
        var text_this = jQuery(obj).text();
        var text = text_parent.replace(text_this, '' );
        text = pmc_sailthru_sanitize_tag_name( text );
        if( show){
            jQuery( '#pmc_tag_' +text ).show();
        }else{
            jQuery( '#pmc_tag_' +text ).hide();
        }
    }

    function pmc_sailthru_load_fast_newsletter_tags(){
        jQuery('.tagchecklist a').each(function( index ){
            pmc_sailthru_toggle_fast_newsletter( this, true );
        });
    }

    window.setTimeout( pmc_sailthru_load_fast_newsletter_tags, 5000);

    jQuery('.tagchecklist').on( 'click', '.ntdelbutton', function(event) {
       pmc_sailthru_toggle_fast_newsletter( this, false );
    });

    function pmc_sailthru_show_fast_newsletter( ){
        var text = jQuery('#new-tag-post_tag').val();
        var text_array = text.split(',');
        if( null != text_array){
            for( var i=0; i<text_array.length; i++){
                if( '' != text_array[i] ){
                    text_val = pmc_sailthru_sanitize_tag_name( text_array[i] );
                    jQuery( '#pmc_tag_' +text_val ).show();
                }
            }
        }
    }

    jQuery('#new-tag-post_tag').keyup(function(e){
        var keycode = ( e.keyCode ? e.keyCode : e.which );
        if( keycode == 13 ) {
            pmc_sailthru_show_fast_newsletter();
        }
    });

    jQuery('#post_tag .tagadd').click(function(e){
       pmc_sailthru_show_fast_newsletter();
    });


});
