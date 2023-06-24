jQuery(document).ready(function ($) {
  /**
   * Infinite scroll start
   */
  if ($("#single-wrap").length) {
    $("#single-wrap").append(
      '<div class="load-more"><div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>'
    );
    var btnLoadMoreArticles = $("#single-wrap .load-more");
    var loading = false;
    var scrollHandling = {
      allow: true,
      reallow: function () {
        scrollHandling.allow = true;
      },
      delay: 400,
    };
    var count_articles = 1;

    var winTop = $(window).scrollTop();
    var page_title = document.title;
    var page_url =
      document.location.protocol +
      "//" +
      document.location.host +
      document.location.pathname;

    var $news_stories = $("section.article-with-sidebar");
    var top_news_story = $.grep($news_stories, function (item) {
      return $(item).position().top <= winTop + 10;
    });
    var visible_news_story = $.grep($news_stories, function (item) {
      return (
        $(item).position().top <=
        winTop + $(window).height() - $(".js-Header-contents").outerHeight()
      );
    });

    $(window).scroll(function () {
      winTop = $(this).scrollTop();

      if (!loading && scrollHandling.allow) {
        scrollHandling.allow = false;
        setTimeout(scrollHandling.reallow, scrollHandling.delay);
        var offset =
          $(btnLoadMoreArticles).offset().top -
          $(window).scrollTop() -
          $(window).outerHeight();

        if (1000 > offset) {
          count_articles++;
          loading = true;

          var data = {
            action: "tbm_ajax_load_next_post",
            exclude_posts: tbm_infinite_scroll.exclude_posts,
            id: tbm_infinite_scroll.current_post,
            count_articles: count_articles,
          };
          $.post(tbm_infinite_scroll.url, data, function (res) {
            if (res.success) {
              tbm_infinite_scroll.current_post = res.data.loaded_post;
              tbm_infinite_scroll.exclude_posts += "," + res.data.loaded_post;
              $("#single-wrap").append(res.data.content);
              $("#single-wrap").append(btnLoadMoreArticles);

              fusetag.setTargeting("pagepath", ["'" + res.data.pagepath + "'"]);

              loading = false;
            } else {
              btnLoadMoreArticles.remove();
            }
          }).fail(function (xhr, textStatus, e) {}); // AJAX post to get prev post
        } // if scrolled more than offset
      } // if scrolling

      if (typeof btnLoadMoreArticles !== "undefined") {
        $news_stories = $("section.article-with-sidebar");
        visible_news_story = $.grep($news_stories, function (item) {
          return $(item).position().top <= winTop + $(window).height() / 2; // + $('#header').outerHeight() - 30;
        });

        if (
          $(visible_news_story).last().find("h1").text() != "" &&
          page_url != $(visible_news_story).last().find("h1").data("href")
        ) {
          page_title_html = $(visible_news_story)
            .last()
            .find("h1")
            .data("title");
          page_title = $("<textarea />").html(page_title_html).text();
          page_url = $(visible_news_story).last().find("h1").data("href");

          var author = $(visible_news_story)
            .last()
            .find(".author")
            .data("author");
          var cats = $(visible_news_story)
            .last()
            .find(".cats")
            .data("category");
          var tags = $(visible_news_story).last().find(".cats").data("tags");
          var pubdate = $(visible_news_story)
            .last()
            .find("time")
            .data("pubdate");
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({
            event: 'articleView',
            AuthorCD: author,
            CategoryCD: cats,
            TagsCD: tags,
            PubdateCD: pubdate,
          });

          document.title = page_title;
          window.history.pushState(null, page_title, page_url);

          article_number = $(visible_news_story)
            .last()
            .find("h1")
            .data("article-number");
        } // If visible_news_story.last().find('h1')
      } // If button exists
    }); // window.scroll
  }
  // Infinite scroll end
});
