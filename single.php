<?php

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content">
	<div class="container">
		<main id="primary" class="site-main">
			<?php while (have_posts()) : ?>
				<?php the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>

					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>

				<?php the_post_navigation(); ?>
				<?php if (comments_open() || get_comments_number()) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
