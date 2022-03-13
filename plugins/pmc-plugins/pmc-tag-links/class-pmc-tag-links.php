<?php

/**
 * PMC Tag Links plugin mojo, all in this class
 *
 * @author Amit Gupta
 *
 * @since 0.1
 * @version 2.0
 * @version 3.0
 * @version 3.1
 */

class PMC_Tag_Links {

	/**
	 * @var constant contains the plugin-id to be used as marker/identifier in code of this plugin
	 */
	const plugin_id = "pmc-tag-links";

	/**
	 * @var array contains meta_key names or id prefixes etc. which are set in the constructor
	 */
	private $_settings = array();	//values set in constructor

	/**
	 * Class constructor -> any class setup, any calls to be invoked on init go in here along with any action/filter setup
	 *
	 * @since 0.1
	 * @version 2.0
	 * @version 3.0
	 */
	public function __construct() {
		//set the meta_key names etc
		$this->_settings = array(
			'link_id_prefix' => 'auto-tag_',
		);

		//hook up filter a bit later on each post before its displayed -> to link tag names in post content
		add_filter( 'the_content', array( $this, 'link_tags' ), 12 );
		add_filter( 'the_content', array( $this, 'link_related_posts' ), 12 );

	}

	/**
	 * This function takes in a URL and returns the URL with query & fragment
	 * parts scrubbed off from it
	 *
	 * @since 2.0
	 * @version 2.0
	 * @version 3.1
	 */
	protected function _clean_url( $url ) {
		if( empty( $url ) || ! is_string( $url ) ) {
			return;
		}

		$url_parts = parse_url( $url );

		if( $url_parts === false || ! isset( $url_parts['scheme'] ) || ! isset( $url_parts['host'] ) ) {
			return;
		}

		$url_new = $url_parts['scheme'] . '://' . $url_parts['host'];

		if( isset( $url_parts['path'] ) && ! empty( $url_parts['path'] ) ) {
			$url_new .= '/' . trim( $url_parts['path'], '/' );
		}

		return trailingslashit( $url_new );
	}

	/**
	 * Function takes in a post_ID and returns TRUE if it belongs to a post, else FALSE
	 *
	 * @since 0.4
	 * @version 2.0
	 * @version 3.1
	 */
	protected function _is_post( $post_id ) {
		$post_id = intval( $post_id );

		if( $post_id < 1 || get_post_type( $post_id ) !== 'post' ) {
			return false;
		}

		return true;
	}

	/**
	 * Called by 'the_content' filter, it receives a post's content before its displayed,
	 * grabs the tags assigned to post, links them to their archive URLs and returns
	 * the content.
	 *
	 * @since 0.1
	 * @version 2.0
	 * @version 3.0
	 */
	public function link_tags( $content ) {
		if( empty( $content ) ) {
			return $content;
		}

		if( apply_filters( 'pmc_tag_links_enabled', true ) == false ) {
			//plugin disabled for current request, bail out
			return $content;
		}

		global $post;

		if( ! isset( $post ) || empty( $post ) || ! isset( $post->ID ) || ! $this->_is_post( $post->ID ) ) {
			return $content;
		}

		$tags = get_the_tags( $post->ID );

		if( empty( $tags ) ) {
			return $content;
		}

		$tag_urls = array();

		foreach( $tags as $tag ) {
			$tag_url = $this->_clean_url( get_tag_link( $tag->term_id ) );

			if( empty( $tag_url ) ) {
				continue;
			}

			if( ! array_key_exists( $tag->slug, $tag_urls ) && stripos( $content, $tag_url ) === false ) {
				$tag_urls[ $tag->slug ] = $tag_url;
			}

			unset( $tag_url );
		}

		$content = $this->_link_tags( $content, $tag_urls );

		return $content;
	}

