<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package visarzo
 */
get_header();

// Get current post data.
$post_id       = get_the_ID();
$title         = get_the_title();
$permalink     = get_permalink();
$categories    = get_the_category();
$category_name = !empty($categories) ? esc_html($categories[0]->name) : '';
$category_link = !empty($categories) ? get_category_link($categories[0]->term_id) : '';
$author_name   = get_the_author_meta('display_name');
$author_avatar = get_avatar($post_id, 96, '', $author_name);
$post_date     = get_the_date('F j, Y');
$thumbnail     = get_the_post_thumbnail_url($post_id, 'full') ?: JOEY_BLOGS_PLUGIN_URL . 'assets/images/elementor-placeholder-image.png';
$categories = wp_get_post_categories($post_id);

// --- Recent Posts (excluding current post) ---
?>
<main class="site-main-blogs">
    <div class="j-article">
        <div class="j-article__wrappper">
            <div class="j-article__head">
                <?php if (!empty($category_name)) : ?>
                    <span class="j-article__category">
                        <a href="<?php echo esc_url($category_link); ?>"><?php echo $category_name; ?></a>
                    </span>
                <?php endif; ?>
                <h1 class="j-article__title"><?php echo esc_html($title); ?></h1>
                <div class="j-article__meta">
                    <div class="j-article__author">
                        <span class="j-article__avatar"><?php echo $author_avatar; ?></span>
                        <span class="j-article__author-name"><?php echo esc_html($author_name); ?></span>
                    </div>
                    <div class="j-article__date"><?php echo esc_html($post_date); ?></div>
                </div>
            </div>
            <div class="j-article__content">
                <div class="j-article__thumbnail">
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                </div>
                <?php
                the_content();
                ?>
            </div>
        </div>
        <?php
        if (is_active_sidebar('single-blog-sidebar')) : ?>
            <aside id="j-secondary" role="complementary">
                <?php dynamic_sidebar('single-blog-sidebar'); ?>
            </aside>
        <?php endif; ?>
    </div>

    <?php

    $recent_args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => array($post_id), // Loại trừ bài hiện tại
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    // Nếu có category, thử tìm bài cùng category trước
    if (!empty($categories)) {
        $recent_args['category__in'] = $categories;
    }

    $recent_query = new WP_Query($recent_args);
    if ($recent_query->have_posts()) :
    ?>
        <section class="j-recent-blogs">
            <div class="container">
                <h2 class="j-recent-blogs__heading"><?php esc_html_e('Recent Blogs', 'nghaihoang9x'); ?></h2>
                <div class="j-recent-blogs__grid">
                    <?php while ($recent_query->have_posts()) : $recent_query->the_post();
                        $categories   = get_the_category();
                        $category_name_card = !empty($categories) ? esc_html($categories[0]->name) : '';
                        $permalink    = get_permalink();
                        $title_card   = get_the_title();
                        $thumbnail    = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        $excerpt      = wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...');
                        $post_id_card = get_the_ID();

                        include JOEY_BLOGS_PLUGIN_DIR . 'templates/content-blog-card.php';
                    endwhile; ?>
                </div>
            </div>
        </section>
    <?php endif;
    wp_reset_postdata(); ?>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bật hiệu ứng cuộn mượt khi click anchor link
        document.documentElement.style.scrollBehavior = 'smooth';

        // Xử lý sự kiện Ẩn/Hiện Mục lục
        var toggleBtn = document.getElementById('j-toc-toggle-btn');
        var tocList = document.getElementById('j-toc-list-content');

        if (toggleBtn && tocList) {
            toggleBtn.addEventListener('click', function() {
                if (tocList.style.display === 'none') {
                    tocList.style.display = 'block';
                    toggleBtn.textContent = '[Hiden]';
                } else {
                    tocList.style.display = 'none';
                    toggleBtn.textContent = '[Show]';
                }
            });
        }
    });
</script>
<?php
get_footer();
