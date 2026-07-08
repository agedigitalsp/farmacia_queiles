<?php

if (!defined('ABSPATH')) {
	exit;
}

$phone_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_text', '976 642 685');
$phone_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_url', 'tel:+34976642685');
$address_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, Tarazona');
$address_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_url', '');
$schedule_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_schedule_text', 'L-V 9:00-13:45 · 16:30-20:00');
$contact_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_contact_url', home_url('/contacto'));
$my_account_url = Farmacia_Queiles_Theme::get_setting(
	'farmacia_queiles_my_account_url',
	class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url()
);
$favorites_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_favorites_url', home_url('/favoritos'));
$fq_fav_count = 0;
if (is_user_logged_in()) {
	$fq_fav_stored = get_user_meta(get_current_user_id(), '_fq_favorites', true);
	$fq_fav_count  = is_array($fq_fav_stored) ? count(array_filter($fq_fav_stored, 'intval')) : 0;
} elseif (isset($_COOKIE['fq_favorites'])) {
	$fq_fav_decoded = json_decode(urldecode(sanitize_text_field(wp_unslash($_COOKIE['fq_favorites']))), true);
	$fq_fav_count   = is_array($fq_fav_decoded) ? count(array_filter($fq_fav_decoded, 'intval')) : 0;
}
$footer_whatsapp_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_whatsapp_url', '');
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

