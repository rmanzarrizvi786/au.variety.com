<?php
global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

$pmc_custom_feed_qs = null;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:amzn="https://amazon.com/ospublishing/1.0/"
>

<channel>
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) . get_wp_title_rss() ) ); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<amzn:rssVersion>1.0</amzn:rssVersion>
<?php
	foreach( $posts as $post ) :

		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

		if ( empty( $post ) ) {
			continue;
		}
		
		$products = PMC_Custom_Feed_Amazon_Deals::get_instance()->get_post_amazon_products( $post->ID );

		$cap = apply_filters( 'pmc_custom_feed_amazon_deals_product_cap', 2 );
		// Post shouldn't be included if < 2 products.
		if ( count( $products ) < intval( $cap ) ) {
			continue;
		}

		$intro_text = get_post_meta( $post->ID, 'amazon_intro_text', true );
		if ( empty( $intro_text ) ) {
			$intro_text = $post->post_excerpt;
		}

		setup_postdata($post);

?>
	<item>
		<title><?php echo get_the_title_rss() ?></title>
		<?php PMC_Custom_Feed_Helper::render_rss_author(); ?>
		<pubDate><?php echo wp_kses_post( apply_filters( 'pmc_custom_feed_amazon_deals_publish_date', get_post_time( 'D, d M Y H:i:s +0000', true, $post ) ) ); ?></pubDate>
		<?php do_action( 'pmc_custom_feed_item', $post, $feed_options ); ?>
		<link><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink() ); ?></link>
		<?php
		$image = PMC_Custom_Feed_Helper::get_featured_or_first_image_in_post( $post->ID );
		if ( ! empty( $image['url'] ) ) : ?>
			<amzn:heroImage><?php echo esc_url( $image['url'] ); ?></amzn:heroImage>
			<?php if ( ! empty( $image['caption'] ) ) : ?>
				<amzn:heroImageCaption><?php echo wp_kses_post( apply_filters( 'pmc_custom_feed_amazon_deals_heroimagecaption', $image['caption'], $image ) ); ?></amzn:heroImageCaption>
			<?php endif;
		endif;
		$intro_text = apply_filters( 'pmc_custom_feed_amazon_deals_introtext', $intro_text, $post->ID );

		$intro_text = wp_strip_all_tags( strip_shortcodes( $intro_text ) );

		if ( ! empty( $intro_text ) ) :
			printf( '<amzn:introText>%s</amzn:introText>', PMC_Custom_Feed_Helper::esc_xml_cdata( $intro_text ) );
		endif; ?>
		<amzn:indexContent><?php echo esc_attr( apply_filters( 'pmc_custom_feed_amazon_deals_indexcontent', 'False' ) ); ?></amzn:indexContent>
		<amzn:products>
		<?php foreach ( $products as $product ) : ?>
			<amzn:product>
				<amzn:productURL><?php echo esc_url( $product['url'] ); ?></amzn:productURL>
				<?php if ( ! empty( $product['headline'] ) ) : ?>
					<amzn:productHeadline><![CDATA[<?php echo wp_kses_data( wp_strip_all_tags( html_entity_decode( $product['headline'] ) ) ); ?>]]></amzn:productHeadline>
				<?php endif; ?>
				<?php if ( ! empty( $product['summary'] ) ) : ?>
					<amzn:productSummary><![CDATA[<?php echo wp_kses_data( wp_strip_all_tags( html_entity_decode( $product['summary'] ) ) ); ?>]]></amzn:productSummary>
				<?php endif; ?>
				<?php if ( ! empty( $product['rank'] ) ) : ?>
					<amzn:rank><?php echo (int) $product['rank']; ?></amzn:rank>
				<?php endif; ?>
				<?php if ( ! empty( $product['award'] ) ) : ?>
					<amzn:award><![CDATA[<?php echo wp_kses_post( $product['award'] ); ?>]]></amzn:award>
				<?php endif; ?>
			</amzn:product>
		<?php endforeach; ?>
		</amzn:products>
		<content:encoded><?php
			$content = apply_filters( 'pmc_custom_feed_amazon_deals_content_encoded', get_the_content(), $post->ID );
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			$content = apply_filters( 'the_content_feed', $content, 'rss2' );
			$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
			echo \PMC_Custom_Feed_Helper::esc_xml_cdata( $content );
        ?></content:encoded><?php rss_enclosure(); ?>
	</item>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	wp_reset_postdata();
	endforeach; ?>
</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
