/** ajax load "more comments" **/
var pmc_ajax_comments = {

    more_comments: function (postid) {

        var $wrapper_div = '#pmc-lazy-comments-' + postid;

        var $more_comments = $wrapper_div + " #more-comments";

        jQuery($more_comments).html('(loading)');

        var pagenum = parseInt(jQuery($more_comments).attr('pagenum')) + 1;

        var url = pmc_ajax_comments_obj.ajaxurl + postid + '/comment-page-' + pagenum;

        jQuery.get(url,
            function (response) {

                var $comments_wrapper = $wrapper_div + " ol#comment-list-wrapper";

                jQuery($comments_wrapper).append(response);

                if (jQuery($more_comments).attr('maxpages') <= pagenum) {
                    jQuery($more_comments).hide();
                } else {
                    jQuery($more_comments).attr('pagenum', pagenum);
                }

                jQuery($more_comments).html('See More Comments');
            }
        );

        return false;
    }
}