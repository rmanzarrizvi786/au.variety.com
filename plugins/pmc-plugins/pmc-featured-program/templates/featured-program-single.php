<?php
/**
 * Single Featured Program template.
 *
 * @package pmc-sheknows-2020
 */

get_header();

// Normal Featured Program content
$header = [];

$header_image_id = get_post_meta( get_the_ID(), \PMC\Featured_Program\Config::get_instance()->prefix() . '_fp_banner_image', true );

$field_overrides = \PMC\Featured_Program\Utils::get_instance()->get_field_overrides( get_the_ID() );

$header['c_lazy_image']          = \PMC\Featured_Program\Utils::get_instance()->get_image_data( [], $header_image_id, 'featured-large' );
$header['image']                 = \PMC\Featured_Program\Utils::get_instance()->get_image_markup( $header['c_lazy_image'], 'lrv-u-width-auto u-max-height-150 lrv-u-display-block lrv-u-margin-lr-auto' );
$header['c_dek']['c_dek_markup'] = $field_overrides['dek'] ?? '';
$featured_program_posts          = \PMC\Featured_Program\Plugin::get_instance()->get_featured_program_posts( get_the_ID() );
    
if ( empty( $featured_program_posts ) ) {
    return;
}

$card_array = [];

foreach ( $featured_program_posts as $featured_program_post ) {

    $featured_program_post_id = $featured_program_post->ID;

    $permalink = get_the_permalink( $featured_program_post_id );

    $card = [];

    $field_overrides = \PMC\Core\Inc\Theme::get_instance()->get_field_overrides( $featured_program_post_id );

    $card['c_title']['c_title_url']    = $permalink;
    $card['c_title']['c_title_markup'] = $field_overrides['hed'];

    $card['c_lazy_image'] = \PMC\Featured_Program\Utils::get_instance()->get_image_data( $card['c_lazy_image'], get_post_thumbnail_id( $featured_program_post_id ), 'landscape-medium', $permalink );
    $card['image']        = \PMC\Featured_Program\Utils::get_instance()->get_image_markup( $card['c_lazy_image'], 'lrv-u-width-100p lrv-u-height-auto lrv-u-display-block' );

    $card_array[] = $card;
}

?>

<div class="lrv-a-wrapper lrv-u-padding-t-1 lrv-u-padding-b-2">
    <header class="lrv-u-padding-tb-1 lrv-u-padding-t-2@desktop u-margin-lr-1@mobile-max lrv-u-margin-tb-1 lrv-u-margin-tb-2@desktop">
        <h1>
            <?php echo wp_kses_post( $header['image'] );?>
        </h1>
        <div class="c-dek lrv-u-font-size-16 lrv-u-color-grey-dark lrv-u-text-align-center u-width-67p@tablet lrv-u-display-block lrv-u-margin-lr-auto lrv-u-margin-tb-1">
            <?php echo wp_kses_post( $header['c_dek']['c_dek_markup'] ?? '' ); ?>
        </div>
    </header>

    <div class="lrv-a-grid lrv-a-cols3@tablet lrv-u-padding-lr-1 u-padding-lr-00@tablet">
    <?php foreach ( $card_array as $card ) : ?>
        <article class="lrv-u-text-align-center">
            <div class="lrv-a-glue-parent u-padding-t-1@mobile-max lrv-u-flex-direction-column lrv-u-height-100p lrv-u-justify-content-start lrv-u-margin-r-00">
                <div class="lrv-a-glue-parent">
                    <div class="c-lazy-image ">
                        <a href="<?php echo esc_url( $card['c_title']['c_title_url'] ); ?>"
                            class="c-lazy-image__link lrv-a-unstyle-link">
                            <div class="lrv-a-crop-4x3">
                                <?php echo wp_kses_post( $card['image'] ); ?>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="lrv-u-flex lrv-u-flex-direction-column lrv-u-height-100p">
                <h3 class="lrv-u-margin-t-1">
                    <a href="<?php echo esc_url( $card['c_title']['c_title_url'] ); ?>" class="c-title__link ">
                    <?php echo wp_kses_post( $card['c_title']['c_title_markup'] ); ?>
                    </a>
                </h3>
                <div>
                    <a class="c-button larva event-read-more" href="<?php echo esc_url( $card['c_title']['c_title_url'] ); ?>">
                        <span class="c-button__inner u-letter-spacing-012"> Read more </span>
                    </a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    </div>
</div>

<?php
get_footer();
