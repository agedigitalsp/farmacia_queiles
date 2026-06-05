<?php

if (!defined('ABSPATH')) {
	exit;
}

?>
	<footer class="site-footer">
		<div class="container site-footer__inner">
			<div class="site-footer__left">
				<?php echo esc_html(get_bloginfo('name')); ?>
			</div>

			<nav class="site-footer__nav" aria-label="<?php echo esc_attr__('Menú pie', 'farmacia-queiles'); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'footer',
						'container' => false,
						'menu_class' => 'primary-menu',
						'fallback_cb' => false,
					]
				);
				?>
			</nav>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
