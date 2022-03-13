<?php
/*
 * Plugin Name: PMC Google Site Search Widget
 * Description:	 Displays the google site search box. 2012-04-17 by Adaeze Esiobu.
 * Version: 1.0
 * License: PMC Proprietary.  All rights reserved.
 */
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

class PMC_Google_Site_Search_widget extends WP_Widget
{
	protected $_count = 0;
    /*
     * Defines the widget name
     */
    function __construct(){
        parent::__construct(false , 'Google Site Search' );
    }

	function widget( $args , $instance ){

		$this->_count += 1;

		$default_instance = array(
			'site_search_key' => '',
			'search_url' => home_url('/results/'),
		);

		$instance = wp_parse_args( $instance, $default_instance );

?>
		<!-- Google Site Search -->
		<div id="cse">
			<div id="cse-search-form<?php echo (int)$this->_count; ?>"></div>
			<script type="text/javascript">
				var _cse_options = _cse_options || [];
				_cse_options.push({
					cse_id: '<?php echo esc_js( $instance['site_search_key'] ); ?>',
					search_url: '<?php echo esc_js( $instance['search_url'] ); ?>',
					search_box: 'cse-search-form<?php echo (int)$this->_count; ?>'
				});
			</script>
		</div>
		<!-- End Google Site Search -->
<?php
			wp_enqueue_script('google-jsapi', '//www.google.com/jsapi', array(), false, true );
			wp_enqueue_script( 'pmc-gss-script', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'google-jsapi' ), false, true );
	}

    function update($new_instance , $old_instance ){

		$instance = $old_instance;

		$instance['site_search_key'] = sanitize_text_field( $new_instance['site_search_key'] );

		return $instance;
    }

    function form( $instance ){
        $instance = wp_parse_args( (array)$instance , array( 'site_search_key' => '') );

       ?>
		<label for="<?php echo esc_attr( $this->get_field_id( 'site_search_key' ) ); ?>"> Site Search Key
			<input id="<?php echo esc_attr( $this->get_field_id( 'site_search_key' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'site_search_key' ) ); ?>" value="<?php echo esc_attr( $instance['site_search_key'] ); ?>" type="text" />
		</label>
        <?php
    }
}

/**
 * Add google site search shortcode
 * This allows the search result to be displayed on a page that we have the shortcode
 * defined on.
 * @since   1.0.1.0 /ppt-3210: Added order_by attribute that orders search result by date. Later on can be extended to use other order_by options.
 * @version 1.0.1.0 2014-08-19 Amit Sannad
 */
function pmc_add_google_site_search( $atts ){

	$default_atts = array(
		'site_search_key' => '',
		'count'           => 'google.search.Search.FILTERED_CSE_RESULTSET',
		'order_by'        => ""
	);
	$atts = shortcode_atts( $default_atts, $atts );

	$order_by_options = "";

	//Only order by date is supported. Order by relevance is by default.
	if ( ! empty( $atts["order_by"] ) && "date" == $atts["order_by"] ) {
		$order_by_options = "
			var orderByOptions = {};
			orderByOptions['keys'] = [ {label: 'Date', key: 'date'}, {label: 'Relevance', key: 'relevance'}];
			customSearchOptions['enableOrderBy'] = true;
			customSearchOptions['orderByOptions'] = orderByOptions;
		";
	}

	return "<div id='cse-result'>Loading</div>
	<script src='//www.google.com/jsapi' type='text/javascript'></script>
	<script type='text/javascript'>
	  google.load('search', '1', {language : 'en', style : google.loader.themes.V2_DEFAULT});
	  google.setOnLoadCallback(function() {
	    var customSearchOptions = {};
	    var googleAnalyticsOptions = {};
	    googleAnalyticsOptions['queryParameter'] = 's';
	    googleAnalyticsOptions['categoryParameter'] = '';
		". $order_by_options /* Already js string prepared above */ ."
	    customSearchOptions['googleAnalyticsOptions'] = googleAnalyticsOptions;  var customSearchControl = new google.search.CustomSearchControl(
	      '".  esc_js($atts['site_search_key'])."', customSearchOptions);
	    customSearchControl.setResultSetSize(" . esc_js( $atts['count'] ) . ");
	    var options = new google.search.DrawOptions();
	    options.setAutoComplete(true);
	    customSearchControl.draw('cse-result', options);
	    function parseParamsFromUrl() {
	      var params = {};
	      var parts = window.location.search.substr(1).split('\x26');
	      for (var i = 0; i < parts.length; i++) {
	        var keyValuePair = parts[i].split('=');
	        var key = decodeURIComponent(keyValuePair[0]);
	        params[key] = keyValuePair[1] ?
	            decodeURIComponent(keyValuePair[1].replace(/\+/g, ' ')) :
	            keyValuePair[1];
	      }
	      return params;
	    }

	    var urlParams = parseParamsFromUrl();
	    var queryParamName = 'q';
	    if (urlParams[queryParamName]) {
	      customSearchControl.execute(urlParams[queryParamName]);
	    }
	  }, true);
	</script>";
}
/*
 * register the google site search widget
 */
function register_pmc_google_site_search(){

    register_widget( 'PMC_Google_Site_Search_widget' );
}

//register widget
add_action( 'widgets_init' , 'register_pmc_google_site_search' ) ;
//Add shortcode
add_shortcode( 'pmc_google_site_search', 'pmc_add_google_site_search' );

// add filter to indicate robots noindex
add_filter ('pmc_meta_robots_noindex', function( $value ) {

	if ( is_admin() || ! is_page() ) {
		return $value;
	}

	if ( strpos ( $_SERVER['REQUEST_URI'], '/results/') === 0) {
		return true;
	}

	return $value;
});



require_once __DIR__ . '/class-pmc-google-site-search.php';
require_once __DIR__ . '/pmc-google-site-search-widget-v2.php';
require_once __DIR__ . '/class-pmc-customized-google-site-search.php';


//EOF
