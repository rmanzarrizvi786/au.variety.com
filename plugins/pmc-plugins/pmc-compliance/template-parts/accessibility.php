<?php

$blogname = get_bloginfo( 'name' );
if ( ! defined( 'PMC_SITE_NAME' ) || empty( PMC_SITE_NAME ) ) {

	if ( empty( $blogname ) ) {
		return;
	}

	$sitename = strtolower( $blogname );

} else {
	$sitename = PMC_SITE_NAME;
}

$data = \PMC\Compliance\Accessibility::get_instance()->get_site_data( $sitename );

$data = wp_parse_args(
	$data,
	[
		'sitename' => $blogname,
		'email'    => 'accessibility@pmc.com',
		'address'  => $blogname . ', Attn: Accessibility, 11175 Santa Monica Blvd, Los Angeles, CA 90025',
	]
);

?>
<strong>Accessibility Commitment</strong>
<p>
<?php echo esc_html( $data['sitename'] ); ?> is committed to ensuring digital accessibility to the widest possible audience, regardless of ability. We are continually improving the user experience for everyone, and aim to comply with relevant accessibility standards, including WCAG 2.1 accessibility standards up to level AA. We are devoting resources and implementing technological improvements to continue enhancing the accessibility of our website and mobile apps.
</p>
<strong>Feedback</strong>
<p>
    We welcome your feedback on the accessibility of <?php echo esc_html( $data['sitename'] ); ?> to improve your site
    experience. Please let us know if you encounter accessibility barriers
    on <?php echo esc_html( $data['sitename'] ); ?>, including any video that is not already captioned:
<ul>
    <li>
        E-mail: <a href="<?php echo esc_attr( 'mailto:' . $data['email'] ); ?>">
			<?php echo wp_kses_post( $data['email'] ); ?>
        </a>
    </li>
    <li>
        Postal address: <?php echo wp_kses_post( $data['address'] ); ?>
    </li>
</ul>
</p>
<p>
    We will work with you to provide the information you seek through alternative communication methods, if possible.
</p>
<p>
    Our accessibility efforts are ongoing to ensure that our website and mobile site are accessible to everyone.
</p>
<strong>Limitations and alternatives</strong>
<p>
    Despite our best efforts to ensure accessibility, there may be some limitations. Below is a description of known
    limitations, and potential solutions. Please contact us if you observe an issue not listed below.
    Known limitations:
    <ol>
        <li>
<p>
    <strong>User generated content, such as comments or forum posts:</strong> Since this content is created by users
    it may not include accessibility considerations.
</p>
</li>
<li>
    <p>
        <strong>Site navigation:</strong> Our site navigation has not been fully tested with assistive technologies. We
        are in the process of fully evaluating and testing the ability to navigate our site using assistive
        technologies, to remediate any issues we find. Please send us an email at <a href="<?php echo esc_attr( 'mailto:' . $data['email'] ); ?>"><?php echo wp_kses_post( $data['email'] ); ?>
        </a> to report any issues you encounter.
    </p>
</li>
<li>
    <p><strong>Older content:</strong> Content created over a year ago may not work well with assistive technologies.
        This content may have been created before we had processes in place to ensure best practices such as meaningful
        alternative descriptions for images. Please send us an email at <a href="<?php echo esc_attr( 'mailto:' . $data['email'] ); ?>"><?php echo wp_kses_post( $data['email'] ); ?></a>
        to report any issues you encounter.
    </p>
</li>
</ol>
</p>
