(function ($) {

  var is_mobile = ($('body.pmc-mobile').length) ? true : false;

  // define a handler for the bootstrap carousel's "after slide" event
  $('.pmc-listicle-gallery').on('slid.bs.carousel', function (e) {
    // get the index of the current slide
    var i = $('.item.active', e.target).index();
    // slide the thumbnails strip based on the current slide index, and the number of visible thumbnails
    $('.pmc-listicle-gallery-thumbs').carousel(Math.floor(i / settings.thumbs_count));
    // refresh the ad banners
    refresh_ads();
  });

  function refresh_ads() {
    // todo - add param to refresh() for all ads except the leaderboard
  }

  // show image in modal box, except on mobile
  if (!is_mobile) {
    $('.slide-image').on('click', function() {
      var src = $(this).attr('data-src');
      $('#modal-image-preview').attr('src', src);

      var img = new Image();
      img.src = src;

      $('.modal-dialog').css('width', img.width);
      $('#modal-image').modal('show');
    });
  }

  // add mobile touch events
  if (is_mobile) {

    var slide = $('.slide-image');
    var start = {};
    var end = {};

    slide.on('touchstart', onTouchstart);
    slide.on('touchmove', onTouchmove);
    slide.on('touchend', onTouchend);

    function onTouchstart(e) {
      start.pageX = e.originalEvent.targetTouches[0].pageX;
      start.pageY = e.originalEvent.targetTouches[0].pageY;
      end.pageX = e.originalEvent.targetTouches[0].pageX;
      end.pageY = e.originalEvent.targetTouches[0].pageY;
    }

    function onTouchmove(e) {
      e.preventDefault();
      end.pageX = e.originalEvent.targetTouches[0].pageX;
      end.pageY = e.originalEvent.targetTouches[0].pageY;
    }

    function onTouchend(e) {
      var gallery = $('.pmc-listicle-gallery');

      if (start.pageX < end.pageX) {
        gallery.carousel('prev');
      } else if (start.pageX > end.pageX) {
        gallery.carousel('next');
      }
    }
  }

})(jQuery);