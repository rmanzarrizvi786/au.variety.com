/**
 * A user on desktop or tablet should view the light box on
 *  the 3rd visit in a 24 hr time frame.
 */
function display_newsletter_lightbox(){
    var newsletter_lightbox_view_count  = pmc.cookie.get("pmc_newsletter_view_count");

    if(newsletter_lightbox_view_count != null && parseInt( newsletter_lightbox_view_count ) == 2 ){
        return true
    }

    return false;
}

/**
 * if the newsletter cookie count is less than 3
 * increase the count by 1.
 * if cookie does not exist create it, set to expire in 24 hrs
 * and set value to 1
 */
function log_newsletter_lightbox_view(){
    var newsletter_lightbox_view_count  = pmc.cookie.get("pmc_newsletter_view_count");
    if(  newsletter_lightbox_view_count == null  ) {
        pmc.cookie.set( "pmc_newsletter_view_count" ,"1" , 24*60*60 );
    }else{
        newsletter_lightbox_view_count =  parseInt( newsletter_lightbox_view_count );
        if( newsletter_lightbox_view_count < 3   ){
            newsletter_lightbox_view_count ++;
            pmc.cookie.set( "pmc_newsletter_view_count" , newsletter_lightbox_view_count , 24*60*60 );
        }

    }
}