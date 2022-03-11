<?php

/**
 * Template for AMP author.
 *
 * @package pmc-variety
 */

$author_data = PMC\Core\Inc\Author::get_instance()->authors_data();

if (!empty($author_data['byline'])) :
?>
	<div class="amp-wp-meta amp-wp-byline">
		<span class="amp-wp-author author vcard">
			<?php echo esc_html(__('By', 'pmc-variety')); ?>
			<?php echo wp_kses_post($author_data['byline']); ?>
		</span>
	</div>
<?php
endif;
