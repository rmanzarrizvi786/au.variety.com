<?php
/**
 * Widget template for Whizzco
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-25
 */
?>
<!-- Whizzco Widget -->

<div class="ads_container_<?php echo intval( $key ); ?>" website_id="<?php echo intval( $website_id ); ?>" widget_id="<?php echo intval( $widget_id ); ?>"></div>

<script type="text/javascript">var uniquekey = <?php echo wp_json_encode( $key ); ?>; </script>

<!-- End Whizzco Widget -->

