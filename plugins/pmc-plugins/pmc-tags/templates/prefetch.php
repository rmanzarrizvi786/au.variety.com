<?php

printf( '<meta http-equiv="x-dns-prefetch-control" content="on">' );

$dns_domains = apply_filters( \PMC\Tags\Tags::TAGS_FILTER_PREFETCH_DOMAINS, [
	'//aax.amazon-adsystem.com',
	'//ajax.googleapis.com',
	'//apiservices.krxd.net',
	'//beacon.krxd.net',
	'//boygeniusreport.files.wordpress.com',
	'//cdn.krxd.net',
	'//cdn.syndication.twimg.com',
	'//disqus.com',
	'//edge.quantserve.com',
	'//googleads.g.doubleclick.net',
	'//i0.wp.com',
	'//i1.wp.com',
	'//i2.wp.com',
	'//load.instinctiveads.com',
	'//load.s3.amazonaws.com',
	'//o.twimg.com',
	'//p.skimresources.com',
	'//pagead2.googlesyndication.com',
	'//pbs.twimg.com',
	'//pixel.quantserve.com',
	'//pmcdeadline2.files.wordpress.com',
	'//pmcfootwearnews.files.wordpress.com',
	'//pmchollywoodlife.files.wordpress.com',
	'//pmcvariety.files.wordpress.com',
	'//r.skimresources.com',
	'//s.gravatar.com',
	'//s.skimresources.com',
	'//s0.wp.com',
	'//securepubads.g.doubleclick.net',
	'//stats.wordpress.com',
	'//summits.wwd.com',
	'//ton.twimg.com',
	'//tpc.googlesyndication.com',
	'//www.google-analytics.com',
	'//www.googletagmanager.com',
	'//securepubads.g.doubleclick.net',
	'//www.youtube.com',
	'//x.skimresources.com',
] );

if ( is_array( $dns_domains ) ) {
	foreach ( $dns_domains as $domain ) {
		if ( ! empty( $domain ) ) {
			printf(
				'<link rel="dns-prefetch" href="%1$s"/>',
				esc_url( $domain )
			);
		}
	}
}
