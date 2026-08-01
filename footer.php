<?php

if (!defined('ABSPATH')) {
	exit;
}

$newsletter_title = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_newsletter_title', __('Únete a nuestra comunidad', 'farmacia-queiles'));
$newsletter_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_newsletter_text', __('Recibe consejos farmacéuticos exclusivos y descubre antes que nadie nuestras novedades botánicas.', 'farmacia-queiles'));
$newsletter_placeholder = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_newsletter_placeholder', __('Tu correo electrónico', 'farmacia-queiles'));
$newsletter_button = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_newsletter_button', __('Suscribirme', 'farmacia-queiles'));
$brand_text = Farmacia_Queiles_Theme::get_setting(
	'farmacia_queiles_footer_brand_text',
	__('Donde la ciencia farmacéutica se encuentra con el bienestar profundo. Cuidamos tu piel y tu salud con el rigor de un boticario y la sensibilidad de quien valora la vida.', 'farmacia-queiles')
);
$custom_logo_id = get_theme_mod('custom_logo');
$footer_address_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_address_text', Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, 50500 Tarazona'));
$footer_address_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_address_url', Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_url', ''));
$footer_phone_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_phone_text', Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_text', '976 642 685'));
$footer_phone_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_phone_url', Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_url', 'tel:+34976642685'));
$footer_whatsapp_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_whatsapp_text', '689 123 456');
$footer_whatsapp_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_whatsapp_url', '');
$footer_schedule_title = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_schedule_title', __('Nuestra Botica:', 'farmacia-queiles'));
$footer_schedule_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_schedule_text', "L-V: 9:00 - 13:45 | 16:30 - 20:00\nSábados: 9:00 - 13:45");
$footer_copyright = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_copyright', '© {year} {site}. ELEVATING PHARMACEUTICAL CARE.');
$footer_copyright = str_replace(
	['{year}', '{site}'],
	[wp_date('Y'), get_bloginfo('name')],
	(string) $footer_copyright
);
$footer_explore_fallback = [
	[
		'label' => __('Dermocosmética', 'farmacia-queiles'),
		'url' => home_url('/categoria-producto/dermocosmetica'),
	],
	[
		'label' => __('Protección Solar', 'farmacia-queiles'),
		'url' => home_url('/categoria-producto/solar'),
	],
	[
		'label' => __('Cuidado Facial', 'farmacia-queiles'),
		'url' => home_url('/categoria-producto/facial'),
	],
	[
		'label' => __('Bienestar Infantil', 'farmacia-queiles'),
		'url' => home_url('/categoria-producto/infantil'),
	],
	[
		'label' => __('Cuidado Corporal', 'farmacia-queiles'),
		'url' => home_url('/categoria-producto/corporal'),
	],
];
$footer_support_fallback = [
	[
		'label' => __('Envíos y Entregas', 'farmacia-queiles'),
		'url' => home_url('/envios-y-entregas'),
	],
	[
		'label' => __('Gestión de Devoluciones', 'farmacia-queiles'),
		'url' => home_url('/devoluciones'),
	],
	[
		'label' => __('Seguimiento de Pedido', 'farmacia-queiles'),
		'url' => home_url('/seguimiento-pedido'),
	],
	[
		'label' => __('Preguntas Frecuentes', 'farmacia-queiles'),
		'url' => home_url('/preguntas-frecuentes'),
	],
];
$footer_legal_fallback = [
	[
		'label' => __('Aviso legal', 'farmacia-queiles'),
		'url' => home_url('/aviso-legal'),
	],
	[
		'label' => __('Privacidad', 'farmacia-queiles'),
		'url' => home_url('/privacidad'),
	],
	[
		'label' => __('Cookies', 'farmacia-queiles'),
		'url' => home_url('/cookies'),
	],
];

