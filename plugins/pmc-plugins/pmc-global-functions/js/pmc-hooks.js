/**
 * PMC Filters - js library to mimic simple wp add_filter & apply_filters for use on client side javascript
 *
 * To add a filter event:
 * pmc.hooks.add_filter( 'filter-name', function( data, p1, p2, ... ) {
 *   // some code....
 *   return data;
 * } );
 *
 * add_filter( 'filter-name', function( data, p1, p2, ... ) {
 *   // some code....
 *   return data;
 * } );
 *
 * To apply a data value throught a filter:
 * data = pmc.hooks.apply_filters( 'filter-name', data, p1, p2, ... );
 * data = apply_filters( 'filter-name', data, p1, p2, ... );
 *
 * @TODO: should integrate with core wp-hook.js when available
 * @ref: https://core.trac.wordpress.org/ticket/21170
 */

// extending the pmc object
var pmc = pmc || {};
pmc.hooks = pmc.hooks || {
	pmc_filters: [],
	pmc_actions: [],

	/**
	 * var data = pmc.hooks.apply_filters( 'filter', 'data', 'p1', 'p2', ... );
	 */
	apply_filters: function (/* filter, data, arg1, arg2, ... */) {
		var filter = [].shift.call( arguments );

		if ( ! filter == 'undefined' || typeof arguments[0] == 'undefined' ) {
			return;
		}

		if ( this.pmc_filters[ filter ] ) {
			filters = this.pmc_filters[ filter ];
			for (i = 0; i < filters.length; i++) {
				if ( typeof filters[i].callback == 'function' ) {
					try {
						arguments[0] = filters[i].callback.apply( null, arguments );
					} catch ( e ) {}
				}
			}
		}

		return arguments[0];
	},

	/*
	 *	pmc.hooks.add_filter( 'filter', function ( data, p1, p2, ... ) {
	 *		return data;
	 *	});
	 */
	add_filter: function( filter, callback ) {

		if ( typeof callback != 'function' || typeof filter == '' ) {
			return;
		}

		try {
			if ( typeof this.pmc_filters[ filter ] != 'object' ) {
				this.pmc_filters[ filter ] = [];
			}
			this.pmc_filters[ filter ].push( { filter: filter, callback: callback } );
		} catch ( e ) {}

	},

	/**
	 * pmc.hooks.do_action( 'action', 'p1', 'p2', ... );
	 */
	do_action: function (/* action, arg1, arg2, ... */) {
		var action = [].shift.call( arguments );

		if ( ! action == 'undefined' ) {
			return;
		}

		if ( this.pmc_actions[ action ] ) {
			actions = this.pmc_actions[ action ];
			for (i = 0; i < actions.length; i++) {
				if ( typeof actions[i].callback == 'function' ) {
					try {
						actions[i].callback.apply( null, arguments );
					} catch ( e ) {}
				}
			}
		}
	},

	/*
	 *	pmc.hooks.add_action( 'action', function ( p1, p2, ... ) {
	 *		return data;
	 *	});
	 */
	add_action: function( action, callback ) {

		if ( typeof callback != 'function' || typeof action == '' ) {
			return;
		}

		try {
			if ( typeof this.pmc_actions[ action ] != 'object' ) {
				this.pmc_actions[ action ] = [];
			}
			this.pmc_actions[ action ].push( { action: action, callback: callback } );
		} catch ( e ) {}

	}

};

if ( typeof add_filter === 'undefined' ) {
	function add_filter() {
		pmc.hooks.add_filter.apply( pmc.hooks, arguments );
	}
}
if ( typeof apply_filters === 'undefined' ) {
	function apply_filters() {
		return pmc.hooks.apply_filters.apply( pmc.hooks, arguments );
	}
}
