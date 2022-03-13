<?php

define( 'PMC_CUSTOM_FEED_V2_DIR', untrailingslashit( __DIR__ ) );
define( 'PMC_CUSTOM_FEED_V2_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

require_once __DIR__ . '/dependencies.php';

require_once( __DIR__ . '/class-pmc-custom-feed-curated-posts.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-popular-posts.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-related-links.php' );
require_once( __DIR__ . '/class-pmc-custom-feed.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-msn.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-helper.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-amazon-deals.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-reuters.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-ms.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-allow-embed.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-taxonomy.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-features.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-strip-shortcodes.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-extract-embeds.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-smartnews.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-facebook-instant-articles.php' );
require_once( __DIR__ . '/class-pmc-custom-feed-yahoo.php' );

// We need these to manually include until our auto loader be able to auto load plugin with version number info
require_once( __DIR__ . '/classes/class-lists.php' );
require_once( __DIR__ . '/classes/class-google-newsstand.php' );
require_once( __DIR__ . '/classes/class-rss2-hearst.php' );
require_once( __DIR__ . '/classes/class-pmc-censor.php' );
require_once( __DIR__ . '/classes/class-pmc-censor-feed.php' );
require_once( __DIR__ . '/classes/class-pmc-msn-year-in-review.php' );
require_once( __DIR__ . '/classes/class-pmc-newsbreak.php' );
require_once( __DIR__ . '/classes/class-pmc-option-inappropriate-for-syndication.php' );
require_once( __DIR__ . '/classes/class-pmc-yahoo-top-deals.php' );
require_once( __DIR__ . '/classes/class-pmc-add-utm-params-feed-links.php' );
require_once( __DIR__ . '/classes/features/class-featured-videos.php' );


if ( defined('WP_CLI') && WP_CLI ) {
	require_once( dirname( __FILE__ ) . '/pmc-custom-feed-cli.php' );
}

PMC_Custom_Feed::get_instance();

//EOF
