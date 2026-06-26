<?php
/**
 * Template part for displaying a blog post card (blogs-grid-card).
 *
 * @package Joey_Blogs
 *
 * Variables expected:
 *   $post_id    - int    The post ID
 *   $title      - string The post title (escaped)
 *   $permalink  - string The post permalink (escaped URL)
 *   $thumbnail  - string The post thumbnail URL (escaped URL)
 *   $category   - string The category name (escaped)
 *   $excerpt    - string The post excerpt (escaped)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Default to placeholder image if no thumbnail.
if (empty($thumbnail)) {
    $thumbnail = JOEY_BLOGS_PLUGIN_URL . 'assets/images/elementor-placeholder-image.png';
}
if(empty($category)) {
    // get category name from post id
    $category_obj = get_the_category($post_id);
    if (!empty($category_obj) && !is_wp_error($category_obj)) {
        $category = esc_html($category_obj[0]->name);
    }
    // disable category Uncategorized
    if (!empty($category_obj) && $category_obj[0]->slug === 'uncategorized') {
        $category = '';
    }
}
?>
<div class="blogs-grid-card">
    <div class="blogs-grid-card__thumbnail">
        <a href="<?php echo esc_url($permalink); ?>">
            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
        </a>
        <?php if (!empty($category)) : ?>
            <span class="blogs-grid-card__category"><?php echo $category; ?></span>
        <?php endif; ?>
    </div>
    <div class="blogs-grid-card__content">
        <h3 class="blogs-grid-card__title">
            <a href="<?php echo esc_url($permalink); ?>">
                <?php echo esc_html($title); ?>
            </a>
        </h3>
        <p class="blogs-grid-card__description"><?php echo esc_html($excerpt); ?></p>
    </div>
    <a href="<?php echo esc_url($permalink); ?>" class="blogs-grid-card__redmore"><?php esc_html_e('Read More', 'nghaihoang9x'); ?></a>
</div>