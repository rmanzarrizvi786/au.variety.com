var pmc = pmc || {};

pmc.stocks = ( function( $ ) {

	var margin = {
		top: 0,
		right: 30,
		bottom: 25,
		left: 0
	};

	var width = 640 - margin.left - margin.right;

	var height = 320 - margin.top - margin.bottom;

	var months = {
		'01': 'January',
		'02': 'February',
		'03': 'March',
		'04': 'April',
		'05': 'May',
		'06': 'June',
		'07': 'July',
		'08': 'August',
		'09': 'September',
		'10': 'October',
		'11': 'November',
		'12': 'December'
	};

	var menu = [
		{
			name: '1-Month',
			value: '1 month ago',
			shrt: '1m',
			selected: false
		},
		{
			name: '3-Month',
			value: '3 months ago',
			shrt: '3m',
			selected: true
		},
		{
			name: '6-Month',
			value: '6 months ago',
			shrt: '6m',
			selected: false
		},
		{
			name: '1-Year',
			value: '1 year ago',
			shrt: '1y',
			selected: false
		},
		{
			name: 'All',
			value: 'all',
			shrt: 'alltime',
			selected: false
		}
	];

	var parse_date = d3.time.format( '%Y-%m-%d' ).parse;

	var x = d3.time.scale()
		.range( [ 40, width - 20] );

	var y = d3.scale.linear()
		.range( [ height - 20, 40 ] );

	var x_axis = d3.svg.axis()
		.scale( x )
		.ticks( 4 );

	var y_axis = d3.svg.axis()
		.scale( y )
		.ticks( 4 )
		.tickSize( -width )
		.orient( 'right' );

	var line = d3.svg.line()
		.interpolate( 'monotone' )
		.x( function( d ) { return x( d.date ); } )
		.y( function( d ) { return y( d.index_value ); } );

	var end_date = 'NA';

	var prev = 'NA';

	var map = {
		symbol: 'Symbol',
		name: 'Name',
		stock_exchange_country: 'Country',
		stock_exchange_currency_code: 'Local CCY',
		close: 'Close',
		//usd_adj_1d_pct_change: '1-Day Change', Commenting out to resolve PMCBA-74 until data is resolved.
		usd_adj_1m_pct_change: '1-Month Change',
		market_cap: 'Market Cap'
	};

	var region = '';

	var category = '';

	var flags_path = '';

	return {

		data: [],

		sort: 'desc',

		sort_name: 'name',

		/**
		 * Builds graph for shortcode.
		 *
		 * @param data
		 * @param summary
		 */
		build_graph: function( data, summary, loader ) {
			var self = this;

			if ( 'undefined' !== typeof data [ data.length - 1 ] ) {
				end_date = data[ data.length - 1 ].date;
			}

			if ( 'undefined' !== typeof data [ data.length - 2 ] ) {
				prev = parseFloat( data[ data.length - 2 ].index_value ).toFixed( 2 );
			}

			// Clean up data.
			data.forEach( function( d ) {
				d.date = parse_date( d.date );
				d.index_value = +d.index_value;
			});

			d3.select( '.pmc-stocks-graph' )
				.append( 'div' )
				.attr( 'class', 'svg-container' );

			var svg = d3.select('.svg-container').append('svg')
				.classed( 'svg-content-responsive', true)
				.attr( 'preserveAspectRatio', 'xMinYMin meet' )
				.attr( 'width', '100%' )
				.attr( 'height', '100%' )
				.attr( 'viewBox', '0 0 640 320' )
					.append( 'g' );


			// Compute the minimum and maximum date, and the minimum and maximum index_value.
			x.domain( [ data[0].date, data[ data.length - 1 ].date ] );
			y.domain( [ d3.min( data, function( d ) {
				return d.index_value;
			} ), d3.max( data, function( d ) {
				return d.index_value;
			} ) ] ).nice();

			svg.append( 'g')
				.attr( 'class', 'x axis' )
				.attr( 'transform', 'translate(0,' + height + ')' )
				.call( x_axis );

			svg.append( 'g' )
				.attr( 'class', 'y axis' )
				.attr( 'transform', 'translate(' + width + ',0)' )
				.call( y_axis );

			svg.append( 'path' )
				.attr( 'class', 'line' )
				.datum( data )
				.attr( 'd', line( data ) );

			var order = data.sort( function( a, b ) {
				return d3.ascending( a.index_value, b.index_value );
			} ),
			min = order[0],
			max = order[ data.length - 1 ];

			self.add_marker( svg, min, 'min' );
			self.add_marker( svg, max, 'max' );

			self.build_menu( svg );
			self.build_overview( summary, end_date );
			self.build_summary( summary );

			var $loader = d3.select( '.svg-container' ).append( 'div' )
				.attr( 'class', 'pmc-stocks-loader' );

			$loader.append( 'img' )
				.attr( 'src', loader );
		},

		/**
		 * Build Global Index widget.
		 *
		 * @param data
		 * @param summary
		 * @param gainers
		 * @param decliners
		 */
		build_global_index: function( data, summary, gainers, decliners ) {
			var self = this;

			if ( null === data || $.isEmptyObject( data ) ) {
				// Data is failing, hide widget.
				$('.pmc-stocks-global-index-widget').parent('.hp-panel').hide();
				return false;
			}

			width = 375 - margin.left - margin.right,
			height = 248 - margin.top - margin.bottom;

			x = d3.time.scale()
				.range( [ margin.right, width - 20] );

			y = d3.scale.linear()
				.range( [ height - 20, margin.bottom ] );

			x_axis = d3.svg.axis()
				.scale( x )
				.ticks( 3 )
				.tickFormat( d3.time.format( '%_m/%_d' ) );

			var y_axis = d3.svg.axis()
				.scale( y )
				.ticks( 3 )
				.tickSize( -width )
				.orient( 'left' );

			if ( 'undefined' !== typeof data[ data.length - 1 ] ) {
				end_date = data[ data.length - 1 ].date;
			}

			// Clean up data.
			data.forEach( function( d ) {
				d.date = parse_date( d.date );
				d.index_value = +d.index_value;
			} );

			x_axis = d3.svg.axis()
				.scale( x )
				.ticks( 3 )
				.tickFormat( d3.time.format( '%_m/%_d' ) );

			d3.select( '.pmc-stocks-global-index-graph' )
				.append( 'div' )
					.attr( 'class', 'svg-container' );

			var svg = d3.select('.svg-container').append('svg')
				.classed( 'svg-content-responsive', true)
				.attr( 'preserveAspectRatio', 'xMinYMin meet' )
				.attr( 'width', '100%' )
				.attr( 'height', '100%' )
				.attr( 'viewBox', '0 0 375 248' )
					.append( 'g' );

			// Compute the minimum and maximum date, and the minimum and maximum index_value.
			x.domain( [ data[0].date, data[ data.length - 1 ].date ] );
			y.domain( [ d3.min( data, function( d ) {
				return d.index_value;
			} ), d3.max( data, function( d ) {
				return d.index_value;
			} ) ] ).nice();

			svg.append( 'g')
				.attr( 'class', 'x axis' )
				.attr( 'transform', 'translate(0,' + height + ')' )
				.call( x_axis );

			svg.append( 'g' )
				.attr( 'class', 'y axis' )
				.attr( 'transform', 'translate(' + margin.right + ',0)' )
				.call( y_axis );

			svg.append( 'path' )
				.attr( 'class', 'line' )
				.datum( data )
				.attr( 'd', line( data ) );

			self.build_global_index_overview( summary, end_date );

			var $gainers = d3.select('.pmc-stocks-global-index-graph').append( 'div' )
				.attr( 'class', 'pmc-stocks-global-index-gainers' );

			var $decliners = d3.select('.pmc-stocks-global-index-graph').append( 'div' )
				.attr( 'class', 'pmc-stocks-global-index-decliners' );

			$gainers.append( 'h4' )
				.attr( 'class', 'pmc-stocks-up' )
				.text( 'Biggest Gainers' );

			$gainers.append( 'table' )
				.attr( 'class', 'pmc-stocks-global-index-table' )
					.append( 'tbody' );

			gainers = self.clean_data( gainers );

			gainers.forEach( function( d, i ) {
				var $tr = $gainers.select( 'tbody' ).append( 'tr' );
				var $first = $tr.append( 'td' );

				$first.attr( 'class', 'pmc-stocks-left' );

				$first.append('div')
					.attr( 'class', 'pmc-stocks-ellipsis' )
					.text(d.name);

				$first.append('div').text('[' + d.symbol + ']' );

				$tr.append( 'td' )
					.attr( 'class', 'pmc-stocks-right' )
					.text( d.usd_adj_1m_pct_change + '%' );
			});

			$decliners.append ('h4' )
				.attr( 'class', 'pmc-stocks-down' )
				.text( 'Biggest Decliners' );

			$decliners.append( 'table' )
				.attr( 'class', 'pmc-stocks-global-index-table' )
					.append( 'tbody' );

			decliners = self.clean_data( decliners );

			decliners.forEach( function( d, i ) {
				var $tr = $decliners.select( 'tbody' ).append( 'tr' );
				var $first = $tr.append( 'td' );

				$first.attr( 'class', 'pmc-stocks-left' );

				$first.append('div')
					.attr( 'class', 'pmc-stocks-ellipsis' )
					.text(d.name);

				$first.append('div').text('[' + d.symbol + ']' );

				$tr.append( 'td' )
					.attr( 'class', 'pmc-stocks-right' )
					.text( d.usd_adj_1m_pct_change + '%' );
			});
		},

		/**
		 * Build Global Index overview.
		 *
		 * @param summary
		 * @param date
		 */
		build_global_index_overview: function( summary, date ) {
			var regex = /^(\d{4})-(\d{2})-(\d{2})$/;
			var matches = regex.exec( date );

			var $group = d3.select( '.pmc-stocks-global-index-graph' ).insert( 'div', '.svg-container' )
				.attr( 'class', 'pmc-stocks-overview' );

			var $current = $group.append('div')
				.attr( 'class', 'pmc-stocks-overview-current');

			var cl = '';
			var arrow = '';
			if ( parseFloat( summary.index_1m_pct_change ) > 0 ) {
				cl = 'up';
			} else if ( parseFloat( summary.index_1m_pct_change ) < 0 ) {
				cl = 'down';
			}

			var diff = parseFloat( summary.index_latest ) * parseFloat( summary.index_1m_pct_change );
			diff = parseFloat( diff ).toFixed( 2 );
			var text = diff + ' (' + summary.index_1m_pct_change + '%) ' + arrow;

			$current.append( 'span' )
				.attr( 'class', cl )
				.text( text );

		},

		/**
		 * Builds overview.
		 *
		 * @param summary
		 * @param date
		 */
		build_overview: function( summary, date ) {
			var regex = /^(\d{4})-(\d{2})-(\d{2})$/;
			var matches = regex.exec( date );
			date = months[ matches[ 2 ] ] + ' ' + parseInt( matches[3], 10 ) + ', ' + matches[1];
			date = 'As of ' + date + '.';

			var selected = '1d';

			var $group = d3.select( '.pmc-stocks-graph' ).insert( 'div', '.pmc-stocks-menu' )
				.attr( 'class', 'pmc-stocks-overview' );

			var $current = $group.append('div')
				.attr( 'class', 'pmc-stocks-overview-current');

			$current.append('span')
				.text( parseFloat( summary.index_latest ).toFixed(2) );

			var cl = '';
			var arrow = '';

			if ( parseFloat( summary['index_' + selected + '_pct_change'] ) > 0 ) {
				cl = 'up';
			} else if ( parseFloat( summary['index_' + selected + '_pct_change'] ) < 0 ) {
				cl = 'down';
			}

			var diff = parseFloat( summary.index_latest ) - parseFloat( prev );
			var per = ( parseFloat( summary['index_' + selected + '_pct_change'] ) / 100 );
			per = per.toFixed( 2 );

			var text = diff.toFixed(2) + ' (' + per + '%) ' + arrow;

			// If no arrow because value is 0.00, remove any - or + that may be present.
			if ( ! arrow && ( '-' === text.charAt(0) || '+' === text.charAt(0) ) ) {
				text = text.slice(1);
			}

			$current.append( 'span' )
				.attr( 'class', cl )
				.text( text );

			$group.append( 'div' )
				.attr( 'class', 'pmc-stocks-overview-date' )
				.attr( 'y', 0 )
				.text( date );
		},

		/**
		 * Builds summary.
		 *
		 * @param summary
		 * @param prev
		 */
		build_summary: function( summary ) {
			var $selected = d3.select( '.pmc-stocks-graph .pmc-stocks-menu .selected' );
			var name = $selected.attr('data-short');
			var title = $selected.text();

			var arrow = '';
			var cl = '';

			if ( parseFloat( summary['index_' + name + '_pct_change'] ) >= 0 ) {
				arrow = '↑';
				cl = ' up';
			} else {
				arrow = '↓';
				cl = ' down';
			}

			if ( 'All' === title ) {
				title = title + ' Time';
			}

			var summary_data = [
				{
					title: 'Previous Close',
					cl: 'pmc-summary-previous-close',
					value: prev
				},
				{
					title: title + ' High',
					cl: 'pmc-summary-high',
					value: summary['index_' + name + '_high']
				},
				{
					title: title + ' Advancers',
					cl: 'pmc-summary-advancers',
					value: summary['is_' + name + '_advancer_sum'] + ' ↑'
				},
				{
					title: title + ' Change',
					cl: 'pmc-summary-change',
					value: ( parseFloat( summary['index_' + name + '_pct_change'] ) / 100 ).toFixed( 2 ) + '%' + arrow
				},
				{
					title: title + ' Low',
					cl: 'pmc-summary-low',
					value: summary['index_' + name + '_low']
				},
				{
					title: title + ' Decliners',
					cl: 'pmc-summary-decliners',
					value: summary['is_' + name + '_decliner_sum'] + ' ↓'
				}
			];

			var x = -margin.left;
			var y = height + 45;

			var $ul = d3.select( '.pmc-stocks-graph' )
				.append( 'ul' )
				.attr( 'class', 'pmc-summary-items' + cl );

			summary_data.forEach( function( d, i ) {
				var $li = $ul.append( 'li' )
					.attr( 'class', d.cl );

				$li.append( 'span' ).attr( 'class', 'pmc-summary-left' ).text(d.title);
				$li.append( 'span' ).attr( 'class', 'pmc-summary-right' ).text(d.value);

			});
		},

		/**
		 * Builds menu.
		 *
		 * @param svg
		 */
		build_menu: function( svg ) {
			var self = this;

			var $ul = d3.select( '.pmc-stocks-graph' ).insert( 'ul', '.svg-container' )
				.attr( 'class', 'pmc-stocks-menu' );


			menu.forEach( function( d, i ) {

				var $selected = $ul.append( 'li' )
					.append( 'span' )
						.attr( 'data-value', d.value )
						.attr( 'data-short', d.shrt )
						.text( d.name )
						.on( 'click', function() {
							self.update_graph_data( this, svg );
						});

				if ( d.selected ) {
					$selected.attr( 'class', 'selected' );
				}

			});
		},

		/**
		 * Updates data via JSON endpoint.
		 *
		 * @param text
		 * @param svg
		 * @returns {boolean}
		 */
		update_graph_data: function( text, svg ) {
			var self = this;

			var $text = d3.select( text );

			if ( $text.classed( 'selected' ) ) {
				return false;
			}

			d3.select('.pmc-stocks-loader')
				.style( 'display', 'block' );

			var value = $text.attr('data-value').replace( / /g, '-' );
			d3.json( '/pmc-stocks-graph/' + value, function( error, data ) {
				if ( ! data.success ) {
					d3.select('.pmc-stocks-loader')
						.style( 'display', 'none' );

					return false;
				}
				var summary = {};
				try {
					summary = data.data.summary;
					data = data.data.data;
				} catch ( e ) {
					d3.select('.pmc-stocks-loader')
						.style( 'display', 'none' );

					return false;
				}

				// Clean up data.
				data.forEach( function( d ) {
					d.date = parse_date( d.date );
					d.index_value = +d.index_value;
				});

				// Compute the minimum and maximum date, and the minimum and maximum index_value.
				x.domain( [ data[0].date, data[ data.length - 1 ].date ] );
				y.domain( [ d3.min( data, function( d ) {
					return d.index_value;
				} ), d3.max( data, function( d ) {
					return d.index_value;
				} ) ] ).nice();

				svg.transition().duration(750).select( '.x.axis' ) // change the x axis
					.call( x_axis );

				svg.transition().duration(750).select( '.y.axis' ) // change the y axis
					.call( y_axis );

				svg.select( '.line' )  // change the line
					.attr( 'd', line( data ) );

				var order = data.sort(function(a, b) { return d3.ascending(a.index_value, b.index_value); }),
				min = order[0],
				max = order[ data.length - 1 ];

				svg.selectAll('.marker').remove();

				setTimeout( function() {
					self.add_marker( svg, min, 'min' );
					self.add_marker( svg, max, 'max' );
				}, 750 );

				d3.select( '.pmc-stocks-menu .selected' ).attr( 'class', '' );

				$text.attr( 'class', 'selected' );
				d3.select( '.pmc-summary-items' ).remove();

				self.build_summary( summary );
				d3.select('.pmc-stocks-loader')
					.style( 'display', 'none' );
			});

		},

		/**
		 * Adds markers for min and max on graph.
		 *
		 * @param svg
		 * @param obj
		 * @param cl
		 */
		add_marker: function( svg, obj, cl ) {
			var self = this;
			var $group = svg.append( 'g' ).attr( 'class', 'marker ' + cl );

			obj.index_value = parseFloat( obj.index_value ).toFixed(2);

			var offset = 'min' === cl ? 6 : -23;

			var $rectangle = $group.append( 'rect' )
				.attr( 'height', 16 )
				.attr( 'rx', 5 )
				.attr( 'ry', 5 );

			var $text = $group.append( 'text' )
				.style( { 'font-size': '10px' } )
				.text( obj.index_value )
				.attr( 'x', 3 );

			var rect_width = $text.node().getBBox().width + 6;

			$rectangle.attr( 'width', rect_width )
				.attr( 'transform', function( d ) {
					return 'translate(' + ( x( obj.date ) - ( rect_width - 10 ) ) + ',' + ( y( obj.index_value ) + offset ) + ')';
				});

			offset = 'min' === cl ? 18 : -12;

			$text.attr( 'transform', function( d ) {
				return 'translate(' + ( x( obj.date ) - ( rect_width - 10 ) ) + ',' + ( y( obj.index_value ) + offset ) + ')';
			});

			var arrow = 'min' === cl ? '\u25b2' : '\u25bc';

			offset = 'min' === cl ? 9 : -3;

			$group.append( 'text' )
				.style( { 'font-size': '10px' } )
				.attr( 'x', 3 )
				.attr( 'class', 'arrow' )
				.text( arrow )
				.attr( 'transform', function( d ) {
					return 'translate(' + ( x( obj.date ) - 8 ) + ',' + ( y( obj.index_value ) + offset ) + ')';
				});

		},

		/**
		 * Build table for shortcode.
		 *
		 * @param data
		 * @param regions
		 * @param categories
		 * @param flags
		 */
		build_table: function( data, regions, categories, flags ) {
			flags_path = flags;
			var self = this;
			self.data = self.clean_data( data );
			self.regions = regions;
			self.categories = categories;
			self.build_timeframe_select();
			self.build_regions_select();
			self.build_categories_select();
			self.$table = d3.select( '.pmc-stocks-table' ).append( 'table' )
				.attr( 'width', '100%' );

			self.$table.append( 'thead' );
			self.$table.append( 'tbody' );
			self.update_table_data();
		},

		/**
		 * Build timeframe select.
		 */
		build_timeframe_select: function() {
			var self = this;

			var timeframes = {
				//usd_adj_1d_pct_change: '1-Day', commenting out to resolve PMCBA-74 until numbers are fixed
				usd_adj_1m_pct_change: '1-Month',
				usd_adj_3m_pct_change: '3-Months',
				usd_adj_6m_pct_change: '6-Months',
				usd_adj_1y_pct_change: '1-Year',
				usd_adj_3y_pct_change: '3-Years'
			};

			var $label = d3.select( '.pmc-stocks-table' ).append( 'label' )
				.text( 'Select a Time Frame' );

			var $select = $label.append( 'select' )
				.on( 'change', function() {
					self.change_timeframe( this.value, timeframes );
				} );

			var $options = $select.selectAll( 'option' )
				.data( self.json_to_array( timeframes ) ).enter()
				.append( 'option' )
					.attr( 'value', function( d ) { return d[ 0 ]; } )
					.text( function( d ) { return d[ 1 ]; } );
		},

		/**
		 * Change timeframe.
		 *
		 * @param value
		 * @param timeframes
		 */
		change_timeframe: function( value, timeframes ) {
			var self = this;

			map = self.json_to_array( map );
			for ( var i = 0; i < map.length; i++ ) {
				for ( var j in timeframes ) {
					if ( map[i][0] === j ) {
						map[i] = [
							value,
							timeframes[value] + ' Change'
						];
					}
				}
			}

			map = self.array_to_json( map );
			self.update_table_data();
		},

		/**
		 * Build regions select.
		 */
		build_regions_select: function() {
			var self = this;

			var $label = d3.select( '.pmc-stocks-table' ).append( 'label' )
				.text( 'Filter By Region' );

			var $select = $label.append( 'select' )
				.on( 'change', function() {
					self.change_region( this.value );
				} );

			var $options = $select.selectAll( 'option' )
				.data( self.regions ).enter()
				.append( 'option' )
					.attr( 'value', function( d ) { return d.toLowerCase(); } )
					.text( function( d ) { return d; } );
		},

		/**
		 * Change region.
		 *
		 * @param value
		 */
		change_region: function( value ) {
			var self = this;

			value = 'all regions' === value ? '' : value;

			region = value;

			self.update_table_data();
		},

		/**
		 * Build categories select.
		 */
		build_categories_select: function() {
			var self = this;

			var $label = d3.select( '.pmc-stocks-table' ).append( 'label' )
				.text( 'Filter By Category' );

			var $select = $label.append( 'select' )
				.on( 'change', function() {
					self.change_category( this.value );
				} );

			var $options = $select.selectAll( 'option' )
				.data( self.categories ).enter()
				.append( 'option' )
					.attr( 'value', function( d ) { return d.toLowerCase(); } )
					.text( function( d ) { return d; } );
		},

		/**
		 * Change category functionality.
		 *
		 * @param value
		 */
		change_category: function( value ) {
			var self = this;

			value = 'all categories' === value ? '' : value;

			category = value;

			self.update_table_data();
		},

		/**
		 * Table display.
		 *
		 * @param d
		 * @returns {Array}
		 */
		table_display: function( d ) {
			var table_data = [];
			for ( var i = 0; i < d.length; i++ ) {
				if ( 'undefined' !== typeof map[d[i][0]] ) {
					table_data.push(d[i]);
				}
			}
			return table_data;
		},

		/**
		 * Update table data.
		 */
		update_table_data: function() {
			var self = this;

			self.$table.select( 'tbody' ).selectAll( 'tr' ).remove();
			self.$table.select( 'thead' ).selectAll( 'tr' ).remove();

			// Header
			var $th_tr = self.$table.select( 'thead' ).append( 'tr' );
			var th = $th_tr.selectAll( 'th' )
				.data( self.json_to_array( map ) )
				.enter().append( 'th' )
				.attr( 'class', function( d ) {
					if ( self.sort_name === d[0] ) {
						d[0] += ' ' + self.sort;
					}
					return d[0];
				} )
				.text( function( d ) { return d[1]; } )
				.on( 'click', function( d, i ) {
					self.sort_table( d, i, this );
				});


			// Rows
			var data = self.data.slice( 0 );
			data = data.filter( self.filter_data );

			var tr = self.$table.select( 'tbody' ).selectAll( 'tr' )
				.data( data )
				.enter().append( 'tr' )
				.sort( function ( a, b ) {
					return null === a || null === b ? 0 : self.compare( a[ self.sort_name ], b[ self.sort_name ] );
				});

			// Cells
			var td = tr.selectAll( 'td' )
				.data( function( d ) {
					d = self.json_to_array( d );
					d = self.table_display( d );

					return d;
				} );

			td.enter().append( 'td' )
				.attr( 'class', function( d ) {
					var cl = d[0];
					if ( d[0].match( /^usd_adj_\S{2}_pct_change$/g ) ) {
						if ( d[1] < 0 ) {
							cl = d[0] + ' down';
						} else if ( d[1] === 0 ) {
							// Do nothing.
						} else {
							cl = d[0] + ' up';
						}
					}
					return cl;
				} )
				.attr( 'title', function( d ) {
					return self.format_text( d );
				} )
				.text( function( d ) { return d[1]; } );

			var span = tr.selectAll( 'td.stock_exchange_country' )
				.append( 'img' )
					.data( function( d ) { return self.json_to_array( d, 'stock_exchange_country_iso2_code' ); } )
					.attr( 'width', '25' )
					.attr( 'src', function( d ) {
						var image = d[1].toLowerCase();
						if ( ! image || ! flags_path ) {
							return;
						}
						return flags_path + image + '.svg';
					} );

		},

		/**
		 * Sort table helper.
		 * @param d
		 * @param i
		 * @param t
		 * @returns {*}
		 */
		sort_table: function( d, i, t ) {
			var self = this;

			var $this = d3.select(t);
			var name = d[0].split( ' ' );
			self.sort_name = name[0];
			if ( false === $this.classed( 'desc' ) && false === $this.classed( 'asc' ) ) {
				self.sort = 'desc';
			} else if ( false === $this.classed( 'desc' ) ) {
				self.sort = 'desc';
			} else if ( false === $this.classed( 'asc' ) ) {
				self.sort = 'asc';
			}
			return self.update_table_data();
		},

		/**
		 * Filter data helper.
		 *
		 * @param value
		 * @returns {boolean}
		 */
		filter_data: function( value ) {
			var self = this;

			var rtn = true;

			if ( region ) {
				if (  value.region !== region ) {
					rtn = false;
				}
			}

			if ( category ) {
				if (  value.category !== category ) {
					rtn = false;
				}
			}

			return rtn;
		},

		/**
		 * Format text helper.
		 *
		 * @param value
		 * @returns {*}
		 */
		format_text: function( value ) {
			if ( value[0] === 'market_cap' ) {
				if ( value[1] === 0 ) {
					value[1] = 'N/A';
				} else {
					var len = value[1].toString().length;
					if ( len < 10 ) {
						value[1] = ( value[1] / 1000000 ).toFixed(2) + ' M';
					} else if ( len < 13  ) {
						value[1] = ( value[1] / 1000000000 ).toFixed(2) + ' B';
					} else if ( len < 16  ) {
						value[1] = ( value[1] / 1000000000000 ).toFixed(2) + ' T';
					}
				}
			}
			if ( 'number' === typeof value[1] ) {
				value[1] = value[1].toLocaleString( undefined, {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				} );
			}

			if ( value[0].match( /^usd_adj_\S{2}_pct_change/g ) ) {
				value[1] = value[1] + '%';
			}
			return value[1];
		},

		/**
		 * Clean data helper.
		 *
		 * @param data
		 * @returns {*}
		 */
		clean_data: function( data ) {
			var self = this;
			for ( var i = 0; i < data.length; i++ ) {
				for( var d in data[i] ) {
					var value = data[i][d];

					// Format dollar values
					if ( value.match( /^[0-9\.-]+$/g ) ) {
						value = parseFloat( value );
					}

					if ( 'symbol' === d ) {
						// Format Symbol
						value = value.replace( /^0+/, '' );
						value = value.split('.');
						value = value[0].toUpperCase();
					}

					if ( 'category' === d || 'region' === d ) {
						value = value.toLowerCase();
					}

					if ( 'market_cap' === d ) {
						if ( ! value || value === 'N/A' ) {
							value = 0;
						} else {
							switch ( value.substr( -1 ) ) {
							case 'M':
								value = parseFloat( value.replace( /\w{1}$/, '' ) );
								value = parseInt( value * 1000000, 10 );
								break;
							case 'B':
								value = parseFloat( value.replace( /\w{1}$/, '' ) );
								value = parseInt( value * 1000000000, 10 );
								break;
							case 'T':
								value = parseFloat( value.replace( /\w{1}$/, '' ) );
								value = parseInt( value * 1000000000000, 10 );
								break;
							}
						}

					}
					data[i][d] = value;
				}
			}
			return data;
		},

		/**
		 * Compare helper.
		 * @param a
		 * @param b
		 * @returns {number}
		 */
		compare: function( a, b ) {
			var self = this;

			if ( 'string' === typeof a ) {
				a = a.toLowerCase();
			}

			if ( 'string' === typeof b ) {
				b = b.toLowerCase();
			}

			if ( self.sort === 'desc' ) {
				return a > b ? 1 : a === b ? 0 : -1;
			} else {
				return a < b ? 1 : a === b ? 0 : -1;
			}
		},

		/**
		 * JSON to Array helper.
		 *
		 * @param json
		 * @param override
		 * @returns {Array}
		 */
		json_to_array: function( json, override ) {
			var self = this;

			var ret = [],
			key;

			if ( 'undefined' !== typeof override ) {
				if ( json.hasOwnProperty( override ) ) {
					ret.push( self.json_key_value_to_array( override, json[ override ] ) );
				}
			} else {
				for ( key in json ) {
					if ( json.hasOwnProperty( key ) ) {
						ret.push( self.json_key_value_to_array( key, json[ key ] ) );
					}
				}
			}
			return ret;
		},

		/**
		 * Array to JSON helper.
		 *
		 * @param arr
		 * @returns {{}}
		 */
		array_to_json: function( arr ) {
			var obj = {};
			for ( var i = 0; i < arr.length; i++ ) {
				obj[ arr[ i ][ 0 ] ] = arr[ i ][ 1 ];
			}
			return obj;
		},

		/**
		 * JSON key/value to Array helper.
		 *
		 * @param k
		 * @param v
		 * @returns {*[]}
		 */
		json_key_value_to_array: function( k, v ) {
			return [ k, v ];
		},

		/**
		 * Build Market Movers widget.
		 *
		 * @param gainers
		 * @param decliners
		 * @returns {boolean}
		 */
		build_market_movers: function( gainers, decliners ) {
			var self = this;

			if ( 'object' !== typeof gainers || 'object' !== typeof decliners || $.isEmptyObject( gainers ) || $.isEmptyObject( decliners ) ) {
				// Data is failing, hide widget.
				$('.pmc-stocks-market-movers-widget').parent('.hp-panel').hide();
				return false;
			}

			var $ul = d3.select( '.pmc-stocks-market-movers-table' ).append( 'ul' )
				.attr( 'class', 'pmc-stocks-market-movers-nav' );

			$ul.append( 'li' )
				.attr( 'class', 'pmc-stocks-market-movers-gainers selected' )
				.on( 'click', self.market_movers_change_table )
				.text( '% Gainers' );

			$ul.append( 'li' )
				.attr( 'class', 'pmc-stocks-market-movers-decliners' )
				.on( 'click', self.market_movers_change_table )
				.text( '% Losers' );

			var $gainers = d3.select( '.pmc-stocks-market-movers-table' ).append( 'table' )
				.attr( 'class', 'pmc-stocks-market-movers-table-gainers selected' )
				.append( 'tbody' );

			gainers = self.clean_data( gainers );

			var $tr_th = $gainers.append( 'tr' );

			$tr_th.append( 'th' )
				.attr( 'class', 'pmc-stocks-left' )
				.text( 'Name' );

			$tr_th.append( 'th' )
				.attr( 'class', 'pmc-stocks-right' )
				.text( '% Gain' );

			gainers.forEach( function( d, i ) {
				var $tr = $gainers.append( 'tr' );
				var $first = $tr.append( 'td' );

				$first.attr( 'class', 'pmc-stocks-left' );

				$first.append('div')
					.attr( 'class', 'pmc-stocks-ellipsis' )
					.text(d.name);

				$first.append('div').text('[' + d.symbol + ']' );

				$tr.append( 'td' )
					.attr( 'class', 'pmc-stocks-right' )
					.text( d.usd_adj_1m_pct_change + '%' );
			});

			var $decliners = d3.select( '.pmc-stocks-market-movers-table' ).append( 'table' )
				.attr( 'class', 'pmc-stocks-market-movers-table-decliners' )
				.append( 'tbody' );

			decliners = self.clean_data( decliners );

			$tr_th = $decliners.append( 'tr' );

			$tr_th.append( 'th' )
				.attr( 'class', 'pmc-stocks-left' )
				.text( 'Name' );

			$tr_th.append( 'th' )
				.attr( 'class', 'pmc-stocks-right' )
				.text( '% Lost' );

			decliners.forEach( function( d, i ) {
				var $tr = $decliners.append( 'tr' );
				var $first = $tr.append( 'td' );

				$first.attr( 'class', 'pmc-stocks-left' );

				$first.append('div')
					.attr( 'class', 'pmc-stocks-ellipsis' )
					.text(d.name);

				$first.append('div').text('[' + d.symbol + ']' );

				$tr.append( 'td' )
					.attr( 'class', 'pmc-stocks-right' )
					.text( d.usd_adj_1m_pct_change + '%' );
			});
		},

		/**
		 * Market Movers change table.
		 */
		market_movers_change_table: function() {
			var $this = d3.select(this);

			d3.selectAll('.pmc-stocks-market-movers-nav li').classed( 'selected', false );

			$this.classed( 'selected', true );

			d3.select( '.pmc-stocks-market-movers-table-gainers').classed( 'selected', false );
			d3.select( '.pmc-stocks-market-movers-table-decliners').classed( 'selected', false );

			if ( $this.classed('pmc-stocks-market-movers-gainers') ) {
				d3.select( '.pmc-stocks-market-movers-table-gainers').classed( 'selected', true );
			} else if ( $this.classed('pmc-stocks-market-movers-decliners') ) {
				d3.select( '.pmc-stocks-market-movers-table-decliners').classed( 'selected', true );
			}

		}

	};
})( jQuery );