	/**
	 * called on filter 'the_content' used to add a link to a related post without stepping over
	 * the tag links on the first or second occurence in the content.
	 *
	 * @version 2015-07-22
	 * @since 2015-07-22 Adaeze Esiobu PPT-5079 SEO - Automated linking to related story in WWD posts
	 * @param $content
	 * @return array|string
	 */
	public function link_related_posts( $content ){
		global $post;

		$link_tags_to_related_post = apply_filters( 'pmc_link_tags_to_related_posts', false, false );
		if( $link_tags_to_related_post === true && isset( $post->ID ) ){
			$content = $this->_link_tags_to_related_post( $content, $post->ID );
		}

		return $content;

	}

	/**
	 * Function to run through $content & add the links to the first occurrence of each tag's Name in $tags
	 * $tags can be an array containing tag-slugs or can be a single tag-slug
	 *
	 * @since 0.4
	 * @version 2.0
	 * @version 3.0
	 * @version 3.1
	 */
	protected function _link_tags( $content, $tag_urls = array() ) {
		if( empty( $content ) || empty( $tag_urls ) || ! is_array( $tag_urls ) ) {
			return $content;
		}

		/**
		 * Static variable to keep count of number of time tag has been linked.
		 * Key of array will consider as tag slug.
		 */
		static $auto_tagging_counter = [];

		$original_content = $content;

		foreach( $tag_urls as $slug => $url ) {
			//check if tag is linked
			if( strpos( $content, $url ) !== false ) {
				//tag link already exists, skip to next iteration
				continue;
			}

			//fetch the tag object
			$the_tag = get_term_by( 'slug', $slug, 'post_tag' );

			if( empty( $the_tag ) ) {
				//tag doesn't exist in DB, skip to next iteration
				continue;
			}

			//allow a site to skip first occurrence of Tag Name in post content if its also
			//first word in the post content
			$skip_first_occurrence = apply_filters( 'pmc_tag_links_skip_first_occurrence', false, $the_tag );
			$skip_first_occurrence = ( $skip_first_occurrence === true ) ? true : false;	//this can only be a boolean

			$pos = stripos( $content, $the_tag->name );		//position of first occurrence in HTML
			$content_start = '';	//text before actual word at first occurrence - lets keep it empty for now
			$word_at_pos = '';		//actual word at first occurrence - lets keep it empty for now

			//if first occurrence of tag is to be skipped and tag is first word in content and
			//tag is present more than once in content then skip its first occurrence so that
			//next occurrence can be linked
			if(
				$skip_first_occurrence === true && stripos( strip_tags( $content ), $the_tag->name ) === 0 &&
				substr_count( strtolower( $content ), strtolower( $the_tag->name ) ) > 1
			) {
				$word_at_pos = substr( $content, $pos, strlen( $the_tag->name ) );

				$content = explode( $word_at_pos, $content );
				$content_start = array_shift( $content );

				$content = implode( $word_at_pos, $content );
			}

			/**
			 * NOTE about regex below.
			 * Regex uses Negative Lookbehind of `A photo posted by TAG` (where TAG is the tag name)
			 * because it messes up Instagram embeds and they do not render.
			 * So basically, we want to match `TAG` but not `A photo posted by TAG`.
			 * Example if the tag name is `Cara Delevingne`, `A photo posted by Cara Delevingne`
			 * will not match, but `Cara Delevingne` surrounded by other words will.
			 *
			 * Also don't link `TAG` word contained in h1 to h6 tags.
			 */
			$tag_links_pattern = sprintf( '/(\b(?<!A photo posted by )%s\b(?![^><]*?(?:>|<\/a|<\/h1|<\/h2|<\/h3|<\/h4|<\/h5|<\/h6|<\/script)))/im', preg_quote( $the_tag->name, '/' ) );
			$pattern           = apply_filters( 'pmc_tag_links_pattern', $tag_links_pattern, $the_tag->name );

			$link_id = $this->_settings['link_id_prefix'] . $slug;

			// Make sure that, Page don't have same id multiple time.
			if ( ! isset( $auto_tagging_counter[ $slug ] ) ) {
				$auto_tagging_counter[ $slug ] = 0;
			} else {
				$auto_tagging_counter[ $slug ] += 1;

				$link_id .= '_' . $auto_tagging_counter[ $slug ];
			}

			$replace  = '<a href="' . esc_url( $url ) . '" id="' . esc_attr( $link_id ) . '" ';
			$replace .= 'data-tag="' . esc_attr( $slug ) . '">$1</a>';

			$content = $content_start . $word_at_pos . preg_replace( $pattern, $replace, $content, 1 );

			unset( $replace, $pattern, $the_tag );
			unset( $word_at_pos, $content_start, $pos, $skip_first_occurrence );
		}

		return $content;
	}

