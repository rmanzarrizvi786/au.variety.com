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
});
