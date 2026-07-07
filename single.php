<?php
/*
Template Name: Blog
*/

if (!defined('ABSPATH')) {
	exit;
}



$post_id = (int) get_queried_object_id();
$header_image_url = (string) get_post_meta($post_id, '_fq_page_header_image', true);
if ('' === $header_image_url) {
	$thumb_id = (int) get_post_thumbnail_id($post_id);
	if ($thumb_id > 0) {
		$header_image_url = (string) wp_get_attachment_image_url($thumb_id, 'full');
	}
}
if ('' === $header_image_url) {
	$header_image_url = get_template_directory_uri() . '/assets/img/category-default.webp';
}
$header_style = "background-image:linear-gradient(rgba(255,255,255,0.72),rgba(255,255,255,0.72)),url('" . esc_url($header_image_url) . "');";

get_header();
?>

<div class="content">
	
	<div class="container container--wide">
		<?php if (function_exists('yoast_breadcrumb')) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
	</div>


	<header class="entry-header" style="<?php echo esc_attr($header_style); ?>">
		<div class="container">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</div>
	</header>

	<div class="container">
		<main id="primary" class="site-main">
			<?php while (have_posts()) : ?>
				<?php the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<p><?php the_content(); ?></p>
					</div>
				</article>

				<?php
				$related = new WP_Query([
					'category__in'   => wp_get_post_categories(get_the_ID()),
					'post__not_in'   => [get_the_ID()],
					'posts_per_page' => 3,
				]);
				if ($related->have_posts()) : ?>
					<section class="related-posts">
						<h2 class="related-posts__title">También te puede interesar</h2>
						<div class="blog-grid">
							<?php while ($related->have_posts()) : $related->the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class('blog-card'); ?>>
									<a href="<?php the_permalink(); ?>" style="display:block;height:100%;text-decoration:none;color:inherit;position:relative">
										<?php
										$cats = get_the_category();
										$badge_cats = array_values(array_filter($cats, function ($cat) {
											return !in_array($cat->slug, ['uncategorized', 'sin-categoria']);
										}));
										if (!empty($badge_cats)):
										?>
											<span class="blog-card__badge"><?php echo esc_html($badge_cats[0]->name); ?></span>
										<?php endif; ?>

										<span class="blog-card__image-link">
											<?php if (has_post_thumbnail()): ?>
												<?php the_post_thumbnail('medium'); ?>
											<?php else: ?>
												<img class="blog-card__default-img" src="<?php echo content_url('/uploads/2026/06/cropped-favicon-farmacia-queiles-300x300.png'); ?>" alt="" loading="lazy">
											<?php endif; ?>
										</span>


                                        <div class="blog-card__body">
                                            <h2 class="blog-card__title"><?php the_title(); ?></h2>
                                            <p class="blog-card__excerpt"><?php the_excerpt(); ?></p>
                                            <span class="blog-card__link">
                                                Leer más <span class="material-symbols-outlined">arrow_forward</span>
                                            </span>
                                        </div>
									</a>
								</article>
							<?php endwhile; ?>
						</div>
					</section>
				<?php wp_reset_postdata();
				endif; ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
