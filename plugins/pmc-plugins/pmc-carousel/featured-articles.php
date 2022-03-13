<?php
/**
 * Class PMC_Master_Featured_Articles
 *
 * @package pmc-carousel
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Master_Featured_Articles {

	use Singleton;

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	public $featured_post_type = 'pmc_featured';

	/**
	 * Initialization function called when object is instantiated. Does nothing by default.
	 */
	protected function __construct() {
		add_action( 'after_setup_theme', array( $this, 'action_after_theme_setup' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
		add_action( 'admin_head-edit.php', array( $this, 'admin_head' ), 100 );
		add_filter( 'manage_pmc_featured_posts_columns', array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_pmc_featured_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
		add_filter( 'request', array( $this, 'request' ) );
		add_action( 'wp_ajax_pmc_master_publish_carousel', array( $this, 'ajax_publish_carousel' ) );
	}

	public function action_after_theme_setup() {
		add_image_size( 'carousel-small-thumb', 125, 70, true );
	}

	public function init() {
		register_post_type( $this->featured_post_type, array(
			'labels'               => array(
				'name'               => __( 'Featured Carousel', 'pmc-carousel' ),
				'singular_name'      => __( 'Featured Article', 'pmc-carousel' ),
				'add_new'            => __( 'Add New', 'pmc-carousel' ),
				'add_new_item'       => __( 'Add New Featured Article', 'pmc-carousel' ),
				'edit_item'          => __( 'Edit Featured Article', 'pmc-carousel' ),
				'new_item'           => __( 'New Featured Article', 'pmc-carousel' ),
				'view_item'          => __( 'View Featured Article', 'pmc-carousel' ),
				'search_items'       => __( 'Search Carousel', 'pmc-carousel' ),
				'not_found'          => __( 'Not found in carousel.', 'pmc-carousel' ),
				'not_found_in_trash' => __( 'No featured articles found in trash.', 'pmc-carousel' ),
				'parent_item_colon'  => __( 'Parent Article:', 'pmc-carousel' ),
				'all_items'          => __( 'Carousel', 'pmc-carousel' ),
			),
			'public'               => false,
			'show_ui'              => true,
			'show_in_menu'         => 'edit.php',
			'supports'             => array( 'title', 'thumbnail', 'excerpt', 'page-attributes', 'category' ),
			'register_meta_box_cb' => array( $this, 'featured_meta_box_cb' ),
			'rewrite'              => false,
		));
	}

	public function featured_meta_box_cb() {

		add_meta_box( 'pmc-master-link-meta-box', __( 'Select an Article/Section Front', 'pmc-carousel' ), array( $this, 'link_meta_box' ), $this->featured_post_type, 'normal' );

	}

	public function link_meta_box( $post ) {

		wp_nonce_field( 'pmc-master-save-link', 'pmc-master-link-nonce' );

		$linked_data = get_post_meta( $post->ID, '_pmc_master_article_id', true );
		$type        = 'Article'; // Default to 'Article' type.

		if ( $linked_data ) {

			if ( intval( $linked_data ) > 0 ) {

				$linked_data = intval( $linked_data );

				$linked_data = array(
					'id'    => $linked_data,
					'url'   => get_permalink( $linked_data ),
					'title' => get_the_title( $linked_data ),
				);

				$linked_data = wp_json_encode( $linked_data );
			}

			$decoded = json_decode( $linked_data );

			if ( isset( $decoded->type ) ) {
				$type = $decoded->type;
			}

		} else {
			$linked_url = '';
		}

		do_action( 'pmc-linkcontent-before-insert-field' ); // @codingStandardsIgnoreLine
		$linked_data = apply_filters( 'pmc-linkcontent-insert-field-linked-data', $linked_data ); // @codingStandardsIgnoreLine
		$type        = apply_filters( 'pmc-linkcontent-insert-field-type', $type ); // @codingStandardsIgnoreLine
		PMC_LinkContent::insert_field( $linked_data, $type );
		do_action( 'pmc-linkcontent-after-insert-field' ); // @codingStandardsIgnoreLine
	}

	public function transition_post_status( $new_status, $old_status, $post ) {

		// Trigger an action when a post is published or unpublished. This allows selective cache-busting.
		if ( ( 'publish' === $new_status || 'publish' === $old_status ) && ( $old_status !== $new_status ) ) {

			$meta_tax  = get_post_meta( $post->ID, '_pmc_tax', true );
			$meta_term = get_post_meta( $post->ID, '_pmc_term', true );
			do_action( 'pmc_carousel_post_published', $post, $meta_term, $meta_tax );

		}

	}

	public function save_post( $post_id ) {

		$nonce = \PMC::filter_input( INPUT_POST, 'pmc-master-link-nonce' );

		// Check nonce before proceeding.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'pmc-master-save-link' ) ) {
			return;
		}

		if ( isset( $_POST['pmclinkcontent-post-value'] ) ) {

			$url_path = \PMC::filter_input( INPUT_POST, 'pmclinkcontent-post-value' );

			// Clear field by sending '' to avoid unnecessary data.
			if ( empty( $url_path ) || ! is_string( $url_path ) ) {
				delete_post_meta( $post_id, '_pmc_master_article_id' );
				return;
			}

			$url_parts = json_decode( $url_path );
			if ( is_object( $url_parts ) ) {
				$new_meta_value = array(
					'url'   => esc_url_raw( $url_parts->url ),
					'id'    => intval( $url_parts->id ),
					'title' => esc_html( $url_parts->title ),
				);
			}

			$type = \PMC::filter_input( INPUT_POST, 'pmclinkcontent-type' );
			if ( ! empty( $type ) ) {
				$new_meta_value['type'] = $type;
			}

			if ( ! empty( $url_parts->taxonomy ) ) {
				$new_meta_value['taxonomy'] = $url_parts->taxonomy;
			}

			$current_meta_value = get_post_meta( $post_id, '_pmc_master_article_id', true );

			if ( $new_meta_value !== $current_meta_value ) {
				update_post_meta( $post_id, '_pmc_master_article_id', wp_json_encode( $new_meta_value ) );
			}
		}
	}

	/**
	 * Sort by menu order, Title in admin by default (even though not hierarchical)
	 *
	 * @version 2015-10-01 - Javier Martinez - PMCVIP-248 - Removed orderby check since it always resolves to true
	 * @version 2018-03-22 - Dhaval Parekh - READS-1122
	 *
	 * @param \WP_Query $query WP_Query.
	 */
	public function pre_get_posts( $query ) {

		if ( is_admin() && $query->is_main_query() && 'pmc_featured' === $query->get( 'post_type' ) ) {

			$query->set( 'orderby', 'menu_order title' );
			$query->set( 'order', 'ASC' );
			remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		}

	}

	/**
	 * Clever solution to previewing carousel changes.
	 */
	public function request( $request ) {
		if ( ! empty( $request['preview'] ) && ( isset( $request[ $this->featured_post_type ] ) || ( isset( $request['p'] ) && get_post_type( $request['p'] ) === $this->featured_post_type ) ) ) {
			$request = array(
				'preview' => true,
			);
		}

		return $request;
	}

	/**
	 * Preview column.
	 */
	public function manage_posts_columns( $cols ) {

		$cols = array(
			'cb'     => '<input type="checkbox" />',
			'image'  => __( 'Image', 'pmc-carousel' ),
			'title'  => __( 'Title', 'pmc-carousel' ),
			'author' => __( 'Author', 'pmc-carousel' ),
			'date'   => __( 'Date', 'pmc-carousel' ),
		);

		return $cols;
	}

	public function manage_posts_custom_column( $column, $post_id ) {

		if ( ! empty( $column ) && 'image' === $column ) {
			echo get_the_post_thumbnail( $post_id, 'carousel-small-thumb' );
		}
	}

	/**
	 * Highlight first four items (those in the carousel),
	 * other admin styling tweaks, filter for previewing images.
	 */
	public function admin_head() {
		if ( 'pmc_featured' !== get_post_type() ) {
			return;
		}

		add_filter( 'post_thumbnail_html', array( $this, 'carousel_thumbnail_fallback' ), 10, 4 );
	?>
		<style type="text/css">
			.widefat thead #image { width: 125px; }
			.widefat tbody td.image { padding:0; }
			.widefat tbody td.image img { display:block; }
		</style>
	<?php
	}

	public function carousel_thumbnail_fallback( $html, $post_id, $post_thumbnail_id, $size ) {
		if ( ! empty( $html ) ) {
			return $html;
		}

		$dest_id = get_post_meta( $post_id, '_pmc_master_article_id', true );

		// Try getting the thumbnail from the related article.
		if ( ! empty( $dest_id ) && has_post_thumbnail( $dest_id ) ) {
			return get_the_post_thumbnail( $dest_id, $size );
		}

		// End up at the fallback.
		return '<img width="125" height="70" alt="" class="attachment-' . $size . ' wp-post-image" src="' . get_template_directory_uri() . '/images/carousel-fallback/' . $size . '.jpg">';
	}

	/**
	 * Generate carousel output.
	 */
	public function generate_carousel( $query_args = array() ) {
		$featured_args = wp_parse_args( (array) $query_args, array(
			'post_type'      => 'pmc_featured',
			'post_status'    => 'publish',
			'posts_per_page' => '4',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		));

		$featured_articles = new WP_Query( $featured_args );

		ob_start();

		if ( $featured_articles->have_posts() ) :
			add_filter( 'post_thumbnail_html', array( $this, 'carousel_thumbnail_fallback' ), 10, 4 );
		?>
		<section id="featured-content">
			<ul id="slides">
				<?php
				$thumbs = '';
				global $post;

				while ( $featured_articles->have_posts() ) :
					$featured_articles->the_post();
					$dest_id   = get_post_meta( get_the_ID(), '_pmc_master_article_id', true );
					$permalink = get_permalink( $dest_id );

					$caption = $post->post_excerpt;
					if ( empty( $caption ) ) {
						$dest_post = get_post( $dest_id );
						$caption   = empty( $dest_post->post_excerpt ) ? $dest_post->post_content : $dest_post->post_excerpt;
					}

					$title = get_the_title();

					if ( empty( $title ) ) {
						$title = get_the_title( $dest_id );
					}
					?>
					<li id="slide-<?php the_ID(); ?>" class="slide">
						<a href="<?php echo esc_url( $permalink ); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'carousel-big-thumb' ); ?></a>
						<h3><a href="<?php echo esc_url( $permalink ); ?>" title="<?php the_title_attribute(); ?>"><?php echo wp_kses_post( pmc_master_trim_by_characters( $title, 38 ) ); ?></a></h3>
						<p><a href="<?php echo esc_url( $permalink ); ?>" title="<?php the_title_attribute(); ?>"><?php echo wp_kses_post( pmc_master_trim_by_characters( $caption, 130 ) ); ?></a></p>
					</li>
					<?php
					$thumbs .= '<li><a href="#slide-' . get_the_ID() . '" class="slide">' . get_the_post_thumbnail( get_the_ID(), 'carousel-small-thumb' ) . '<span class="arrow"></span></a></li>';
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
			<ul id="slider-nav" class="nav">
				<?php echo wp_kses_post( $thumbs ); ?>
			</ul>
		</section> <!-- /#featured-content -->

		<?php
			remove_filter( 'post_thumbnail_html', array( $this, 'carousel_thumbnail_fallback' ) );
		endif;

		return ob_get_clean();
	}

	/**
	 * Generate option storing published carousel via ajax.
	 */
	public function ajax_publish_carousel() {
		if ( current_user_can( 'edit_published_posts' ) ) {
			update_option( 'pmc_master_published_carousel', $this->generate_carousel() );
		}

		die;
	}
}

$pmc_master_featured_articles = PMC_Master_Featured_Articles::get_instance();

/**
 * Display carousel.
 */
function pmc_master_show_carousel() {
	global $pmc_master_featured_articles;

	$carousel_preview = \PMC::filter_input( INPUT_GET, 'carousel-preview' );
	if ( ! empty( $carousel_preview ) && current_user_can( 'edit_posts' ) ) {
		echo '<p style="margin-bottom:15px;"><em>' . esc_html__( 'preview mode', 'pmc-carousel' ) . '</em></p>';
		echo wp_kses( $pmc_master_featured_articles->generate_carousel() );
		return;
	}

	$preview_id = PMC::filter_input( INPUT_GET, 'preview_id', FILTER_SANITIZE_NUMBER_INT );
	$post_id    = PMC::filter_input( INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT );
	$preview    = PMC::filter_input( INPUT_REQUEST, 'preview' );

	if ( current_user_can( 'edit_posts' ) && ( ! empty( $preview_id ) || ! empty( $post_id ) ) && ! empty( $preview ) ) {
		echo '<p style="margin-bottom:15px;"><em>' . esc_html__( 'single preview mode', 'pmc-carousel' ) . '</em></p>';

		$id = empty( $preview_id ) ? intval( $post_id ) : intval( $preview_id );

		echo wp_kses_post( $pmc_master_featured_articles->generate_carousel( array(
			'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ),
			'posts_per_page' => 1,
			'p'              => intval( $id ),
		) ) );

		return;
	}

	echo wp_kses_post( get_option( 'pmc_master_published_carousel' ) );
}

// EOF
