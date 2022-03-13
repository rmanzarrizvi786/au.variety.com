<?php
/**
 * This class adds functionality to admin feed option newsbreak ga.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Newsbreak {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_custom_feed_options_toggles', [ $this, 'add_newsbreak_feed_option' ] );
		add_action( 'rss2_ns', [ $this, 'add_newsbreak_namespace' ] );
	}

	/**
	 * Hook callback to add feed option.
	 *
	 * @param array $feed_options
	 * @return array
	 */
	public function add_newsbreak_feed_option( $feed_options = [] ) : array {
		$feed_options['newsbreak-ga'] = 'Newsbreak GA';

		return $feed_options;
	}

	/**
	 * Hook callback to add namespacing.
	 */
	public function add_newsbreak_namespace() {
		$feed_options = \PMC_Custom_Feed::get_instance()->get_feed_config();

		if ( is_feed() && ! empty( $feed_options['newsbreak-ga'] ) ) {
			echo 'xmlns:nb="https://www.newsbreak.com/"' . PHP_EOL;
		}
	}

	/**
	 * Renders at the end of item nodes for News Break on rss2 feed template.
	 */
	public static function add_ga_tags_to_newsbreak_feeds() {

		$feed_options  = \PMC_Custom_Feed::get_instance()->get_feed_config();
		$ga_id         = apply_filters( 'pmc_custom_feed_newsbreak_ga_id', get_option( 'pmc_google_analytics_account' ) );
		$permalink_rss = apply_filters( 'the_permalink_rss', get_permalink() );

		if ( is_feed() && ! empty( $feed_options['newsbreak-ga'] ) && ! empty( $ga_id ) ) {
			?>
			<nb:scripts><![CDATA[
				<script>
					(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
					ga('create', decodeURIComponent( '<?php echo rawurlencode( (string) $ga_id ); ?>' ), 'auto');
					ga('require', 'displayfeatures');
					ga('set', 'checkProtocolTask', null);
					ga('set', 'checkStorageTask', null);
					ga('set', 'referrer', 'https://www.newsbreak.com/');
					ga('set', 'campaignName', 'News Break');
					ga('set', 'campaignSource', 'News Break');
					ga('set', 'campaignMedium', 'Article');
					ga('set', 'page', <?php echo wp_json_encode( $permalink_rss ); ?>);
					ga('set', 'title', decodeURIComponent( '<?php echo rawurlencode( (string) get_the_title() ); ?>' ) );
					ga('send', 'pageview' );
				</script>
				<script type="text/javascript">
					var _comscore = _comscore || [];
					_comscore.push({ c1: "2", c2: "6035310", c4: <?php echo wp_json_encode( $permalink_rss ); ?>, c9: "newsbreak.com", comscorekw: "newsbreak" });
					(function() {
						var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
						s.src = "https://sb.scorecardresearch.com/cs/6035310/x-beacon.js";
						el.parentNode.insertBefore(s, el);
					})();
				</script>
				]]>
			</nb:scripts>
			<?php
		}
	}

}

PMC_Newsbreak::get_instance();
