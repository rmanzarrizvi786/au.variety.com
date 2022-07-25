<?php
$base_url = 'https://thebrag.com/jobs/';

if (!isset($size)) {
    $size = 6;
}
if (!isset($pos)) {
    $pos = 'sidebar';
}
if (!isset($order_by)) {
    $order_by = 'desc';
}

$url = $base_url . "wp-json/api/v1/jobs?order={$order_by}&size={$size}";
$jobs_res = wp_remote_get($url);
$jobs = json_decode(wp_remote_retrieve_body($jobs_res));

$is_home = is_home() || is_front_page();

if ($jobs && is_array($jobs) && !empty($jobs)) {
    if ($is_home) {
?>
        <section class="u-margin-t-125 lrv-a-wrapper lrv-a-grid u-grid-gap-0">
            <div class=" homepage-awards__curation-and-list // lrv-u-height-1010p lrv-a-wrapper lrv-a-grid u-grid-gap-0">
            <?php } ?>
            <section class="brag-jobs-sidebar // u-border-t-6 lrv-u-background-color-white lrv-u-padding-b-1 lrv-u-padding-lr-1 u-border-color-picked-bluewood lrv-u-padding-lr-1@tablet ">
                <div class="must-read-widget__header">
                    <?php
                    $c_heading = [
                        'c_heading_classes' => 'lrv-u-font-weight-bold lrv-u-font-family-secondary u-font-size-25 u-letter-spacing-009@mobile-max u-line-height-1 lrv-u-text-align-center@mobile-max lrv-u-margin-t-050 u-margin-b-075 u-margin-b-2@tablet',
                        'c_heading_id_attr' => 'section-heading',
                        'c_heading_text' => 'Latest Jobs',
                        'c_heading_url' => 'https://thebrag.com/jobs/',
                        'c_heading_link_classes' => 'c-heading larva  a-font-accent-m lrv-u-text-align-center lrv-u-padding-tb-075 u-padding-t-025@tablet',
                        'c_heading_outer' => false,
                        'c_heading_outer_classes' => '',
                        'c_heading_is_primary_heading' => false,
                    ];
                    \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
                </div>
                <div class="sidebar-widget">
                    <ul class="o-jobs-list <?php echo $pos; ?> lrv-a-unstyle-list u-border-color-brand-secondary-40 u-border-t-1 <?php echo !$is_home ? 'a-separator-b-1' : ''; ?> <?php echo 'article-bottom' == $pos || $is_home ? 'd-flex flex-wrap' : ''; ?>">
                        <?php
                        foreach ($jobs as $job) {
                        ?>
                            <li class="o-tease-list__item u-border-color-brand-secondary-40 <?php echo $is_home ? 'col-12 col-md-4' : ('article-bottom' == $pos ? 'col-12 col-md-6' : ''); ?>">
                                <a href="<?php echo $job->link; ?>" target="_blank">
                                    <article class="o-tease  lrv-u-flex">
                                        <div class="o-tease__primary lrv-u-flex-grow-1">
                                            <span class="c-span  lrv-u-display-block lrv-u-margin-b-025 lrv-u-text-transform-uppercase u-font-family-basic lrv-u-font-size-12 u-font-size-13@tablet u-letter-spacing-009">
                                                <span class="c-span__link u-color-pale-sky-2 u-color-black:hover lrv-u-display-block lrv-u-padding-t-050 lrv-u-padding-b-025">
                                                    <?php echo $job->company_name; ?> | <?php echo $job->location; ?>
                                                </span>
                                            </span>

                                            <h3 id="title-of-a-story" class="c-title  a-font-secondary-bold-xs lrv-u-padding-b-025">
                                                <span class="c-title__link lrv-u-color-black lrv-u-display-block u-color-brand-secondary-50:hover">
                                                    <?php echo $job->title; ?>
                                                </span>
                                            </h3>
                                        </div>

                                        <div class="o-tease__secondary lrv-u-flex-shrink-0 lrv-u-margin-r-1@tablet u-padding-l-075 u-padding-l-00@tablet lrv-u-padding-tb-075 u-order-n1@tablet  u-width-25p">
                                            <div class="c-lazy-image  ">
                                                <span class="c-lazy-image__link lrv-a-unstyle-link">
                                                    <div class="lrv-a-crop-1x1" style="">
                                                        <img class="c-lazy-image__img lrv-u-background-color-grey-lightest lrv-u-width-100p lrv-u-display-block lrv-u-height-auto" src="<?php echo $job->image; ?>" alt="<?php echo $job->company_name; ?>" height="" width="100">
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </article>
                                </a>
                            </li>
                        <?php
                        } ?>
                    </ul>

                    <div class="d-flex <?php echo $is_home ? 'flex-column flex-md-row' : ('article-bottom' == $pos ? 'flex-row' : 'flex-column'); ?> justify-content-between" style="padding-left: .5rem; padding-right: .5rem; margin-top: .5rem;">
                        <div class="d-flex">
                            <div>Powered by</div>
                            <div style="margin-left: .25rem"><a href="https://thebrag.com/jobs/" target="_blank" rel="noreferrer"><img src="https://thebrag.com/jobs/wp-content/themes/bj/images/brag-jobs-logo.svg" width="48"></a></div>
                        </div>
                        <div style="text-align: center; margin-top: .5rem;">
                            Looking to hire?
                            <?php echo 'article-bottom' != $pos && !$is_home ? '<br>' : ''; ?>
                            <a href="https://thebrag.com/jobs/employer/job/post/" target="_blank" rel="noreferrer">List your vacancy today!</a>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            if ($is_home) {
            ?>
            </div>
        </section>
<?php }
        }
