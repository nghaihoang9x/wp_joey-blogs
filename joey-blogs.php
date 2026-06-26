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

define('JOEY_BLOGS_VERSION', '0.1.0');
define('JOEY_BLOGS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JOEY_BLOGS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once JOEY_BLOGS_PLUGIN_DIR . 'inc/class-joey-blogs.php';

/**
 * Initialize the plugin.
 */
function joey_blogs_init() {
    new Joey_Blogs();
}
add_action('plugins_loaded', 'joey_blogs_init');
