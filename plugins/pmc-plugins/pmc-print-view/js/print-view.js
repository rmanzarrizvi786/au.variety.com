var pmcPrint = pmcPrint || {};

(function( document, $ ) {
	pmcPrint.vars = pmcPrintVars || {};

	pmcPrint.statusimages = {
		init: function() {
			$title_img = $( '<a title="Changes to this content are reflected in print and web versions.">' ).css(
				{
					'width': '16px',
					'height': '16px',
					'background': 'url(' + pmcPrintVars.flagImgPath + ') no-repeat top left',
					'display': 'block',
					'position': 'absolute',
					'right': '8px',
					'top': '.8em',
				}
			);

			$( '#post-body-content #title' ).before( $title_img );

			$issue_img = $( '<a title="Changes to this content are reflected in print and web versions.">' ).css(
				{
					'width': '16px',
					'height': '16px',
					'background': 'url(' + pmcPrintVars.flagImgPath + ') no-repeat top left',
					'display': 'inline-block',
					'margin-right': '5px',
				}
			);

			$( '#print-issuesdiv h3' ).prepend( $issue_img )

			$content_img = $( '<a title="Changes to this content are reflected in print and web versions.">' ).css(
				{
					'width': '16px',
					'height': '16px',
					'background': 'url(' + pmcPrintVars.flagImgPath + ') no-repeat top left',
					'display': 'block',
					'position': 'absolute',
					'right': '111px',
					'top': '.8em',
				}
			);

			$( '#postdivrich' ).append( $content_img );
		}
	};

	// Count all the words!
	pmcPrint.wordcount = {
		init: function() {
			// Make sure we have our headdings, only will happen on the Print edit page.
			if ( pmcPrint.vars.pagenow !== 'post.php' || 'y' !== pmcPrint.vars.onPrintView ) {
				return;
			}

			var $heds = [
				$( '#title' ),
				$( '#pmc_hed2_dek' ),
				$( '#pmc_hed3_overline' )
			];

			for ( var i = 0, len = $heds.length; i < len; i++ ) {
				this.bindHandler( $heds[i] );
			}
		},
		bindHandler: function( $hed ) {
			var $span = $( '<span></span>' )
				.attr( 'id', 'count-' + $hed.attr( 'id' ) )
				.addClass( 'pmc-wc' );
			$hed.after( $span );

			$hed.on( 'keyup blur focus', this.countWords( $hed, $span ) );
			$hed.trigger( 'keyup' );
		},
		countWords: function( $hed, $span ) {
			var wordcount;
			return function( e ) {
				if ( e.keyCode == undefined || e.keyCode == 32 ) {
					var count = $hed.val().match(/\S+/g);
					count = ( null === count ) ? 0 : count.length;

					if ( count !== wordcount ) {
						wordcount = count;
						$span.html( 'words: ' + count );
					}
				}
			};
		}
	};

	pmcPrint.togglePrintArticle = ( function( window, undefined ) {
		var switch_button = $( 'button[name=pmc-view-switch]' );

		function init() {
			$( 'input[name=pmc-print-post]' ).on( 'change', function() {
				var new_state = ( this.checked ) ? 1 : 0;

				if ( new_state ) {
					switch_button.show();
				} else {
					switch_button.hide();
				}

				var params = {
					action: 'toggle_print_article',
					nonce: pmcPrintVars.toggle_print_article_nonce,
					new_state: new_state,
					post_id: pmcPrintVars.post_id,
				};

				$.post( ajaxurl, params );
			} );
		}

		return {
			init: init,
		}
	} )( window );

	// Real time finalization feedback.
	pmcPrint.finalizer = {
		checks: [],
		events: {},
		handlers: {},
		finalizeButton: $( '#pmc_finalized' ),

		init: function() {
			// check if we are in print view.
			if ( ( pmcPrint.vars.pagenow !== 'post.php' && pmcPrint.vars.pagenow !== 'post-new.php' ) || 'y' !== pmcPrint.vars.onPrintView ) {
				return;
			}

			// check if we have a finalize button.
			if ( 0 === this.finalizeButton.length ) {
				return;
			}

			var i, length, check;
			for ( i = 0, length = this.checks.length; i < length; i++ ) {
				check = this.checks[ i ];
				if ( ! this.validateObject( check ) ){
					continue;
				}

				this.addEvent( check );
				this.addCallback( check );
				this.setUpEls( check );
			}

			this.bindEvents( this.events );
		},
		validateObject: function( check ) {
			var required = [ 'el', 'parent', 'light', 'e' ], i, length;
			for ( i = 0, length = required.length; i < length; i++ ) {
				if ( typeof check[ required[ i ] ] !== 'string' || '' === check[ required[ i ] ] ) {
					return false;
				}
			}
			return true;
		},
		addEvent: function( check ) {
			if ( undefined === this.events[ check.parent ] ) {
				this.events[ check.parent ] = [ check.e ];
			} else {
				var i, length;
				for ( i = 0, length = this.events[ check.parent ].length; i < length; i++ ) {
					if ( check.e === this.events[ check.parent ][ i ] ){
						return;
					}
				}
				this.events[ check.parent ].push( check.e );
			}
		},
		addCallback: function( check ) {
			var e = check.parent + ':' + check.e;
			if ( undefined === this.handlers[ e ] ) {
				this.handlers[ e ] = [ check ];
			} else {
				this.handlers[ e ].push( check );
			}
		},
		setUpEls: function( check ) {
			check.el = $( '#' + check.el );
			check.light = $( '#' + check.light );
			check.completed = check.handleEvent();
		},
		bindEvents: function( events ) {
			for ( var el in events ) {
				var i, length,
				$el = $( document.getElementById( el ) );

				for ( i = 0, length = events[ el ].length; i < length; i++ ) {
					$el.on( events[ el ][ i ], { context: this }, this.handleEvents );
				}
			}
		},
		handleEvents: function( e ) {
			var handler, i, length, key, previousCheck, self = pmcPrint.finalizer,
			parent = this.id, target = e.target, eName = e.type;

			key = parent + ':' + eName;
			if ( undefined !== self.handlers[ key ] ){
				handler = self.handlers[ key ];
				for ( i = 0, length = handler.length; i < length; i++ ) {
					if ( handler[i].check.call( handler[ i ], e ) ) {
						previousCheck = handler[ i ].completed;
						handler[ i ].completed = handler[ i ].handleEvent.call( handler[ i ], e );
						if ( handler[ i ].completed !== previousCheck ) {
							self.toggleLight.call( self, handler[i] );
						}
					}
				}
			}
			target = target.parentNode;
		},
		toggleLight: function( check ) {
			if( check.completed ) {
				check.light.attr( 'class', 'pmc-status pmc-complete' );
			} else {
				check.light.attr( 'class', 'pmc-status pmc-pending' );
			}
			this.checkStatus();
		},
		checkStatus: function() {
			var i, length, status = true;
			for ( i = 0, length = this.checks.length; i < length; i++ ) {
				if ( ! this.checks[ i ].completed ) {
					status = false;
				}
			}
			if ( status ) {
				this.finalizeButton.removeAttr( 'disabled' );
			} else {
				this.finalizeButton.attr( 'disabled', 'disabled' );
			}
		}
	};
	pmcPrint.finalizer.issue = {
		el: 'print-issuesdiv',
		parent: 'postbox-container-1',
		light: 'pmc-print-issue-light',
		e: 'change',
		allPanel: $( '#print-issues-all' ),
		popPanel: $( '#print-issues-pop' ),

		check: function( e ) {
			if ( 'INPUT' === e.target.nodeName && 'checkbox' === e.target.type ) {
				var target = e.target.parentNode;
				while ( target.id !== this.parent && null !== target ) {
					if ( target.id === this.el.attr( 'id' ) ) {
						return true;
					}
					target = target.parentNode;
				}
			}
			return false;
		},
		handleEvent: function( e ) {
			var i, length, checkboxes, complete, id;

			if ( this.allPanel.is( ':visible' ) ) {
				checkboxes = this.allPanel.find( 'input[type="checkbox"]:checked' );
				complete = ( 0 !== checkboxes.size() );
			} else {
				checkboxes = this.popPanel.find( 'input[type="checkbox"]:checked' );
				if ( 0 === checkboxes.size() ) {
					checkboxes = this.allPanel.find( 'input[type="checkbox"]:checked' );
					complete = false;
					if ( 0 !== checkboxes.size() ) {
						for ( i = 0, length = checkboxes.size(); i < length; i++ ) {
							id = checkboxes[ i ].attr( 'id' ).substring( 3 );
							id = $( '#in-popular-' + id );
							if ( null === id ) {
								complete = true;
							}
						}
					}
				} else {
					complete = true;
				}
			}

			return complete;
		}
	};
	pmcPrint.finalizer.section = {
		el: 'pmc_print_sectiondiv',
		parent: 'postbox-container-1',
		light: 'pmc-print-section-light',
		e: 'change',
		allPanel: $( '#pmc_print_section-all' ),
		popPanel: $( '#pmc_print_section-pop' ),

		check: function( e ) {
			if ( 'INPUT' === e.target.nodeName && 'checkbox' === e.target.type ) {
				var target = e.target.parentNode;
				while ( target.id !== this.parent && null !== target ) {
					if ( target.id === this.el.attr( 'id' ) ) {
						return true;
					}
					target = target.parentNode;
				}
			}
			return false;
		},
		handleEvent: function( e ) {
			var i, length, checkboxes, complete, id;
			if ( this.allPanel.is( ':visible' ) ) {
				checkboxes = this.allPanel.find( 'input[type="checkbox"]:checked' );
				complete = ( 0 < checkboxes.size() );
			} else {
				checkboxes = this.popPanel.find( 'input[type="checkbox"]:checked' );
				if ( 0 === checkboxes.size() ) {
					checkboxes = this.allPanel.find( 'input[type="checkbox"]:checked' );
					complete = false;
					if ( 0 !== checkboxes.size() ) {
						for ( i = 0, length = checkboxes.size(); i < length; i++ ) {
							id = checkboxes[ i ].attr( 'id' ).substring( 3 );
							id = $( '#in-popular-' + id );
							if ( null === id ) {
								complete = true;
							}
						}
					}
				} else {
					complete = true;
				}
			}

			return complete;
		}
	};
	pmcPrint.finalizer.notPrint = {
		el: 'pmc-print-web-only',
		parent: 'pmc-print-web-only-container',
		light: 'pmc-print-web-only-light',
		e: 'change',

		check: function( e ) {
			return ( this.el.attr('id') === e.target.id ) ? true : false;
		},
		handleEvent: function( e ) {
			return !this.el.is(':checked');
		}
	};

	pmcPrint.dropdown = {
		dds: [
			'tax-dd-print-issues',
			'tax-dd-pmc_print_section'
		],
		$dds: [],
		$ddopts: {},
		init: function() {
			// check if we are in edit view.
			if ( 'edit.php' !== pmcPrint.vars.pagenow || 'y' !== pmcPrint.vars.onPostList ) {
				return;
			}

			var i, length;
			for ( i = 0, length = this.dds.length; i < length; i++ ) {
				this.$dds[ i ] = $( '#' + this.dds[ i ] );
				if ( this.isMoreThanTen( this.$dds[ i ] ) ) {
					this.createFilterable( this.$dds[ i ] );
					this.bindEvents( this.$dds[ i ] );
				}
			}
		},
		isMoreThanTen: function( $el ) {
			var options = $el.find( 'option' );
			if ( options.size() > 10 ) {
				this.$ddopts[ $el.attr( 'id' ) ] = options;
				return true;
			}
			return false;
		},
		createFilterable: function( $el ) {
			var i, length, html, newList, newCover, id, option, selected;
			$newList = $( '<div/>' );
			id = $el.attr( 'id' );
			$newList.attr( 'id', id + '-cfContainer' );
			$newList.attr( 'class', 'pmc_cfContainer' );

			html = '<span id="pmc-hide-' + id + '" class="pmc-hide-dd"></span>';
			html += '<div id="pmc-fake-dd-' + id + '">';
			html += '<label><input type="text" autocomplete="off" id="pmc-filter-' + id + '" />';
			html += '<span>Filter</span></label>';
			html += '<ul>';
			i = 0;
			this.$ddopts[ id ].each( function() {
				selected = ( i === 0 ) ? ' class="pmc-cf-selected"' : '';
				html += '<li data-opt="' + $( this ).val() + '" ' + selected + '>' + $( this ).html() + '</li>';
				i++;
			} );

			html += '</ul></div>';

			$newList.html( html );
			$el.before( $newList );
			$newList.find( '#pmc-hide-' + id ).prepend( $el );
		},
		bindEvents: function( $el ) {
			var id = $el.attr( 'id' );
			var $span = $( document.getElementById( 'pmc-hide-' + id ) );
			var $fakeDD = $( document.getElementById( 'pmc-fake-dd-' + id ) );
			var $filterBox = $( document.getElementById( 'pmc-filter-' + id ) );
			var $container = $( document.getElementById( id + '-cfContainer' ) );

			$span.on( 'click', this.handleClick( $el, $fakeDD, $filterBox ) );
			$fakeDD.on( 'click', 'li', this.handleLiClick( $el, $fakeDD ) );
			$filterBox.on( 'keyup', this.handleFilter( $el, $fakeDD ) );
			$filterBox.on( 'keydown', this.handleSpecialKeys( $el, $fakeDD, $span ) );
			$( document ).on( 'click', this.handleBodyClick( $fakeDD ) );
			$container.on( 'click', this.killClickPropagation );
		},
		handleClick: function( $el, $fakeDD, $filterBox ) {
			return function( e ) {
				e.preventDefault();
				if ( $fakeDD.is( ':visible' ) ) {
					$fakeDD.hide();
					$( '#pmc-selected' ).attr( 'id', '' );
					$fakeDD.data( 'currentIndex', 0 );
				} else {
					$fakeDD.show();
					$filterBox.focus();
				}
			};
		},
		handleLiClick: function( $el, $fakeDD ) {
			var $dropdowns = this.$ddopts[ $el.attr( 'id' ) ];
			return function( e ) {
				var i, length,
				$target = $( e.target ),
				value = $target.data( 'opt' ).toString();

				$dropdowns.each( function() {
					if ( $( this ).val() === value ) {
						$( this ).attr( 'selected', 'selected' );
						$fakeDD.find( '.pmc-cf-selected' ).removeClass( 'pmc-cf-selected' );
						$target.addClass( 'pmc-cf-selected' );
						$fakeDD.hide();
						$( '#pmc-selected' ).attr( 'id', '' );
						$fakeDD.data( 'currentIndex', 0 );
						return;
					}
				} );
			};
		},
		handleFilter: function( $el, $fakeDD ) {
			return function( e ) {
				var i, length, text, selected, keycode = e.keyCode,
				currentIndex = $fakeDD.data( 'currentIndex' );
				list = $fakeDD.find( 'li' ),
				filterText = e.target.value,
				filter = new RegExp( filterText );

				// Label in the text area trick
				if ( '' === filterText ) {
					e.target.className = '';
				} else {
					e.target.className = 'pmc-filter-entered';
				}

				list.each( function() {
					text = $( this ).html();
					if ( '' ===  filterText || text.match( filter ) ) {
						$( this ).show();
					} else {
						$( this ).hide();
					}
				} );

				// Fix the currently selected item...
				if ( keycode !== 40 && keycode !== 38 ) {
					list = $fakeDD.find( 'li:visible' );
					$( '#pmc-selected' ).attr( 'id', '' );
					$fakeDD.data( 'currentIndex', 0 );
					if ( 0 < list.length ) {
						list[ currentIndex ].id = 'pmc-selected';
					}
				}
			};
		},
		handleSpecialKeys: function( $el, $fakeDD, $span ) {
			$fakeDD.data( 'currentIndex', 0 );
			var list;
			return function ( e ) {
				var keyCode = e.keyCode, currentIndex = $fakeDD.data( 'currentIndex' );
				list = $fakeDD.find( 'li:visible' );
				if ( keyCode === 40 ) {
					if ( ++currentIndex < list.length ) {
						list[ currentIndex - 1 ].id = '';
						list[ currentIndex ].id = 'pmc-selected';
						$fakeDD.data( 'currentIndex', currentIndex );
					}
				} else if ( keyCode === 38 ) {
					if ( 0 !== currentIndex ) {
						list[ currentIndex ].id = '';
						currentIndex--;
						list[ currentIndex ].id = 'pmc-selected';
						$fakeDD.data( 'currentIndex', currentIndex );
					}
				} else if ( keyCode === 13 ) {
					e.preventDefault();
					$( list[ currentIndex ] ).trigger( 'click' );
				} else if ( keyCode === 27 ) {
					$span.trigger( 'click' );
				}
			};
		},
		handleBodyClick: function( $fakeDD ) {
			return function( e ) {
				$fakeDD.hide();
				$( '#pmc-selected' ).attr( 'id', '' );
				$fakeDD.data( 'currentIndex', 0 );
			};
		},
		killClickPorpagation: function( e ){
			e.stopPropagation();
		}
	};

	pmcPrint.finalizer.checks.push( pmcPrint.finalizer.issue );
	pmcPrint.finalizer.checks.push( pmcPrint.finalizer.section );
	pmcPrint.finalizer.checks.push( pmcPrint.finalizer.notPrint );
	pmcPrint.finalizer.init();
	pmcPrint.dropdown.init();
	pmcPrint.wordcount.init();
	pmcPrint.statusimages.init();

	pmcPrint.togglePrintArticle.init();
})( document, jQuery );