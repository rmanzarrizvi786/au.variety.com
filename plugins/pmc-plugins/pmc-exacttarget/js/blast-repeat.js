jQuery(document).ready(function () {
    function sailthru_prepare_msg( msg ){
        new_msg = jQuery.parseJSON( msg );
        if( new_msg.error ){
            return msg;
        } else{
            return "Blast Sent Successfully";
        }
    }
    jQuery('.test-media-recurring-newsletter').click(function () {
        var data = {
            action:'sailthru_media_send_repeat',
            test_email:jQuery("#test-email").val(),
            '_sailthru_media_newsletter_nonce': jQuery("#_sailthru_media_newsletter_nonce").val()
        };
        jQuery.post(ajaxurl, data, function (response) {
            if (response.error) {
                alert(response.error);
            } else {
                alert( sailthru_prepare_msg( response ) );
            }
            jQuery('#test-email-dialog').dialog("close");
        });
    });
    jQuery('.test-media-dialog-open').click(function () {
        jQuery('#test-email-dialog').dialog();
        jQuery('#test-email-dialog').dialog("open");
        jQuery("#blast-repeat-id").val(jQuery(this).attr('rel'));
        return false;
    });
    jQuery('.send-media-newsletter-now').click(function () {

        var answer;

        answer = confirm("Are you sure you want to send the newsletter now?");

        if (answer === true) {
            var data = {
                action:'sailthru_media_send_repeat',
                '_sailthru_media_newsletter_nonce': jQuery("#_sailthru_media_newsletter_nonce").val()
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response.error) {
                    alert(response.error);
                    //return false;
                } else {
                    alert( sailthru_prepare_msg( response ) );
                }
            });
        } else {
            return false;
        }
    })
});
