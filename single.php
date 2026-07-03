<?php
/*
Template Name: Blog
*/


if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content">
	<div class="container">
		<?php if (function_exists('yoast_breadcrumb')) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
		<main id="primary" class="site-main">
			<?php while (have_posts()) : ?>
				<?php the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<span class="entry-badge"><?php echo esc_html(get_the_category()[0]->name ?? ''); ?></span>
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>

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

										<?php if (has_post_thumbnail()): ?>
											<span class="blog-card__image-link">
												<?php the_post_thumbnail('medium'); ?>
											</span>
											<?php endif; ?>


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
