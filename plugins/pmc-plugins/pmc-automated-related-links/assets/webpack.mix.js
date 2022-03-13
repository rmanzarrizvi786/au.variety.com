/**
 * Reference : https://laravel.com/docs/5.7/mix
 */

let mix = require( 'laravel-mix' );

mix.sass( 'src/scss/style.scss', 'build/css' );
mix.js( 'src/js/app.js', 'build/js' );
