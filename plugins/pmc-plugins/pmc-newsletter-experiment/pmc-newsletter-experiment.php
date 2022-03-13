<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use PMC\Global_Functions\Traits\Singleton;

class PMC_Newsletter_Experiment {

	use Singleton;

	// we need to track if scripts has been render do we don't output duplicate code
	protected $_scripts_rendered = false;

    function pmc_newsletter_register_script(){
        add_thickbox(); // load thickbox for modal dialog.
        wp_enqueue_script( 'newsletter-experiment', plugins_url( 'js/script.js', __FILE__  ) );
    }
    /**
     * show modal dialog
     * We want to show the dialog only on desktop or tablet devices
     * and only when a user visits an article page for the third time.
     * */
    function pmc_newsletter_experiment_show_modal(){
		// prevent script from rendering multiple time
		if ( !empty( $this->_scripts_rendered ) ) {
			return;
		}

		$this->_scripts_rendered = true;

        if(!jetpack_is_mobile() && is_single()  && get_post_type(get_the_ID() ) == "post") {
            ?>
        <script language="javascript">
            log_newsletter_lightbox_view();
            if( display_newsletter_lightbox()){
                jQuery(document).ready(function() {
                    setTimeout(function(){
                        tb_show(null,"#TB_inline?height=256&width=570&inlineId=newsletter-modal-content-id",null);
                    },10);


                });

            }

        </script>
        <?php
        }
    }
}



// use action wp_print_scripts instead of wp_head to make scripts rendered after stylesheets
//insert the JS call to show the modal.
add_action( 'wp_print_scripts' , array(PMC_Newsletter_experiment::get_instance(),'pmc_newsletter_experiment_show_modal'));
//enqueue scripts
add_action( 'wp_enqueue_scripts' , array(PMC_Newsletter_experiment::get_instance(),'pmc_newsletter_register_script'),10);
