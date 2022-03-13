<?php
/**
 * class PMC_Syndicate_To_Yahoo
 * @author PMC| Adaeze Esiobu
 * @since 09-30-2014
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Syndicate_To_Yahoo {

	use Singleton;

    const META_KEY = "_pmc_syndicate_to_yahoo";
    const SYNDICATE_TO_YAHOO_NONCE_KEY = "pmc_syndicate_to_yahoo_nonce";

	protected function __construct() {
        if( is_admin() ){
            add_action( 'post_submitbox_misc_actions', array( $this, 'show_syndicate_to_yahoo_flag' ) );
            add_action( 'save_post', array( $this , 'save_syndicate_to_yahoo_flag' ) );
            add_action( 'manage_posts_custom_column', array( $this, 'render_post_columns' ), 10, 2 );
            add_action( 'quick_edit_custom_box', array( $this, 'display_quickedit_box'), 10, 2 );

        }

        add_filter( 'pmc_syndicate_post_feed-yahoo' , array( $this, 'modify_feed_post_args' ),10,1);

    }

    /**
     * This function shows a checkbox, on add/edit post page in wpadmin, in the metabox
     * containing Update/Publish controls, to allow a post to be excluded from yahoo partner feeds
     *
     * @since 2014-10-01 Adaeze Esiobu
     * @version 2014-10-01 Adaeze Esiobu
     */
    public function show_syndicate_to_yahoo_flag() {
        global $post;


        if( empty( $post ) || ! is_object( $post ) || ! isset( $post->ID )   ) {
            return;
        }

        $flag = get_post_meta( $post->ID , PMC_Syndicate_To_Yahoo::META_KEY, true );
        ?>
    <div class="misc-pub-section">
        <?php wp_nonce_field( PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY, PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY ); ?>
        <label>
            <input type="checkbox" value="yes" name="pmc_syndicate_to_yahoo" radomthing="<?php echo $flag; ?>" <?php checked( $flag, "yes" ); ?> /> Syndicate Post to Yahoo Feeds
        </label>
    </div>
    <?php
    }

    /**
     * This function saves the flag as meta value if flag is checked on a post
     * else deletes the flag from post meta
     *
     * @since 2014-10-01 Adaeze Esiobu
     * @version 2014-10-01 Adaeze Esiobu
     */
    public function save_syndicate_to_yahoo_flag( $post_id ) {
        if( empty( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || (defined('WPCOM_DOING_REBLOG') && true === WPCOM_DOING_REBLOG) ) {
            return;
        }

        if( empty($_POST[PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY]) || !wp_verify_nonce( $_POST[PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY], PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY ) ) {
            return;
        }

        if( isset( $_POST['pmc_syndicate_to_yahoo'] ) && $_POST['pmc_syndicate_to_yahoo'] == "yes" ) {
            add_post_meta( $post_id, PMC_Syndicate_To_Yahoo::META_KEY, 'yes', true );

        } else {
            delete_post_meta( $post_id, PMC_Syndicate_To_Yahoo::META_KEY );
        }
    }
    /**
     * @param $args
     * modifies the feed_post args to include posts with "_pmc_syndicate_to_yahoo" post meta set to 1
     */
    public function modify_feed_post_args( $args ){

        if( !is_array( $args ) ){
            $args = array();
        }

        $args['meta_key'] = PMC_Syndicate_To_Yahoo::META_KEY;
        $args['meta_value'] = "yes";

        return $args;

    }
    /**
     * trickery to get quickedit to load with existing values.
     * see http://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
     */
    public function admin_footer(){
        ?>
        <script type="text/javascript">
            function pmc_syndicate_to_yahoo_quickedit(){
                var _edit = inlineEditPost.edit;
                inlineEditPost.edit = function( id ){
                    // "call" the original WP edit function
                    // we don't want to leave WordPress hanging
                    _edit.apply(this, arguments );
                    //now we take care of our business
                    //get the post ID
                    if( typeof(id) == 'object' ) {
                        post_id = this.getId(id);
                    }
                    if( post_id > 0 && this.type == 'post'){
                        // define the edit row
                        var edit_row = jQuery( '#edit-' + post_id );
                        var post_row = jQuery( '#post-' + post_id );

                        //get data
                        var syndicate_to_yahoo_checkbox = jQuery('.pmc-syndicate-to-yahoo-value', post_row ).attr('checked');
                        //populate data
                        jQuery( ':input[name="pmc_syndicate_to_yahoo"]', edit_row ).attr( 'checked', syndicate_to_yahoo_checkbox );
                    }
                };
            }
            if( inlineEditPost ) {
                pmc_syndicate_to_yahoo_quickedit();
            } else {
                jQuery( pmc_syndicate_to_yahoo_quickedit );
            }
        </script>
        <?php
    }


    /**
     * @param $column_name
     * @param $post_id
     * still part of the trickery required to show existing data in the quickedit
     * page. here we render the value of the postmeta in a hidden input
     */
    public function render_post_columns( $column_name, $post_id ) {
        $pmc_syndicate_to_yahoo = get_post_meta( $post_id , PMC_Syndicate_To_Yahoo::META_KEY,true );
        if ( ( self::is_bgr() && 'coauthors' === $column_name ) || 'status' === $column_name ) {
            ?>
            <input type="hidden" class="pmc-syndicate-to-yahoo-value" value="yes" <?php checked( $pmc_syndicate_to_yahoo, "yes" ); ?> />
            <?php
        }
    }

    /**
     * @param $column_name
     * @param $post_type
     * adds the syndicate to yahoo check box to the quickedit page.
     */
    public function display_quickedit_box( $column_name, $post_type){

        static $nonce_printed = false;
        //we have a special case for BGR. BGR doesn't have a "status" column like the rest of the LOBs but it has a callout column. I really
        //hate to have LOB specific code in a shared plugin. an alternative will be nice.
        if( $column_name != 'status' && ( self::is_bgr() && $column_name != 'taxonomy-channel' ) ) {
            return;
        }

        if( $post_type != "post"){
            return;
        }
        if( !$nonce_printed ){
            $nonce_printed = true;
            wp_nonce_field( PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY, PMC_Syndicate_To_Yahoo::SYNDICATE_TO_YAHOO_NONCE_KEY );
            add_action( 'admin_footer-edit.php', array( $this , 'admin_footer'), 11 );

        }

        ?>
    <fieldset class="inline-edit-col-right inline-edit-custom ">
        <div class="inline-edit-col">
            <div class="inline-edit-group">
                <label class="inline-edit-status alignleft inline-edit-<?php echo esc_attr($column_name); ?>"">
                <input class="pmc-syndicate-to-yahoo-checkbox" type="checkbox" value="yes" name="pmc_syndicate_to_yahoo"  /> Syndicate post to yahoo feeds
                </label>
            </div>
        </div>
    </fieldset>
        <?php
    }

    private static function is_bgr() {
        return false !== stripos( home_url(), 'bgr' );
    }

}
