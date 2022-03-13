<?php

/**
 * Feed options/taxonomy related
 */

use \PMC\Global_Functions\Traits\Singleton;

final class PMC_Custom_Feed_Taxonomy {

	use Singleton;

	private $_taxonomies = array(
		'reuters-feed'                                   => 'Reuters feed',
		'msfeed'                                         => 'MS Custom Feed',
		'most-popular-posts'                             => 'Most popular posts',
		'one-sentence-excerpt'                           => 'One sentence excerpt',
		'text-only'                                      => 'Text only',
		'disable-autoembed'                              => 'Disable autoembed',
		'allow-known-embeds'                             => 'Allow known embeds',
		'related-links'                                  => 'Related links',
		'show-post-type'                                 => 'Show post type',
		'disable-autotag'                                => 'Disable auto tagging',
		'use-full-size-images'                           => 'Use Full Size Images',
		'add-linked-gallery-link'                        => 'Add Linked Gallery Link',
		'use-title-override'                             => 'Use Title Override',
		'extract-embeds'                                 => 'Extract embeds into media:embeds node',
		'reuters-curated-posts'                          => 'Reuters: Curated Posts',
		'featured-media-title'                           => 'Featured Image Media Title',
		'overwrite-featured-media-title-with-caption'    => 'Overwrite Featured Image Media Title with Caption',
		'replace-categories-with-site-name'              => 'Replace Categories with Site Name',
		'external-urls-link-to-source-post'              => 'External URLs: Link to Source Post',
		// @TODO: SADE-517 to be removed
		'external-urls-whitelist-only'                   => 'External URLs: Whitelist Only',
		'external-urls-allowlist-only'                   => 'External URLs: Allow List Only',
		'external-urls-strip-all'                        => 'External URLs: Strip All',
		'append-featured-video'                          => 'Append Featured Video',
		'feed-title-site-name'                           => 'Only Site Name in Feed Title',
		'add-feed-logo'                                  => 'Add Feed Logo',
		'smartnews-related-links'                        => 'Smartnews: Related Links',
		'auto-tag-affiliate-links'                       => 'Auto Tag Affiliate Links',
		'strip_related_links'                            => 'Strip Related Links',
		'disable-https-rewriting-of-links'               => 'Disable HTTPS Rewriting of Links',
		'enable-protected-iframe-embeds'                 => 'Enable Protected Iframe Embeds in Instant Articles',
		'move-post-media-to-media-content-nodes'         => 'Move post media to media:content nodes',
		'move-gallery-media-to-media-content-nodes'      => 'Move gallery media to media:content nodes',
		'move-related-links-to-the-middle-of-story'      => 'Move Related Links to the Middle of Story',
		'exclude-media-content-items-missing-copyrights' => 'Exclude media:content items missing copyrights',
		'msn-syndication-rights'                         => 'MSN Syndication Rights',
		'strip-inline-images'                            => 'Strip inline images',
		'strip-all-images'                               => 'Strip All Images',
		'modify-youtube-iframe'                          => 'Modify YouTube iframe',
		'use-seo-title'                                  => 'Use SEO Title',
		'send-paginated-content'                         => 'Send the entire post content if it is paginated',
		'truncate-post-content'                          => 'Truncate post content',
		'convert-html-entities'                          => 'Convert Html Entities',
		'is-pmc-maz'                                     => 'Is PMC maz feed',
		'is-google-newsstand-gallery'                    => 'Is Google Newsstand Gallery',
		'include-best-of-brand-articles'                 => 'Include Best of Brand (Evergreen) Articles',
		'sort-by-last-modified'                          => 'Sort by Last Modified',
		'msn-hide-image-title'                           => 'MSN Feeds - Hide Image Title',
		'msn-hide-media-content-description'             => 'MSN Feeds - Hide Media Content Description',
		'add-article-link-on-top'                        => 'Add "Click To Read Article" - To Top of Post',
		// This option is intended for international licensees who need to see future posts
		// in order to have them translate before publication.
		'show-future-posts-only'                         => 'Only show future posts',
		'override-caption-shortcode-removal'             => 'Override Caption Shortcode Removal',
		'censor-curse-words'                             => 'Censor Curse Words',
		'include-featured-video-in-content'              => 'Include Featured Video In Content',
		'include-featured-video-after-para-1'            => 'Include Featured Video in After Paragraph 1',
		'add-article-link-to-middle'                     => 'Add "Click To Read Article" - Middle of Article',
		'add-article-link-to-bottom'                     => 'Add "Click To Read Article" - Bottom of Article',
		'paywall-bypass-pmpro'                           => 'Paywall Bypass - PMPRO',
		'remove-cont-img-width-height-attributes'        => 'Remove Content Image Width & Height Attributes',
		'msn-feeds-button-styling'                       => 'MSN Feeds - Add Button Styling',
		'remove-feed-img-srcset'                         => 'Remove srcset attribute from img tag',
	);

	function __construct() {
		// we want to run init function before all taxonomy have a chance to register
		add_action( 'admin_init', array( $this, 'action_admin_init' ), 11 );
	}

	public function action_admin_init() {

		// Filter the list of taxonomy terms
		$this->_taxonomies = apply_filters( 'pmc_custom_feed_options_toggles', $this->_taxonomies );

		// add taxonomy entries if not exists
		PMC_Custom_Feed::get_instance()->add_taxonomy_term_if_not_exist( $this->_taxonomies );
	}

}

PMC_Custom_Feed_Taxonomy::get_instance();

// EOF
