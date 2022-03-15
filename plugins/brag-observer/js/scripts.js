jQuery(document).ready(function ($) {
  $(document).on("submit", ".tastemaker-form", function (e) {
    e.preventDefault();
    var this_form = $(this);
    var btn = $(this).find(".tastemaker-submit");

    var elem_loading = this_form.find(".loading");
    var elem_js_errors = this_form.find(".js-errors");
    var elem_js_success = this_form.find(".js-success");
    var elem_tastemaker_wrap = this_form.find(".tastemaker-wrap");

    elem_loading.show();
    elem_js_errors.addClass("d-none").html("");
    btn.attr("disabled", true).hide();

    var formData = this_form.serialize();
    var the_url = this_form
      .closest(".single-article")
      .find("h1:first")
      .data("href");
    formData += "&source=" + the_url;

    js_errors = "";
    console.log(this_form.find('input[name="rating"]:checked').length);
    if (this_form.find('input[name="rating"]:checked').length == 0) {
      js_errors += "Please select a valid rating from 1 to 5 stars.<br>";
    }
    if (
      this_form.find('input[name="email"]').length &&
      this_form.find('input[name="email"]').val() == ""
    ) {
      js_errors += "Please enter a valid email address.";
    }
    if ("" != js_errors) {
      btn.attr("disabled", false).show();
      elem_loading.hide();
      elem_js_errors
        .removeClass("d-none")
        .addClass("alert alert-danger")
        .html(js_errors);
      return false;
    }

    $.post(
      brag_observer.url,
      {
        action: "save_tastemaker_review",
        formData: formData,
      },
      function (res) {
        elem_loading.hide();
        if (res.success) {
          elem_tastemaker_wrap.hide();
          if (res.data.verified) {
            elem_js_success
              .addClass("alert alert-success")
              .html(res.data.message);
          } else {
            elem_js_success.addClass("alert alert-info").html(res.data.message);
          }
        } else {
          var errors = "";
          $.each(res.data, function (index, error) {
            errors += "<div>" + error + "</div>";
          });
          btn.attr("disabled", false).show();
          elem_js_errors
            .removeClass("d-none")
            .addClass("alert alert-danger")
            .html(errors);
        }
      }
    )
      .fail(function (e) {
        btn.attr("disabled", false).show();
        elem_loading.hide();
        elem_js_errors
          .removeClass("d-none")
          .addClass("alert alert-danger")
          .html("Failed to save.");
      })
      .error(function (e) {
        btn.attr("disabled", false).show();
        elem_loading.hide();
        elem_js_errors
          .removeClass("d-none")
          .addClass("alert alert-danger")
          .html("Error while saving.");
      });
  });

  $(document).on("submit", ".lead_generator-form", function (e) {
    e.preventDefault();
    var this_form = $(this);
    var btn = $(this).find(".lead_generator-submit");

    var elem_loading = this_form.find(".loading");
    var elem_js_errors = this_form.find(".js-errors");
    var elem_js_success = this_form.find(".js-success");
    var elem_lead_generator_wrap = this_form.find(".lead_generator-wrap");

    elem_loading.show();
    elem_js_errors.addClass("d-none").html("");
    btn.attr("disabled", true).hide();

    var formData = this_form.serialize();
    var the_url = this_form
      .closest(".single-article")
      .find("h1:first")
      .data("href");
    formData += "&source=" + the_url;

    js_errors = "";
    if (
      this_form.find('input[name="email"]').length &&
      this_form.find('input[name="email"]').val() == ""
    ) {
      js_errors += "Please enter a valid email address.";
    }
    if ("" != js_errors) {
      btn.attr("disabled", false).show();
      elem_loading.hide();
      elem_js_errors
        .removeClass("d-none")
        .addClass("alert alert-danger")
        .html(js_errors);
      return false;
    }

    $.post(
      brag_observer.url,
      {
        action: "save_lead_generator_response",
        formData: formData,
      },
      function (res) {
        elem_loading.hide();
        if (res.success) {
          elem_lead_generator_wrap.hide();
          if (res.data.verified) {
            elem_js_success
              .addClass("alert alert-success")
              .html(res.data.message);
          } else {
            elem_js_success.addClass("alert alert-info").html(res.data.message);
          }
        } else {
          var errors = "";
          $.each(res.data, function (index, error) {
            errors += "<div>" + error + "</div>";
          });
          btn.attr("disabled", false).show();
          elem_js_errors
            .removeClass("d-none")
            .addClass("alert alert-danger")
            .html(errors);
        }
      }
    )
      .fail(function (e) {
        btn.attr("disabled", false).show();
        elem_loading.hide();
        elem_js_errors
          .removeClass("d-none")
          .addClass("alert alert-danger")
          .html("Failed to save.");
      })
      .error(function (e) {
        btn.attr("disabled", false).show();
        elem_loading.hide();
        elem_js_errors
          .removeClass("d-none")
          .addClass("alert alert-danger")
          .html("Error while saving.");
      });
  });
});
