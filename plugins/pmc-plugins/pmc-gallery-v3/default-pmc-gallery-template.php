<?php
/**
 * Gallery Template
 * This is loaded by PMC_Gallery_View::gallery_shortcode()
 * @todo Need to document what this really does...
 * @var obj $gallery Instance of PMC_Gallery_View
 */
?>

<div class="gallery">
	<?php
	$gallery->the_navigation();
	$gallery->the_image();
	$gallery->the_count();
	$gallery->the_caption();
	$gallery->the_thumbs();
	?>
</div>

<?php
//EOF
