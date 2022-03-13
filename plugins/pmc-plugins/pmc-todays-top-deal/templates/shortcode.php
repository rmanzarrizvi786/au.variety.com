<?php
/**
 * Renders a product shortcode with its expected web format.
 * Javascript added to this template in order to load variant quickly.
 *
 * @var string  $title             Title of the product.
 * @var string  $image_url         URL to the image.
 * @var mixed   $image_height      (Optional) Height of the image.
 * @var mixed   $image_width       (Optional) Width of the image.
 * @var string  $price             Product price.
 * @var string  $original_price    (Optional) Original price.
 * @var string  $discount_amount   (Optional) Discount price.
 * @var string  $coupon_code       (Optional) coupon code.
 * @var string  $link              Link to the product.
 * @var string  $is_amazon         Is an amazon product.
 *
 * @package pmc
 */

$variants = isset( $variants ) ? $variants : [];

?>

<a class="product-callout-todays-top-deal amzn-product-callout product-callout" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="nofollow noopener noreferrer">
	<span class="top-deal-label"><?php echo esc_html( $ecommerce_title ); ?></span>
	<span class="image-container">
		<img loading="lazy" src="<?php echo esc_url( $image_url ); ?>" height="<?php echo esc_attr( $image_height . 'px' ); ?>" width="<?php echo esc_attr( $image_width . 'px' ); ?>" alt="<?php echo wp_kses_post( $title ); ?>">
	</span>
	<span class="details-container">
		<span class="product-title"><?php echo wp_kses_post( $title ); ?></span>
		<span class="price-container">
			<span class="numbers-container">
				<?php if ( $original_price ) : ?>
					<span class="product-original-price"><span class="numbers-label">List Price:</span><span class="numbers-value"><?php echo esc_html( $original_price ); ?></span></span>
				<?php endif; ?>
				<span class="product-price">
					<span class="numbers-label">Price:</span><span class="numbers-value"><?php echo esc_html( $price ); ?></span>
				</span>
				<?php if ( $discount_amount ) : ?>
					<span class="product-discount"><span class="numbers-label">You Save:</span><span class="numbers-value"><?php echo esc_html( $discount_amount . ' (' . $discount_percent . ')' ); ?></span></span>
				<?php endif; ?>
			</span>
			<?php if ( $is_amazon ) : ?>
				<span class="disclaimer-container">
					<img class="product-prime-logo" src="<?php echo esc_url( PMC_TODAYS_TOP_DEAL_PLUGIN_URL . '/assets/images/prime-logo.png' ); ?>" width="60px" height="26px" alt="<?php esc_attr_e( 'Amazon Prime logo', 'pmc-todays-top-deal' ); ?>" loading="lazy" />
				</span>
			<?php endif; ?>
		</span>
		<span class="product-buy-button"><span class="product-buy-text"><?php echo esc_html( $buy_button_text ); ?></span><?php echo ! empty( $coupon_code ) ? '<span class="product-coupon"><span class="product-coupon-label">Coupon Code:</span> <span class="product-coupon-value">' . esc_html( $coupon_code ) . '</span></span>' : ''; ?></span>
		<span class="product-callout-todays-top-deal-disclaimer"><?php echo esc_html( $description ); ?></span>
	</span>
</a>

<script>
(function(){
	let testData = <?php echo wp_json_encode( $variants ); ?>;
	let randomVariant = testData[ Math.floor( Math.random() * testData.length ) ];
	let todaysTopDeal = window.document.querySelector( '.product-callout-todays-top-deal' );

	todaysTopDeal.setAttribute( 'href', randomVariant.link );
	if ( ! randomVariant.link.includes( 'amazon.com' ) && ! randomVariant.link.includes( 'amzn.to' ) ) {
		todaysTopDeal.querySelector('.product-prime-logo').style.display = 'none';
	}

	todaysTopDeal.querySelector('.product-title').innerText = randomVariant.title;

	if (randomVariant.image_url) {
		todaysTopDeal.querySelector('.image-container img').setAttribute('src', randomVariant.image_url);
		todaysTopDeal.querySelector('.image-container img').setAttribute('width', randomVariant.image_width + 'px');
		todaysTopDeal.querySelector('.image-container img').setAttribute('height', randomVariant.image_height + 'px');
	} else {
		todaysTopDeal.querySelector('.image-container img').style.display = 'none';
	}

	let replaceElementToken = function(el, token, value) {
		el.innerText = el.innerText.replace(token, value);
	};

	[ 'original_price', 'price' ].forEach(function(elementKey) {
		let elementClass = '.product-' + elementKey.replace(/_/g, '-');
		if ( randomVariant[elementKey] ) {
			replaceElementToken(
				todaysTopDeal.querySelector(elementClass + ' .numbers-value'),
				'%' + elementKey + '%',
				randomVariant[elementKey]
			);
		} else {
			todaysTopDeal.querySelector(elementClass).style.display = 'none';
		}
	});

	if (randomVariant.discount_amount) {
		replaceElementToken(
			todaysTopDeal.querySelector('.product-discount .numbers-value'),
			'%discount_amount%',
			randomVariant.discount_amount
		);
		replaceElementToken(
			todaysTopDeal.querySelector('.product-discount .numbers-value'),
			'%discount_percent%',
			randomVariant.discount_percent
		);
	} else {
		todaysTopDeal.querySelector('.product-discount').style.display = 'none';
	}

	if (randomVariant.coupon_code) {
		replaceElementToken(
			todaysTopDeal.querySelector('.product-coupon .product-coupon-value'),
			'%coupon_code%',
			randomVariant.coupon_code
		);
	} else {
		todaysTopDeal.querySelector('.product-coupon').style.display = 'none';
	}
}())
</script>
