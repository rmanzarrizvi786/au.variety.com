<?php
if ( empty( $no_header ) ) {
	header( 'Cache-Control: no-cache, must-revalidate' );
	header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
}

$defaults = [
	'subject'    => '',
	'thumbs'     => [ 'height' => '', 'width' => '', ],
	'feat_thumb' => [ 'height' => '', 'width' => '', ],
	'posts'      => [],
];

$data = array_merge( $defaults, $data );

// Merge featured post
if ( ! empty( $data['featured_post'] ) ) {
	$data['featured_post']['is_featured'] = true;
	array_unshift( $data['posts'], $data['featured_post'] );
}

if ( ! empty( $data['meta_data'] ) ) {
	foreach( $data['meta_data'] as $k => $special_node ){
		$special_node['is_special'] = true;
		array_unshift( $data['posts'], $special_node );
	}
}

echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?' . '>';
?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
     xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title><?php bloginfo_rss( 'name' ); wp_title_rss(); ?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml"/>
		<link><?php bloginfo_rss( 'url' ) ?></link>
		<description><?php bloginfo_rss( "description" ) ?></description>
		<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
		<subject><?php echo wp_kses_post( $data['subject'] ); ?></subject>
		<thumbs height="<?php echo intval( $data['thumbs']['height'] ); ?>" width="<?php echo intval( $data['thumbs']['width'] ); ?>"/>
		<feat_thumb height="<?php echo intval( $data['feat_thumb']['height'] ); ?>" width="<?php echo intval( $data['feat_thumb']['width'] ); ?>"/>
<?php
		do_action( 'rss2_head' );

		foreach ( $data['posts'] as $post ) :
			if ( empty( $post ) ) {
				continue;
			}

			$defaults = [
				'thumb'             => '',
				'permalink'         => '',
				'author'            => '',
				'categories'        => [],
				'verticals'         => [],
				'editorials'        => [],
				'primary_category'  => [ 'link' => '', 'name' => '' ],
				'primary_vertical'  => [ 'link' => '', 'name' => '' ],
				'primary_editorial' => [ 'link' => '', 'name' => '' ],
				'pubDate'           => '',
				'excerpt'           => '',
				'content'           => '',
			];

			$post = array_merge( $defaults, (array) $post );

			$fields = [ 'categories', 'verticals', 'editorials' ];
			foreach( $fields as $field ) {
				if ( ! empty( $post[ $field ] ) ) {
					$post[ $field ] = implode(',', wp_list_pluck( $post[ $field ], 'name' ) );
				} else {
					$post[ $field ] = '';
				}
			}

			if ( ! empty( $post['ID'] ) ) {
				$post['pubDate'] = get_post_time( 'D, d M Y H:i:s +0000', true, $post['ID'] );
			}

			$node = 'item';
			if ( ! empty( $post['is_featured'] ) ) {
				$node = 'itemfeatured';
			}

			if( ! empty( $post['is_special'] ) ){
				$node = 'itemspecial';
			}

?>
		<<?php echo tag_escape( $node ); ?>>
			<guid isPermaLink="false"><?php echo esc_url( $post['permalink'] ); ?></guid>
			<title><![CDATA[<?php echo html_entity_decode( wp_kses_post( $post['title'] ) ); ?>]]></title>
			<link><?php echo esc_url( $post['permalink'] );  ?></link>
			<dc:creator><![CDATA[<?php echo esc_html( $post['author'] ); ?>]]></dc:creator>
			<pubDate><?php echo esc_html( $post['pubDate'] ); ?></pubDate>
			<primary_category type="primary" url="<?php echo esc_url( $post['primary_category']['link'] ); ?>"><![CDATA[<?php echo esc_html( $post['primary_category']['name'] ); ?>]]></primary_category>
			<category><![CDATA[<?php echo esc_html( $post['categories'] ); ?>]]></category>
			<primary_editorial type="primary" url="<?php echo esc_url( $post['primary_editorial']['link'] ); ?>"><![CDATA[<?php echo esc_html( $post['primary_editorial']['name'] ); ?>]]></primary_editorial>
			<editorial><![CDATA[<?php echo esc_html( $post['editorials'] ); ?>]]></editorial>
			<primary_vertical type="primary" url="<?php echo esc_url( $post['primary_vertical']['link'] ); ?>"><![CDATA[<?php echo esc_html( $post['primary_vertical']['name'] ); ?>]]></primary_vertical>
			<vertical><![CDATA[<?php echo esc_html( $post['verticals'] ); ?>]]></vertical>
			<media:thumbnail url="<?php echo esc_url( $post['thumb'] ); ?>"><?php echo esc_url( $post['thumb'] ); ?></media:thumbnail>
			<description><![CDATA[<?php echo wp_kses_post( $post['excerpt'] ); ?>]]></description>
			<content:encoded><![CDATA[<?php echo wp_kses_post( $post['content'] ); ?>]]></content:encoded>
			<?php do_action( 'sailthru_rss2_item', $post ); ?>
		</<?php echo tag_escape( $node ); ?>>
<?php
		endforeach;
?>
	</channel>
</rss>
