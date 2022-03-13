<?php
/*
 * Template to the email body for new device notifications
 *
 * @since 2016-09-29 Corey Gilmore
 *
 */

?>

Hello,

This is an automated email to all <?php echo esc_html( $args['blogname'] ); ?> site moderators to inform you that <?php echo esc_html( $user_obj->display_name ); ?> has logged into <?php echo esc_url( trailingslashit( home_url() ) ); ?> from a device that we don't recognize or that had last been used before <?php echo esc_html( $args['installed_time'] ); ?> when this monitoring was first enabled.

It's likely that <?php echo esc_html( $user_obj->display_name ); ?> simply logged in from a new web browser or computer (in which case this email can be safely ignored), but there is also a chance that their account has been compromised and someone else has logged into their account.

Here are some details about the login to help verify if it was legitimate:

WP.com Username: <?php echo esc_html( $user_obj->user_login ); ?>

IP Address: <?php echo esc_html( $user_details['ip'] ); ?>

Hostname: <?php echo esc_html( $args['hostname'] ); ?>

Guessed Location: <?php echo esc_html( $args['location'] ); ?> <?php if( $user_details['is_mobile'] ) : ?>(likely completely wrong for mobile devices)<?php endif; ?>

Is Mobile: <?php echo esc_html( $is_mobile_message ); ?>

Browser User Agent: <?php echo esc_html( strip_tags( $user_details['user_agent'] ) ); ?>

Country: <?php echo esc_html( $user_details['location']->country_short ); ?>

<?php echo esc_html( $high_risk_message ); ?>


If you believe that this log in was unauthorized, please immediately reply to this e-mail and our VIP team will work with you to remove <?php echo esc_html( $user_obj->display_name ); ?>'s access.

You should also advise <?php echo esc_html( $user_obj->display_name ); ?> to change their password immediately if you feel this log in was unauthorized:

https://support.wordpress.com/passwords/

Feel free to also reply to this e-mail if you have any questions whatsoever.

- WordPress.com VIP
