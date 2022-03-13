<div class="wrap">
	<h1 class="wp-heading-inline">Amazon Onsite</h1>
	<hr class="wp-header-end">

	<?php
		$table = new \PMC\Amzn_Onsite\Table();
		$table->views();
		$table->prepare_items();
		$table->display();
	?>
</div>
