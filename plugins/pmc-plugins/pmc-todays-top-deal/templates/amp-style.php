<?php
/**
 * Style for AMP page for Today's Top Deal.
 *
 * @package pmc
 */
?>

.product-callout:not(.product-callout-grid-item) {
	margin-top: 1rem;
	margin-bottom: 1rem;
	font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
	display: flex;
	flex-direction: column;
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) {
		max-width: 635px;
		margin-left: auto;
		margin-right: auto;
		flex-direction: row;
	}
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .details-container {
		margin-left: 15px;
	}
}

.product-callout:not(.product-callout-grid-item) span {
	display: block;
}

.product-callout:not(.product-callout-grid-item) .image-container {
	display: flex;
	justify-content: center;
	object-fit: contain;
	flex: none;
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .image-container {
		align-items: start;
		width: 160px;
	}
}

.product-callout:not(.product-callout-grid-item) .product-disclaimer {
	font-size: 10px;
	line-height: 14px;
	color: #666;
}

.product-callout:not(.product-callout-grid-item) .product-prime-logo {
	width: 60px;
	height: 26px;
	height: auto;
}

.product-callout:not(.product-callout-grid-item) .product-title {
	font-weight: 700;
	color: #000;
	color: #4e4e4e;
	font-size: 20px;
	line-height: 26px;
	margin-bottom: 10px;
	margin-top: 5px;
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .product-title {
		margin-top: 0;
	}
}

.product-callout:not(.product-callout-grid-item) .price-container {
	margin-top: 6px;
	margin-bottom: 6px;
}

.product-callout:not(.product-callout-grid-item) .disclaimer-container {
	padding-left: 30px;
	padding-right: 30px;
	margin-top: 5px;
	text-align: center;
}

.product-callout:not(.product-callout-grid-item) .disclaimer-container img {
	margin-left: -4px;
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .disclaimer-container {
		padding-left: 15px;
		padding-right: 15px;
		margin-top: 0;
	}
}

.product-callout:not(.product-callout-grid-item) .numbers-container {
	font-size: 14px;
	line-height: 20px;
	font-weight: 500;
	display: table;
	width: 100%;
}

.product-callout:not(.product-callout-grid-item) .numbers-container>span {
	display: table-row;
}

.product-callout:not(.product-callout-grid-item) .numbers-container>span>span {
	display: table-cell;
}

.product-callout:not(.product-callout-grid-item) .numbers-container .numbers-label {
	text-align: right;
	color: #666;
	padding-right: 10px;
	min-width: 75px;
}

.product-callout:not(.product-callout-grid-item) .numbers-container .product-original-price .numbers-value {
	text-decoration: line-through;
	color: #92929d;
}

.product-callout:not(.product-callout-grid-item) .numbers-container .product-price .numbers-value {
	font-size: 20px;
	color: red;
}

.product-callout:not(.product-callout-grid-item) .numbers-container .product-discount .numbers-value {
	color: red;
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .price-container {
		display: flex;
	}
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .price-container>span: first-child {
		width: 70%;
	}
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .price-container>span: last-child {
		width: 30%;
	}
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .price-container .disclaimer-container {
		order: 1;
	}
}

@media (min-width: 768px) {
	.product-callout:not(.product-callout-grid-item) .price-container .numbers-container {
		order: 2;
	}
}

.product-callout:not(.product-callout-grid-item) .product-buy-button {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-top: 8px;
	background-color: #0084ff;
	font-weight: 700;
	border-radius: 3px;
	padding-top: 8px;
	padding-bottom: 8px;
}

.product-callout:not(.product-callout-grid-item) .product-buy-button .product-buy-text {
	margin-top: -2px;
	font-size: 20px;
	line-height: 20px;
	color: #fff;
}

.product-callout:not(.product-callout-grid-item) .product-buy-button .product-coupon {
	display: inline-block;
	font-size: 14px;
	margin-left: 10px;
	color: #000;
	background-color: #ffb800;
	padding: 3px 5px;
	border-radius: 3px;
}

.product-callout:not(.product-callout-grid-item) .product-buy-button .product-coupon>span {
	display: inline-block;
}

.product-callout:not(.product-callout-grid-item) .product-buy-button .product-coupon>span: first-child {
	font-weight: 400;
}

.product-callout-grid .product-callout-grid-disclaimer {
	margin-top: 10px;
	color: #c4c4c4;
	font-size: 12px;
	line-height: 20px;
	text-align: center;
}

.product-callout-grid-compact,.product-callout-grid-editorial {
	display: grid;
	grid-template-columns: repeat(1, minmax(0, 1fr));
	grid-row-gap: 15px;
	margin-top: 1rem;
	margin-bottom: 1rem;
}

