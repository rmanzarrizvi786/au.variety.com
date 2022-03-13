<div id="pmc-group-users-modal">

	<table class="wp-list-table widefat fixed adm-list" cellspacing="0" id="pmc-group-list-modal" class="pmc-group-list-modal">
		<thead>
			<tr>
				<th scope="col" class="manage-column pmc-groups-login"><?php esc_html_e( 'Login' ); ?></th>
				<th scope="col" class="manage-column pmc-groups-displayname"><?php esc_html_e( 'Display Name' ); ?></th>
				<th scope="col" class="manage-column pmc-groups-emailaddress"><?php esc_html_e( 'Email Address' ); ?></th>
				<th scope="col" class="manage-column pmc-groups-action"><?php esc_html_e( 'Action' ); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<div class="acm-ui-wrapper wrap">
	<h2><?php esc_html_e( 'PMC Groups Manager' ); ?></h2>
</div>

<table class="wp-list-table widefat fixed adm-list" cellspacing="0" id="pmc-group-list">
	<thead>
	<tr>
		<th scope="col" class="manage-column"><?php esc_html_e( 'Group' ); ?></th>
		<th scope="col" class="manage-column"><?php esc_html_e( 'Ticket' ); ?></th>
		<th scope="col" class="manage-column"><?php esc_html_e( 'Description' ); ?></th>
		<th scope="col" class="manage-column"><?php esc_html_e( 'Count' ); ?></th>
		<th scope="col" class="manage-column"><?php esc_html_e( 'Users in Group' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php

	if ( $pmc_groups ) {
		foreach ( $pmc_groups as $slug => $config ) {
			$tickets = explode( ',', $config['ticket'] );
			$tickets = array_map( 'trim', $tickets );
			?>
				<tr class="<?php echo esc_attr( $slug );?>" id="<?php echo esc_attr( 'row-' . $slug );?>">
					<td class="key">
						<?php esc_html_e( $slug );?> <br />
						<a href="#" data-groupkey="<?php echo esc_attr( $slug );?>" class="btn-show-users"><?php esc_html_e( 'Edit Group' ); ?></a>
					</td>
					<td class="ticket">
						<?php foreach ( $tickets as $ticket_index => $ticket_num ) : ?>
							<a target="_blank" href="<?php echo esc_url( \PMC\Groups\Group::TICKET_URL . $ticket_num );?>"><?php esc_html_e( $ticket_num ); ?></a>
							<?php if ( count( $tickets ) - 1 !== $ticket_index ) : ?>
								,&nbsp;
							<?php endif; ?>
						<?php endforeach; ?>
					</td>
					<td class="desc"><?php esc_html_e( $config['description'] );?></td>
					<td class="count">
						<a href="#" data-groupkey="<?php echo esc_attr( $slug );?>" class="btn-show-users"><?php esc_html_e( $config['user_count'] );?></a>
					</td>
					<td class="users">
						<?php
						$x = sizeof( $config['users'] );
						foreach( $config['users'] as $user_login ) {
							$x--;
						?>
							<a href="<?php echo esc_url( admin_url( 'users.php?s=' . $user_login ) );?>" id="<?php echo  esc_attr('pmc-groups-user-' . $user_login );?>"><?php esc_html_e( $user_login ); ?></a><?php if ( $x >= 1 ) :?>, <?php endif; ?>
						<?php
						}
						?>

					</td>
				</tr>
			<?php
		}
	}
	?>
	</tbody>
</table>
