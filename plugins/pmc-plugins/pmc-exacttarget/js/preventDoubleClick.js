jQuery().ready(function () {
    jQuery('#p75-thumbnail-posting #publish.button-primary').click(function () {
        if (jQuery('#mc_meta_field').prop('checked') === true) {
            //hmm... disabling this button here caused wordpress to only save a post as a draft
            //you could never publish! So, lets let WP take care of its own doubleclick BS,
            //all we care about is not sending more than one Email blast for the same thing.
            //jQuery("#publish.button-primary").attr('disabled','disabled');//removed, see comment above
            return true;
        } else {
            var meta_fields = jQuery(".fast_breaking_news_meta_field");
            var bln_return = true;
            meta_fields.each(function () {
                if (jQuery(this).prop('checked') === false) {
                    //hmm... disabling this button here caused wordpress to only save a post as a draft
                    //you could never publish! So, lets let WP take care of its own doubleclick BS,
                    //all we care about is not sending more than one Email blast for the same thing.
                    //jQuery("#publish.button-primary").attr('disabled','disabled');//removed, see comment above
                    //return true;
                } else {
                    alert("You have opted to send a Breaking News Alert to all your subscribers. However, you are only updating an image.  Please keep Breaking News Sailthru email alert checkbox unchecked until you are ready to send the alert.  The form submit process is cancelling now, you will need to uncheck the the Newsletter box and then update your image again because this form submit has been cancelled.          ");
                    bln_return = false;
                }
            });

            return bln_return;
        }
    });

    jQuery('#submitpost #publish.button-primary').click(function () {
        var currentDate = new Date();

        //get utc date in millisecond
        var utc_ms =  currentDate.getTime() + (currentDate.getTimezoneOffset() * 60000);

        //get wp_offset
        var offset = jQuery('#pmc_sailthru_timegmt').data('date_time');

        //get date in wordpress timezone
        var wp_timezone_date = new Date( utc_ms + (3600000*offset) );

        // the date value contains `@ ` or `at ` in the string. This only works in chrome.
        var post_date = jQuery( "#timestamp b" ).html().replace( '@ ', '' ).replace( 'at ', '' );
        post_date = post_date.split('-');
        post_date = post_date[post_date.length-1];

        var postDate = new Date( post_date );
        var meta_fields = jQuery(".fast_breaking_news_meta_field");
        var shouldSend = false;
        var attemptFutureSend = false;
        var shouldConfirmSend = false;
		var custom_subject = jQuery('#fastnewsletters-subject').val();

        var alertList = "";
        meta_fields.each(function () {
            if (jQuery(this).prop('checked') === true) {
                //hmm... disabling this button here caused wordpress to only save a post as a draft
                //you could never publish! So, lets let WP take care of its own doubleclick BS,
                //all we care about is not sending more than one Email blast for the same thing.
                //jQuery("#publish.button-primary").attr('disabled','disabled');//removed, see comment above
                if (postDate > wp_timezone_date) {
                    attemptFutureSend = true;
                }
                shouldConfirmSend = true;
                //get Alert Checkbox ID

                var alertCbId = jQuery(this).attr('id');
                //get Alert Label
                var alertTitleLabel = jQuery('label[for="' + alertCbId + '"]').html();
                var arrAlertTitleLabel = alertTitleLabel.split(" [", 1);
                var alertTitle = arrAlertTitleLabel[0];
                //make Alert ID for display
                var alertId = alertCbId.replace('_meta_field', '');
                alertList += alertTitle + " (List ID: " + alertId + ")\n";


            }
        });

        if (shouldConfirmSend) {

            var confirm_message = "You have opted to send a Breaking News Alert to the following list(s):\n\n" + alertList;

			if ( 'string' === typeof ( custom_subject ) && '' !== custom_subject ) {
				confirm_message = confirm_message + "\n\nThe Breaking News Alert has the subject line : " + custom_subject + "\n\n";
			}

            if( attemptFutureSend ) {
                confirm_message = confirm_message + "This is a scheduled post, alert will be sent at the time of publish\n\n";
            }
            confirm_message = confirm_message +  "\n\n\nAre you sure you want to do this?";
            var answer = confirm( confirm_message );

            if (answer == true) {
                //same issue as above, I'd love to disable the button, but I think WP is using js to submit the button twice, once for a draft/revision and then again for the actual publish.  But if we disable the button then the post never gets published.
                //jQuery("#publish.button-primary").attr('disabled','disabled');//removed

                //wow, WP 2.8.5 update added a hidden fieled named 'action'
                //this prevents javascript from being able to access and modify the action forms
                //action attribute! So we have the 3 lines instead of just the one
                //We want to update the forms action so that we have a reference of an email blast
                //in the apache access logs
                jQuery('#hiddenaction').attr('name', 'tmpaction');
                jQuery('#post').attr('action', 'post.php?breakingNewsBlast=true');
                jQuery('#hiddenaction').attr('name', 'action');
                return true;
            } else {
                setTimeout(function () {
                    jQuery('#ajax-loading').css('visibility', 'hidden');
                    jQuery('#publish').removeClass('button-primary-disabled');
                }, 500);

                return false;
            }
        } else {
            return true;
        }
    });

});

