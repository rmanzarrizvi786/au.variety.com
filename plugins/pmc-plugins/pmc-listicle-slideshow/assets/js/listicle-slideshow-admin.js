(function ($) {

  $(document).on('change', '.fm-element', function (e) {

    var group = $(this).closest('.fm-item.fm-galleries.fm-group');
    var item = $(this).closest('.fm-item');

    if (group && item && item.hasClass('fm-title')) {
      var title = group.find('.fm-title').find('input[type="text"]');
      var slug = group.find('.fm-slug').find('input[type="text"]');
      if (slug.val() == '') {
        var cleanSlug = title.val().toLowerCase().replace(/[^a-zA-Z0-9]+/g, '-');
        slug.val(cleanSlug);
      }
    }
  });

  // todo - duplicate check slugs

  $('#publish').on('click', function (e) {
    // todo - ensure a category is selected
  });

})(jQuery);
