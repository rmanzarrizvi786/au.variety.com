(
	function ( $ ) {
		pmc_seo_poll = {
			ttl: 60000,

			params: {},

			seo_fields: {
				slug: "",
				title: "",
				description: "",
				keywords: ""
			},

			init: function () {
				pmc_seo_poll.params = {
					action: "pmc-seo-poll",
					post_ID: $( "#post_ID" ).val(),
					_pmc_seo_poll: pmc_seo_poll_l10n._pmc_seo_poll
				};

				if ( $( "#editable-post-name" ).length > 0 ) {
					this.seo_fields.slug = $( "#editable-post-name" );
				}

				if ( $( "#mt_seo_title" ).length > 0 ) {
					this.seo_fields.title = $( "#mt_seo_title" );
				} else if ( $( "#yoast_wpseo_title" ).length > 0 ) {
					this.seo_fields.title = $( "#yoast_wpseo_title" );
				}

				if ( $( "#mt_seo_description" ).length > 0 ) {
					this.seo_fields.description = $( "#mt_seo_description" );
				} else if ( $( "#yoast_wpseo_metadesc" ).length > 0 ) {
					this.seo_fields.description = $( "#yoast_wpseo_metadesc" );
				}

				if ( $( "#mt_seo_keywords" ).length > 0 ) {
					this.seo_fields.keywords = $( "#mt_seo_title" );
				}

				pmc_seo_poll.poll();

				var timer = setInterval( "pmc_seo_poll.poll()", pmc_seo_poll.ttl );
			},

			poll: function () {
				$.get( ajaxurl,
					pmc_seo_poll.params,
					function ( response ) {

						if ( "undefined" !== typeof response._pmc_seo_slug
						     && response._pmc_seo_slug.length > 0
						     && pmc_seo_poll.seo_fields.slug.length > 0 ) {
							if ( pmc_seo_poll.seo_fields.slug.text() !== response._pmc_seo_slug ) {
								pmc_seo_poll.seo_fields.slug.text( response._pmc_seo_slug );
							}
						}

						if ( "undefined" !== typeof response._pmc_seo_title
						     && response._pmc_seo_title.length > 0
						     && pmc_seo_poll.seo_fields.title.length > 0 ) {
							if ( pmc_seo_poll.seo_fields.title.val() !== response._pmc_seo_title ) {
								pmc_seo_poll.seo_fields.title.val( response._pmc_seo_title );
							}
						}

						if ( "undefined" !== typeof response._pmc_seo_description
						     && response._pmc_seo_description.length > 0
						     && pmc_seo_poll.seo_fields.description.length > 0 ) {
							if ( pmc_seo_poll.seo_fields.description.val() !== response._pmc_seo_description ) {
								pmc_seo_poll.seo_fields.description.val( response._pmc_seo_description );
							}
						}

						if ( "undefined" !== typeof response._pmc_seo_keywords
						     && response._pmc_seo_keywords.length > 0
						     && pmc_seo_poll.seo_fields.keywords.length > 0 ) {
							if ( pmc_seo_poll.seo_fields.keywords.val() !== response._pmc_seo_keywords ) {
								pmc_seo_poll.seo_fields.keywords.val( response._pmc_seo_keywords );
							}
						}
					},
					"json"
				);

			}

		};

		$( document ).ready( function () {
			pmc_seo_poll.init();
		} );
	}
)( jQuery );
