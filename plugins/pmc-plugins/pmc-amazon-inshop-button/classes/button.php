<?php

namespace PMC\Amazon_InShop_Button;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Scripts;

/**
 * Button class adds the necessary script to render an Amazon InShop button
 *
 * @version 2015-08-06
 * @since 2015-08-06 - Mike Auteri - PPT-5224: Add Amazon InShop Button Integration Into All Post Content
 */
class Button {

	use Singleton;

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'pmc-tags-footer', array( $this, 'get_javascript' ) );
	}

	/**
	 * Set config and async loads script
	 *
	 * @version 2015-08-06
	 * @since 2015-08-06 - Mike Auteri - PPT-5224: Add Amazon InShop Button Integration Into All Post Content
	 *
	 * @return void
	 */
	public function get_javascript() {
		/**
		 * pmc_amazon_inshop_tracking_id is the filter to set the Amazon InShop Tracking ID.
		 *
		 * @version 2015-08-12
		 * @since 2015-08-12 - Mike Auteri - PPT-5224: Add Amazon InShop Button Integration Into All Post Content
		 */
		$tracking_id = apply_filters( 'pmc_amazon_inshop_tracking_id', '' );

		if( !empty( $tracking_id ) ) {
			?>
			<script type="text/javascript">
				if( jQuery('#pmc_amzn_btn_id').length > 0 ) {
					amzn_assoc_ad_type = "shopnshare";
					amzn_assoc_marketplace = "amazon";
					amzn_assoc_region = "US";
					amzn_assoc_placement = "sns1";
					amzn_assoc_tracking_id = <?php echo wp_json_encode( $tracking_id ) ?>;
					amzn_assoc_custom_button_id = "pmc_amzn_btn_id";
					amzn_assoc_div_name = "pmc_amzn_wrapper";

					// Commenting out callback for now. May want to use it soon, so not removing code.
					/*amzn_assoc_callbacks = {
						'onload': function(d) {
							if( d.recsAvailable ) {
								jQuery('#pmc_amzn_btn_id').show();
							}
						}
					};*/

					(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) { return; }
						js = d.createElement(s);
						js.id = id;
						js.async = true;
						js.src = "//z-na.amazon-adsystem.com/widgets/onejs?MarketPlace=US&source=ac";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'amzn-onejs'));
				}
			</script>
			<?php
		}
	}
}
