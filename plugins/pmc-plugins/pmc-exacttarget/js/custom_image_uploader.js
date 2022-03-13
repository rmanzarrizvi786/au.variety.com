jQuery().ready(function () {
    ///////////////////////////////////////////
    // Image picker for default thumb
    //////////////////////////////////////////
    jQuery('#upload_default_thumbnail_src').click(function () {
        formfield = jQuery('#default_thumbnail_src').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    window.send_to_editor = function (html) {
        imgurl = jQuery('img', html).attr('src');
        jQuery('#default_thumbnail_src').val(imgurl);
        tb_remove();
    }
});
