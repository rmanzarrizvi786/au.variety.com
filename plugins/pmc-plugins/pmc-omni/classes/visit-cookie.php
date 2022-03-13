<?php

namespace PMC\Omni;

use PMC\Global_Functions\Traits\Singleton;

class Visit_Cookie {

	use Singleton;

	/*
	 * Initialize the class and setup hooks
	 *
	 * @since 2016-04-07
	 * @version 2016-04-09 Archana Mandhare PMCVIP-1055
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/*
	 * Load the script at the top of the page hence at priority 1
	 *
	 * @since 2016-04-07 Corey Gilmore
	 * @version 2016-04-09 Archana Mandhare PMCVIP-1055
	 *
	 */
	private function _setup_hooks() {
		add_action( 'pmc_enqueue_scripts_using_pmc_page_meta', array( $this, 'pmc_set_onmi_visit_id' ), 1); // should be always be at priority 1
		add_action( 'pmc_google_analytics_custom_dimensions_js', array( $this, 'pmc_omni_to_google_analytics_custom_dimensions_js'), 10, 2 );

		add_filter( 'pmc_krux_allowed_data_attributes', array( $this, 'add_omni_visit_id_to_krux' ) );
	}

	/*
	 * Note from Corey Gilmore: IMPORTANT !! - The javascript below should always be minified just to keep it a little more compact
	 * (since it's going to load nearly at the top of the page), and make it a little less obvious that we're tagging everyone.
	 *
	 * @since 2016-04-07 Corey Gilmore
	 * @version 2016-04-09 Archana Mandhare PMCVIP-1055
	 *
	 */
	public function pmc_set_onmi_visit_id( $meta ) {
		$lob = defined( 'PMC_SITE_NAME' ) ? PMC_SITE_NAME : 'pmc';
		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => '',
		];

		if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
			$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0002' );
		}
		?>
		<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
			(function(d,w){
				var i, parts, name, c, rdecode = /(%[0-9A-Z]{2})+/g, rspace = /\+/g, ac = (d ? d.split('; ') : []);
				for(w.pmc_cookies = {}, i = 0; i < ac.length; i++) {
					parts = ac[i].split('='), name = parts[0].replace(rdecode, decodeURIComponent), c = parts.slice(1).join('=');
					if(c.charAt(0) === '"') { c = c.slice(1, -1); } c = c.replace(rdecode, decodeURIComponent).replace(rspace, ' '); w['pmc_cookies'][name] = c;
				}
			})(document.cookie, window);

			pmc_meta=pmc_meta || {}, pmc_meta.omni_visit_id = window.pmc_cookies.omni_visit_id || <?php echo wp_json_encode( $lob . '.' ); ?> + new Date().getTime() + '.' + (function(l,b,a,c,i,d){for(i=0;i<256;i++){l[i]=(i<16?'0':'')+(i).toString(16);}if(c&&c.getRandomValues){try{d=new Uint32Array(4),c.getRandomValues(d);}catch(e){d=0;}}d=d||[b()*a>>>0,b()*a>>>0,b()*a>>>0,b()*a>>>0];a=d[0],b=d[1],c=d[2],d=d[3];return l[a&0xff]+l[a>>8&0xff]+l[a>>16&0xff]+l[a>>24&0xff]+'-'+l[b&0xff]+l[b>>8&0xff]+'-'+l[b>>16&0x0f|0x40]+l[b>>24&0xff]+'-'+l[c&0x3f|0x80]+l[c>>8&0xff]+'-'+l[c>>16&0xff]+l[c>>24&0xff]+l[d&0xff]+l[d>>8&0xff]+l[d>>16&0xff]+l[d>>24&0xff];})([],Math.random,0x100000000,window.crypto||window.msCrypto);
			var d = new Date(); d.setTime(d.getTime() + ( 60 * 60 * 1000 )); var expires = d.toGMTString(); var path = "/"; var domain = window.location.hostname;
			document.cookie = 'omni_visit_id=' + encodeURIComponent(pmc_meta.omni_visit_id) + ( expires ? '; expires=' + expires : '' ) + ( path ? '; path=' + path : '' ) + ( domain ? '; domain=' + domain : '' );
		</script>
		<?php
	}

	/*
	 * Add omni_visit_id to GA custom dimension 28
	 *
	 * @since 2016-04-12
	 * @version 2016-04-12 Archana Mandhare PMCVIP-977
	 *
	 */
	public function pmc_omni_to_google_analytics_custom_dimensions_js( $dimensions, $dimension_map ) {

		if ( empty( $dimension_map['omni-visit-id'] ) ) {
			return;
		}
		$dim_key = 'dimension' . $dimension_map['omni-visit-id'];
		?>
			if( 'undefined' !== typeof pmc_meta && 'string' === typeof pmc_meta.omni_visit_id ){
				dim[<?php echo wp_json_encode( $dim_key ); ?>] = pmc_meta.omni_visit_id;
			}
		<?php
	}

	/*
	 * Add omni_visit_id to krux data attributes
	 *
	 * @since 2016-04-12
	 * @version 2016-04-12 Archana Mandhare PMCVIP-978
	 *
	 */
	public function add_omni_visit_id_to_krux( $allowed_data_attributes ) {
		$allowed_data_attributes[] = 'omni_visit_id';
		return $allowed_data_attributes;
	}

}

//EOF
