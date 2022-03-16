(function ($) {
  "use strict";

  $(function () {
    // Article Download (Feed)
    $("#start-download-article-feed").on("click", function () {
      downloadArticleFeed();
    });

    function downloadArticleFeed() {
      if ("" == $("#article_url").val()) {
        return;
      }

      $("#migration-results").html("Downloading, please wait...");
      var data = {
        action: "start_download_article_feed",
        nonce: tbm_download_variety_com_article.nonce,
        article_url: $("#article_url").val(),
      };

      $.post(tbm_download_variety_com_article.url, data, function (response) {
        if (response.success) {
          $("#migration-results").html(
            "<div>" + response.data.result + "</div>"
          );
        } else {
          $("#migration-results").html(
            '<div style="color: red;">' + response.data.result + "</div>"
          );
        }
      });
    } // Start Download List
  });
})(jQuery);
