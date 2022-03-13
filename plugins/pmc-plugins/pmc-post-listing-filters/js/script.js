/*jslint browser: true, devel: true, ass: true, eqeq: true, forin: true, plusplus: true, unparam: true, sloppy: true, stupid: true, vars: true, white: true */
/*global jQuery: true, pmc_pfs_data: true, $coauthors_loading: true */
jQuery.migrateMute = true;
/*
 * Version 1.05
 */


var PmcPostListingHelper = {
	RefreshButton: function () {
		var canSave = jQuery('#post-filter-search').val() !== ''
			&& (jQuery('#pmc-post-listing-filters-saved-query').length == 0
					|| jQuery('#pmc-post-listing-filters-saved-query').val() === ''
				);

		if ( !canSave ) {
			jQuery(".chzn-select option[selected]").each(function (idx,el){
				if ( jQuery(el).parent().attr('id') !== 'pmc-post-listing-filters-saved-query' ) {
					canSave = true;
				}
			});

			jQuery('.pfs-author-hidden-input').each( function() {
				if (jQuery(this).val() && jQuery(this).val() != '') {
					canSave = true;
				}
			});
		}

		if ( !canSave ) {
			jQuery('#pmc-post-save-filter').attr('disabled','disabled');
		} else {
			jQuery('#pmc-post-save-filter').removeAttr('disabled');
		}
	},
	UpdateButton: function() {
		jQuery('#pmc-post-listing-filters-saved-query').val('').trigger('liszt:updated');
		jQuery('#pmc-post-delete-filter').attr('disabled','disabled');
		PmcPostListingHelper.RefreshButton();
	},
	OnChange: function ( event, data ) {
		if (jQuery(event.target).attr("id") == 'pmc-post-listing-filters-saved-query') {
			var val = jQuery(event.target).val();
			if ( val !== '' ) {
				jQuery("#post-filter-search").val('');
				jQuery(".chzn-select").val('').trigger("liszt:updated");
				jQuery("#pmc-post-listing-filters-saved-query").val(val).trigger("liszt:updated");
			}
		} else {
			jQuery("#pmc-post-listing-filters-saved-query").val('').trigger("liszt:updated");
		}
		jQuery('.chzn-drop').hide();
		jQuery("#post-filter-submit").trigger('click');
	},
	CreateAutoSuggest: function(authorName, inputName) {

		if( !inputName ) {
			inputName = 'pfs-author-field';
		}

		var $authors_div = jQuery('.post-listing-filters-author');

		var $author_field = jQuery('<input/>');

		$author_field.attr({
			'class': 'chzn-container chzn-container-multi chzn-with-drop chzn-container-active',
			'name': inputName,
			'type': 'text',
			'style': 'width: 200px; font-size: 12px;'
		}).appendTo($authors_div).suggest(pmc_pfs_data.suggest_link, {
			onSelect: PmcPostListingHelper.AutoSuggestSelect
		}).keydown(PmcPostListingHelper.AutoSuggestKeyDown);

		if( authorName ) {
			$author_field.attr( 'value', unescape( authorName ) );
		} else {
			$author_field.attr( 'value', '' ).focus( function() { $author_field.val( '' ); } ).blur( function() { $author_field.val( '' ); } );
		}

		return $author_field;

	},
	AutoSuggestSelect: function() {
		var vals = this.value.split("|");

		var author = {};
		author.id = jQuery.trim(vals[0]);
		author.login = jQuery.trim(vals[1]);
		author.name = jQuery.trim(vals[2]);
		author.email = jQuery.trim(vals[3]);
		author.nicename = jQuery.trim(vals[4]);

		jQuery(this).val('');

		PmcPostListingHelper.AddAuthorRow(author.login);
	},
	AutoSuggestKeyDown: function(e) {
		if(e.keyCode == 13) {
			return false;
		}
	},
	AddAuthorRow: function(author_login) {
		var author_row = jQuery('<div>')
			.addClass('pfs-author-row')
			.html(author_login);
		var delete_button = jQuery('<span>')
			.css({'padding-left': '4px'})
			.addClass('delete-button')
			.html('&times;');
		var hidden_field = jQuery('<input>').attr({
				'name': 'pmc-post-listing-filters[author][]',
				'type': 'hidden',
				'class': 'pfs-author-hidden-input',
				'value': 'cap-' + author_login
			});

		author_row.append(delete_button);
		author_row.append(hidden_field);

		jQuery('#pfs-authors-list').prepend(author_row);

		jQuery('#pmc-post-save-filter').removeAttr('disabled');
	}
};


jQuery(document).ready(function () {
	var $author_suggest = PmcPostListingHelper.CreateAutoSuggest('', false);
	jQuery('.post-listing-filters-author').prepend($author_suggest);

	// Show laoding cursor for autocomplete ajax requests
	jQuery(document).ajaxSend(function(e, xhr, settings) {
		if( settings.url.indexOf(pmc_pfs_data.suggest_link) != -1 ) {
			settings.url += '&existing_authors=';
		}
	});

	// Enable author row delete buttons
	jQuery('#pfs-authors-list').on('click', '.pfs-author-row .delete-button', function() {
		jQuery(this).parents('.pfs-author-row').remove();
	});

	// remove search box
	jQuery("form>p.search-box").remove();
	// re-arrange search filter panel
	jQuery("#post-list-filter-panel").insertBefore("div.tablenav.top");
	// remove date filters
	jQuery("select[name='m']").parent().remove();

	jQuery(".chzn-select").chosen({"allow_single_deselect": true}).change(PmcPostListingHelper.OnChange);

	jQuery("#pmc-post-listing-dialog-modal").dialog({
		autoOpen: false,
		buttons: {
			"Save": function () {
				jQuery("#pmc-post-listing-save-filter").val(jQuery(this).find('input').val());
				jQuery(this).dialog("close");
				jQuery("#post-filter-submit").trigger('click');
			},
			"Cancel": function () {
				jQuery(this).dialog("close");
			}
		}
	});

	jQuery('.pmc-post-listing-button').click(function () {
		jQuery("#pmc-post-listing-dialog-modal").dialog("open");
	});

	jQuery(' .pmc-post-listing-delete-button').click(function () {
		var del_query = jQuery("select#pmc-post-listing-filters-saved-query").val();
		jQuery("#pmc-post-listing-delete-query").val(del_query);
		jQuery("#post-filter-submit").trigger('click');
	});

	jQuery('#post-filter-clear').click(function () {
		jQuery("#post-filter-search").val('');
		jQuery('#pfs-authors-list').html('');
		jQuery('#post-list-filter-panel').find('input[type=text]').val('');
		jQuery(".chzn-select").val('').trigger("liszt:updated");
		jQuery("#post-filter-submit").trigger('click');
	});

	jQuery('#post-filter-search').click(PmcPostListingHelper.UpdateButton).change(PmcPostListingHelper.UpdateButton);
	PmcPostListingHelper.RefreshButton();

	/*
	 @since 2014-10-07 Amit Sannad
	 PPT-2745 Fake $coauthors_loading variable. Since this is not present in edit page. We are faking it. It will get overridden when quick edit link is clicked.
	 */
	if ("undefined" == typeof $coauthors_loading) {
		$coauthors_loading = jQuery("<span id='ajax-loading'></span>");
	}
});
