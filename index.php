<?php

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content">
	<div class="container">
		<main id="primary" class="site-main">
			<?php if (have_posts()) : ?>
				<?php while (have_posts()) : ?>
					<?php the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<?php if (is_singular()) : ?>
								<h1 class="entry-title"><?php the_title(); ?></h1>
							<?php else : ?>
								<h2 class="entry-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>
							<?php endif; ?>
						</header>

						<div class="entry-content">
							<?php
							if (is_singular()) {
								the_content();
							} else {
								the_excerpt();
							}
							?>
						</div>
					</article>
				<?php endwhile; ?>

				<?php the_posts_navigation(); ?>
			<?php else : ?>
				<p><?php echo esc_html__('No hay contenido para mostrar.', 'farmacia-queiles'); ?></p>
			<?php endif; ?>
		</main>
	</div>
</div>
<?php
get_footer();
