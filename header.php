<?php

if (!defined('ABSPATH')) {
	exit;
}

$phone_text = get_theme_mod('farmacia_queiles_phone_text', '976 642 685');
$phone_url = get_theme_mod('farmacia_queiles_phone_url', 'tel:+34976642685');
$address_text = get_theme_mod('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, Tarazona');
$address_url = get_theme_mod('farmacia_queiles_address_url', '');
$schedule_text = get_theme_mod('farmacia_queiles_schedule_text', 'L-V 9:00-13:45 · 16:30-20:00');
$contact_url = get_theme_mod('farmacia_queiles_contact_url', home_url('/contacto'));
$my_account_url = get_theme_mod(
	'farmacia_queiles_my_account_url',
	class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url()
);
$favorites_url = get_theme_mod('farmacia_queiles_favorites_url', home_url('/favoritos'));
$cart_count = 0;

if (class_exists('WooCommerce') && WC()->cart) {
	$cart_count = (int) WC()->cart->get_cart_contents_count();
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php if (function_exists('wp_body_open')) : ?>
	<?php wp_body_open(); ?>
<?php endif; ?>
<div id="page" class="site">
	<aside class="site-preheader">
		<div class="container container--wide site-preheader__inner">
			<div class="site-preheader__left">
				<div class="preheader-item">
					<span class="material-symbols-outlined">call</span>
					<?php if (!empty($phone_url)) : ?>
						<a class="preheader-item__link" href="<?php echo esc_url($phone_url); ?>"><?php echo esc_html($phone_text); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($phone_text); ?></span>
					<?php endif; ?>
				</div>
				<div class="preheader-item">
					<span class="material-symbols-outlined">location_on</span>
					<?php if (!empty($address_url)) : ?>
						<a class="preheader-item__link" href="<?php echo esc_url($address_url); ?>"><?php echo esc_html($address_text); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($address_text); ?></span>
					<?php endif; ?>
				</div>
				<div class="preheader-item">
					<span class="material-symbols-outlined">schedule</span>
					<span><?php echo esc_html($schedule_text); ?></span>
				</div>
			</div>
			<div class="site-preheader__right">
				<a class="preheader-cta" href="<?php echo esc_url($contact_url); ?>">
					<?php echo esc_html__('Contacto', 'farmacia-queiles'); ?>
					<span class="material-symbols-outlined">arrow_forward</span>
				</a>
			</div>
		</div>
	</aside>

	<header class="site-header site-header--luxury">
		<div class="site-header__top">
			<div class="container container--wide site-header__top-inner">
				<div class="site-header__brand">
					<?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
							<span class="site-title"><?php bloginfo('name'); ?></span>
						</a>
					<?php endif; ?>
				</div>

				<div class="site-header__search">
					<?php if (class_exists('WooCommerce')) : ?>
						<form class="header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
							<span class="material-symbols-outlined header-search__icon">search</span>
							<label class="screen-reader-text" for="header-search-field"><?php echo esc_html__('Buscar productos', 'farmacia-queiles'); ?></label>
							<input id="header-search-field" class="header-search__input" type="search" name="s" placeholder="<?php echo esc_attr__("Busca por necesidad (ej. 'manchas', 'crema solar piel grasa')...", 'farmacia-queiles'); ?>">
							<input type="hidden" name="post_type" value="product">
						</form>
					<?php else : ?>
						<form class="header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
							<span class="material-symbols-outlined header-search__icon">search</span>
							<label class="screen-reader-text" for="header-search-field"><?php echo esc_html__('Buscar', 'farmacia-queiles'); ?></label>
							<input id="header-search-field" class="header-search__input" type="search" name="s" placeholder="<?php echo esc_attr__("Busca por necesidad (ej. 'manchas', 'crema solar piel grasa')...", 'farmacia-queiles'); ?>">
						</form>
					<?php endif; ?>
				</div>

				<div class="site-header__utils">
					<a class="util-link" href="<?php echo esc_url($my_account_url); ?>">
						<span class="material-symbols-outlined util-link__icon">person</span>
						<span class="util-link__label"><?php echo esc_html__('Mi cuenta', 'farmacia-queiles'); ?></span>
					</a>

					<a class="util-link" href="<?php echo esc_url($favorites_url); ?>">
						<span class="material-symbols-outlined util-link__icon">favorite</span>
						<span class="util-link__label"><?php echo esc_html__('Favoritos', 'farmacia-queiles'); ?></span>
					</a>

					<?php if (class_exists('WooCommerce')) : ?>
						<a
							class="util-link util-link--cart"
							href="<?php echo esc_url(wc_get_cart_url()); ?>"
							data-open-site-cart="true"
							aria-controls="site-cart-drawer"
							aria-expanded="false"
						>
							<span class="material-symbols-outlined util-link__icon">shopping_bag</span>
							<span class="util-link__badge cart-count-fragment<?php echo $cart_count < 1 ? ' is-empty' : ''; ?>">
								<?php echo esc_html((string) $cart_count); ?>
							</span>
							<span class="util-link__label"><?php echo esc_html__('Carrito', 'farmacia-queiles'); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<nav class="site-header__nav" aria-label="<?php echo esc_attr__('Navegación', 'farmacia-queiles'); ?>">
			<div class="container container--wide site-header__nav-inner">
				<div class="site-header__nav-left">
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'primary',
							'container' => false,
							'menu_class' => 'primary-menu primary-menu--luxury',
							'fallback_cb' => false,
						]
					);
					?>
				</div>

				<div class="site-header__nav-right">
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'services',
							'container' => false,
							'menu_class' => 'services-menu',
							'fallback_cb' => false,
						]
					);
					?>
				</div>
			</div>
		</nav>
	</header>
