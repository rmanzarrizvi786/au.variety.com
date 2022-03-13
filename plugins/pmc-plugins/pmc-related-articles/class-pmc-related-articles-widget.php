<?php

/**
 * Widget for PMC Related Articles
 *
 * @author Amit Gupta
 * @since 2013-01-02
 * @version 2013-01-12 Amit Gupta
 */

class PMC_Related_Articles_Widget extends WP_Widget {

	const widget_id = "pmc_related_articles_widget";	//unique widget ID
	const cache_expiry = 1800;	//30 minutes - widget content cache expiry

	protected $_options = array();	//array that contains widget options
	protected $_default_options = array(	//array that contains default options
		'offset' => 0,
		'limit' => 0,
        'wrapper_template'=>'',
		'template' => '',
		'post_type' => 'post'
	);

	public function __construct() {
		$this->_default_options['limit'] = ( defined('PMC_RELATED_POSTS_MAX_ITEMS') ) ? PMC_RELATED_POSTS_MAX_ITEMS : 20;

		parent::__construct(
			self::widget_id,
			'PMC Related Articles',
			array(
				'description' => '',
			)
		);
	}

	/**
	 * This function returns an array containing names of templates created for
	 * widgets using PMC Templatized Widgets plugin. If there are no such templates
	 * it returns boolean FALSE.
	 */
	protected function _get_templates() {
		$wt_templates = get_posts( array(
			'numberposts' => 60,
			'orderby' => 'modified',
			'order' => 'DESC',
			'post_type' => 'pmc_widget_template',
			'post_status' => 'publish',
			'suppress_filters' => false,
		) );

		if( empty($wt_templates) ) {
			return false;
		}

		$templates = array();
		foreach( $wt_templates as $wt_template ) {
			if( isset( $wt_template->post_name ) && ! empty( $wt_template->post_name ) ) {
				$templates[] = $wt_template->post_name;
			}
		}
		unset( $wt_templates );

		if( empty($templates) ) {
			return false;
		}

		sort($templates);

		return $templates;
	}

	/**
	 * This function renders the form for widget configuration when the widget's
	 * instance is added to a sidebar
	 */
	public function form( $instance ) {
		$title = ( ! empty ( $instance['title'] ) ) ? $instance['title'] : '';
		$limit = ( isset( $instance['limit'] ) ) ? intval( $instance['limit'] ) : $this->_default_options['limit'];
		$offset = ( isset( $instance['offset'] ) ) ? intval( $instance['offset'] ) : $this->_default_options['offset'];
		$template = ( isset( $instance['template'] ) ) ? $instance['template'] : $this->_default_options['template'];
        $wrapper_template = ( isset( $instance['wrapper_template'] ) ) ? $instance['wrapper_template'] : $this->_default_options['wrapper_template'];
		$post_type_saved = ( isset( $instance['post_type'] ) ) ? $instance['post_type'] : $this->_default_options['post_type'];
		$wt_templates = $this->_get_templates();	//get a list of template names

		if( empty($wt_templates) ) {
?>
		<p style="color:#990000;"><strong>
			This widget needs a template created in PMC Templatized Widgets before it can be used.
		</strong></p>
<?php
			return;
		}

		//all ok, print out the configuration form
?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">Number of articles to show:</label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" size="3" />
			<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>">Number of articles to skip:</label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="text" value="<?php echo esc_attr( $offset ); ?>" size="3" />
			<br />
            <label for="<?php echo esc_attr( $this->get_field_id( 'wrapper_template' ) ); ?>">Wrapper Template:</label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'wrapper_template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wrapper_template' ) ); ?>" type="text" value="<?php echo esc_attr( $wrapper_template ); ?>" />
            <br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>">Template to use:</label><br />
			<select id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>">
<?php
		foreach( $wt_templates as $wt_template ) {
?>
				<option value="<?php echo esc_attr( $wt_template ); ?>" <?php selected( $template, $wt_template ); ?>><?php echo esc_html( $wt_template ); ?></option>
<?php
		}
?>
			</select>

			<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>">Post Type:</label><br/>
			<select id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>">
				<?php
				$args = array(
					'public'   => true,
				);

				$post_types = get_post_types( $args );

				foreach ( $post_types as $post_type ) {
					?>
					<option value="<?php echo esc_attr( $post_type ); ?>" <?php selected( $post_type_saved, $post_type ); ?>><?php echo esc_html( $post_type ); ?></option>
				<?php
				}
				?>
			</select>
		</p>
