<?php
namespace PMC\Amazon_Auto_Tagger;

use \PMC;
use \PMC_Cheezcap;
use \PMC\Global_Functions\Traits\Singleton;

class Tagger {

	use Singleton;

	protected $_tag = null;
	protected $_plugin_status = 'disabled';

	/**
	 * Filters and actions that needs to run go in here.
	 */
	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );
		add_action( 'pmc-tags-footer', array( $this, 'get_javascript' ) );
	}

	/*
	 * Create the Cheezcap settings in the admin
	 * @since 2015-10-12
	 * @version 2015-10-12 Javier Martinez PMCVIP-91
	 *
	 * @param $cheezcap_groups array
	 * @return array
	 */
	public function set_cheezcap_group( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';

		}

		$cheezcap_options = array(

			new \CheezCapDropdownOption(
				'Enable Amazon Link Auto Tagger',
				'When enabled, existing Amazon links will automatically convert to tagged referral links',
				'pmc_amazon_auto_tagger_status',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new \CheezCapTextOption(
				'Amazon Tag',
				'Enter the amazon tag.',
				'pmc_amazon_auto_tagger_tag',
				''
			),
		);

		$cheezcap_groups[] = new $cheezcap_group_class( "Amazon Auto Tagger", "pmc_amazon_auto_tagger", $cheezcap_options );

		return $cheezcap_groups;

	}

	/*
	 * Render the javascript tag based on the cheezcap setting
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Javier Martinez PMCVIP-91
	 * @version 2015-12-17 Archana Mandhare PMCVIP-697
	 *
	 */
	public function get_javascript() {

		$pmc_cheezcap         = PMC_Cheezcap::get_instance();
		$this->_tag           = $pmc_cheezcap->get_option( 'pmc_amazon_auto_tagger_tag' );
		$this->_plugin_status = $pmc_cheezcap->get_option( 'pmc_amazon_auto_tagger_status' );

		// Bail?
		if ( ! is_single() || is_admin() || 'enabled' !== strtolower( $this->_plugin_status ) || empty( $this->_tag ) ) {
			return;
		}

		$script_args = array(
			'tag'       => $this->_tag,
			'locale'    => 'US',
			'overwrite' => 'Y',
		);

		$script_args = array_map( 'rawurlencode', $script_args );

		$url = add_query_arg( $script_args, '//wms.assoc-amazon.com/20070822/US/js/auto-tagger.js' );

		?>
		<script type="text/javascript" src="<?php echo esc_url( $url ); ?>"></script>
	<?php
	}
}
