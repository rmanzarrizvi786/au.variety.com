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
var pmc=pmc||{};if(pmc.hooks=pmc.hooks||{pmc_filters:[],pmc_actions:[],apply_filters:function(){var t=[].shift.call(arguments);if("undefined"!=!t&&void 0!==arguments[0]){if(this.pmc_filters[t])for(filters=this.pmc_filters[t],i=0;i<filters.length;i++)if("function"==typeof filters[i].callback)try{arguments[0]=filters[i].callback.apply(null,arguments)}catch(i){}return arguments[0]}},add_filter:function(i,t){if("function"==typeof t&&""!=typeof i)try{"object"!=typeof this.pmc_filters[i]&&(this.pmc_filters[i]=[]),this.pmc_filters[i].push({filter:i,callback:t})}catch(i){}},do_action:function(){var t=[].shift.call(arguments);if("undefined"!=!t&&this.pmc_actions[t])for(actions=this.pmc_actions[t],i=0;i<actions.length;i++)if("function"==typeof actions[i].callback)try{actions[i].callback.apply(null,arguments)}catch(i){}},add_action:function(i,t){if("function"==typeof t&&""!=typeof i)try{"object"!=typeof this.pmc_actions[i]&&(this.pmc_actions[i]=[]),this.pmc_actions[i].push({action:i,callback:t})}catch(i){}}},void 0===add_filter)function add_filter(){pmc.hooks.add_filter.apply(pmc.hooks,arguments)}if(void 0===apply_filters)function apply_filters(){return pmc.hooks.apply_filters.apply(pmc.hooks,arguments)}
