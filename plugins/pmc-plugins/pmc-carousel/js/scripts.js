( function( $ ) {

    $( document ).ready( function() {
        var pmc_tax = $( 'select[name=pmc_taxonomy]' );
        var pmc_term = $( 'select[name=pmc_term]' );
        
        if ( pmc_tax.val() == 0 ) {
            pmc_term.hide();
        } else {
            
        }
        
        pmc_tax.on( 'change', function() {
            
            var tax = $( this ).val();
            
            if ( tax == 0 ) {
                pmc_term.hide();
            } else {
            
                var data = {
                    action: 'carousel_cats',
                    taxonomy: tax,
                    nonce: pmcCarouselCats.nonce
                };
    
                jQuery.post(
                    ajaxurl,
                    data,
                    function( response ) {
                        pmc_term.html( response ).show();
                        $( '#posts-filter' ).submit();
                    }
                );
            
            }
            
        } );
        
        $( '#posts-filter select[name=pmc_term]' ).on( 'change', function() {
            $( this ).parents( 'form' ).submit();
        } );
    
    } );

} )( jQuery );