	/**
	 * Link the second occurrence of a tag in the post content
	 * to a related post. Since an LOB might choose to skip the first occurrence of a tag in a post to link to the tag page
	 * we need to make sure we don't step over that functionality. first we have to determine if we are replacing the second
	 * occurrence of a tag or the third.
	 *
	 * Tags within HTML comments <!-- disable-pmc_link_tags_to_related_posts-starts --> and <!-- disable-pmc_link_tags_to_related_posts-ends --> will be ignored by tag linking.
	 *
	 * @since 2016-09-13 - Mike Auteri - Added HTML comments to disable tag linking in parts of content.
	 *
*@param $content
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function _link_tags_to_related_post( $content, $post_id ){

		if( empty( $content ) || empty( $post_id ) ) {
			return $content;
		}

		$tags = get_the_tags( $post_id );

		if( !is_array( $tags ) ){
			return $content;
		}

		$disable_starts = '<!-- disable-pmc_link_tags_to_related_posts-starts -->';
		$disable_ends = '<!-- disable-pmc_link_tags_to_related_posts-ends -->';

		//need to iterate through all the tags we have . and see which one is in the content.
		foreach( $tags as $tag_object ) {
			$tag_name = $tag_object->name;
			$tag_slug = $tag_object->slug;

			// since we only want one occurrence to be linked, we need to have a check.
			$tag_check = false;

			//check if tag is in content
			if( stripos( $content, $tag_name ) !== false  ) {
				$related_articles = $this->_get_related_posts_by_tag( array( $tag_object ) );
				$counter = 0;

				$related_link = '';

				while( empty( $related_link ) && $counter < count( $related_articles ) ){
					if( isset( $related_articles[ $counter ]->ID ) ){
						$related_link = get_permalink( $related_articles[ $counter ]->ID );
						$current_permalink = get_permalink( $post_id );
						if( stripos( $content, $related_link ) !== false || $related_link == $current_permalink ){
							$related_link = '';
						}
					}
					$counter++;
				}

				if ( empty( $related_link ) ) {
					continue;
				}
				// the tag is in the post. now we need to find out if it is the second occurrence of that tag or the first
				// allow a site to skip first occurrence of Tag Name in post content if its also
				// first word in the post content
				$tag_skipped_first_occurrence = apply_filters( 'pmc_tag_links_skip_first_occurrence', false, $tag_object );

				$tag_skipped_first_occurrence = ( $tag_skipped_first_occurrence === true );	//this can only be a boolean

				// Escape any forward slashes in the tag name
				// On WWD the tag 'Y/Project' caused the following
				// preg_match_all to treat the P as a regex modifier
				// and displayed a warning in the admin (though the regex still worked)
				$escaped_tag_name = str_replace( '/', '\/', $tag_name );

				preg_match_all("/".$escaped_tag_name."/", $content, $matches, PREG_OFFSET_CAPTURE);

				$pos = $matches[0][0][1];		// position of first occurrence in HTML
				// if first occurrence of tag is to be skipped and tag is first word in content and
				// tag is present more than twice in content then skip its two occurrence so that
				// third occurrence can be linked
				if(
					$tag_skipped_first_occurrence === true && stripos( strip_tags( $content ), $tag_name ) === 0 &&
					count( $matches[0] ) > 2
				) {
					$pos = $matches[0][2][1]; // third occurrence
				}
				// skip first location of tag cos there will be a tag link on it.
				$word_at_pos = substr( $content, $pos, strlen( $tag_name ) );

				$content_array = explode( $word_at_pos, $content );

				$content_start = array_shift( $content_array );

				$content = implode( $word_at_pos, $content_array );

				// If disabling HTML comment is in $content_start, we need to prepend it to the rest of $content.
				if ( false !== strpos( $content_start, $disable_starts ) && false === strpos( $content_start, $disable_ends ) ) {
					$content = $disable_starts . $content;
				}

				/**
				 * NOTE about regex below.
				 * Regex uses Negative Lookbehind of `A photo posted by TAG` (where TAG is the tag name)
				 * because it messes up Instagram embeds and they do not render.
				 * So basically, we want to match `TAG` but not `A photo posted by TAG`.
				 * Example if the tag name is `Cara Delevingne`, `A photo posted by Cara Delevingne`
				 * will not match, but `Cara Delevingne` surrounded by other words will.
				 *
				 * BR-212: Fix bug in pattern with nested tags: https://regex101.com/r/6GKD9Z/3/
				 */
				$pattern = sprintf( '/(\b(?<!A photo posted by )%s\b(?![^><]*?(?:>|(<\/.*>)?<\/a>)))/im', preg_quote( $tag_name, '/' ) );