<?php
	}

	/**
	 * This function processes widget options to be saved
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$max_limit = $this->_default_options['limit'];
		$min_offset = $this->_default_options['offset'];
		$wrapper_template = $this->_default_options['wrapper_template'];

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		
		if( isset( $new_instance['limit'] ) ) {
			$new_instance['limit'] = intval( $new_instance['limit'] );
			$instance['limit'] = ( $new_instance['limit'] > $max_limit || $new_instance['limit'] < 1 ) ? $max_limit : $new_instance['limit'];
		}
		if( isset( $new_instance['offset'] ) ) {
			$new_instance['offset'] = intval( $new_instance['offset'] );
			$instance['offset'] = ( $new_instance['offset'] >= $max_limit || $new_instance['offset'] < $min_offset ) ? $min_offset : $new_instance['offset'];
		}
        if( isset( $new_instance['wrapper_template'] ) ) {
            $new_instance['wrapper_template'] =  wp_kses_post( $new_instance['wrapper_template'] );
            $instance['wrapper_template'] =  $new_instance['wrapper_template'];
        }
		if( isset( $new_instance['template'] ) ) {
			$instance['template'] = $new_instance['template'];
		}
		if ( ! empty( $new_instance['post_type'] ) ) {
			$instance['post_type'] = $new_instance['post_type'];
		} else {
			$instance['post_type'] = '';
		}

		return $instance;
	}

	/**
	 * This function outputs the content of the widget
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$this->_options['offset'] = ( isset( $instance['offset'] ) ) ? intval( $instance['offset'] ) : $this->_default_options['offset'];
		$this->_options['limit'] = ( isset( $instance['limit'] ) ) ? intval( $instance['limit'] ) : $this->_default_options['limit'];
        $this->_options['wrapper_template'] = ( isset( $instance['wrapper_template'] ) ) ? $instance['wrapper_template'] : $this->_default_options['wrapper_template'];
		$this->_options['template'] = ( isset( $instance['template'] ) ) ? $instance['template'] : $this->_default_options['template'];
		$this->_options['post_type'] = ( isset( $instance['post_type'] ) ) ? $instance['post_type'] : $this->_default_options['post_type'];
		$this->_options['widget_id'] = ( isset($widget_id) ) ? $widget_id : '';


		if( empty( $this->_options['template'] ) ) {
			return;
		}
		$html          = $this->_get_html( $this->_options['post_type'] );
		$template_html = "";
		if ( ! empty( $this->_options['wrapper_template'] ) ) {
			$wrapper_template = pmc_wt_get_templates( array( 'name' => $this->_options['wrapper_template'] ), 0, 1 );
			if ( ! empty( $wrapper_template[0]->post_content ) ) {
				$template_html = $wrapper_template[0]->post_content;
			}
		}
		//if we have a wrapper template we want to put the generated HTML in wrapper.
		if ( ! empty( $html ) && ! empty( $template_html ) && strpos( $template_html, "%%template%%" ) ) {
			$template = $html;
			$html     = str_replace( "%%template%%", $template, $template_html );
		}

		if ( ! empty( $html ) ) {
			echo $args['before_widget'];
			//if title is set then only render following code
			if( ! empty( $instance['title'] ) ) {
				echo wp_kses_post( $args['before_title'] );
				echo wp_kses_post( $instance['title'] );
				echo wp_kses_post( $args['after_title'] );
			}
			echo $html;
			echo $args['after_widget'];
		}
	}

	/**
	 * This function takes in the template & array containing related posts
	 * and returns the parsed template as HTML
	 */
	protected function _generate_template_html( $template, $related_posts ) {
		if( empty($template) || empty($related_posts) ) {
			return;
		}

		$token_array = array(
			'%%rel%%',
			'%%image%%',
			'%%image_src%%',
			'%%image_width%%',
			'%%image_height%%',
			'%%image_id%%',
			'%%image_alt%%',
			'%%image_title%%',
			'%%link%%',
			'%%link_title%%',
			'%%title%%',
            '%%title_shortview%%',
            '%%title_fullview%%',
			'%%author%%',
			'%%author_id%%',
			'%%post_id%%',
			'%%excerpt%%',
			'%%date%%',
			'%%date_utc_timestamp%%',
			'%%date_iso%%',
			'%%year%%',
			'%%month%%',
			'%%day%%',
			'%%comment_count%%',
            '%%comment_class%%',
			'%%readmore%%',
		);

		$content = "";

		foreach( $related_posts as $post ) {
			$post_id = '';
			$date_iso = '';
			$day = '';
			$month = '';
			$year = '';
			$readmore = 'Read Article';
			if( isset( $post->post_id ) && intval( $post->post_id ) > 0 ) {
				$post_id = intval( $post->post_id );
				$date_iso = esc_attr( get_the_time( 'c', $post_id ) );
				$day = esc_html( get_the_time( 'd', $post_id ) );
				$month = esc_html( get_the_time( 'M', $post_id ) );
				$year = esc_html( get_the_time( 'Y', $post_id ) );

				if( get_post_type( $post_id ) == 'pmc_gallery' ) {
					$readmore = 'View Gallery';
				}
			}

			$date = "";
			if( isset( $post->date ) ) {
				$date = esc_html( $post->date );
			}

			$date_utc_timestamp = "";
			if( isset( $post->date_utc_timestamp ) ) {
				$date_utc_timestamp = intval( $post->date_utc_timestamp );
			}

			$link = "";
			if( isset( $post->link ) ) {
				$link = esc_url( $post->link );
			}

			$title = "";
			$link_title = "";
            $title_shortview="";
            $title_fullview="";
			if( isset( $post->title ) ) {
				$link_title = esc_attr( htmlspecialchars_decode( $post->title ) );
				$title = esc_attr( pmc_truncate( htmlspecialchars_decode( $post->title ), 80 ) );
                $title_shortview = esc_attr( pmc_truncate( htmlspecialchars_decode( $post->title ), 50 ) );
                $title_fullview = esc_attr( pmc_truncate( htmlspecialchars_decode( $post->title ), 100 ) );
			}

			$rel = "";
			if( isset( $post->rel ) ) {
				$rel = esc_attr( $post->rel );
			}

			$image_title = "";
			if( isset( $post->image_title ) ) {
				$image_title = esc_attr( pmc_truncate( $post->image_title, 80 ) );
			}

			$image_alt = "";
			if( isset( $post->image_alt ) ) {
				$image_title = esc_attr( $post->image_alt );
			}

			$image_id = "";
			if( isset( $post->image_id ) ) {
				$image_id = esc_attr( $post->image_id );
			}

			$image_width = 200;
			if( isset( $post->image_width ) && intval( $post->image_width ) > 0 ) {
				$image_width = intval( $post->image_width );
			}
			$image_height = 150;
			if( isset( $post->image_height ) && intval( $post->image_height ) > 0 ) {
				$image_height = intval( $post->image_height );
			}

			$image_src = esc_url( get_template_directory_uri() . "/images/medium-preview-placeholder.jpg" );
			if( isset( $post->image_src ) && ! empty( $post->image_src ) ) {
				$image_src = esc_url( $post->image_src );
			}
			if( ! empty( $image_src ) && function_exists('wpcom_vip_get_resized_remote_image_url') ) {
				if( strpos( $image_src, 'wordpress.com' ) === false && strpos( $image_src, get_template_directory_uri() ) === false ) {
					//external image, resize it
					$image_src_resized = wpcom_vip_get_resized_remote_image_url( $image_src, $image_width, $image_height );
					$image_src = ( empty($image_src_resized) ) ? $image_src : $image_src_resized;
					unset( $image_src_resized );
				}
			}

			$image = "";
            if( empty( $image_src ) ){
                $image_src = get_stylesheet_directory_uri().'/images/related_article_2.jpg';
                $image ='<img src="' . get_stylesheet_directory_uri() . '/images/transparent.png" style="background-image: url(' . esc_url($image_src) . ')" width="200" height="150"';
                $image .= ' alt="'.$image_alt.'" />';
            }else{
                $image ='<img src="' . get_stylesheet_directory_uri() . '/images/transparent.png" style="background-image: url(' . esc_url($image_src) . ')" width="200" height="150"';
                $image .= ' alt="'.$image_alt.'" />';
            }



			$excerpt = "";
			if( isset( $post->excerpt ) && ! empty( $post->excerpt ) ) {
				$excerpt = pmc_truncate( $post->excerpt, 75 );
			}

			$comment_count = "NEW!";
            $comment_class = "comments_new";
			if( isset( $post->comment_count ) && intval($post->comment_count) > 0 ) {
				$comment_count = intval( $post->comment_count );
                $comment_class="comments";
			}

			$author = "";
			if( isset( $post->author ) ) {
				$author = esc_html( $post->author );
			}

			$author_id = "";
			if( isset( $post->author_id ) && intval( $post->author_id ) > 0 ) {
				$author_id = intval( $post->author_id );
			}


			$defaults = array(
				'post_id' => $post_id,
				'wt_template' => $this->_options['template'],
				'rel' => $rel,
				'image_id' => $image_id,
				'image_src' => $image_src,
				'image_alt' => $image_alt,
				'image_width' => $image_width,
				'image_height' => $image_height,
				'image_title' => $image_title,
				'image' => $image,
				'link_title' => $link_title,
				'title' => $title,
                'title_shortview'=>$title_shortview,
                'title_fullview'=>$title_fullview,
				'excerpt' => $excerpt,
				'comment_count' => $comment_count,
                'comment_class' => $comment_class,
				'readmore' => $readmore,
			);

			//allow override on a few things like post/link/image title, excerpt etc
			//which allows flexibility in truncate length etc
			$overrides = apply_filters( 'pmc_related_articles_widget_overrides', array(
				'related_post' => $post,
				'defaults' => $defaults,
			) );

			if( isset( $overrides['defaults'] ) && is_array( $overrides['defaults'] ) ) {
				//just in case there are no filters
				$overrides = $overrides['defaults'];
			}

			$overrides = wp_parse_args( $overrides, $defaults );

			unset( $overrides['wt_template'] );	//don't need this anymore

			//override existing vars
			extract( $overrides );

			$replacement_array = array(
				$rel,
				$image,
				$image_src,
				$image_width,
				$image_height,
				$image_id,
				$image_alt,
				$image_title,
				$link,
				$link_title,
				$title,
                $title_shortview,
                $title_fullview,
				$author,
				$author_id,
				$post_id,
				$excerpt,
				$date,
				$date_utc_timestamp,
				$date_iso,
				$year,
				$month,
				$day,
				$comment_count,
                $comment_class,
				$readmore,
			);

			$content .= str_replace( $token_array, $replacement_array, $template  );

			unset( $replacement_array, $overrides, $defaults, $image );
			unset( $readmore, $comment_count,$comment_class, $day, $month, $year, $date_iso, $date_utc_timestamp );
			unset( $date, $excerpt, $post_id, $author_id, $author, $title, $title_fullview,$title_shortview,$link_title, $link );
			unset( $image_title, $image_alt, $image_id, $image_height, $image_width, $image_src, $rel );
		}

		unset( $token_array );

		return $content;
	}

	/**
	 * This function returns the HTML content for the widget. It returns the HTML
	 * from cache if it exists else it generates the content HTML using associated
	 * template and caches+returns that
	 */
	protected function _get_html( $post_type = "" ) {
		global $post;
		if( ! isset( $post->ID ) || intval( $post->ID ) < 1 ) {
			return;
		}

		$post_id = intval( $post->ID );

		$offset = ( isset( $this->_options['offset'] ) ) ? $this->_options['offset'] : $this->_default_options['offset'];
		$limit = ( isset( $this->_options['limit'] ) ) ? $this->_options['limit'] : $this->_default_options['limit'];

		$cache_key = '_' . $post_id . '-' . $this->_options['widget_id'] . '-' . $offset . '-' . $limit;
		$cache_group = self::widget_id . '-' . $post_type . '_v' . PMC_RELATED_ARTICLES_CACHE_VERSION;

		$html = wp_cache_get( $cache_key, $cache_group );

		if( ! empty( $html ) ) {
			return $html;
		}

		//no cache, gotta fetch related articles & parse template

		//if related articles function or templatized widgets function doesn't exist
		//then bail out
		if( ! function_exists( 'pmc_related_articles' ) || ! function_exists( 'pmc_wt_get_templates' ) ) {
			return;
		}

		$options = array();
		if ( ! empty( $post_type ) ) {
			$options = array( 'post_types' => array( $post_type ) );
		}

		$related_articles = pmc_related_articles( $post_id, $options );
		if ( empty( $related_articles ) ) {
			return;
		}

		$related_articles = array_slice( (array) $related_articles, $offset, $limit, true );

		$html = "";

		$template = pmc_wt_get_templates( array( 'name' => $this->_options['template'] ), 0, 1 );
		if( ! empty( $template ) && isset( $template[0] ) ) {
			$html = $this->_generate_template_html( $template[0]->post_content, $related_articles );
		}

		if( ! empty( $html ) ) {
			wp_cache_set( $cache_key, $html, $cache_group, self::cache_expiry );	//cache the parsed template HTML
		}

		unset( $template, $related_articles, $cache_group, $cache_key, $limit, $offset, $post_id );

		return $html;
	}

//end of class
}

//EOF