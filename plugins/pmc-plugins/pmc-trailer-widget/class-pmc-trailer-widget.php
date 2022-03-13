<?php
/**
 * Latest Videos
 */
class PMC_Trailer_Widget extends WP_Widget {
	private $video_posts = array();

	public function __construct() {
		parent::__construct(
			false, // Base ID
			'PMC - Latest Trailers', // Name
			array(
				'description'   => 'Most recent Trailers from video module.',
				'classname'     => 'video'
			)
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	}

	function wp_enqueue_scripts() {
			wp_enqueue_style( 'pmc_trailer_css', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'pmc_trailer_js', plugins_url( 'js/scripts.js', __FILE__ ) );
	}

	public function widget( $args, $instance ) {
		$query = new WP_Query(array(
			'numberposts' => 3,
			'meta_key' => '_pmc_trailer_data',
			'post_type' => 'pmc-trailer',
		));

		$videos = $this->video_posts = $query->posts;

		if ( empty( $videos ) )
			return;

		add_action( 'wp_footer', array( $this, 'wp_footer' ), 1 );

		extract( $args );

		echo "\n\t\t<!-- video -->";
		echo $before_widget;

		$i = 1;
		foreach( $videos as $video ) {
			$video_data = get_post_meta( $video->ID, '_pmc_trailer_data', true );
			$video_title = get_the_title( $video->ID );
			$video_youtube_link = get_post_meta( $video->ID, '_pmc_trailer_link', true );
            $latest_trailer_title = apply_filters('pmc_trailer_latest_trailer_title','Latest Trailer');

			if( isset( $video_data['link'] ) && !empty( $video_data['link'] ) ){

				//prepare data
				if( !isset( $video_data['thumbnail'] ) ){
					$video_data['thumbnail'] = ''; //change this to use default image later
				}
				if( !isset( $video_data['desc'] ) ){
					$video_data['desc'] = ''; //change this to have default description later
				}
				if( !isset( $video_data['duration'] ) ){
					$video_data['duration'] = 60; //Make it a one min default
				}

				if ( $i == 1 ) {
		?>
					<div class="pmc-trailer">
					<div class="pmc-trailer-banner widgettitle"><h3>Video Gallery</h3></div>
					<div class="video">
						<h2><?php echo $latest_trailer_title; ?></h2>
						<a href="#video-lightbox-<?php echo $video->ID; ?>" class="block button open-popup">
							<img src="<?php if( function_exists( 'wpcom_vip_get_resized_remote_image_url' ) ) {
									echo esc_url(wpcom_vip_get_resized_remote_image_url( $video_data['thumbnail'], 278, 156, true ) );
								}else{
									echo esc_url( $video_data['thumbnail'] ) ;
								}
							?>" alt="<?php echo esc_attr( $video_title ); ?>" width="278" height="156" />
							<span class="btn-play">play</span>
						</a>
						<a href="<?php echo esc_url( $video_youtube_link )?>" class="block button open-popup" target="_blank">
							<span class="text">
								<strong class="title"><?php echo pmc_truncate( $video_title, 90 ); ?></strong>
								<?php echo pmc_truncate( $video_data['desc'], 90 ); ?>
							</span>
						</a>
					</div>
					<div class="more-video">
						<h3>More Videos</h3>
						<div class="holder">
			<?php
				} else {
			?>
							<a href="#video-lightbox-<?php echo $video->ID; ?>" class="block button open-popup">
								<img src="<?php if( function_exists( 'wpcom_vip_get_resized_remote_image_url' ) ){
										echo esc_url(wpcom_vip_get_resized_remote_image_url( $video_data['thumbnail'], 134, 75, true ) );
									}else{
										echo esc_url( $video_data['thumbnail'] ) ;
									}
								?>" width="134" height="75" />
								<span class="btn-play">play</span>
								<span class="text">
									<?php echo pmc_truncate( $video_title, 32 ); ?>
									<strong class="dur">Video (<?php echo floor( $video_data['duration']/60)  . " min " . $video_data['duration'] % 60;  ?> sec)</strong>
								</span>
							</a>
			<?php
				}

				if ( $i == 3 ) {
					echo '
						</div>
					</div>
					';
				}

				$i++;
			}
		}
		?>
		</div>
		<?php
		echo $after_widget;
	}

	public function posts_where( $where ) {
		global $wpdb;
		$seven_ago = date( 'Y-m-d', strtotime( '-7 days' ) );
		$where .= $wpdb->prepare( " AND post_date >= %s", $seven_ago );
		return $where;
	}

	public function embed_oembed_html( $html, $url, $attr ){

		$html = str_replace('width','id="'.$attr['id'].'" class="pmc-video-lightbox" width',$html);

		return $html;
	}

	public function wp_footer() {
		$videos = $this->video_posts;

		add_filter( 'oembed_result', array( $this, 'embed_oembed_html' ), 10, 4 );

		foreach ( $videos as $video ) {
			$video_data = get_post_meta( $video->ID, '_pmc_trailer_data', true );
			$video_youtube_link = get_post_meta( $video->ID, '_pmc_trailer_link', true );

			if( isset( $video_data['link'] ) && !empty( $video_data['link'] ) ){

				if( function_exists( 'wpcom_vip_wp_oembed_get' ) ){
					$oembed_video =  wpcom_vip_wp_oembed_get( $video_youtube_link , array( 'width' => 640, 'height' => 390, 'id'=> 'video-lightbox-iframe-'.$video->ID ) );
				} else {
					$oembed_video = wp_oembed_get( $video_youtube_link, array( 'width' => 640, 'height' => 390, 'id'=> 'video-lightbox-iframe-'.$video->ID ) );
				}
	?>
				<div class='pmc-trailer-widget-lightbox'>
				<div id="video-lightbox-<?php echo $video->ID; ?>" class="lightbox">
					<?php echo $oembed_video; ?>
					<div class="text">
						<h3><?php echo get_the_title( $video->ID ); ?></h3>
						<?php echo apply_filters( 'the_content', $video_data['desc'] ); ?>
					</div>
					<a href="#" class="close">close</a>
				</div>
				</div>
				<?php
			}
		}

		remove_filter( 'oembed_result', array( $this, 'embed_oembed_html' ) );
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'PMC_Trailer_Widget' );
}, 20 );
//EOF
