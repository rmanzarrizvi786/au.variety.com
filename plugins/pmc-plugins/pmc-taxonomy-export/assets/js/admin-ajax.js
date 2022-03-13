/**
 * Javascript for the taxonomy export screen
 *
 * @package PMC Taxonomy Export 1.0
 */

jQuery(document).ready(function () {

    /*
     * Setup the progress bar
     */
    var progressbar = jQuery("#progressbar"),
        progressLabel = jQuery(".progress-label");

    progressbar.hide();

    progressbar.progressbar({
        value: false,
        max: 100,
        change: function () {
            progressLabel.text(progressbar.progressbar("value") + "%");
        },
        complete: function () {
            progressLabel.text("Complete!");
        }
    });

    jQuery('.spin-loader').hide();

    /* set the first taxonomy as default selected */
    jQuery("#taxonomy").val(jQuery("#taxonomy").find("option:first").val());

    /* Fire the ajax event on click of the submit button for taxonomy export */
    if ('undefined' !== typeof pmc_taxonomy_export_admin_options) {

        jQuery('#submit').click(function (e) {

            e.preventDefault();
            jQuery('#error-log-data').empty();
            jQuery('#term-csv-files').empty();
            jQuery('.inside').css('height', "180px");
            progressbar.progressbar('option', 'value', 0);
            progressbar.show();
            PMC_Taxonomy_Export.get_total_terms();

        });
    }

    PMC_Taxonomy_Export = {
        /*
         * Get the terms based on a taxonomy using ajax call and save it as octet-stream in csv file
         * to be downloaded by the browser
         * @since 2015-10-29 Archana Mandhare PMCVIP-226
         */
        get_terms_csv: function (term_offset, total_pages, percentage_complete) {

            jQuery.ajax({

                type: 'post',

                url: pmc_taxonomy_export_admin_options.ajaxurl,

                data: {
                    action: 'export_report',
                    offset: term_offset,
                    taxonomy: jQuery('#taxonomy').val(),
                    report_type: jQuery("input[type='radio'][name='report-type']:checked").val(),
                    export_nOnce: pmc_taxonomy_export_admin_options.export_nOnce
                },

                success: function (response, textStatus, jqXHR) {

                    if (true === response.success) {

                        var csv_files = response.files;
                        var file_names = Object.keys(csv_files);
                        for (var i = 0; i < file_names.length; i++) {

                            var a = document.createElement('a');
                            a.download = file_names[i];
                            a.href = encodeURI("data:text/csv;charset=utf-8," + csv_files[file_names[i]]);
                            a.text = file_names[i];

                            jQuery(a).addClass('download-link');
                            jQuery('#term-csv-files').append(a);

                            a.click();

                            progressbar.progressbar({value: percentage_complete});

                            var outerdiv = jQuery('.inside').height();
                            jQuery('.inside').css('height', outerdiv + 50 + "px");

                        }
                    }
                    else {
                        jQuery('#error-log-data').text(response.message);
                        jQuery('#submit').prop('disabled', false);
                        jQuery('.spin-loader').hide();
                        progressbar.hide();
                    }
                },

                error: function (data, textStatus, jqXHR) {
                    jQuery('#error-log-data').text(data + textStatus + jqXHR);
                    jQuery('#submit').prop('disabled', false);
                    jQuery('.spin-loader').hide();
                    progressbar.hide();
                },

                complete: function () {

                    progressbar.progressbar({value: percentage_complete});
                    term_offset = term_offset + 1;
                    if (term_offset <= total_pages) {

                        if (term_offset === total_pages || term_offset > total_pages) {
                            percentage_complete = 100;
                        } else {
                            percentage_complete = Math.ceil(term_offset * ( 100 / total_pages));
                        }

                        PMC_Taxonomy_Export.get_terms_csv(term_offset, total_pages, percentage_complete);
                    }

                    if (100 === percentage_complete) {
                        jQuery('#submit').prop('disabled', false);
                        jQuery('.spin-loader').hide();
                    }

                }

            });

        },

        /*
         * Get the total terms each taxonomy has to decide for the
         * pagination and the number of CSV files to send for download.
         * @since 2015-10-29 Archana Mandhare PMCVIP-226
         */
        get_total_terms: function () {

            var total_terms = 0;

            jQuery('#submit').prop('disabled', true);
            jQuery('.spin-loader').show();
            jQuery.ajax({

                type: 'post',

                url: pmc_taxonomy_export_admin_options.ajaxurl,

                data: {
                    action: 'get_total_term_count',
                    taxonomy: jQuery('#taxonomy').val(),
                    export_nOnce: pmc_taxonomy_export_admin_options.export_nOnce
                },

                success: function (response, textStatus, jqXHR) {

                    if (true === response.has_terms) {

                        total_terms = response.total_terms;
                        if (total_terms > 0) {

                            var pages = response.pages;
                            var offset = 1;

                            if (pages > 0) {
                                var percentage_complete = Math.ceil(offset * ( 100 / pages));

                                if (offset === pages) {
                                    percentage_complete = 100;
                                }

                                PMC_Taxonomy_Export.get_terms_csv(offset, pages, percentage_complete);

                            }

                        }
                    }
                    else {
                        jQuery('#error-log-data').text('No terms found for this taxonomy');
                        jQuery('#submit').prop('disabled', false);
                        jQuery('.spin-loader').hide();
                        progressbar.hide();
                    }
                },

                error: function (data, textStatus, jqXHR) {
                    jQuery('#error-log-data').text(data + textStatus + jqXHR);
                    jQuery('#submit').prop('disabled', false);
                    jQuery('.spin-loader').hide();
                    progressbar.hide();
                }

            });

        }

    }

});
