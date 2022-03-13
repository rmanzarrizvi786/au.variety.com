/**
 * This file allows custom fields to work within the quick editor of the taxonomies table
 */
function pst_quick_edit_term() {

    if ('undefined' === typeof inlineEditTax) {
        return;
    }

    var $ = jQuery;
    var _edit = inlineEditTax.edit;
    inlineEditTax.edit = function( id ) {
        
        var args = [].slice.call( arguments );
        _edit.apply( this, args );

        if ( typeof( id ) == 'object' ) {
            id = this.getId(id);
        }
        
        if ( this.type == 'tag' ) {
            var editRow = $( '#edit-' + id );
            
            var postRow = $( '#tag-' + id );
            var seo_title = $( '.column-pmc_seo_tweaks_title', postRow ).text();
            var seo_description = $( '.column-pmc_seo_tweaks_description', postRow ).text();
            
            // set the values in the quick-editor
            $( ':input[name="pmc_seo_tweaks_title"]', editRow ).val( seo_title );
            $( ':input[name="pmc_seo_tweaks_description"]', editRow ).val( seo_description );
        }
    };
}

if ('undefined' !== typeof inlineEditTax && inlineEditTax) {
    pst_quick_edit_term();
} else {
    jQuery(pst_quick_edit_term);
}