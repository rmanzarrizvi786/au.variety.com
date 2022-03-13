<?php
/**
 * Feed template of amazon products for amazon feed.
 *
 * @author  Kelin Chauhan <kelin.chauhan@rtcamp.com>
 *
 * @package pmc-amzn-onsite
 */
echo do_shortcode(
	sprintf(
		'[buy-now url="%s" asin="%s" title="%s" price="%s" button_type="amazon" /]',
		$product['product_link'],
		$product['product_id'],
		$product['title'],
		$product['product_price']
	)
);
