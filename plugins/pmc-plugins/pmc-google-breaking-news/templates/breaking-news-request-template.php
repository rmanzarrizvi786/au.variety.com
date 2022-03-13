<?php
/**
 * This is the Request template sent to Google API for Indexing
 */
setup_postdata( $post );

$more_news_xml_url = home_url( '/sitemap_index.xml' );
echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ). '"?' . '>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:inline="http://www.google.com/schemas/atom-inline/1.0">
	<id><?php echo esc_url( $more_news_xml_url ); ?></id>
	<title><?php echo esc_html( 'Breaking News' );?></title>
	<author><name><?php the_author(); ?></name></author>
	<updated><?php echo mysql2date( 'Y-m-d\TH:i:s\Z', get_lastpostmodified( 'GMT' ), false ); ?></updated>
	<entry>
		<id><?php echo esc_url( $amp_url ); ?></id>
		<updated><?php echo mysql2date( 'Y-m-d\TH:i:s\Z', get_lastpostmodified( 'GMT' ), false ); ?></updated>
		<title><?php echo esc_html( get_the_title( $post->ID ) ); ?></title>
		<link rel="alternate" type="text/html" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" />
		<link rel="amphtml" href="<?php echo esc_url( $amp_url ); ?>" >
			<inline:inline type="text/html">
			<![CDATA[
				<?php
					// This content has already been escaped during creation by the WPCOM AMP plugin.
					echo $content_safe;
				?>
			]]>
			</inline:inline>
		</link>
	</entry>
</feed>
<?php

// EOF