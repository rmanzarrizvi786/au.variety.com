/**
 * PMC-Gallery v2 feature improvements
 *
 * @ticket PPT-6781
 */


function PMC_Gallery_UI_Improvements( jq ) {

	this.short_circuit = true;

	if ( typeof jq !== 'undefined' && typeof Countable !== 'undefined' ) {

		this.short_circuit = false;

		this.$jq = jq;
		this.is_counter_attached = false;
		this.word_counter_class = 'word-counter';
		this.$counter_div = this.$jq( '<div></div>', { class: this.word_counter_class } ).empty().append( [
			document.createTextNode( 'Word Count:' ),
			$( '<span />', {
				text: 0
			} )
		] );

	}

}

PMC_Gallery_UI_Improvements.prototype.attach_word_counter = function ( elem ) {

	if ( this.short_circuit || typeof elem === 'undefined' ) {
		return;
	}

	var $parent = this.$jq( elem ).parent();

	if ( $parent.hasClass( 'caption-focus' ) && ! this.is_counter_attached ) {

		$parent.append( this.$counter_div );
		this.is_counter_attached = true;

		var self = this;

		Countable.live( elem, function ( o_counter ) {

			if ( typeof o_counter !== 'undefined' && typeof o_counter.words !== 'undefined' ) {
				self.$jq( '.' + self.word_counter_class + ' span' ).text( o_counter.words );
			}

		} );

	}

};

PMC_Gallery_UI_Improvements.prototype.detach_word_counter = function ( elem ) {

	if ( this.short_circuit || typeof elem === 'undefined' ) {
		return;
	}

	var $parent = this.$jq( elem ).parent();

	if ( ! $parent.hasClass( 'caption-focus' ) && this.is_counter_attached ) {

		Countable.die( elem );

		$parent.find( '.' + this.word_counter_class ).remove();
		this.is_counter_attached = false;

	}

};

jQuery( document ).ready( function ( $ ) {

	var pmc_gallery_ui_improvements = new PMC_Gallery_UI_Improvements( $ );

	$( document ).on( 'focusin', 'ul.attachments textarea.caption', function () {

		pmc_gallery_ui_improvements.attach_word_counter( this );

	} );

	$( document ).on( 'focusout', 'ul.attachments textarea.caption', function () {

		pmc_gallery_ui_improvements.detach_word_counter( this );

	} );

} );


//EOF
