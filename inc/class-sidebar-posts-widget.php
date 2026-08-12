<?php
if (!defined('ABSPATH')) exit; // Block direct access

class Custom_Sidebar_Posts_Widget extends WP_Widget {

    public function __construct() {
        $widget_ops = array(
            'classname'                   => 'j-sidebar-widget-container',
            'description'                 => __('Hiển thị danh sách bài viết kèm ảnh đại diện cho Sidebar.', 'textdomain'),
            'show_instance_in_rest'       => true,
            'customize_preview_post_args' => array(
                'save_post_after_execution' => true,
            ),
        );

        parent::__construct(
            'custom_sidebar_posts_widget',
            __('📌 JSidebar Posts Widget', 'textdomain'),
            $widget_ops
        );
    }

    /**
     * Render nội dung Widget ngoài Front-end
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        $title      = !empty($instance['title']) ? $instance['title'] : __('Bài viết mới', 'textdomain');
        $number     = !empty($instance['number']) ? absint($instance['number']) : 5;
        $order_by   = !empty($instance['order_by']) ? $instance['order_by'] : 'date';
        $show_thumb = isset($instance['show_thumb']) ? (bool) $instance['show_thumb'] : true;
        $show_date  = isset($instance['show_date']) ? (bool) $instance['show_date'] : true;

        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        // Query lấy danh sách bài viết
        $query_args = array(
            'post_type'           => 'post',
            'posts_per_page'      => $number,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
        );

        if ($order_by === 'rand') {
            $query_args['orderby'] = 'rand';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }

        $posts_query = new WP_Query($query_args);

        if ($posts_query->have_posts()) : ?>
            <ul class="j-sidebar-list">
                <?php while ($posts_query->have_posts()) : $posts_query->the_post(); ?>
                    <li class="j-sidebar-item">
                        <?php if ($show_thumb) : ?>
                            <div class="j-sidebar-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('thumbnail', array('alt' => get_the_title())); ?>
                                    <?php else : ?>
                                        <img src="https://via.placeholder.com/80x80?text=No+Image" alt="<?php the_title_attribute(); ?>" />
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="j-sidebar-info">
                            <h4 class="j-sidebar-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <?php if ($show_date) : ?>
                                <span class="j-sidebar-date">📅 <?php echo get_the_date(); ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
            <?php
            wp_reset_postdata();
        else :
            echo '<p class="j-sidebar-empty">' . __('Không có bài viết nào.', 'textdomain') . '</p>';
        endif;

        echo $args['after_widget'];
    }

    /**
     * Form cấu hình trong Admin
     */
    public function form($instance) {
        $title      = !empty($instance['title']) ? $instance['title'] : __('Bài viết mới', 'textdomain');
        $number     = !empty($instance['number']) ? absint($instance['number']) : 5;
        $order_by   = !empty($instance['order_by']) ? $instance['order_by'] : 'date';
        $show_thumb = isset($instance['show_thumb']) ? (bool) $instance['show_thumb'] : true;
        $show_date  = isset($instance['show_date']) ? (bool) $instance['show_date'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Tiêu đề Widget:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Số lượng bài viết:'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order_by')); ?>"><?php _e('Sắp xếp theo:'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order_by')); ?>" name="<?php echo esc_attr($this->get_field_name('order_by')); ?>">
                <option value="date" <?php selected($order_by, 'date'); ?>><?php _e('Mới nhất'); ?></option>
                <option value="rand" <?php selected($order_by, 'rand'); ?>><?php _e('Ngẫu nhiên'); ?></option>
            </select>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_thumb); ?> id="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>" name="<?php echo esc_attr($this->get_field_name('show_thumb')); ?>" value="1" />
            <label for="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>"><?php _e('Hiển thị ảnh đại diện'); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo esc_attr($this->get_field_id('show_date')); ?>" name="<?php echo esc_attr($this->get_field_name('show_date')); ?>" value="1" />
            <label for="<?php echo esc_attr($this->get_field_id('show_date')); ?>"><?php _e('Hiển thị ngày đăng bài'); ?></label>
        </p>
        <?php
    }

    /**
     * Xử lý lưu cài đặt
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title']      = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number']     = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        $instance['order_by']   = (!empty($new_instance['order_by'])) ? sanitize_text_field($new_instance['order_by']) : 'date';
        $instance['show_thumb'] = !empty($new_instance['show_thumb']) ? 1 : 0;
        $instance['show_date']  = !empty($new_instance['show_date']) ? 1 : 0;
        return $instance;
    }
}