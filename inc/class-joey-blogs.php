<?php

/**
 * Main plugin class for Joey Blogs
 *
 * @package Joey_Blogs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Joey_Blogs
 */
class Joey_Blogs
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks()
    {
        // Admin-only hooks.
        if (is_admin()) {
            add_action('add_meta_boxes', array($this, 'register_banner_image_metabox'));
            add_action('save_post', array($this, 'save_banner_image_metabox'));
            add_action('add_meta_boxes', array($this, 'register_blogs_slider_metabox'));
            add_action('save_post', array($this, 'save_blogs_slider_metabox'));
        }

        // Frontend hooks.
        add_filter('theme_page_templates', array($this, 'register_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_global_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_blogs_page_assets'));

        // AJAX hooks (both admin and frontend).
        add_action('wp_ajax_blogs_filter', array($this, 'ajax_blogs_filter'));
        add_action('wp_ajax_nopriv_blogs_filter', array($this, 'ajax_blogs_filter'));
    }

    /**
     * Override the default single post template with our custom template.
     *
     * @param string $template The path to the single post template.
     * @return string
     */
    public function override_single_template($template)
    {
        $custom_template = JOEY_BLOGS_PLUGIN_DIR . 'templates/single-page.php';

        global $post;

        // Kiểm tra xem đây có phải là single post của loại bài viết 'post' không
        if ($post->post_type === 'post') {
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Register our custom page template so it appears in the page template dropdown.
     *
     * @param array $templates Current list of page templates.
     * @return array
     */
    public function register_page_template($templates)
    {
        $templates['templates/blogs-page.php'] = __('Blogs Page', 'nghaihoang9x');
        return $templates;
    }

    /**
     * Load our custom page template when it is selected for a page.
     *
     * @param string $template The path of the template to include.
     * @return string
     */
    public function load_page_template($template)
    {
        global $post, $wp_query;

        // Override category archive template.
        if (is_category()) {
            $custom_template = JOEY_BLOGS_PLUGIN_DIR . 'templates/category-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        // Override single post template - check queried object directly.
        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->post_type) && 'post' === $queried_object->post_type) {
            $custom_template = JOEY_BLOGS_PLUGIN_DIR . 'templates/single-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        // Override page template for Blogs Page.
        if (is_page() && $post) {
            $page_template_slug = get_page_template_slug($post->ID);
            if ('templates/blogs-page.php' === $page_template_slug) {
                $custom_template = JOEY_BLOGS_PLUGIN_DIR . 'templates/blogs-page.php';
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }

        return $template;
    }

    /**
     * Enqueue global CSS assets for the entire website.
     */
    public function enqueue_global_assets()
    {
        wp_enqueue_style(
            'joey-blogs-global',
            JOEY_BLOGS_PLUGIN_URL . 'assets/css/blogs.css',
            array(),
            JOEY_BLOGS_VERSION
        );
    }

    /**
     * Enqueue slick CSS and JS assets when the Blogs Page template or category page is used.
     */
    public function enqueue_blogs_page_assets()
    {
        $enqueue = false;

        if (is_category()) {
            $enqueue = true;
        } elseif (is_page()) {
            $template_slug = get_page_template_slug();
            if ('templates/blogs-page.php' === $template_slug) {
                $enqueue = true;
            }
        }

        if (!$enqueue) {
            return;
        }

        // Enqueue slick CSS.
        wp_enqueue_style(
            'slick-css',
            JOEY_BLOGS_PLUGIN_URL . 'assets/js/slick.css',
            array(),
            JOEY_BLOGS_VERSION
        );

        // Enqueue slick JS.
        wp_enqueue_script(
            'slick-js',
            JOEY_BLOGS_PLUGIN_URL . 'assets/js/slick.min.js',
            array('jquery'),
            JOEY_BLOGS_VERSION,
            true
        );

        // Enqueue blogs filter AJAX script.
        wp_enqueue_script(
            'blogs-filter-js',
            JOEY_BLOGS_PLUGIN_URL . 'assets/js/blogs-filter.js',
            array('jquery'),
            JOEY_BLOGS_VERSION,
            true
        );

        wp_localize_script(
            'blogs-filter-js',
            'blogs_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('blogs_filter_nonce'),
            )
        );
    }

    /**
     * Register the banner-image metabox for posts.
     */
    public function register_banner_image_metabox()
    {
        add_meta_box(
            'banner_image_metabox',
            __('Banner Image', 'nghaihoang9x'),
            array($this, 'render_banner_image_metabox'),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render the banner-image metabox.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_banner_image_metabox($post)
    {
        // Enqueue WordPress media scripts for the media uploader.
        wp_enqueue_media();

        // Add nonce for security.
        wp_nonce_field('banner_image_metabox_action', 'banner_image_metabox_nonce');

        $banner_image_id = get_post_meta($post->ID, '_banner_image_id', true);
        $banner_image_url = '';

        if ($banner_image_id) {
            $banner_image_url = wp_get_attachment_image_url($banner_image_id, 'medium');
        }
?>
        <div class="banner-image-metabox">
            <div id="banner-image-preview" style="margin-bottom: 10px;">
                <?php if ($banner_image_url) : ?>
                    <img src="<?php echo esc_url($banner_image_url); ?>" style="max-width: 100%; height: auto;" />
                <?php endif; ?>
            </div>
            <input type="hidden" name="banner_image_id" id="banner-image-id" value="<?php echo esc_attr($banner_image_id); ?>" />
            <button type="button" class="button" id="banner-image-upload-btn"><?php esc_html_e('Choose Image', 'nghaihoang9x'); ?></button>
            <button type="button" class="button" id="banner-image-remove-btn" <?php echo !$banner_image_id ? 'style="display:none;"' : ''; ?>><?php esc_html_e('Remove Image', 'nghaihoang9x'); ?></button>
        </div>
        <script>
            (function($) {
                $(document).ready(function() {
                    var frame;

                    $('#banner-image-upload-btn').on('click', function(e) {
                        e.preventDefault();

                        if (frame) {
                            frame.open();
                            return;
                        }

                        frame = wp.media({
                            title: '<?php echo esc_js(__('Select Banner Image', 'nghaihoang9x')); ?>',
                            button: {
                                text: '<?php echo esc_js(__('Use this image', 'nghaihoang9x')); ?>'
                            },
                            multiple: false
                        });

                        frame.on('select', function() {
                            var attachment = frame.state().get('selection').first().toJSON();
                            $('#banner-image-id').val(attachment.id);
                            $('#banner-image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;" />');
                            $('#banner-image-remove-btn').show();
                        });

                        frame.open();
                    });

                    $('#banner-image-remove-btn').on('click', function(e) {
                        e.preventDefault();
                        $('#banner-image-id').val('');
                        $('#banner-image-preview').html('');
                        $(this).hide();
                    });
                });
            })(jQuery);
        </script>
    <?php
    }

    /**
     * Save the banner-image metabox data.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_banner_image_metabox($post_id)
    {
        // Check if nonce is set and valid.
        if (!isset($_POST['banner_image_metabox_nonce']) || !wp_verify_nonce($_POST['banner_image_metabox_nonce'], 'banner_image_metabox_action')) {
            return;
        }

        // Check if autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save or delete the banner image ID.
        if (isset($_POST['banner_image_id']) && !empty($_POST['banner_image_id'])) {
            update_post_meta($post_id, '_banner_image_id', intval($_POST['banner_image_id']));
        } else {
            delete_post_meta($post_id, '_banner_image_id');
        }
    }

    /**
     * Register the Blogs Banner Slider metabox for the Blogs Page template.
     */
    public function register_blogs_slider_metabox()
    {
        add_meta_box(
            'blogs_slider_metabox',
            __('Blogs Banner Slider', 'nghaihoang9x'),
            array($this, 'render_blogs_slider_metabox'),
            'page',
            'normal',
            'default'
        );
    }

    /**
     * Render the Blogs Banner Slider metabox.
     *
     * @param WP_Post $post The current page object.
     */
    public function render_blogs_slider_metabox($post)
    {
        // Only show on Blogs Page template.
        $template_slug = get_page_template_slug($post->ID);
        if ('templates/blogs-page.php' !== $template_slug) {
            echo '<p>' . esc_html__('This metabox is only available for the "Blogs Page" template.', 'nghaihoang9x') . '</p>';
            return;
        }

        wp_nonce_field('blogs_slider_metabox_action', 'blogs_slider_metabox_nonce');

        $selected_posts = get_post_meta($post->ID, '_blogs_slider_posts', true);
        if (!is_array($selected_posts)) {
            $selected_posts = array();
        }

        $all_posts = get_posts(array(
            'post_type'      => 'post',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
    ?>
        <p><?php esc_html_e('Select up to 5 posts to display in the banner slider. Leave empty to show the 5 most recent posts.', 'nghaihoang9x'); ?></p>
        <select name="blogs_slider_posts[]" id="blogs-slider-posts" multiple style="width: 100%; min-height: 200px;">
            <?php foreach ($all_posts as $p) : ?>
                <option value="<?php echo esc_attr($p->ID); ?>" <?php echo in_array($p->ID, $selected_posts) ? 'selected' : ''; ?>>
                    <?php echo esc_html($p->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p><em><?php esc_html_e('Hold Ctrl (or Cmd on Mac) to select multiple posts.', 'nghaihoang9x'); ?></em></p>
<?php
    }

    /**
     * Save the Blogs Banner Slider metabox data.
     *
     * @param int $post_id The ID of the page being saved.
     */
    public function save_blogs_slider_metabox($post_id)
    {
        if (!isset($_POST['blogs_slider_metabox_nonce']) || !wp_verify_nonce($_POST['blogs_slider_metabox_nonce'], 'blogs_slider_metabox_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['blogs_slider_posts']) && is_array($_POST['blogs_slider_posts'])) {
            $posts = array_map('intval', $_POST['blogs_slider_posts']);
            $posts = array_slice($posts, 0, 5); // Limit to 5.
            update_post_meta($post_id, '_blogs_slider_posts', $posts);
        } else {
            delete_post_meta($post_id, '_blogs_slider_posts');
        }
    }

    /**
     * AJAX handler for blog filtering (category + sort).
     */
    public function ajax_blogs_filter()
    {
        // Verify nonce.
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'blogs_filter_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $sort     = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : '';
        $search   = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $paged    = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $posts_per_page = 6;

        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
        );

        // Category filter.
        if (!empty($category)) {
            $args['category_name'] = $category;
        }

        // Search filter.
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Sort.
        switch ($sort) {
            case 'oldest':
                $args['orderby'] = 'date';
                $args['order']   = 'ASC';
                break;
            case 'popular':
                $args['orderby'] = 'comment_count';
                $args['order']   = 'DESC';
                break;
            default: // Newest.
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
        }

        $query = new WP_Query($args);
        $posts_html = '';
        $pagination_html = '';

        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                $categories   = get_the_category();
                $category_name = !empty($categories) ? esc_html($categories[0]->name) : '';
                $permalink    = get_permalink();
                $title        = get_the_title();
                $thumbnail    = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $excerpt      = wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...');
                $post_id      = get_the_ID();

                include JOEY_BLOGS_PLUGIN_DIR . 'templates/content-blog-card.php';
            }
            $posts_html = ob_get_clean();

            // Pagination.
            $total_pages = $query->max_num_pages;
            if ($total_pages > 1) {
                ob_start();
                if ($paged > 1) {
                    echo '<a href="#" class="blogs-list-pagination__button" data-page="' . ($paged - 1) . '"><</a>';
                }
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active_class = ($i === $paged) ? ' active' : '';
                    echo '<a href="#" class="blogs-list-pagination__link' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
                }
                if ($paged < $total_pages) {
                    echo '<a href="#" class="blogs-list-pagination__button" data-page="' . ($paged + 1) . '">></a>';
                }
                $pagination_html = ob_get_clean();
            }
        } else {
            $posts_html = '<p>' . esc_html__('No posts found.', 'nghaihoang9x') . '</p>';
        }

        wp_reset_postdata();

        wp_send_json_success(array(
            'posts'      => $posts_html,
            'pagination' => $pagination_html,
        ));
    }
}
