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
$header_categories = class_exists('WooCommerce') ? Farmacia_Queiles_Theme::get_header_product_categories(5) : ['featured' => [], 'more' => []];
$current_category_id = 0;
$search_placeholder = __("Busca por necesidad (ej. 'manchas', 'crema solar piel grasa')...", 'farmacia-queiles');

if (class_exists('WooCommerce') && WC()->cart) {
	$cart_count = (int) WC()->cart->get_cart_contents_count();
}

if (is_tax('product_cat')) {
	$current_term = get_queried_object();
	if ($current_term instanceof WP_Term) {
		$current_category_id = (int) $current_term->term_id;
	}
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
					<?php if (!empty($phone_url)) : ?>
						<a class="preheader-item__icon-link" href="<?php echo esc_url($phone_url); ?>" aria-label="<?php echo esc_attr__('Llamar por teléfono', 'farmacia-queiles'); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span class="material-symbols-outlined">call</span>
						</a>
					<?php else : ?>
						<span class="preheader-item__icon-link preheader-item__icon-link--static" aria-hidden="true">
							<span class="material-symbols-outlined">call</span>
						</span>
					<?php endif; ?>
					<?php if (!empty($phone_url)) : ?>
						<a class="preheader-item__link" href="<?php echo esc_url($phone_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($phone_text); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($phone_text); ?></span>
					<?php endif; ?>
				</div>
				<div class="preheader-item">
					<?php if (!empty($address_url)) : ?>
						<a class="preheader-item__icon-link preheader-item__icon-link--desktop" href="<?php echo esc_url($address_url); ?>" aria-label="<?php echo esc_attr__('Abrir ubicación', 'farmacia-queiles'); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span class="material-symbols-outlined">location_on</span>
						</a>
					<?php else : ?>
						<span class="preheader-item__icon-link preheader-item__icon-link--desktop preheader-item__icon-link--static" aria-hidden="true">
							<span class="material-symbols-outlined">location_on</span>
						</span>
					<?php endif; ?>
					<button class="preheader-item__icon-link preheader-item__icon-button preheader-item__icon-button--mobile" type="button" data-open-contact-modal="true" aria-controls="site-contact-modal" aria-expanded="false" aria-label="<?php echo esc_attr__('Ver dirección y horario', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">location_on</span>
					</button>
					<?php if (!empty($address_url)) : ?>
						<a class="preheader-item__link" href="<?php echo esc_url($address_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($address_text); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($address_text); ?></span>
					<?php endif; ?>
				</div>
				<div class="preheader-item">
					<span class="preheader-item__icon-link preheader-item__icon-link--desktop preheader-item__icon-link--static" aria-hidden="true">
						<span class="material-symbols-outlined">schedule</span>
					</span>
					<button class="preheader-item__icon-link preheader-item__icon-button preheader-item__icon-button--mobile" type="button" data-open-contact-modal="true" aria-controls="site-contact-modal" aria-expanded="false" aria-label="<?php echo esc_attr__('Ver horario', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">schedule</span>
					</button>
					<span class="preheader-item__text"><?php echo esc_html($schedule_text); ?></span>
				</div>
			</div>
			<div class="site-preheader__right">
				<a class="preheader-cta" href="<?php echo esc_url($contact_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($contact_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
							<input id="header-search-field" class="header-search__input trigger-search" type="search" name="s" placeholder="<?php echo esc_attr($search_placeholder); ?>">
							<input type="hidden" name="post_type" value="product">
						</form>
					<?php else : ?>
						<form class="header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
							<span class="material-symbols-outlined header-search__icon">search</span>
							<label class="screen-reader-text" for="header-search-field"><?php echo esc_html__('Buscar', 'farmacia-queiles'); ?></label>
							<input id="header-search-field" class="header-search__input trigger-search" type="search" name="s" placeholder="<?php echo esc_attr($search_placeholder); ?>">
						</form>
					<?php endif; ?>
				</div>

				<div class="site-header__utils">
					<a class="util-link" href="<?php echo esc_url($my_account_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($my_account_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span class="material-symbols-outlined util-link__icon">person</span>
						<span class="util-link__label"><?php echo esc_html__('Mi cuenta', 'farmacia-queiles'); ?></span>
					</a>

					<a class="util-link" href="<?php echo esc_url($favorites_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($favorites_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span class="material-symbols-outlined util-link__icon">favorite</span>
						<span class="util-link__label"><?php echo esc_html__('Favoritos', 'farmacia-queiles'); ?></span>
					</a>

					<button class="util-link util-link--menu" type="button" data-open-mobile-menu="true" aria-controls="site-mobile-menu" aria-expanded="false">
						<span class="material-symbols-outlined util-link__icon">menu</span>
						<span class="util-link__label"><?php echo esc_html__('Menú', 'farmacia-queiles'); ?></span>
					</button>

					<?php if (class_exists('WooCommerce')) : ?>
						<?php echo do_shortcode('[sp_minicart]'); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<nav class="site-header__nav" aria-label="<?php echo esc_attr__('Navegación', 'farmacia-queiles'); ?>">
			<div class="container container--wide site-header__nav-inner">
				<div class="site-header__nav-left">
					<?php if (!empty($header_categories['featured']) || !empty($header_categories['more'])) : ?>
						<ul class="header-categories" role="list">
							<?php foreach ($header_categories['featured'] as $category) : ?>
								<li class="header-categories__item<?php echo (int) $category->term_id === $current_category_id ? ' is-current' : ''; ?>">
									<a class="header-categories__link" href="<?php echo esc_url(get_term_link($category)); ?>">
										<?php echo esc_html($category->name); ?>
									</a>
								</li>
							<?php endforeach; ?>

							<?php if (!empty($header_categories['more'])) : ?>
								<li class="header-categories__item header-categories__item--dropdown">
									<details class="header-categories__dropdown">
										<summary class="header-categories__toggle">
											<span class="material-symbols-outlined header-categories__toggle-icon">grid_view</span>
											<span><?php echo esc_html__('Más categorías', 'farmacia-queiles'); ?></span>
											<span class="material-symbols-outlined header-categories__toggle-arrow">expand_more</span>
										</summary>

										<ul class="header-categories__menu" role="list">
											<?php foreach ($header_categories['more'] as $category) : ?>
												<li class="header-categories__menu-item<?php echo (int) $category->term_id === $current_category_id ? ' is-current' : ''; ?>">
													<a class="header-categories__menu-link" href="<?php echo esc_url(get_term_link($category)); ?>">
														<?php echo esc_html($category->name); ?>
													</a>
												</li>
											<?php endforeach; ?>
										</ul>
									</details>
								</li>
							<?php endif; ?>
						</ul>
					<?php else : ?>
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
					<?php endif; ?>
				</div>

			</div>
		</nav>
	</header>

	<div id="site-contact-modal" class="site-contact-modal" aria-hidden="true">
		<div class="site-contact-modal__overlay" data-close-contact-modal="true"></div>
		<div class="site-contact-modal__panel" role="dialog" aria-modal="true" aria-labelledby="site-contact-modal-title">
			<div class="site-contact-modal__header">
				<h2 id="site-contact-modal-title" class="site-contact-modal__title"><?php echo esc_html__('Horario y ubicación', 'farmacia-queiles'); ?></h2>
				<button class="site-contact-modal__close" type="button" data-close-contact-modal="true" aria-label="<?php echo esc_attr__('Cerrar información de contacto', 'farmacia-queiles'); ?>">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
			<div class="site-contact-modal__content">
				<div class="site-contact-modal__item">
					<span class="material-symbols-outlined site-contact-modal__icon">location_on</span>
					<div>
						<strong><?php echo esc_html__('Dirección', 'farmacia-queiles'); ?></strong>
						<p><?php echo esc_html($address_text); ?></p>
					</div>
				</div>
				<div class="site-contact-modal__item">
					<span class="material-symbols-outlined site-contact-modal__icon">schedule</span>
					<div>
						<strong><?php echo esc_html__('Horario', 'farmacia-queiles'); ?></strong>
						<p><?php echo nl2br(esc_html($schedule_text)); ?></p>
					</div>
				</div>
				<?php if (!empty($address_url)) : ?>
					<div class="site-contact-modal__actions">
						<a class="site-contact-modal__button" href="<?php echo esc_url($address_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span><?php echo esc_html__('Ver ubicación', 'farmacia-queiles'); ?></span>
							<span class="material-symbols-outlined">arrow_forward</span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div id="site-mobile-search" class="site-mobile-search" aria-hidden="true">
		<div class="site-mobile-search__overlay" data-close-mobile-search="true"></div>
		<div class="site-mobile-search__panel">
			<div class="site-mobile-search__header">
				<h2 class="site-mobile-search__title"><?php echo esc_html__('Buscar', 'farmacia-queiles'); ?></h2>
				<button class="site-mobile-search__close" type="button" data-close-mobile-search="true" aria-label="<?php echo esc_attr__('Cerrar buscador', 'farmacia-queiles'); ?>">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
			<?php if (class_exists('WooCommerce')) : ?>
				<form class="header-search header-search--mobile" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
					<span class="material-symbols-outlined header-search__icon">search</span>
					<label class="screen-reader-text" for="mobile-header-search-field"><?php echo esc_html__('Buscar productos', 'farmacia-queiles'); ?></label>
					<input id="mobile-header-search-field" class="header-search__input trigger-search" type="search" name="s" placeholder="<?php echo esc_attr($search_placeholder); ?>">
					<input type="hidden" name="post_type" value="product">
				</form>
			<?php else : ?>
				<form class="header-search header-search--mobile" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
					<span class="material-symbols-outlined header-search__icon">search</span>
					<label class="screen-reader-text" for="mobile-header-search-field"><?php echo esc_html__('Buscar', 'farmacia-queiles'); ?></label>
					<input id="mobile-header-search-field" class="header-search__input trigger-search" type="search" name="s" placeholder="<?php echo esc_attr($search_placeholder); ?>">
				</form>
			<?php endif; ?>
		</div>
	</div>

	<div id="site-mobile-menu" class="site-mobile-menu" aria-hidden="true">
		<div class="site-mobile-menu__overlay" data-close-mobile-menu="true"></div>
		<aside class="site-mobile-menu__panel">
			<div class="site-mobile-menu__header">
				<div class="site-mobile-menu__brand">
					<?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
							<span class="site-title"><?php bloginfo('name'); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<button class="site-mobile-menu__close" type="button" data-close-mobile-menu="true" aria-label="<?php echo esc_attr__('Cerrar menú', 'farmacia-queiles'); ?>">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>

			<div class="site-mobile-menu__content">
				<div class="site-mobile-menu__block">
					<h2 class="site-mobile-menu__title"><?php echo esc_html__('Categorías', 'farmacia-queiles'); ?></h2>
					<?php if (!empty($header_categories['featured']) || !empty($header_categories['more'])) : ?>
						<ul class="site-mobile-menu__list" role="list">
							<?php foreach (array_merge($header_categories['featured'], $header_categories['more']) as $category) : ?>
								<li>
									<a class="site-mobile-menu__link<?php echo (int) $category->term_id === $current_category_id ? ' is-current' : ''; ?>" href="<?php echo esc_url(get_term_link($category)); ?>">
										<?php echo esc_html($category->name); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<?php
						wp_nav_menu(
							[
								'theme_location' => 'primary',
								'container' => false,
								'menu_class' => 'site-mobile-menu__list',
								'fallback_cb' => false,
							]
						);
						?>
					<?php endif; ?>
				</div>

			</div>
		</aside>
	</div>

	<nav class="mobile-bottom-bar" aria-label="<?php echo esc_attr__('Accesos móviles', 'farmacia-queiles'); ?>">
		<button class="mobile-bottom-bar__item" type="button" data-open-mobile-search="true" aria-controls="site-mobile-search" aria-expanded="false">
			<span class="material-symbols-outlined mobile-bottom-bar__icon">search</span>
			<span class="screen-reader-text"><?php echo esc_html__('Abrir buscador', 'farmacia-queiles'); ?></span>
		</button>
		<a class="mobile-bottom-bar__item" href="<?php echo esc_url(home_url('/')); ?>">
			<span class="material-symbols-outlined mobile-bottom-bar__icon">home</span>
			<span class="screen-reader-text"><?php echo esc_html__('Ir al inicio', 'farmacia-queiles'); ?></span>
		</a>
		<?php if (class_exists('WooCommerce')) : ?>
			<a class="mobile-bottom-bar__item mobile-bottom-bar__item--cart" href="<?php echo esc_url(wc_get_cart_url()); ?>" data-open-site-cart="true" aria-controls="site-cart-drawer" aria-expanded="false">
				<span class="material-symbols-outlined mobile-bottom-bar__icon">shopping_bag</span>
				<span class="mobile-bottom-bar__badge cart-count-fragment<?php echo $cart_count < 1 ? ' is-empty' : ''; ?>"><?php echo esc_html((string) $cart_count); ?></span>
				<span class="screen-reader-text"><?php echo esc_html__('Abrir carrito', 'farmacia-queiles'); ?></span>
			</a>
		<?php endif; ?>
	</nav>
