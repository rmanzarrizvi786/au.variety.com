<?php
/**
 * Sends additional GA ECommerce data in the <head>
 *
 * @var array $products Product data present on the page.
 */
?>
ga('require', 'ec');
<?php foreach ( $products as $product ) : ?>
	ga('ec:addImpression',
	<?php
	echo wp_json_encode(
		array(
			// See https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#impression-data
			'id'       => $product->id,
			'name'     => $product->title,
			'list'     => $product->display_context,
			'brand'    => $product->manufacturer,
			'category' => $product->category,
			'variant'  => $product->variant,
			'position' => $product->position,
			'price'    => str_replace( '$', '', $product->price ),
		)
	);
	?>
	);
<?php endforeach; ?>