				$replace = '<a href="' . esc_url( $related_link ) . '" id="' . esc_attr('related_article_link_' . $tag_slug ) . '" ';

				$replace .= 'data-tag="' . esc_attr( $tag_slug ) . '">$1</a>';

				$content_array = explode( $disable_starts, $content );

				// count will be 1 if there is no occurrences of the HTML comment.
				// if no occurrence, else just puts it back as a string and runs preg_match
				// if there IS an occurrence, we explode on the closing HTML comment and exclude any content within.
				// we only want to link ONE occurrence of the tag we find, so we use $tag_check to know when to bail.
				if ( 1 < count( $content_array ) ) {
					foreach ( $content_array as $key => $sections ) {
						$sections = explode( $disable_ends, $sections );
						if ( ! $tag_check ) {

							switch( count( $sections ) ) {
								case 1:
									$index = 0;
									break;
								case 2:
									$index = 1;
									break;
								default:
									$index = false;
							}

							if ( false !== $index ) {
								if ( preg_match( $pattern, $sections[ $index ] ) ) {
									$sections[ $index ] = preg_replace( $pattern, $replace, $sections[ $index ], 1 );
									$tag_check = true;
								}
							}
						}

						$content_array[ $key ] = implode( $disable_ends, $sections );

						if ( $tag_check ) {
							break;
						}
					}
					$content = implode( $disable_starts, $content_array );

					// If we added the disable_start to start of content, we need to remove it now.
					if ( false !== strpos( $content_start, $disable_starts ) && false === strpos( $content_start, $disable_ends ) ) {
						$content = substr( $content, strlen( $disable_starts ) );
					}
				} else {
					$content = current( $content_array );
					$content = preg_replace( $pattern, $replace, $content, 1 );
				}
				$content = $content_start . $word_at_pos . $content;
			}
		}

		return $content;

	}

	/**
	 * helper function to generate a related post to a tag.
	 * @since 2015-07-22 Adaeze Esiobu - PPT-5079 SEO - Automated linking to related story in WWD posts
	 * @param $term
	 * @return array
	 */
	private function _get_related_posts_by_tag( $term ){

		$args = array(
			'posts_per_page'   => 5,
			'post_status' => 'publish',
			'no_found_rows' => true,
		);
		$args['tax_query'] = [
			[
				'taxonomy' => 'post_tag',
				'terms' =>wp_list_pluck( array_values( $term ), 'term_id'),
				'include_children' => false,
			]

		]; //VIP: do not include children in related post query

		$query = new WP_Query( $args );

		$posts = $query->get_posts();

		return $posts;

	}

//End of Class
}


//EOF
