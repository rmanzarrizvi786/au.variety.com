<?php
/**
 * Style for AMP page for Related links
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-variety-2017
 */
?>

.c-related {
	margin: 15px 0;
	padding: 0 13px 16px;
	-webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, .5);
	box-shadow: 0 2px 4px rgba(0, 0, 0, .5);
	background-color: #f3f3f3;
}

.c-related {
	float: left;
	width: 206px;
	margin: 15px 15px 15px 0;
}

.c-related .c-heading {
	margin: 0 -13px;
	padding: 13px;
}

.c-related__list {
	list-style: none;
	padding: 0;
}

.c-related__list-item a {
	color: #000;
	text-decoration: none;
}

.c-related__list-item a:hover {
	color: #000;
}

.c-related__list-item:after {
	content: '';
	margin: 10px 0;
	display: block;
	height: 1px;
	width: 100%;
	background: #979797;
}

.c-related__list-item:last-child:after {
	content: none;
}
