<?php

namespace TBM;

class Jobs extends \Variety\Inc\Widgets\Variety_Base_Widget
{
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::ID,
            __('TBM - Jobs', 'pmc-variety'),
            array('description' => __('Displays Latest Jobs from The Brag Jobs.', 'pmc-variety'))
        );
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * @throws \Exception
     */
    public function widget($args, $instance)
    {
        \PMC::render_template(
            sprintf('%s/template-parts/widgets/brag-jobs.php', untrailingslashit(CHILD_THEME_PATH)),
            [],
            true
        );
    }

    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     *
     * @return string|void
     * @throws \Exception
     * @since 2017.1.0
     * @see   WP_Widget::form()
     *
     */
    public function form($instance)
    {
?>
        <p class="no-options-widget">
            <?php esc_html_e('There are no options for this widget.', 'pmc-variety'); ?>
        </p>
<?php
        return 'noform';
    }
}
