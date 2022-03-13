<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Admin_Google_Analytics {

	use Singleton;

	const GA_DEV_ACCOUNT_ID = 'UA-1915907-67';

	private $username;
	private $role;
	private $sitename;
	private $ga_account_id;
	private $user_id;
	private $guest_author_id;
	private $screen_id;
	private $screen_action;
	private $screen_base;
	private $screen_parent_base;
	private $screen_parent_file;
	private $screen_post_type;
	private $screen_taxonomy;

	protected function __construct() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		if ( PMC::is_production() && defined( 'PMC_ADMIN_GA_ACCOUNT_ID' ) ) {
			$this->ga_account_id = PMC_ADMIN_GA_ACCOUNT_ID;
		} else {
			$this->ga_account_id = self::GA_DEV_ACCOUNT_ID;
		}

		if ( empty( $this->ga_account_id ) ) {
			return;
		}

		add_action( 'wp', array( $this, 'init_variables' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );

	}

	public function init_variables() {
		$current_user = wp_get_current_user();

		$this->username = $current_user->user_login;
		$this->role     = $current_user->roles[0];
		$this->sitename = get_bloginfo( 'name' );
		$this->user_id  = $current_user->ID;

		$args = array(
			'post_type'  => 'guest-author',
			'meta_key'   => 'cap-linked_account',
			'meta_value' => $this->username
		);

		$guest_authors = get_posts( $args );
		if ( ! empty( $guest_authors ) ) {
			$guest_author          = $guest_authors[0];
			$this->guest_author_id = $guest_author->ID;
		}

		$current_screen = get_current_screen();
		if ( ! empty( $current_screen ) ) {
			$this->screen_id          = $current_screen->id;
			$this->screen_action      = $current_screen->action;
			$this->screen_base        = $current_screen->base;
			$this->screen_parent_base = $current_screen->parent_base;
			$this->screen_parent_file = $current_screen->parent_file;
			$this->screen_post_type   = $current_screen->post_type;
			$this->screen_taxonomy    = $current_screen->taxonomy;
		}
	}

	public function admin_print_scripts() {
		?>
		<script type="text/javascript">
			(
				function ( i, s, o, g, r, a, m ) {
					i['GoogleAnalyticsObject'] = r;
					i[r] = i[r] || function () {
							(
								i[r].q = i[r].q || []
							).push( arguments )
						}, i[r].l = 1 * new Date();
					a = s.createElement( o ),
						m = s.getElementsByTagName( o )[0];
					a.async = 1;
					a.src = g;
					m.parentNode.insertBefore( a, m )
				}
			)( window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga' );
			ga( 'create', '<?php echo esc_js( $this->ga_account_id ); ?>', 'auto' );
			ga( 'set', 'location', document.getElementsByTagName( "title" )[0].innerHTML );
			ga( 'set', 'dimension1', '<?php echo esc_js( $this->username ); ?>' );
			ga( 'set', 'dimension2', '<?php echo esc_js( $this->role ); ?>' );
			ga( 'set', 'dimension3', '<?php echo esc_js( $this->sitename ); ?>' );
			ga( 'set', 'dimension4', '<?php echo esc_js( $this->user_id ); ?>' );
			ga( 'set', 'dimension5', '<?php echo esc_js( $this->guest_author_id ); ?>' );
			ga( 'set', 'dimension6', '<?php echo esc_js( $this->screen_id ); ?>' );
			ga( 'set', 'dimension7', '<?php echo esc_js( $this->screen_action ); ?>' );
			ga( 'set', 'dimension8', '<?php echo esc_js( $this->screen_base ); ?>' );
			ga( 'set', 'dimension9', '<?php echo esc_js( $this->screen_parent_base ); ?>' );
			ga( 'set', 'dimension10', '<?php echo esc_js( $this->screen_parent_file ); ?>' );
			ga( 'set', 'dimension11', '<?php echo esc_js( $this->screen_post_type ); ?>' );
			ga( 'set', 'dimension12', '<?php echo esc_js( $this->screen_taxonomy ); ?>' );
			ga( 'send', 'pageview' );

		</script>
		<?php
	}
}

PMC_Admin_Google_Analytics::get_instance();
