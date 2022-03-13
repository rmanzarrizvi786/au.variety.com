/*
 * This is the Javascript API class bundled with PMC Term Meta plugin
 * which loads up on edit terms screen in wp-admin and provides a simple
 * interface to get/set meta info to a term.
 *
 * @author Amit Gupta
 * @since 2013-09-25
 */


/**
 * Constructor for the PMC_Term_Meta class
 */
function PMC_Term_Meta() {
	this.desc_id = 'description';	//assume description form field name

	//if this form field exists then use this instead
	if ( jQuery( "#tag-description" ).length ) {
		this.desc_id = 'tag-description';
	}
}

/**
 * This function returns all meta data of the term as a JSON object
 */
PMC_Term_Meta.prototype.get_all = function() {
	var default_meta = {};

	var meta_json = jQuery( '#' + this.desc_id ).val();

	if ( ! meta_json ) {
		return default_meta;
	}

	try {
		var meta_data = jQuery.parseJSON( meta_json );
	} catch ( e ) {
		console.log( "Exception thrown in PMC_Term_Meta.get_all()" + "\n" + e.message );
	}

	if ( meta_data ) {
		return meta_data;
	}

	return default_meta;
};

/**
 * This function accepts one or multiple key:value pairs in an object and saves
 * them as serialized JSON object in the description form field
 */
PMC_Term_Meta.prototype.set_all = function( data ) {
	if ( typeof data !== 'object' || ! data ) {
		return false;
	}

	var meta_data = this.get_all();

	for ( var i in data ) {
		meta_data[ i ] = data[ i ];
	}

	jQuery( '#' + this.desc_id ).val( JSON.stringify( meta_data ) );

	return true;
};

/**
 * This function accepts a key and returns the value of that meta key if it
 * exists else it returns FALSE
 */
PMC_Term_Meta.prototype.get = function( key ) {
	if ( typeof key !== 'string' || ! key ) {
		return false;
	}

	var meta_data = this.get_all();

	if ( meta_data[ key ] ) {
		return meta_data[ key ];
	}

	return false;
};

/**
 * This function accepts a key and value and saves them in description form
 * field. It returns TRUE on success else FALSE.
 */
PMC_Term_Meta.prototype.set = function( key, data ) {
	if ( typeof key !== 'string' || ! key || typeof data == 'undefined' ) {
		return false;
	}

	var meta_data = {};
	meta_data[ key ] = data;

	return this.set_all( meta_data );
};

var pmc_term_meta;

jQuery( document ).ready( function( $ ) {
	pmc_term_meta = new PMC_Term_Meta();	//init the JS class object for use
} );


//EOF