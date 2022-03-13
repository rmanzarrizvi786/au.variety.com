/**
 * PMC A/B Test frame work
 *
 * @version 1.0
 * @author Hau Vong
 *
 */

/**
 * To create minified version of pmc-ab-test.min.js:
 *
 * - npm install uglify -g (to install uglify)
 * - uglify -s pmc-ab-test.js -o pmc-ab-test.min.js
 */

function PMC_AB_Test( options ) {
	if ( this.init ) {
		try {
			this.init( options );
			this.run();
		} catch (e) {}
	} else {
		return new PMC_AB_Test( options );
	}
}

PMC_AB_Test.prototype.init = function ( options ) {
	this.options = {
			id  :  'pmc_abt',
			bias: 50,
            usecookie:false,
			a   : function(){},
			b   : function(){}
		};
	if ( typeof options.a == 'function' ) {
		this.options.a = options.a;
	}
	if ( typeof options.b == 'function' ) {
		this.options.b = options.b;
	}
	if ( typeof options.id == 'string' ) {
		this.options.id = options.id;
	}
	if ( typeof options.bias == 'number' ) {
		this.options.bias = options.bias;
	}
    if( typeof options.usecookie == 'boolean' ){
        this.options.usecookie = options.usecookie;
    }

    if( this.options.usecookie ){
        this.cookie = pmc.cookie.get( this.options.id + options.bias );
        if ( typeof this.cookie == 'undefined' || !this.cookie ) {
            this.cookie = this.get_ab();
            pmc.cookie.set( this.options.id + options.bias, this.cookie );
        }
    }else{
    	// Not using cookie, but use the same var anyway
    	this.cookie = this.get_ab();
    }

	if ( this.cookie == 'a' ) {
		this.do_test = this.options.a;
	} else {
		this.do_test = this.options.b;
	}

};

PMC_AB_Test.prototype.run = function() {
	try {
		if ( typeof this.do_test == 'function' ) {
			this.do_test();
		}
	} catch (e) {}
}

PMC_AB_Test.prototype.get_ab = function() {
	if ( Math.floor((Math.random()*100)+1) <= this.options.bias ) {
		return 'a';
	} else {
		return 'b';
	}
};


PMC_AB_Ads = window.PMC_AB_Ads || {};
PMC_AB_Ads.LoadScript = function( src ) {
	var a, s = document.getElementsByTagName("script")[0];
	a = document.createElement("script");
	a.type="text/javascript";
	a.async = true;
	a.src = src;
	s.parentNode.insertBefore(a, s);
};

// Luminate In-Photo Ads
PMC_AB_Ads.Luminate = function( id ) {
	if ( typeof id == 'undefined' || !id ) {
		id = '9502f9019';
	}
	PMC_AB_Ads.LoadScript("https://www.luminate.com/widget/async/" + id + "/");
};
