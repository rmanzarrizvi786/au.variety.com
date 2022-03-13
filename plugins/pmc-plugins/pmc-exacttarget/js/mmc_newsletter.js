var pluginbase = '/wp-content/plugins/MMC_newsletter';
jQuery().ready(function () {

    /////////////////////////////
    //AddEditNewsletter page
    /////////////////////////////
    //manage mailchimp groups when choosing a mailchimp list
    jQuery('select#mmc_newsletter_list_id_select').change(function () {
        var val = jQuery(this).val();
        var valsplit = val.split('_');
        var listid = valsplit[0];
        var groups = '#mmc_newsletter_list_groups_details';
        var newsletter_id = jQuery('#mmc_newsletter_id').val();
        jQuery(groups).html('<img src="' + pluginbase + '/ajax-loader.gif" />');
        jQuery(groups).load(pluginbase + '/ajax.php?ajax_action=getGroupsForList&listid=' + listid + '&newsletter_id=' + newsletter_id + '&rnd=' + mmc_get_random_number());
    });
    jQuery('select#mmc_newsletter_list_id_select').change();

    //manage display of all categories if you choose to filter by categories
    jQuery('#mmc_newsletter_filter_posts_by_cat').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#category_select_box").show('fast');
        } else {
            jQuery("#category_select_box input[type='checkbox']").prop('checked', false);
            jQuery("#category_select_box").hide('fast');
        }
    });
    jQuery('#mmc_newsletter_filter_posts_by_cat').change();

    //manage display of all tags if you choose to filter by tags
    jQuery('#mmc_newsletter_filter_posts_by_tag').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#tag_select_box").show('fast');
        } else {
            jQuery("#tag_select_box input[type='checkbox']").prop('checked', false);
            jQuery("#tag_select_box").hide('fast');
        }
    });
    jQuery('#mmc_newsletter_filter_posts_by_tag').change();

    //disable categories and tags checkbox if most viewed or most commented is selected.
    jQuery('#mmc_newsletter_story_source').change(function () {
        if (jQuery(this).val() == "Choose") {
            jQuery("#mmc_newsletter_filter_posts_by_cat").prop('disabled', '');
            jQuery("#mmc_newsletter_filter_posts_by_tag").prop('disabled', '');
            jQuery("#mmc_newsletter_span_story_source_days").hide();
        } else {
            jQuery("#mmc_newsletter_filter_posts_by_cat").prop('checked', '')
            jQuery('#mmc_newsletter_filter_posts_by_cat').change();
            jQuery("#mmc_newsletter_filter_posts_by_cat").prop('disabled', 'disabled');

            jQuery("#mmc_newsletter_filter_posts_by_tag").prop('checked', '')
            jQuery('#mmc_newsletter_filter_posts_by_tag').change();
            jQuery("#mmc_newsletter_filter_posts_by_tag").prop('disabled', 'disabled');

            jQuery("#mmc_newsletter_span_story_source_days").show();
        }
    });
    jQuery('#mmc_newsletter_story_source').change();

    //disable Featured post requires image checkbox if template does not use feature post is selected
    jQuery("input[name=template_uses_featured_post]").change(function () {
        if (jQuery(this).val() == "never") {
            jQuery("#featured_post_requires_image").prop('checked', '')
            jQuery("#featured_post_requires_image").prop('disabled', 'disabled')
        } else {
            jQuery("#featured_post_requires_image").prop('disabled', '')
        }
    })

    jQuery("input[name=template_uses_featured_post]:checked").change();

    ///////////////////////////////////////////
    // newsletter options page
    //////////////////////////////////////////
    //sending test newsletter emails
    jQuery('.test_newsletter').click(function () {
        var newsletter_id = jQuery(this).attr('rel');
        var email = prompt("Please enter an email address to recieve the test newsletter.");
        if (email.length > 0) {
            jQuery.get(pluginbase + '/ajax.php?ajax_action=testEmail&newsletter_id=' + newsletter_id + '&email=' + email + '&rnd=' + mmc_get_random_number(), function (data) {
                if (data == 'success') {
                    alert('Test email was sent');
                } else {
                    alert('There was an error sending the email. ' + data);
                }
            });
        }
    });

    jQuery('.send_newsletter').click(function () {
        var newsletter_id = jQuery(this).attr('rel');
        var confirmed = confirm("Are you sure you want to send this newsletter right now?");
        if (confirmed) {
            jQuery.get(pluginbase + '/ajax.php?ajax_action=sendEmail&newsletter_id=' + newsletter_id + '&rnd=' + mmc_get_random_number(), function (data) {
                if (data == 'success') {
                    alert('The newsletter was sent');
                } else {
                    alert('There was an error sending the newsletter. ' + data);
                }
            });
        }
    });

    //confirm delete of newsletter
    jQuery('.delete_newsletter').click(function () {
        var shouldDelete = confirm("Are you sure you want to delete this newsletter");
        if (shouldDelete) {
            return true;
        } else {
            return false;
        }
    });

    //////////////////////////////////////////////////////////////
    // manage newsletter module on add/edit wordpress post page
    //////////////////////////////////////////////////////////////
    //textarea for newsletter featured post html content flys out when checking a featured post
    jQuery('.mmc_newsletter_select_featured_post').change(function () {
        var newsletter_id = jQuery(this).attr('value');
        if (jQuery(this).is(':checked')) {

            //check if newsletter featured post requires an image when clicking checkbox
            var rel_split = jQuery(this).attr('rel').split('|');
            var requires_image = rel_split[0] == 'image_required' ? true : false;
            var post_id = rel_split[1];
            var this_checkbox = jQuery(this);
            jQuery.getJSON(pluginbase + '/ajax.php?ajax_action=getFeaturedImageID&post_id=' + post_id + '&rnd=' + mmc_get_random_number(), function (data) {
                if (data.featured_post_image_id || requires_image == false) {
                    newsletter_id = this_checkbox.attr('value');
                    jQuery('.ajax_loader').show();
                    jQuery.getJSON(pluginbase + '/ajax.php?ajax_action=getFeaturedImageHTML&post_id=' + post_id + '&rnd=' + mmc_get_random_number(), function (data) {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            jQuery('#mmc_newsletter_featured_post_content_' + newsletter_id).val(data.html);
                        }
                        jQuery('.ajax_loader').hide();
                    });
                    jQuery('#mmc_newsletter_featured_post_content_wrap_' + newsletter_id).show('fast');
                } else {
                    alert('This newsletter requires that you select an image to be used in the featured post.  Please select an image from this posts image gallery');
                    this_checkbox.prop('checked', false);
                }
            });
        } else {
            jQuery('#mmc_newsletter_featured_post_content_wrap_' + newsletter_id).hide('fast');
        }
    });

    //when checking an image as being the featured image, make sure we remove any previously checked checkboxes because only one can be checked.  Its not a radio because we're allowing them to uncheck as well?
    jQuery('.mmc_newsletter_featured_post_image_checkbox').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery('.mmc_newsletter_featured_post_image_checkbox').prop('checked', false);
            jQuery(this).prop('checked', true);
        }
    });

    /*
     //when choosing a featured image the html for the featured post content block needs to be updated
     jQuery('#gallery-form #save-all').click(function() {
     jQuery('.mmc_newsletter_featured_post_image_checkbox').each(function() {
     //NOTE: there should only be one checked due to the function above this
     if(jQuery(this).prop('checked')==true) {
     //NOTE: this is being called from a thick box.  We need to call a function on
     //on the parent of the thickbox in order to update the actual post edit page
     var attachment_id = jQuery(this).attr('value');
     window.parent.mmc_newsletter_add_featured_image_to_editor(attachment_id);
     }
     });
     });
     */

    jQuery('#gallery-form #save-all').click(function () {
        jQuery('.mmc_newsletter_featured_post_image_checkbox').each(function () {
            //NOTE: there should only be one checked due to the function above this
            if (jQuery(this).prop('checked') == true) {
                //NOTE: this is being called from a thick box.  We need to call a function on
                //on the parent of the thickbox in order to update the actual post edit page
                var attachment_id = jQuery(this).attr('value');
                window.parent.mmc_newsletter_overide_attachment_id(attachment_id);
            }
        });
    });

    jQuery('.populate_with_featured_image').click(function () {

        relsplit = jQuery(this).attr('rel').split('|');
        post_id = relsplit[0];
        newsletter_id = relsplit[1];
        jQuery('.ajax_loader').show();
        jQuery.getJSON(pluginbase + '/ajax.php?ajax_action=getFeaturedImageHTML&post_id=' + post_id + '&rnd=' + mmc_get_random_number(), function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                /**
                 * just replace the HTML in textarea rather than adding to existing HTML
                 * @issue #1381
                 * @change since 2011-04-21 Amit Gupta
                 **/
                jQuery('#mmc_newsletter_featured_post_content_' + newsletter_id).val(data.html);
            }
            jQuery('.ajax_loader').hide();
        });
        return false;

    });

    /**
     * listen for changes in excerpt
     * @since 2011-04-20 Amit Gupta
     **/
    jQuery('#excerpt').change(function () {
        //excerpt change, so flag it for update in featured post HTML
        jQuery('#mmc_newsletter_featured_post_excerpt_flag').val('true');
    });

    jQuery('.mmc_breakingnews_selector div.list_groupings input[type=checkbox]').change(function () {
        var dlname = this.name.replace("_entirelist", "_list_div");

        if (jQuery(this).is(':checked')) {
            jQuery('#' + dlname + ' input[type=checkbox]').removeAttr('checked').prop('disabled', 'disabled');
        } else {
            jQuery('#' + dlname + ' input[type=checkbox]').removeAttr('disabled');
        }
    });

    /***************************************
     * date picker
     * Issue #1380 Fixed blocking gravity from edit issue
     * @since 2011-04-21 Satyanarayan verma
     ***************************************/
    if (jQuery('input[name="posts[schedule_start_date]"]').hasClass('datepicker')) {
        if (jQuery('.datepicker').length) {
            jQuery('.datepicker').datepick();
        }
    }

    ///////////////////////////////////////////
    // breaking news options page
    //////////////////////////////////////////
    //confirm delete of newsletter
    jQuery('.delete_breakingnews').click(function () {
        var shouldDelete = confirm("Are you sure you want to delete this Alert");
        if (shouldDelete) {
            return true;
        } else {
            return false;
        }
    });


    ///////////////////////////////////////////
    // Add/edit breaking news page
    //////////////////////////////////////////
    jQuery('select#mmc_breakingnews_list_select').change(function () {
        var val = jQuery(this).val();
        var valsplit = val.split('_');
        var listid = valsplit[0];
        var groups = '#mmc_breakingnews_list_groups_details';
        jQuery(groups).html('<img src="' + pluginbase + '/ajax-loader.gif" />');
        jQuery(groups).load(pluginbase + '/ajax.php?ajax_action=getGroupsForList&listid=' + listid + '&rnd=' + mmc_get_random_number());
    });
    jQuery('select#mmc_breakingnews_list_select').change();

    if (jQuery('input#list_id').length > 0) {
        var listid = jQuery('input#list_id').val();
        var groups = '#mmc_breakingnews_list_groups_details';
        jQuery(groups).html('<img src="' + pluginbase + '/ajax-loader.gif" />');
        jQuery(groups).load(pluginbase + '/ajax.php?ajax_action=getGroupsForList&listid=' + listid + '&rnd=' + mmc_get_random_number());
    }

    //manage display of weekdays
    jQuery('#schedule_frequency').change(function () {
        var val = jQuery(this).val();
        if (val == "daily") {
            jQuery("#weekday_select_box").show('fast');
        } else {
            jQuery("#weekday_select_box").hide('fast');
        }
    });
    jQuery('#schedule_frequency').change();

	// On keyup/change event to handle character counter.
	jQuery('#fastnewsletters-subject').on('keyup', on_change_fastnewsletters_subject);
	jQuery('#fastnewsletters-subject').on('change', on_change_fastnewsletters_subject);

});//end jQuery ready function

/**
 * Character counter.
 *
 * @ticket CDWE-136
 * @author Dhaval Parekh
 */
function on_change_fastnewsletters_subject() {
	var subject = jQuery(this).val(),
		count = parseInt(subject.length, 0);
	jQuery('#fastnewsletters-subject-counter').html(count);
}

//this function might be called from the image upload/gallery edit thickbox
function mmc_newsletter_add_featured_image_to_editor(attachment_id) {
    //Get the image attachments html image tag and populate all relevant textareas
    jQuery.getJSON(pluginbase + '/ajax.php?ajax_action=getFeaturedImageHTML&attachment_id=' + attachment_id + '&rnd=' + mmc_get_random_number(), function (data) {
        jQuery('.mmc_newsletter_featured_post_content.image_required').val(data.imagehtml);
    });
}

function mmc_newsletter_overide_attachment_id(attachment_id) {
    jQuery('.mmc_newsletter_featured_post_image_attachment_id').attr('value', attachment_id);
}


