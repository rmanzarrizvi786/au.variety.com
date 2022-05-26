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
        action: "tbm_start_download_article_feed",
        nonce: tbm_download_variety_com_content.nonce,
        article_url: $("#article_url").val(),
      };

      $.post(tbm_download_variety_com_content.url, data, function (response) {
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

  // List Download
  $("#start-download-list").on("click", function () {
    download();
  });

  function download() {
    $("#migration-results").html("Downloading, please wait...");
    var data = {
      action: "tbm_start_download_list",
      nonce: tbm_download_variety_com_content.nonce,
      list_url: $("#list_url").val(),
    };

    $.post(tbm_download_variety_com_content.url, data, function (response) {
      if (response.success) {
        $("#migration-results").html("<div>" + response.data.result + "</div>");
        if (response.data.has_next_page) {
          var data = {
            action: "tbm_continue_download_list",
            nonce: tbm_download_variety_com_content.nonce,
            list_url: response.data.list_url,
            list_id: response.data.list_id,
            total_list_items: response.data.total_list_items,
            term_taxonomy_id: response.data.term_taxonomy_id,
          };
          continue_download_list(data);
        }
      } else {
        $("#migration-results").html(
          '<div style="color: red;">' + response.data.result + "</div>"
        );
      }
    });
  } // Start Download List

  function continue_download_list(data) {
    $.post(tbm_download_variety_com_content.url, data, function (response) {
      if (response.success) {
        $("#migration-results").html("<div>" + response.data.result + "</div>");
        if (response.data.has_next_page) {
          var data = {
            action: "tbm_continue_download_list",
            nonce: tbm_download_variety_com_content.nonce,
            list_url: response.data.list_url,
            list_id: response.data.list_id,
            total_list_items: response.data.total_list_items,
            term_taxonomy_id: response.data.term_taxonomy_id,
          };
          continue_download_list(data);
        } else {
          $("#migration-results").html(
            '<div style="color: red;">' + response.data.result + "</div>"
          );
        }
      }
    });
  } // Continue Download List
})(jQuery);
