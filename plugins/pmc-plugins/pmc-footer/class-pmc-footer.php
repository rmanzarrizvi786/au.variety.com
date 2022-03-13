<?php

/**
 * Class PMC_Footer
 * encapsulated the standard footer for PMC Lobs each LOB can use this by calling:
 * PMC_Footer::get_instance()->pmc_render_footer( [ 'footer_id' => 'colophon' , 'footer_class' => 'site-footer' ] );
 *
 * @author PMC, Adaeze Esiobu
 * @since  2014-09-23
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Footer {

	use Singleton;

	/**
	 * add the pmc_footer actions
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up WP hooks with listeners
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {
		add_action( 'pmc_footer_top', [ $this, 'pmc_footer_top' ] );
		add_action( 'pmc_footer_middle', [ $this, 'pmc_footer_middle' ] );
		add_action( 'pmc_footer_list_lob', [ $this, 'pmc_footer_list_lob' ] );
		add_action( 'pmc_footer_bottom', [ $this, 'pmc_footer_bottom' ] );
	}

	/**
	 * renders all parts of the footer. All Each LOB should need to call is this function.
	 */
	public function pmc_render_footer( $args ) {
		$footer_id    = '';
		$footer_class = '';
		if ( ! empty( $args['footer_id'] ) ) {
			$footer_id = $args['footer_id'];
		}
		if ( ! empty( $args['footer_class'] ) ) {
			$footer_class = $args['footer_class'];
		}
		?>
		<footer id="<?php echo esc_attr( $footer_id ); ?>" class="<?php echo esc_attr( $footer_class ); ?>" role="contentinfo">
			<div id="other-pmc-properties">
				<?php
				do_action( 'pmc_footer_top' );
				do_action( 'pmc_footer_middle' );
				do_action( 'pmc_footer_list_lob' );
				do_action( 'pmc_footer_bottom' );
				?>
			</div><!-- closes wrap -->
		</footer>
		<?php
	}

	/**
	 * pmc_footer_feeds should be in the format $list_of_pmc_footer_feeds = [ 0 => [ 'feed_source' =>'https://avalidurl.com', 'feed_title'  => 'a nice valid string' ] ]
	 */
	public function pmc_footer_top() {
		$list_of_pmc_footer_feeds = apply_filters( 'pmc_footer_list_of_feeds', [] );

		if ( empty( $list_of_pmc_footer_feeds ) || ! is_array( $list_of_pmc_footer_feeds ) ) {
			return;
		}
		?>
		<ul id="property-snippets">
		<?php
		foreach ( $list_of_pmc_footer_feeds as $pmc_footer_feed ) {

			if ( is_array( $pmc_footer_feed ) && ! empty( $pmc_footer_feed['feed_source_url'] ) ) {

				$css_classes = ( ! empty( $pmc_footer_feed['css_classes'] ) ) ? $pmc_footer_feed['css_classes'] : [];

				pmc_get_footer_feed( $pmc_footer_feed['feed_source_url'], $pmc_footer_feed['feed_title'], $css_classes );

			}

		}
		?>
		</ul>
		<?php
	}

	/**
	* renders the footer middle. This portion is very specific to LOBs and should be overwritten by each
	* LOB using the action.
	*/
	public function pmc_footer_middle() {
		$links = [
			[
				'title' => 'About Us',
				'link'  => '/about-us/',
			],
			[
				'title' => 'Advertise',
				'link'  => '/advertise/',
			],
			[
				'title' => 'Terms of Use',
				'link'  => 'https://pmc.com/terms-of-use/',
			],
			[
				'title' => 'Privacy Policy',
				'link'  => 'https://pmc.com/privacy-policy/',
			],
			[
				'title' => 'Your Privacy Rights',
				'link'  => 'https://pmc.com/privacy-policy/#california',
			],
			[
				'title' => 'Contact Us',
				'link'  => '/contact-us/',
			],
		];
		$links = apply_filters( 'pmc_footer_links', $links );

		if ( empty( $links ) ) {
			return;
		}

		echo '<section class="footer-links"><ul>';

		foreach ( $links as $item ) {
			if ( empty( $item['link'] ) || empty( $item['title'] ) ) {
				continue;
			}

			printf(
				'<li><a href="%1$s" title="%2$s" class="%3$s" rel="nofollow">%4$s</a></li>',
				esc_attr( $item['link'] ),
				esc_attr( $item['title'] ),
				( empty( $item['class'] ) ? '' : esc_attr( $item['class'] ) ),
				esc_html( $item['title'] )
			);
		}

		echo '</ul></section>';
	}

	/**
	 * renders the Line of business list on the bottom of the footer. Each LOB has the ability to change the
	 * list of LOBs rendered and what order they are rendered with the filter pmc_footer_lob_list
	 *
	 * @return void
	 */
	public function pmc_footer_list_lob() {

		$list_of_lobs = apply_filters(
			'pmc_footer_lob_list',
			[

				0 => [
					'site_url'   => 'https://www.youtube.com/movieline',
					'class'      => 'sister-movieline',
					'site_title' => 'MovieLine',
				],
				1 => [
					'site_url'   => 'https://deadline.com/',
					'class'      => 'sister-deadline',
					'site_title' => 'Deadline',
				],
				2 => [
					'site_url'   => 'https://wwd.com/',
					'class'      => 'sister-wwd',
					'site_title' => 'wwd.com',
				],
				3 => [
					'site_url'   => 'https://www.youtube.com/entv',
					'class'      => 'sister-entv',
					'site_title' => 'entv',
				],
				4 => [
					'site_url'   => 'https://bgr.com',
					'class'      => 'sister-bgr',
					'site_title' => 'BGR',
				],

			]
		);

		if ( empty( $list_of_lobs ) || ! is_array( $list_of_lobs ) ) {
			return;
		}

		?>
		<section class="pmc-links">
			<h3><a href="https://pmc.com/" target="_blank" title="PMC Network"><span><?php esc_html_e( 'The Power of Content', 'pmc-footer' ); ?></span></a></h3>
			<nav>
				<ul class="pmc-properties icon-pmc-logos">
					<?php
					foreach ( $list_of_lobs as $lob ) {
						if ( ! empty( $lob['site_url'] ) && ! empty( $lob['class'] ) && ! empty( $lob['site_title'] ) ) {
							printf(
								'<li class="sister-logo %s"><a href="%s">%s</a></li>',
								esc_attr( $lob['class'] ),
								esc_url( $lob['site_url'] ),
								esc_html( $lob['site_title'] )
							);
						}
					}
					?>
				</ul>
			</nav>
		</section>
		<?php

	}

	/**
	 * renders out the very bottom of the pmc_bottom. this is very specific to each site. ideally this action should be
	 * overwritten by each LOB
	 */
	public function pmc_footer_bottom() {
		/* translators: Copyright statement */
		$copyright = sprintf( __( 'Copyright &copy; %s Penske Business Media, LLC.  All rights reserved.', 'pmc-footer' ), gmdate( 'Y' ) );
		$copyright = apply_filters( 'pmc_footer_copyright', $copyright );

		printf( '<p class="copyright">%s</p>', wp_kses_post( $copyright ) );

		if ( function_exists( 'vip_powered_wpcom' ) ) {
			printf( '<p class="copyright powered-by-vip">%s</p>', wp_kses_post( vip_powered_wpcom() ) );
		}
	}

}
