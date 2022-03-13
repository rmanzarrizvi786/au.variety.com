**explain how to implement the plugin in a new site**

Include Plugin
pmc_load_plugin( 'pmc-gallery', 'pmc-plugins' );

Use as below and implement a css
$gallery = PMC_Gallery_Thefrontend::load_gallery();
$gallery->the_navigation( 'both', array( 'Prev', 'Next' ) );
$gallery->the_count('xofy');
$gallery->the_backlink();
$gallery->the_thumbs( 'medium-preview', 'filmstrip', 6, 'thumb', null, null );
$gallery->the_image( 'hero-image' );
$gallery->the_caption( 9999 );
?>
<!-- posted -->
<span class="posted">
	Posted <?php echo get_the_time( 'D, F j, Y g:ia T' ); ?>
</span>

Please try to build up from any existing template
@todo A default template CSS was to be worked on






**explain core concepts of the plugin and its architecture**
The Plugin divides a Gallery into visibly seperated segments which are treated as 1 object
  Stage (the_image)
  Thumbs (the_thumbs)
  Navigation Buttons (the_navigation)
  Title (the_title)
  Caption (the_caption)
  Meta (the_meta)
  Count (the_count)

Each of above is a list in which one or more items are visible as controlled by the user
the movement and visibility is controlled by gallery.js

The first load of the gallery throws HTML with images for all visible items
The JS preloads all images it anticipates a user can need within a click
On every movement the above step is executed so the gallery always is preloaded for a users next move
The movement triggers as needed for ads refresh, interstitial ads, analytics etc.

Not everyone would like the url as /gallery/gallery-name
maybe anything else like /pics/ we have filters to handle

// Override gallery slug use pics
add_filter('pmc_gallery_standalone_slug', 'hollywoodlife_standalone_slug');
/**
 * Filter to change the url of stand alone gallery
 *
 * @return slug for gallery url
 **/
function hollywoodlife_standalone_slug($slug) {
	return 'pics';
}


**list features of the plugin**
This Plugin generates a Gallery capable of working on a desktop or mobile

CSS can be manipulated to have a responsive, fluid or fixed layout as needed.
The Gallery can be navigated using arrow keys, swipe, navigation buttons or Thumbs
The Gallery is designed to load small (only visible images)
The Gallery is designed to preload any image a user can possibly open (any image which can be reached by navigation)
The Gallery by default (standalone) is supposed to be continuous (moves to another gallery after end of one)
Sizes for Images can be specified so you can use a defined size or a default one
The Gallery can also be an inline gallery in which case its not continuous
The Gallery can be a preview in which case we have no navigation the images just lead to the stand alone Gallery
The Preview can have the 1st image picked from featured images if available or from gallery
If we go from a Preview to a gallery its possible to go back to the Article containing the preview
Interstitial Ads can be set if needed, Has different options like no of clicks and duration to show ad.
Also can show an interstitial on first gallery load
With each movement we can rotate ads if needed
Thumbs can be all or n visible
Thumbs can be only images or Images with Caption
With each movement we an attach analytics to the trigger
