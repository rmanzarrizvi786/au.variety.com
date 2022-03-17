/**
 * Newsletter Subscribe to Observer
 */
jQuery(document).ready(function ($) {
  $(".o-email-capture-form").on("submit", function (e) {
    e.preventDefault();
    var theForm = $(this);
    var emailField = theForm.find(".c-email-field__input");
    if (!emailField.length) {
      return;
    }
    var data = {
      action: "subscribe_observer",
      email: emailField.val(),
    };
    $.post(tbm.ajaxurl, data, function (res) {
      if (res.success) {
        theForm
          .parent()
          .append('<div class="success">' + res.data.message + "</div>");
        theForm.hide();
      }
    });
  });

  /**
 * Toggle Brands menu
 */
  $(".l_toggle_menu_network").on("click", function (e) {
    e.preventDefault();
    $("#menu-network").toggle();
    $("#main-wrapper").toggleClass("freeze");
    // $("body").toggleClass("network-open");
    $("#body-overlay").toggle();
    // $(".is-header-sticky .l-header__search").toggle();
    $(this).toggleClass("expanded");
  });

  $("#body-overlay").on("click", function () {
    $(".l_toggle_menu_network").trigger("click");
  });
  $(".l_toggle_menu_network").trigger("click");
});