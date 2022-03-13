<div class="tablenav <?php echo esc_attr( $which ); ?>">
	<div class="alignleft actions bulkactions">
		<?php
		if ( 'top' === $which ) {
			$table->extra_tablenav( $which );
		}
		?>
	</div>
	<div>
		<?php $table->pagination( $which ); ?>
	</div>
</div>
