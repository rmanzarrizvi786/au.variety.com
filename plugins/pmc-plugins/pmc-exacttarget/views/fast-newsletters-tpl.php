<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
?>
<div class="wrap">
	<h2>Options</h2>

	<h2>Alert Types</h2>
	<?php

	if ( is_array( $notices ) && ! empty( $notices ) ) {

		foreach ( $notices as $notice_type => $notice ) {

			echo "<div class='" . esc_attr( $notice_type ) . " notice-info'><ul>";
			echo '<li>' . esc_html( $notice ) . '</li>';
			echo '</ul></div>';
		}
	}
	?>
	<a href="<?php menu_page_url( 'sailthru_fast_newsletter' ); ?>" id="add_alert_type">Add New
		Breaking News Alert</a>

	<table cellspacing="2" cellpadding="2" class="widefat editablecontainerBNA">
		<tbody>
		<tr style="height: 1.5em;" class="alternate">
			<td align="left"><strong>TITLE</strong></td>
			<td align="left"><strong>DATA EXTENSION</strong></td>
			<td align="left"><strong>TEMPLATE</strong></td>
			<td align="left"><strong>TAG</strong></td>
			<td align="left"><strong>EDIT</strong></td>
			<td align="left"><strong>DELETE</strong></td>
			<td align="left"><strong>ACTION</strong></td>
		</tr>
		<?php	 if ( $sailthru_types ) {
			$et_data_extensions = \PMC\Exacttarget\Cache::get_instance()->get_data_extensions();

			$content_builder_templates = \PMC\Exacttarget\Cache::get_instance()->get_templates_from_content_builder();
			$sailthru_templates        = \PMC\Exacttarget\Cache::get_instance()->get_templates();

			foreach ( $sailthru_types as $key => $type ) {

				$data_extension = $type['dataextension'];
				if ( ! empty( $et_data_extensions[ $data_extension ] ) ) {
					$data_extension = $et_data_extensions[ $data_extension ];
				}

				$action_url_args = [
					'action'          => '',
					'newsletter_name' => urlencode( $key ),
				];

				$template = "";

				if ( ! empty( $type['content_builder'] ) && 'yes' === $type['content_builder'] && ! empty( $content_builder_templates[ $type['template'] ] ) ) {
					$template = $content_builder_templates[ $type['template'] ];
				} else {
					$template = $sailthru_templates[ $type['template'] ];
				}

				$newsletter_name   = ( ! empty( $type['content_builder'] ) && 'yes' === $type['content_builder'] ) ? $key . ' (CB)' : $key;
				$newsletter_status = ( ! empty( $type['newsletter_status'] ) && 'disabled' === $type['newsletter_status'] ) ? ' - Disabled' : '';

				?>
			<tr>
				<td><?php echo esc_html( stripslashes( $newsletter_name ) ); ?> <strong style="color:red"> <?php echo esc_html( $newsletter_status ); ?> </strong></td>
				<td><?php echo stripslashes( esc_html( $data_extension ) );?></td>
				<td><?php echo stripslashes( esc_html( $template ) );?></td>
				<td><?php if( isset( $type['post_tag_name'] ) ){
							echo stripslashes( esc_html( $type['post_tag_name'] ) );
						}?>
				</td>
				<td><a
					href="<?php echo esc_url( menu_page_url( 'sailthru_fast_newsletter', false ) . "&edit_type=" ). urlencode( $key ); ?>">Edit</a>
				</td>
				<td>
					<?php
						$delete_action_args           = $action_url_args;
						$delete_action_args['action'] = 'delete';

						$action_url = add_query_arg(
							$delete_action_args,
							menu_page_url( 'sailthru_fast_newsletters', false ) . '&' . $nonce->get_query_string()
						);
					?>
					<a class="delete_breakingnews" href="<?php echo esc_url( $action_url ); ?>">Delete</a>
				</td>
				<td>
					<?php
						$action_name                  = ( empty( $type['newsletter_status'] ) || 'enabled' === $type['newsletter_status'] ) ? 'Disable' : 'Enable';
						$status_action_args           = $action_url_args;
						$status_action_args['action'] = $action_name;

						$action_url = add_query_arg(
							$status_action_args,
							menu_page_url( 'sailthru_fast_newsletters', false ) . '&' . $nonce->get_query_string()
						);
					?>
					<a class="" href="<?php echo esc_url( $action_url ); ?>"> <?php echo esc_html( $action_name ); ?> </a>
				</td>
			</tr>
				<?php
			}
		}
		?>
		</tbody>
	</table>
</div>
