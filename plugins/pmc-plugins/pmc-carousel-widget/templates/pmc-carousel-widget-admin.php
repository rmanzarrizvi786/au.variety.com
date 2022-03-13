<?php
$template_list = apply_filters( 'pmc_carousel_widget_templates', [] );
$thumbsizes    = apply_filters( 'pmc_carousel_widget_thumbsizes', \PMC\Image\get_intermediate_image_sizes() );
?>
<p>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:'); ?></label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'title' ) ); ?>"
		name="<?php echo esc_attr( $widget->get_field_name( 'title' ) ); ?>"
		value="<?php echo esc_attr( isset($instance['title']) ? $instance['title'] : '' ); ?>"
		class="widefat" type="text" />
</p>

<p>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'template' ) ); ?>"><?php esc_html_e('Template:'); ?></label>
	<select id="<?php echo esc_attr( $widget->get_field_id( 'template' ) ); ?>"
		name="<?php echo esc_attr( $widget->get_field_name( 'template' ) ); ?>"
		class="widefat">
		<option value=""><?php esc_html_e('-- Select a template --'); ?></option>
		<?php
		$template = isset($instance['template']) ? $instance['template'] : '';
		foreach ($template_list as $key => $name) {
		?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected($key, $template); ?>>
				<?php echo esc_html( $name ); ?>
			</option>
		<?php
		}
		?>
	</select>
</p>

<p>
	<a style="float: right" href="<?php echo esc_url( get_admin_url('', 'edit-tags.php?taxonomy=pmc_carousel_modules') ); ?>"><?php esc_html_e('Manage','pmc-footwearnews'); ?></a>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'module' ) ); ?>"><?php esc_html_e('Module:'); ?></label>
	<?php wp_dropdown_categories(array(
		'show_option_all' => '-- Select a module --',
		'orderby' => 'name',
		'hide_empty' => false,
		'id' => $widget->get_field_id( 'module' ),
		'name' => $widget->get_field_name( 'module' ),
		'selected' => isset($instance['module']) ? $instance['module'] : 0,
		'hierarchical' => true,
		'taxonomy' => 'pmc_carousel_modules',
		'class' => 'widefat'
	)) ?>
</p>

<p>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'thumbsize' ) ); ?>"><?php esc_html_e('Thumb size:'); ?></label>
	<select id="<?php echo esc_attr( $widget->get_field_id( 'thumbsize' ) ); ?>"
		name="<?php echo esc_attr( $widget->get_field_name( 'thumbsize' ) ); ?>"
		class="widefat">
		<option value=""><?php esc_html_e('-- Select a thumb size --'); ?></option>
		<?php
		$thumbsize = isset($instance['thumbsize']) ? $instance['thumbsize'] : '';
		foreach ($thumbsizes as $size) {
		?>
			<option value="<?php echo esc_attr( $size ); ?>" <?php selected($size, $thumbsize); ?>>
				<?php echo esc_html( $size ); ?>
			</option>
		<?php
		}
		?>
	</select>
</p>

<p>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'limit' ) ); ?>"><?php esc_html_e('Post Limit:','pmc-footwearnews'); ?></label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'limit' ) ); ?>"
		name="<?php echo esc_attr( $widget->get_field_name( 'limit' ) ); ?>"
		value="<?php echo esc_attr( isset($instance['limit']) ? $instance['limit'] : self::DEFAULT_LIMIT ); ?>"
		maxlength="1" size="3" type="text" />
</p>