?>
	<footer class="site-footer site-footer--luxury">
		<div class="container container--wide">
			<div class="footer-newsletter">
				<div class="footer-newsletter__text">
					<h3 class="footer-newsletter__title"><?php echo esc_html($newsletter_title); ?></h3>
					<p class="footer-newsletter__description"><?php echo esc_html($newsletter_text); ?></p>
				</div>
				<form class="footer-newsletter__form" id="footer-newsletter-form" action="<?php echo esc_url(home_url('/')); ?>" method="post" novalidate>
					<div class="footer-newsletter__row">
						<label class="screen-reader-text" for="footer-newsletter-email"><?php echo esc_html__('Email', 'farmacia-queiles'); ?></label>
						<input id="footer-newsletter-email" class="footer-newsletter__input" type="email" name="email" placeholder="<?php echo esc_attr($newsletter_placeholder); ?>">
						<button class="footer-newsletter__button" type="submit"><?php echo esc_html($newsletter_button); ?></button>
					</div>
					<div class="footer-newsletter__consent">
						<label class="footer-newsletter__checkbox-label" for="footer-newsletter-consent">
							<input type="checkbox" id="footer-newsletter-consent" class="footer-newsletter__checkbox" name="consent">
							<span><?php echo esc_html__('He leído y acepto los ', 'farmacia-queiles'); ?><a href="<?php echo esc_url(home_url('/politica-privacidad')); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('términos y condiciones', 'farmacia-queiles'); ?></a>.</span>
						</label>
						<p class="footer-newsletter__error" id="footer-newsletter-error" role="alert" hidden><?php echo esc_html__('Debes aceptar los términos y condiciones.', 'farmacia-queiles'); ?></p>
					</div>
				</form>
			</div>

			<div class="footer-main">
				<div class="footer-col footer-col--brand">
					<a class="footer-brand" href="<?php echo esc_url(home_url('/')); ?>">
						<?php if (file_exists(get_template_directory() . '/assets/img/logo.svg')) : ?>
							<img class="footer-brand__image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/logo.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
						<?php elseif ($footer_logo_id > 0) : ?>
							<?php echo wp_get_attachment_image($footer_logo_id, 'full', false, ['class' => 'footer-brand__image']); ?>
						<?php elseif ($custom_logo_id > 0) : ?>
							<?php echo wp_get_attachment_image($custom_logo_id, 'full', false, ['class' => 'footer-brand__image']); ?>
						<?php else : ?>
							<span class="footer-brand__name"><?php bloginfo('name'); ?></span>
						<?php endif; ?>
					</a>
					<?php if (!empty($brand_text)) : ?>
						<p class="footer-brand__description"><?php echo esc_html($brand_text); ?></p>
					<?php endif; ?>

					<div class="footer-social">
						<a class="footer-social__link" href="https://www.instagram.com/farmacia_queiles/" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__('Instagram', 'farmacia-queiles'); ?>">
							<svg class="footer-social__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="1.6"/>
								<circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.6"/>
								<circle cx="17.3" cy="6.7" r="1.1" fill="currentColor"/>
							</svg>
						</a>
						<a class="footer-social__link" href="https://www.tiktok.com/@farmacia_queiles?_r=1&_t=ZN-97rFqYup8aM" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__('TikTok', 'farmacia-queiles'); ?>">
							<svg class="footer-social__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M14.5 3v10.9a2.6 2.6 0 1 1-2.1-2.55" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M14.5 3.5c.4 2.2 2.1 3.9 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
						<a class="footer-social__link" href="https://www.facebook.com/profile.php?id=61576813976751" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__('Facebook', 'farmacia-queiles'); ?>">
							<svg class="footer-social__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M14.5 8.5h2V5.6h-2.3c-2.1 0-3.4 1.4-3.4 3.6v1.8H9v3h1.8V21h3v-6.9h2.2l.4-3h-2.6V9.3c0-.6.3-.8.7-.8Z" fill="currentColor"/>
							</svg>
						</a>
					</div>
				</div>

				<div class="footer-col">
					<details class="footer-toggle" data-footer-toggle open>
						<summary class="footer-toggle__summary">
							<span class="footer-heading"><?php echo esc_html__('Categorías principales', 'farmacia-queiles'); ?></span>
							<span class="material-symbols-outlined footer-toggle__icon">chevron_right</span>
						</summary>
						<div class="footer-toggle__content">
							<?php if (has_nav_menu('footer_explore')) : ?>
								<?php
								wp_nav_menu(
									[
										'theme_location' => 'footer_explore',
										'container' => false,
										'menu_class' => 'footer-menu',
										'fallback_cb' => false,
									]
								);
								?>
							<?php else : ?>
								<ul class="footer-menu">
									<?php foreach ($footer_explore_fallback as $item) : ?>
										<li><a href="<?php echo esc_url($item['url']); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($item['url']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($item['label']); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</details>
				</div>

				<div class="footer-col">
					<details class="footer-toggle" data-footer-toggle open>
						<summary class="footer-toggle__summary">
							<span class="footer-heading"><?php echo esc_html__('Soporte', 'farmacia-queiles'); ?></span>
							<span class="material-symbols-outlined footer-toggle__icon">chevron_right</span>
						</summary>
						<div class="footer-toggle__content">
							<?php if (has_nav_menu('footer_support')) : ?>
								<?php
								wp_nav_menu(
									[
										'theme_location' => 'footer_support',
										'container' => false,
										'menu_class' => 'footer-menu',
										'fallback_cb' => false,
									]
								);
								?>
							<?php else : ?>
								<ul class="footer-menu">
									<?php foreach ($footer_support_fallback as $item) : ?>
										<li><a href="<?php echo esc_url($item['url']); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($item['url']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($item['label']); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</details>
				</div>

				<div class="footer-col footer-col--contact">
					<details class="footer-toggle" data-footer-toggle open>
						<summary class="footer-toggle__summary">
							<span class="footer-heading"><?php echo esc_html__('Contacto', 'farmacia-queiles'); ?></span>
							<span class="material-symbols-outlined footer-toggle__icon">chevron_right</span>
						</summary>
						<div class="footer-toggle__content">
							<div class="footer-contact">
								<?php if (!empty($footer_address_text)) : ?>
									<div class="footer-contact__item">
										<span class="material-symbols-outlined footer-contact__icon">location_on</span>
										<?php if (!empty($footer_address_url)) : ?>
											<a class="footer-contact__link" href="<?php echo esc_url($footer_address_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($footer_address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($footer_address_text); ?></a>
										<?php else : ?>
											<span><?php echo esc_html($footer_address_text); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if (!empty($footer_phone_text)) : ?>
									<div class="footer-contact__item">
										<span class="material-symbols-outlined footer-contact__icon">call</span>
										<?php if (!empty($footer_phone_url)) : ?>
											<a class="footer-contact__link" href="<?php echo esc_url($footer_phone_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($footer_phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($footer_phone_text); ?></a>
										<?php else : ?>
											<span><?php echo esc_html($footer_phone_text); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if (!empty($footer_whatsapp_text)) : ?>
									<div class="footer-contact__item">
										<img class="footer-contact__icon footer-contact__icon--image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/whatsapp.svg'); ?>" alt="<?php echo esc_attr__('WhatsApp', 'farmacia-queiles'); ?>">
										<?php if (!empty($footer_whatsapp_url)) : ?>
											<a class="footer-contact__link" href="<?php echo esc_url($footer_whatsapp_url); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($footer_whatsapp_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($footer_whatsapp_text); ?></a>
										<?php else : ?>
											<span class="footer-contact__strong"><?php echo esc_html($footer_whatsapp_text); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if (!empty($footer_schedule_text)) : ?>
									<div class="footer-contact__schedule">
										<p class="footer-contact__schedule-title"><?php echo esc_html($footer_schedule_title); ?></p>
										<p class="footer-contact__schedule-text"><?php echo wp_kses_post(nl2br(esc_html($footer_schedule_text))); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</details>
				</div>
			</div>

			<div class="footer-sub">
				<nav class="footer-legal" aria-label="<?php echo esc_attr__('Legal', 'farmacia-queiles'); ?>">
					<?php if (has_nav_menu('footer_legal')) : ?>
						<?php
						wp_nav_menu(
							[
								'theme_location' => 'footer_legal',
								'container' => false,
								'menu_class' => 'footer-legal__menu',
								'fallback_cb' => false,
							]
						);
						?>
					<?php elseif (has_nav_menu('footer')) : ?>
						<?php
						wp_nav_menu(
							[
								'theme_location' => 'footer',
								'container' => false,
								'menu_class' => 'footer-legal__menu',
								'fallback_cb' => false,
							]
						);
						?>
					<?php else : ?>
						<ul class="footer-legal__menu">
							<?php foreach ($footer_legal_fallback as $item) : ?>
								<li><a href="<?php echo esc_url($item['url']); ?>"<?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($item['url']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html($item['label']); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</nav>

				<p class="footer-copy"><?php echo esc_html($footer_copyright); ?></p>

				<div class="footer-payments" aria-label="<?php echo esc_attr__('Métodos de pago', 'farmacia-queiles'); ?>">
					<span class="footer-payment">
						<img class="footer-payment__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/visa.svg'); ?>" alt="<?php echo esc_attr__('Visa', 'farmacia-queiles'); ?>">
					</span>
					<span class="footer-payment">
						<img class="footer-payment__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/mastercard.svg'); ?>" alt="<?php echo esc_attr__('Mastercard', 'farmacia-queiles'); ?>">
					</span>
					<span class="footer-payment">
						<img class="footer-payment__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/apple-pay.svg'); ?>" alt="<?php echo esc_attr__('Apple Pay', 'farmacia-queiles'); ?>">
					</span>
					<span class="footer-payment">
						<img class="footer-payment__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/google-pay.svg'); ?>" alt="<?php echo esc_attr__('Google Pay', 'farmacia-queiles'); ?>">
					</span>
				</div>
			</div>
		</div>
	</footer>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
	const footerToggles = document.querySelectorAll('[data-footer-toggle]');

	if (!footerToggles.length) {
		return;
	}

	const syncFooterToggles = function () {
		const isMobile = window.innerWidth <= 640;

		footerToggles.forEach(function (toggle) {
			if (isMobile) {
				toggle.removeAttribute('open');
				return;
			}

			toggle.setAttribute('open', 'open');
		});
	};

	syncFooterToggles();
	window.addEventListener('resize', syncFooterToggles);

	const newsletterForm = document.getElementById('footer-newsletter-form');

	if (newsletterForm) {
		const emailInput = document.getElementById('footer-newsletter-email');
		const consentInput = document.getElementById('footer-newsletter-consent');
		const errorMessage = document.getElementById('footer-newsletter-error');

		consentInput.addEventListener('change', function () {
			if (consentInput.checked) {
				errorMessage.hidden = true;
			}
		});

		newsletterForm.addEventListener('submit', function (event) {
			if (!emailInput.value.trim()) {
				event.preventDefault();
				return;
			}

			if (!consentInput.checked) {
				event.preventDefault();
				errorMessage.hidden = false;
				return;
			}

			errorMessage.hidden = true;
		});
	}
});
</script>
<?php wp_footer(); ?>
</body>
</html>