?>
<!doctype html>
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
							<a class="preheader-item__icon-link" href="<?php echo esc_url($phone_url); ?>" aria-label="<?php echo esc_attr__('Llamar por teléfono', 'farmacia-queiles'); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																																																?>>
								<span class="material-symbols-outlined">call</span>
							</a>
						<?php else : ?>
							<span class="preheader-item__icon-link preheader-item__icon-link--static" aria-hidden="true">
								<span class="material-symbols-outlined">call</span>
							</span>
						<?php endif; ?>
						<?php if (!empty($phone_url)) : ?>
							<a class="preheader-item__link" href="<?php echo esc_url($phone_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																										?>><?php echo esc_html($phone_text); ?></a>
						<?php else : ?>
							<span><?php echo esc_html($phone_text); ?></span>
						<?php endif; ?>
					</div>
					<div class="preheader-item">
						<?php if (!empty($address_url)) : ?>
							<a class="preheader-item__icon-link preheader-item__icon-link--desktop" href="<?php echo esc_url($address_url); ?>" aria-label="<?php echo esc_attr__('Abrir ubicación', 'farmacia-queiles'); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																																																								?>>
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
							<a class="preheader-item__link" href="<?php echo esc_url($address_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																										?>><?php echo esc_html($address_text); ?></a>
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
				<div class="preheader-cta-desktop">
					<a href="javascript:void(0);" class="preheader-cta" data-open-guardia-popup="true" aria-label="<?php echo esc_attr__('Ver farmacias de guardia', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined preheader-cta__icon" aria-hidden="true">local_pharmacy</span>
						<?php echo esc_html__('Farmacias de Guardia', 'farmacia-queiles'); ?>
					</a>
					<div class="preheader-separator"></div>
					<a class="preheader-cta" href="<?php echo esc_url($contact_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($contact_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																								?>>
							<span class="material-symbols-outlined preheader-cta__icon" aria-hidden="true">mail</span>
							<?php echo esc_html__('Contacto', 'farmacia-queiles'); ?>
						</a>
				</div>
				<button class="preheader-cta-mobile-toggle" type="button" aria-label="<?php echo esc_attr__('Abrir farmacias de guardia y contacto', 'farmacia-queiles'); ?>" aria-expanded="false">
					<?php echo esc_html__('Guardia y contacto', 'farmacia-queiles'); ?>
					<span class="material-symbols-outlined">expand_more</span>
				</button>
				<div class="preheader-cta-mobile-dropdown">
					<a href="javascript:void(0);" class="preheader-cta-mobile-item" data-open-guardia-popup="true">
						<span class="material-symbols-outlined preheader-cta__icon" aria-hidden="true">local_pharmacy</span>
						<?php echo esc_html__('Farmacias de Guardia', 'farmacia-queiles'); ?>
					</a>
					<a class="preheader-cta-mobile-item" href="<?php echo esc_url($contact_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($contact_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																										?>>
						<span class="material-symbols-outlined preheader-cta__icon" aria-hidden="true">mail</span>
						<?php echo esc_html__('Contáctanos', 'farmacia-queiles'); ?>
					</a>
				</div>
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
						<?php echo do_shortcode('[sp_mi_cuenta_icono]'); ?>

						<a class="util-link" href="<?php echo esc_url($favorites_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($favorites_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																							?>>
							<span class="material-symbols-outlined util-link__icon">favorite</span>
							<span class="util-link__badge fq-fav-count-badge<?php echo $fq_fav_count < 1 ? ' is-empty' : ''; ?>" aria-live="polite">
								<?php echo esc_html((string) $fq_fav_count); ?>
							</span>
							<span class="util-link__label"><?php echo esc_html__('Favoritos', 'farmacia-queiles'); ?></span>
						</a>

						<?php echo do_shortcode('[sp_menu_movil]'); ?>

						<?php if (class_exists('WooCommerce')) : ?>
							<a
								href="javascript:void(0);"
								class="util-link util-link--cart header-cart-icon"
								data-open-site-cart="false"
								aria-controls="site-cart-drawer"
								aria-expanded="false">
								<span class="material-symbols-outlined util-link__icon">shopping_bag</span>
								<span class="util-link__badge cart-count-fragment<?php echo $cart_count < 1 ? ' is-empty' : ''; ?>">
									<?php echo esc_html((string) $cart_count); ?>
								</span>
								<span class="util-link__label"><?php echo esc_html__('Carrito', 'farmacia-queiles'); ?></span>
							</a> <?php endif; ?>
					</div>
				</div>
			</div>

			<nav class="site-header__nav" aria-label="<?php echo esc_attr__('Navegación', 'farmacia-queiles'); ?>">
				<div class="container container--wide site-header__nav-inner">
					<div class="site-header__nav-left">
						<?php if (!empty($header_categories['featured']) || !empty($header_categories['more'])) : ?>
							<ul class="header-categories" role="list">
								<?php
								foreach ($header_categories['featured'] as $category) :
									$fq_children = !empty($category->fq_children) ? $category->fq_children : [];
									$fq_has_children = !empty($fq_children);
								?>
									<li class="header-categories__item<?php echo $fq_has_children ? ' header-categories__item--has-children' : ''; ?><?php echo (int) $category->term_id === $current_category_id ? ' is-current' : ''; ?>">
										<a class="header-categories__link" href="<?php echo esc_url(get_term_link($category)); ?>"<?php echo $fq_has_children ? ' aria-haspopup="true"' : ''; ?>>
											<?php echo esc_html($category->name); ?>
											<?php if ($fq_has_children) : ?>
												<span class="material-symbols-outlined header-categories__link-arrow" aria-hidden="true">expand_more</span>
											<?php endif; ?>
										</a>
										<?php if ($fq_has_children) : ?>
											<div class="header-categories__megamenu">
												<ul class="header-categories__megamenu-list" role="list">
													<?php foreach ($fq_children as $child) : ?>
														<li class="header-categories__megamenu-item<?php echo (int) $child->term_id === $current_category_id ? ' is-current' : ''; ?>">
															<a class="header-categories__megamenu-link" href="<?php echo esc_url(get_term_link($child)); ?>">
																<?php echo esc_html($child->name); ?>
															</a>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
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
							<a class="site-contact-modal__button" href="<?php echo esc_url($address_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																												?>>
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



		<nav class="mobile-bottom-bar" aria-label="<?php echo esc_attr__('Accesos móviles', 'farmacia-queiles'); ?>">
			<a class="mobile-bottom-bar__item" href="<?php echo esc_url(home_url('/')); ?>">
				<span class="material-symbols-outlined mobile-bottom-bar__icon">home</span>
				<span class="mobile-bottom-bar__label"><?php echo esc_html__('Inicio', 'farmacia-queiles'); ?></span>
			</a>
			<button class="mobile-bottom-bar__item" type="button" data-open-mobile-search="true" aria-controls="site-mobile-search" aria-expanded="false">
				<span class="material-symbols-outlined mobile-bottom-bar__icon">search</span>
				<span class="mobile-bottom-bar__label"><?php echo esc_html__('Buscar', 'farmacia-queiles'); ?></span>
			</button>
			<?php if (!empty($footer_whatsapp_url)) : ?>
				<a class="mobile-bottom-bar__item mobile-bottom-bar__item--whatsapp" href="<?php echo esc_url($footer_whatsapp_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($footer_whatsapp_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																																	?>>
					<img class="mobile-bottom-bar__icon-image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/whatsapp.svg'); ?>" alt="" aria-hidden="true">
					<span class="mobile-bottom-bar__label"><?php echo esc_html__('WhatsApp', 'farmacia-queiles'); ?></span>
				</a>
			<?php else : ?>
				<span class="mobile-bottom-bar__item mobile-bottom-bar__item--whatsapp mobile-bottom-bar__item--disabled" aria-hidden="true">
					<img class="mobile-bottom-bar__icon-image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/whatsapp.svg'); ?>" alt="">
					<span class="mobile-bottom-bar__label"><?php echo esc_html__('WhatsApp', 'farmacia-queiles'); ?></span>
				</span>
			<?php endif; ?>
			<?php if (class_exists('WooCommerce')) : ?>
				<button class="mobile-bottom-bar__item mobile-bottom-bar__item--cart header-cart-icon" type="button" data-open-site-cart="false" aria-controls="site-cart-drawer" aria-expanded="false">
					<span class="material-symbols-outlined mobile-bottom-bar__icon">shopping_bag</span>
					<span class="mobile-bottom-bar__badge cart-count-fragment<?php echo $cart_count < 1 ? ' is-empty' : ''; ?>"><?php echo esc_html((string) $cart_count); ?></span>
					<span class="mobile-bottom-bar__label"><?php echo esc_html__('Carrito', 'farmacia-queiles'); ?></span>
				</button>
			<?php endif; ?>
		</nav>




