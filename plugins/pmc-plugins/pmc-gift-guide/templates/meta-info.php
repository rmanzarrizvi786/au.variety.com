<?php
global $post;
$giftLink = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'link' );
$price    = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'price' );
$retailer = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'retailer' );
?>
<div class="gift-guide--info"><!-- Retailer Info -->
	<?php if ( ! empty( $giftLink ) ) { ?>
		<a href="<?php echo esc_url( $giftLink ); ?>" class="gift-guide--buy-button"
		   target="_blank">Buy Now</a>
		<?php
	}
	?>
	<div class="retailer-info">
		<?php if ( ! empty( $price ) ) { ?>
			<span class="price"><?php echo esc_html( $price ); ?></span>
		<?php }
		if ( ! empty( $retailer ) ) { ?>
			<span class="retailer"> <?php echo esc_html( 'at ' . $retailer ); ?></span>
		<?php } ?>
	</div>
</div>