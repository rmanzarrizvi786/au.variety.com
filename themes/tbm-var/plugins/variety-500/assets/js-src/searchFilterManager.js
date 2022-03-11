// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage filtering on Search page.
 */
const searchFilterManager = {

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const $chosenSelect = $( '#search-filter' );

    if (0 === $chosenSelect.length || _.isUndefined(jQuery.fn.chosen)) {
      return;
    }

    $chosenSelect.chosen({
      disable_search_threshold: 10,
      no_results_text: 'Oops, nothing found!',
      width: '100%'
    }).change( this.change );

    /**
     * Detect filters from the URL and apply them to the multiselect.
     */
    window.onload = function() {
        let params = [];

        if ( '' !== window.location.hash ) {
            params = decodeURI( window.location.hash ).split( '&' );

            $( params ).each( function( event, param ) {
                if ( /^filters/.test( param ) && /\[values\]/.test( param ) ) {
                    param = param.split( '=' );
                    let optionValue = param[1];

                    param[0].replace( /^filters\[page]\[([A-Za-z0-9_]+)]\[values]\[0]$/g, ( match, value ) => {
                        if ( 'country_of_citizenship' === value ) {
                            // Adding the citizenship_ prefix is needed in order to maintain unique option values.
                            optionValue = 'citizenship_' + optionValue;
                        }
                        $chosenSelect.find( 'optgroup[data-field="' + value + '"] option[value="' + optionValue + '"]' ).attr( 'selected', 'selected' );
                        $chosenSelect.trigger( 'chosen:updated' );
                    } );
                }
            } );
        }
    };
  },

  /**
   * Trigger change when chosen select updates.
   *
   * @returns {void}
   */
  change: function( event, data ) {
    let filter_type = 'add_and_filter';
    let uniqueValue = '';
    let value = '';

    if ( 'undefined' !== typeof data.selected ) {
        uniqueValue = data.selected;
    } else if ( 'undefined' !== typeof data.deselected ) {
        uniqueValue = data.deselected;
        filter_type = 'remove_filter';
    } else {
        return;
    }

    if ( uniqueValue.startsWith( 'citizenship_' ) ) {
        // The prefix needed just for maintaining the option values unique needs to be stripped off.
        value = uniqueValue.replace( 'citizenship_', '' );
    } else {
        value = uniqueValue;
    }

    SwiftypeComponents.Dispatcher.run( 'search', [{
      type: filter_type,
      data: { field: $( '#search-filter option[value="' + uniqueValue + '"]' ).parent().data( 'field' ), value: value },
      update_results: true,
      reset_pagination: true
    }] );
  }

};

export default searchFilterManager;