.product-callout-grid-compact .product-callout-grid-disclaimer,.product-callout-grid-editorial .product-callout-grid-disclaimer {
	margin-top: 0;
	color: #c4c4c4;
	font-size: 12px;
	line-height: 20px;
	text-align: center;
	grid-column: 1 / -1;
}

@media (min-width: 768px) {
	.product-callout-grid-compact,.product-callout-grid-editorial {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		grid-column-gap: 15px;
		grid-row-gap: 15px;
	}
}

.product-callout.product-callout-grid-item-compact {
	box-shadow: 2px 2px 8px hsla(0, 0%, 76.9%, .5);
	border-radius: 10px;
	padding: 15px;
	font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
	display: inline-flex;
}

.product-callout.product-callout-grid-item-compact span {
	display: block;
}

.product-callout.product-callout-grid-item-compact .image-container {
	justify-content: center;
	object-fit: contain;
	flex: none;
	width: 80px;
	align-self: center;
}

.product-callout.product-callout-grid-item-compact .image-container img {
	object-fit: contain;
}

.product-callout.product-callout-grid-item-compact .product-title {
	font-weight: 700;
	color: #000;
	color: #4e4e4e;
	font-size: 16px;
	line-height: 26px;
	flex-grow: 1;
	margin-left: 10px;
	margin-right: 10px;
	align-self: center;
}

.product-callout.product-callout-grid-item-compact .price-container {
	text-align: center;
}

.product-callout.product-callout-grid-item-compact .product-buy-button {
	flex: none;
	color: #fff;
	background-color: #0084ff;
	font-weight: 700;
	font-size: 18px;
	line-height: 18px;
	border-radius: 3px;
	padding: 8px 10px 9px;
}

.product-callout.product-callout-grid-item-compact .product-unit-price {
	color: #000;
	color: #4e4e4e;
	font-weight: 700;
	margin-top: 5px;
	font-size: 14px;
	line-height: 18px;
	white-space: nowrap;
}

.product-callout.product-callout-grid-item-editorial {
	box-shadow: 2px 2px 8px hsla(0, 0%, 76.9%, .5);
	border-radius: 10px;
	display: block;
	position: relative;
	padding: 15px;
}

.product-callout.product-callout-grid-item-editorial span {
	display: block;
}

.product-callout.product-callout-grid-item-editorial .product-award {
	position: absolute;
	top: 0;
	left: 15px;
	color: #000;
	background-color: #ffb800;
	font-size: 12px;
	padding: 4px 15px;
	line-height: 12px;
	text-transform: uppercase;
	font-weight: 700;
}

.product-callout.product-callout-grid-item-editorial .image-container {
	display: flex;
	justify-content: center;
	margin-bottom: 8px;
}

.product-callout.product-callout-grid-item-editorial .image-container img {
	object-fit: contain;
}

.product-callout.product-callout-grid-item-editorial .product-title {
	font-weight: 700;
	color: #0084ff;
	font-size: 24px;
	line-height: 32px;
	text-decoration: underline;
	margin-bottom: 8px;
}

.product-callout.product-callout-grid-item-editorial .price-container {
	display: flex;
	color: #000;
	color: #4e4e4e;
	font-size: 18px;
	line-height: 30px;
	margin-bottom: 8px;
}

.product-callout.product-callout-grid-item-editorial .price-container>span {
	margin-right: 8px;
}

.product-callout.product-callout-grid-item-editorial .price-container .product-price {
	font-weight: 700;
}

.product-callout.product-callout-grid-item-editorial .price-container .product-original-price {
	text-decoration: line-through;
	font-weight: lighter;
}

.product-callout.product-callout-grid-item-editorial .price-container .product-discount {
	font-size: 14px;
}

.product-callout.product-callout-grid-item-editorial .product-coupon {
	font-size: 14px;
	color: #000;
	background-color: #ffb800;
	padding: 3px 5px;
	border-radius: 3px;
	white-space: nowrap;
	margin-bottom: 8px;
	display: inline-block;
}

.product-callout.product-callout-grid-item-editorial .product-coupon>span {
	display: inline-block;
}

.product-callout.product-callout-grid-item-editorial .product-coupon>span: last-child {
	font-weight: 700;
}

.product-callout.product-callout-grid-item-editorial .product-summary {
	color: #000;
	color: #4e4e4e;
	font-size: 18px;
	line-height: 30px;
	margin-bottom: 8px;
}

.product-callout.product-callout-grid-item-editorial .product-description {
	color: #000;
	color: #4e4e4e;
	font-size: 14px;
	line-height: 24px;
	margin-bottom: 8px;
}

.product-callout.product-callout-grid-item-editorial .product-buy-button {
	font-weight: 700;
	color: #0084ff;
	font-size: 18px;
	line-height: 24px;
	text-decoration: underline;
}

.product-callout.product-callout-grid-item-stacked {
	margin-top: 20px;
	margin-bottom: 20px;
	font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	row-gap: 10px;
}

