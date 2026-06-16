<?php

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content">
	<div class="container">
		<main id="primary" class="site-main">
			<section class="error-404 not-found">
				<header class="page-header">
					<p class="error-404__code">404</p>
					<h1 class="page-title"><?php echo esc_html__('Página no encontrada', 'farmacia-queiles'); ?></h1>
				</header>

				<div class="page-content">
					<p class="error-404__text">
						<?php echo esc_html__('No hemos encontrado lo que buscabas. Prueba a buscar o vuelve al inicio.', 'farmacia-queiles'); ?>
					</p>

					<div class="error-404__actions">
						<a class="error-404__button" href="<?php echo esc_url(home_url('/')); ?>">
							<?php echo esc_html__('Ir al inicio', 'farmacia-queiles'); ?>
						</a>
					</div>

					<div class="error-404__search trigger-search">
						<?php get_search_form(); ?>
					</div>
				</div>
			</section>
		</main>
	</div>
</div>
<?php
get_footer();
