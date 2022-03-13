/*
 * Pure JS implementation of Captcha or challenge question
 *
 * This library outputs a mathematical challenge question and allows verification
 * of answer to determine if a form has been sumbitted by a human or a bot
 *
 * @author Amit Gupta
 */

/**
 * IF YOU WORK ON THIS AND MAKE ANY CHANGES THEN MAKE SURE YOU GENERATE
 * A MINIFIED VERSION OF THIS SCRIPT AS WELL WHICH REPLACES THE EXISTING
 * MINIFIED VERSION. ONLY THE MINIFIED VERSION SHOULD BE USED ON FRONT-END.
 */


/**
 * Class constructor
 */
function PMC_JCaptcha() {
	this.range_outer = 20;
	this.use_single_op = false;
	this.answer = 0;
}

PMC_JCaptcha.prototype.get_random_number = function( range_outer ) {
	if ( typeof range_outer === 'undefined' || parseInt( range_outer ) < 1 ) {
		range_outer = this.range_outer;
	}

	return Math.floor( ( Math.random() * parseInt( range_outer ) ) + 1 );
};

PMC_JCaptcha.prototype.get_operator = function() {
	var num = this.get_random_number( 100 );
	var op_switch = parseInt( num % 2 );

	if ( this.use_single_op ) {
		op_switch = 1;
	}

	switch ( op_switch ) {
		case 0:
			return '*';
		case 1:
			return '+';
	}
};

PMC_JCaptcha.prototype.get_question = function() {
	var num_1 = this.get_random_number();
	var num_2 = this.get_random_number( 10 );
	var op = this.get_operator();

	var question = num_1 + " " + op + " " + num_2;
	this.answer = parseInt( eval( question ) );

	return question;
};

PMC_JCaptcha.prototype.is_answer = function( answer ) {
	if ( typeof answer !== 'undefined' && parseInt( answer ) === parseInt( this.answer ) ) {
		return true;
	}

	return false;
};


/**
 * Initialize class
 */
var pmc_jcaptcha = new PMC_JCaptcha();


//EOF