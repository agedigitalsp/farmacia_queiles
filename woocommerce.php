<?php

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content">
	<div class="container">
		<main id="primary" class="site-main">
			<?php if (function_exists('woocommerce_content')) : ?>
				<?php woocommerce_content(); ?>
			<?php else : ?>
				<?php while (have_posts()) : ?>
					<?php the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</main>
	</div>
</div>
<?php
get_footer();
