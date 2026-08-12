<?php

/**
 * @package Joey blogs
 */

/*
Plugin Name: Joey blogs
Plugin URI: http://nghaihoang9x.info
Description: This is a plugin for Joey blogs
Version: 0.1.0
Requires at least: 0.1.0
Requires PHP: 7.4
Author: nghaihoang9x.info
Author URI: http://nghaihoang9x.info
License: GPLv2 or later
Text Domain: nghaihoang9x
*/

if (!defined('ABSPATH')) {
    exit;
}

define('JOEY_BLOGS_VERSION', '0.1.0.4');
define('JOEY_BLOGS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JOEY_BLOGS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once JOEY_BLOGS_PLUGIN_DIR . 'inc/class-joey-blogs.php';
require_once JOEY_BLOGS_PLUGIN_DIR  . 'inc/class-sidebar-posts-widget.php';
require_once JOEY_BLOGS_PLUGIN_DIR  . 'inc/class-j-toc.php';

/**
 * Initialize the plugin.
 */
function joey_blogs_init() {
    new Joey_Blogs();
}
add_action('plugins_loaded', 'joey_blogs_init');

add_filter('the_content', 'convert_custom_tags_to_buttons');
function convert_custom_tags_to_buttons($content) {
    // Tìm cấu trúc {{%Text%}} và thay thế bằng thẻ button/link
    $pattern = '/\{\{\%(.*?)\%\}\}/';
    $replacement = '<span class="my-custom-button">$1</span>';
    
    return preg_replace($pattern, $replacement, $content);
}

function my_custom_blog_sidebar() {
    register_sidebar( array(
        'name'          => __( 'Single Blog Sidebar', 'textdomain' ),
        'id'            => 'single-blog-sidebar',
        'description'   => __( 'Sidebar dành riêng cho trang Blog và bài viết chi tiết.', 'textdomain' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'my_custom_blog_sidebar' );


function register_custom_sidebar_posts_widget() {
    register_widget('Custom_Sidebar_Posts_Widget');
}
add_action('widgets_init', 'register_custom_sidebar_posts_widget');

function enqueue_sidebar_posts_styles() {
    wp_enqueue_style('sidebar-posts-style', get_template_directory_uri() . '/assets/css/sidebar-posts.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'enqueue_sidebar_posts_styles');