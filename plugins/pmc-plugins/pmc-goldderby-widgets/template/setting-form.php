<?php
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
?>

<div id="offsite_leaderboard">
        <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html( 'Title:' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <?php
        $counter = 1;

        if( ! empty( $instance['featured_league'] ) ) {

                $selected_featured_league = ! empty( $instance['featured_league'] ) ? (int)( $instance['featured_league'] ) : 0;
                $selected_featured_league_category = ! empty( $instance['featured_league_category'] ) ? (int)( $instance['featured_league_category'] ) : 0;
                $selected_user_type = ! empty( $instance['user_type'] ) ? $instance['user_type'] : '';
                $widget_width = ! empty( $instance['widget_width'] ) ? (int)( $instance['widget_width'] ) : 0;
                $total_candidates = ! empty( $instance['total_candidates'] ) ? (int)( $instance['total_candidates'] ) : 0;

        ?>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('featured_league') ); ?>"><?php echo esc_html('Featured League'); ?></label>

                        <select class="widefat offsite-widget-league" id="<?php echo esc_attr( $this->get_field_id( 'featured_league' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('featured_league') ); ?>">
                                <?php foreach ( $featured_leagues as $featured_league_id => $featured_league ) : ?>
                                        <option value="<?php echo esc_attr( (int)( $featured_league_id ) ); ?>" <?php selected( $selected_featured_league, esc_attr( (int)( $featured_league_id ) ) ); ?>><?php echo esc_html( $featured_league ); ?></option>
                                <?php endforeach; ?>
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('featured_league_category') ); ?>"><?php echo esc_html('Category'); ?></label>

                        <select class="widefat offsite-widget-league-category" id="<?php echo esc_attr( $this->get_field_id( 'featured_league_category' ) ); ?>" name="<?php echo esc_attr($this->get_field_name('featured_league_category')); ?>">
                                <?php foreach ( $featured_league_categories as $featured_league_category_id => $featured_league_category ) { ?>
                                        <option value="<?php echo esc_attr( (int)( $featured_league_category_id ) ); ?>" <?php selected( $selected_featured_league_category, esc_attr( (int)( $featured_league_category_id ) ) ); ?> ><?php echo esc_html( $featured_league_category ); ?></option>
                                        <?php
                                } ?>
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('user_type') ); ?>"><?php echo esc_html('User Type'); ?></label>

                        <select class="widefat offsite-widget-user-type" id="<?php echo esc_attr( $this->get_field_id( 'user_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('user_type') ); ?>">
                                <?php foreach ( $user_types as $user_value => $user_type ) { ?>
                                        <option value="<?php echo esc_attr( $user_value ); ?>" <?php selected( $selected_user_type, esc_attr( $user_value ) ); ?> ><?php echo esc_html( $user_type ); ?></option>
                                        <?php
                                } ?>
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('widget_width') ); ?>"><?php echo esc_html('Widget Width'); ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'widget_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_width' ) ); ?>" type="text" value="<?php echo esc_attr( $widget_width ); ?>">
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('total_candidates') ); ?>"><?php echo esc_html('No. of Candidates'); ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'total_candidates' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'total_candidates' ) ); ?>" type="text" value="<?php echo esc_attr( $total_candidates ); ?>">
                </p>

        <?php
        } else {

        ?>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('featured_league') ); ?>"><?php echo esc_html('Featured League'); ?></label>

                        <select class="widefat offsite-widget-league" id="<?php echo esc_attr( $this->get_field_id( 'featured_league' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('featured_league') ); ?>">
                                <?php foreach ( $featured_leagues as $featured_league_id => $featured_league ) { ?>
                                        <option value="<?php echo esc_attr( (int)( $featured_league_id ) ); ?>" ><?php echo esc_html( $featured_league ); ?></option>
                                        <?php
                                } ?>
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('featured_league_category') ); ?>"><?php echo esc_html('Category'); ?></label>

                        <select class="widefat offsite-widget-league-category" id="<?php echo esc_attr( $this->get_field_id( 'featured_league_category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('featured_league_category') ); ?>">
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('user_type') ); ?>"><?php echo esc_html('User Type'); ?></label>

                        <select class="widefat offsite-widget-user-type" id="<?php echo esc_attr( $this->get_field_id( 'user_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('user_type') ); ?>">
                                <?php foreach ( $user_types as $user_value => $user_type ) { ?>
                                        <option value="<?php echo esc_attr( $user_value ); ?>" ><?php echo esc_html( $user_type ); ?></option>
                                        <?php
                                } ?>
                        </select>
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('widget_width') ); ?>"><?php echo esc_html('Widget Width'); ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'widget_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_width' ) ); ?>" type="text" value="300">
                </p>
                <p>
                        <label for="<?php echo esc_attr( $this->get_field_id('total_candidates') ); ?>"><?php echo esc_html('No. of Candidates'); ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'total_candidates' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'total_candidates' ) ); ?>" type="text" value="5">
                </p>

        <?php
        }
        ?>

</div>