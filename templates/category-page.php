<?php
/**
 * Template for displaying category archive pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#category
 * @package visarzo
 */

get_header();

// Get the current category.
$current_category = get_queried_object();
$current_category_slug = $current_category ? $current_category->slug : '';
$current_category_name = $current_category ? $current_category->name : '';

// --- Banner Slider: 5 most featured/popular posts ---
$slider_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 5,
	'orderby'        => 'comment_count',
	'order'          => 'DESC',
);

// If viewing a specific category, only show posts from that category.
if (!empty($current_category_slug)) {
	$slider_args['category_name'] = $current_category_slug;
}
$slider_query = new WP_Query($slider_args);

// --- Blog Posts List ---
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

// Filter by current category.
if (!empty($current_category_slug)) {
	$posts_args['category_name'] = $current_category_slug;
}

$posts_query = new WP_Query($posts_args);

// --- All categories for filter buttons ---
$all_categories = get_categories(array(
	'hide_empty' => true,
));
// Disable category uncategorized from the filter list.
$all_categories = array_filter($all_categories, function ($cat) {
	return $cat->slug !== 'uncategorized';
});
?>
<main class="site-main-blogs">
	<section class="blogs-page-banner">
		<div class="container">
			<div class="blogs-banner-heading">
				<h1 class="blogs-banner-title"><?php echo esc_html($current_category_name ?: __('Category', 'nghaihoang9x')); ?></h1>
			</div>
			<?php if ($slider_query->have_posts()) : ?>
				<div class="blogs-banner-slider">
					<?php while ($slider_query->have_posts()) : $slider_query->the_post();
						$categories = get_the_category();
						$category_name = !empty($categories) ? esc_html($categories[0]->name) : '';
						$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full') ?: JOEY_BLOGS_PLUGIN_URL . 'assets/images/elementor-placeholder-image.png';
					?>
						<div class="blogs-banner-slide-item">
							<img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
							<h2 class="blogs-banner-slide-title"><?php echo esc_html(get_the_title()); ?></h2>
							<div class="blogs-banner-slide-content">
								<span class="blogs-banner-slide-category"><?php echo $category_name; ?></span>
								<p class="blogs-banner-slide-description"><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: get_the_content(), 25, '...')); ?></p>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif;
			wp_reset_postdata(); ?>
		</div>
	</section>
	<section class="blogs-page-content">
		<div class="container">
			<div class="blogs-page-head">
				<div class="blogs-page-search-box">
					<input type="text" class="blogs-page-search-box__input" placeholder="<?php esc_attr_e('Search...', 'nghaihoang9x'); ?>">
					<button class="blogs-page-search-box__button">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
							<path d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z" />
						</svg>
					</button>
				</div>
				<div class="blogs-page-filter">
					<div class="blogs-page-filter__item">
						<div class="blogs-page-filter__buttons" id="category-buttons">
							<button class="blogs-page-filter__btn <?php echo empty($current_category_slug) ? 'active' : ''; ?>" data-category=""><?php esc_html_e('All', 'nghaihoang9x'); ?></button>
							<?php foreach ($all_categories as $cat) : ?>
								<button class="blogs-page-filter__btn <?php echo ($cat->slug === $current_category_slug) ? 'active' : ''; ?>" data-category="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="blogs-page-lists">
				<?php if ($posts_query->have_posts()) : ?>
					<?php while ($posts_query->have_posts()) : $posts_query->the_post();
						$categories = get_the_category();
						$category_name = !empty($categories) ? esc_html($categories[0]->name) : '';
						$permalink = get_permalink();
						$title     = get_the_title();
						$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium');
						$excerpt   = wp_trim_words(get_the_excerpt() ?: get_the_content(), 30, '...');
						$post_id   = get_the_ID();
					?>
						<?php include JOEY_BLOGS_PLUGIN_DIR . 'templates/content-blog-card.php'; ?>
					<?php endwhile; ?>
				<?php else : ?>
					<p><?php esc_html_e('No posts found.', 'nghaihoang9x'); ?></p>
				<?php endif; ?>
			</div>
			<?php
			$total_pages = $posts_query->max_num_pages;
			if ($total_pages > 1) :
			?>
				<div class="blogs-list-pagination">
					<?php if ($paged > 1) : ?>
						<a href="#" class="blogs-list-pagination__button" data-page="<?php echo esc_attr($paged - 1); ?>">
							<</a>
					<?php endif; ?>
					<?php for ($i = 1; $i <= $total_pages; $i++) :
						$active_class = ($i === $paged) ? ' active' : '';
					?>
						<a href="#" class="blogs-list-pagination__link<?php echo $active_class; ?>" data-page="<?php echo esc_attr($i); ?>"><?php echo $i; ?></a>
					<?php endfor; ?>
					<?php if ($paged < $total_pages) : ?>
						<a href="#" class="blogs-list-pagination__button" data-page="<?php echo esc_attr($paged + 1); ?>">></a>
					<?php endif; ?>
				</div>
			<?php endif;
			wp_reset_postdata(); ?>
		</div>
	</section>
</main>
<?php
// Initialize Slick slider for the banner slider.
if ($slider_query->post_count > 0) : ?>
	<script>
		(function($) {
			$(document).ready(function() {
				$('.blogs-banner-slider').slick({
					slidesToShow: 1,
					slidesToScroll: 1,
					autoplay: true,
					autoplaySpeed: 4000,
					speed: 600,
					arrows: true,
					dots: false,
					infinite: true,
					fade: false,
					pauseOnHover: true,
					prevArrow: '<button type="button" class="slick-prev"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M201.4 297.4C188.9 309.9 188.9 330.2 201.4 342.7L361.4 502.7C373.9 515.2 394.2 515.2 406.7 502.7C419.2 490.2 419.2 469.9 406.7 457.4L269.3 320L406.6 182.6C419.1 170.1 419.1 149.8 406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3L201.3 297.3z"/></svg></button>',
					nextArrow: '<button type="button" class="slick-next"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z"/></svg></button>',
					responsive: [{
						breakpoint: 768,
						settings: {
							arrows: false,
							dots: true
						}
					}]
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>
<?php
get_footer();
