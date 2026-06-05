<?php

if (!defined('ABSPATH')) {
	exit;
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php farmacia_queiles_body_open(); ?>
<div id="page" class="site">
	<header class="site-header">
		<div class="container site-header__inner">
			<a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
				<?php bloginfo('name'); ?>
			</a>

			<nav class="site-nav" aria-label="<?php echo esc_attr__('Menú principal', 'farmacia-queiles'); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'container' => false,
						'menu_class' => 'primary-menu',
						'fallback_cb' => false,
					]
				);
				?>
			</nav>

			<?php if (class_exists('WooCommerce')) : ?>
				<a class="header-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
					<?php echo esc_html__('Carrito', 'farmacia-queiles'); ?>
					<?php if (WC()->cart) : ?>
						(<?php echo esc_html((string) WC()->cart->get_cart_contents_count()); ?>)
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>
	</header>
