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
}