@media (min-width: 768px) {
	.product-callout.product-callout-grid-item-stacked {
		display: flex;
		margin-top: 1rem;
		margin-bottom: 1rem;
	}
}

.product-callout.product-callout-grid-item-stacked span {
	display: block;
}

.product-callout.product-callout-grid-item-stacked .image-container {
	display: flex;
	justify-content: center;
	object-fit: contain;
	flex: none;
	width: 120px;
	grid-column: span 1 / span 1;
}

.product-callout.product-callout-grid-item-stacked .image-container img {
	object-fit: contain;
}

.product-callout.product-callout-grid-item-stacked .product-title {
	font-weight: 700;
	color: #000;
	color: #4e4e4e;
	font-size: 20px;
	line-height: 26px;
	margin-left: 15px;
	grid-column: span 2 / span 2;
}

@media (min-width: 768px) {
	.product-callout.product-callout-grid-item-stacked .product-title {
		margin-right: 15px;
		width: 480px;
	}
}

.product-callout.product-callout-grid-item-stacked .product-coupon {
	font-size: 14px;
	color: #000;
	background-color: #ffb800;
	padding: 3px 5px;
	border-radius: 3px;
	white-space: nowrap;
}

.product-callout.product-callout-grid-item-stacked .product-coupon>span {
	display: inline-block;
}

.product-callout.product-callout-grid-item-stacked .product-coupon>span: last-child {
	font-weight: 700;
}

.product-callout.product-callout-grid-item-stacked .product-coupon .product-coupon-label {
	display: block;
}

@media (min-width: 768px) {
	.product-callout.product-callout-grid-item-stacked .product-coupon .product-coupon-label {
		display: inline-block;
	}
}

.product-callout.product-callout-grid-item-stacked .product-prime-logo {
	margin-left: auto;
}

.product-callout.product-callout-grid-item-stacked .disclaimer-container {
	flex: none;
	margin-left: 10px;
	margin-right: 10px;
	text-align: center;
	grid-column: span 2 / span 2;
	display: flex;
	justify-content: space-between;
	align-items: self-start;
}

@media (min-width: 768px) {
	.product-callout.product-callout-grid-item-stacked .disclaimer-container {
		display: block;
		min-width: 100px;
	}
}

.product-callout.product-callout-grid-item-stacked .price-container {
	text-align: right;
}

@media (min-width: 768px) {
	.product-callout.product-callout-grid-item-stacked .price-container {
		width: 170px;
	}
}

.product-callout.product-callout-grid-item-stacked .product-buy-button {
	display: inline-block;
	grid-column: span 1 / span 1;
	color: #fff;
	background-color: #0084ff;
	font-weight: 700;
	font-size: 20px;
	line-height: 20px;
	border-radius: 3px;
	padding: 8px 10px 9px;
	white-space: nowrap;
}

.product-callout.product-callout-grid-item-stacked .product-unit-price {
	color: #000;
	color: #4e4e4e;
	font-weight: 700;
	margin-top: 5px;
	font-size: 14px;
	line-height: 18px;
	white-space: nowrap;
}

.product-callout.product-callout-grid-item-stacked .product-discount {
	margin-top: 5px;
	color: red;
	font-size: 14px;
	line-height: 18px;
	white-space: nowrap;
}

.product-callout.product-callout-todays-top-deal {
	position: relative;
	margin-top: 1.75rem;
	margin-bottom: 1rem;
	padding: 1rem .8rem 2rem;
	border: 3px solid #0084ff;
	max-width: none;
}

@media (min-width: 768px) {
	.product-callout.product-callout-todays-top-deal {
		padding-left: 2rem;
		padding-right: 2rem;
	}
}

.product-callout.product-callout-todays-top-deal .top-deal-label {
	font-size: 24px;
	font-weight: 700;
	color: #0084ff;
	position: absolute;
	transform: translateY(-55%);
	background-color: #fff;
	padding-right: 10px;
	top: 0;
	left: 0;
	padding-left: 10px;
	margin-left: 10px;
}

@media (min-width: 768px) {
	.product-callout.product-callout-todays-top-deal .product-callout-todays-top-deal-disclaimer {
		margin-left: 160px;
	}
}

.product-callout.product-callout-todays-top-deal .disclaimer-container {
	text-align: center;
}

.product-callout.product-callout-todays-top-deal .disclaimer-container .product-disclaimer {
	display: none;
}

.product-callout.product-callout-todays-top-deal .top-deal-label {
	display: block;
}

.product-callout.product-callout-todays-top-deal .details-container {
	flex-grow: 1;
}

.product-callout.product-callout-todays-top-deal .product-callout-todays-top-deal-disclaimer {
	position: absolute;
	left: 0;
	right: 0;
	bottom: .5rem;
	font-size: 10px;
	line-height: 14px;
	color: #666;
	text-align: center;
}

body .product-callout {
	text-decoration: none;
}