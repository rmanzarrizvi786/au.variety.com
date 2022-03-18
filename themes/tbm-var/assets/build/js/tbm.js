jQuery(document).ready(function ($) {
  /**
   * Newsletter Subscribe to Observer
   */
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
    $("#body-overlay").toggle();
    $(this).toggleClass("expanded");
  });

  $("#body-overlay").on("click", function () {
    $(".l_toggle_menu_network").trigger("click");
  });

  $(".js-MegaMenu-Trigger").on("click", function () {
    $("html").toggleClass("is-mega-open");
  });
});


document.addEventListener("keydown", (t) => {
  "Escape" === t.key &&
    document.documentElement.classList.remove("is-mega-open");
});