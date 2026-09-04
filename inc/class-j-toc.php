<?php
if (! defined('ABSPATH')) exit; // Block direct access

class J_Table_Of_Contents
{

    public function __construct()
    {
        // Tự động chèn mục lục vào nội dung bài viết
        add_filter('the_content', array($this, 'generate_toc'));

        // Enqueue CSS và JS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Tự động quét thẻ H2, H3 và sinh ra Table of Contents
     */
    public function generate_toc($content)
    {
        // Chỉ chạy ở trang chi tiết bài viết (single post) trong vòng lặp chính
        if (! is_single() || ! is_main_query()) {
            return $content;
        }

        // Tìm tất cả các thẻ h2 và h3 trong bài viết
        $pattern = '/<h([2-3])(.*?)>(.*?)<\/h[2-3]>/i';

        if (! preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            return $content;
        }

        // Nếu ít hơn 2 tiêu đề thì không hiển thị mục lục
        if (count($matches) < 2) {
            return $content;
        }

        $toc_html  = '<div class="j-toc-container">';
        $toc_html .= '  <div class="j-toc-header">';
        $toc_html .= '      <span class="j-toc-title">Table of Contents</span>';
        $toc_html .= '      <button type="button" class="j-toc-toggle" id="j-toc-toggle-btn">[Hidden]</button>';
        $toc_html .= '  </div>';
        $toc_html .= '  <ul class="j-toc-list" id="j-toc-list-content">';

        $index = 0;
        foreach ($matches as $match) {
            $index++;
            $tag_level  = $match[1]; // 2 hoặc 3
            $attributes = $match[2];
            $title_text = strip_tags($match[3]);

            // Tạo slug anchor ID
            $slug = sanitize_title($title_text);
            if (empty($slug)) {
                $slug = 'toc-heading-' . $index;
            }

            $item_class = 'j-toc-item j-toc-level-' . $tag_level;

            $toc_html .= sprintf(
                '<li class="%s"><a href="#%s">%s</a></li>',
                esc_attr($item_class),
                esc_attr($slug),
                esc_html($title_text)
            );

            // Thêm id vào thẻ H2, H3 trong bài viết
            $replacement = sprintf('<h%s%s id="%s">%s</h%s>', $tag_level, $attributes, $slug, $match[3], $tag_level);
            $content     = str_replace($match[0], $replacement, $content);
        }

        $toc_html .= '  </ul>';
        $toc_html .= '</div>';

        // FIX LỖI: Chèn Mục lục vào ngay trước vị trí thẻ H2/H3 đầu tiên (Không dùng PREG_OFFSET_BINARY)
        $first_match = $matches[0][0]; // Lấy chuỗi thẻ H2/H3 đầu tiên
        $pos         = strpos($content, $first_match);

        if ($pos !== false) {
            $content = substr_replace($content, $toc_html, $pos, 0);
        } else {
            $content = $toc_html . $content;
        }

        return $content;
    }

    /**
     * Đăng ký và nạp file CSS & JS
     */
    public function enqueue_assets()
    {
        if (is_single()) {
            wp_enqueue_style(
                'j-toc-style',
                get_template_directory_uri() . '/assets/css/j-toc.css',
                array(),
                '1.0.0'
            );

            wp_enqueue_script(
                'j-toc-script',
                get_template_directory_uri() . '/assets/js/j-toc.js',
                array(),
                '1.0.0',
                true
            );
        }
    }
}

// Khởi tạo Class
new J_Table_Of_Contents();
