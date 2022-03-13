/**
 * script to handle newsletter related functionality.
 *
 */

jQuery(document).ready(function($) {

    var content_builder_el = $("#content_builder");

    function toggle_template_selection() {

        var classic_content_template_el = $("#template");
        var content_builder_template_el = $("#content_builder_template");
        var content_builder_label_el = $("label[for='content_builder_template']");
        var classic_content_label_el = $("label[for='template']");

        var content_builder = content_builder_el.val();

        if (content_builder && 'yes' === content_builder) {

            classic_content_template_el.hide();
            content_builder_template_el.show();
            content_builder_label_el.show();
            classic_content_label_el.hide();

        } else {

            classic_content_template_el.show();
            content_builder_template_el.hide();
            content_builder_label_el.hide();
            classic_content_label_el.show();
        }
    }

    content_builder_el.on('change', function() {
        toggle_template_selection();
    })

    toggle_template_selection();

});


//EOF