<div class="wrap">
	<?php screen_icon( 'edit' ); ?>
	<h2>Print Articles
		<?php if ( isset($_REQUEST['s']) && $_REQUEST['s'] ) : ?>
			<span class="subtitle">Search results for &#8220;<?php echo get_search_query() ?>&#8221;</span>
		<?php endif; ?>

		<a href="<?php echo admin_url( 'post-new.php?pmc_view=print' ); ?>" class="add-new-h2">Add New</a>
		<a href="<?php echo admin_url( 'edit.php' ); ?>" class="add-new-h2">View Online Articles</a>
	</h2>

	<?php $wp_list_table->views(); ?>
	<?php $wp_list_table->active_filters(); ?>

	<form id="posts-filter" action="edit.php" method="get">

		<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
		<input type="hidden" name="page" value="<?php echo 'PMC_Print_View'; ?>" />
		<?php if ( isset( $_REQUEST['print_status'] ) ) : ?>
			<input type="hidden" name="print_status" value="<?php echo esc_attr( $_REQUEST['print_status'] ); ?>" />
		<?php endif; ?>

		<?php $wp_list_table->display(); ?>

	</form>
	<br class="clear" />
</div>