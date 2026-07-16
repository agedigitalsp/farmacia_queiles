<?php

/**
 * Template Name: Contacto
 * Description: Plantilla de página de contacto
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

$phone_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_text', '976 642 685');
$phone_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_phone_url', 'tel:+34976642685');
$address_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, Tarazona');
$address_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_address_url', '');
$schedule_text = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_schedule_text', 'L-V 9:00-13:45 · 16:30-20:00');
$whatsapp_url = Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_whatsapp_url', '');
$contact_title = __('Contacto', 'farmacia-queiles');
$site_name = get_bloginfo('name');
$site_url = get_home_url();

// Imagen de cabecera: campo propio (URL) → imagen destacada → default
$page_id = (int) get_the_ID();
$header_image_url = (string) get_post_meta($page_id, '_fq_page_header_image', true);
if ('' === $header_image_url) {
	$thumb_id = (int) get_post_thumbnail_id($page_id);
	if ($thumb_id > 0) {
		$header_image_url = (string) wp_get_attachment_image_url($thumb_id, 'full');
	}
}
if ('' === $header_image_url) {
	$header_image_url = get_template_directory_uri() . '/assets/img/category-default.webp';
}
$header_style = "background-image:linear-gradient(rgba(255,255,255,0.72),rgba(255,255,255,0.72)),url('" . esc_url($header_image_url) . "');";

// Schema.org markup
$schema_data = [
	'@context' => 'https://schema.org',
	'@type' => 'LocalBusiness',
	'name' => $site_name,
	'url' => $site_url,
	'telephone' => $phone_text,
	'address' => [
		'@type' => 'PostalAddress',
		'streetAddress' => $address_text,
		'addressCountry' => 'ES',
	],
	'contactPoint' => [
		'@type' => 'ContactPoint',
		'contactType' => 'Customer Service',
		'telephone' => $phone_text,
		'availableLanguage' => ['es', 'en'],
	],
];

get_header();
?>

<div class="content">
	<!-- ══ MIGAS DE PAN ══════════════════════════════════════════ -->
	<div class="fq-product-cat-header__top">
		<div class="container container--wide">
			<nav class="fq-product-cat-breadcrumb fq-sp-breadcrumb" aria-label="<?php echo esc_attr__('Migas de pan', 'farmacia-queiles'); ?>">
				<ol class="fq-product-cat-breadcrumb__list">
					<li class="fq-product-cat-breadcrumb__item">
						<a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Inicio', 'farmacia-queiles'); ?></a>
					</li>
					<li class="fq-product-cat-breadcrumb__sep" aria-hidden="true">
						<span class="material-symbols-outlined">chevron_right</span>
					</li>
					<li class="fq-product-cat-breadcrumb__item is-current" aria-current="page">
						<span><?php echo esc_html($contact_title); ?></span>
					</li>
				</ol>
			</nav>
		</div>
	</div>

	<!-- ══ HERO ══════════════════════════════════════════════════ -->
	<header class="fq-secondary-page__hero" style="<?php echo esc_attr($header_style); ?>">
		<div class="container container--wide fq-secondary-page__hero-inner">
			<h1 class="fq-secondary-page__title"><?php echo esc_html($contact_title); ?></h1>
		</div>
	</header>

	<div class="container container--wide">
		<main id="primary" class="site-main">
			<!-- Schema.org markup para LocalBusiness -->
			<script type="application/ld+json">
				<?php echo wp_json_encode($schema_data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</script>

			<!-- Location and Info Cards -->
			<section class="contact-info-grid" aria-label="<?php echo esc_attr__('Información de contacto', 'farmacia-queiles'); ?>">
				<div class="contact-info-grid__container">
					<!-- Ubicación -->
					<div class="contact-info-card">
						<div class="contact-info-card__icon-wrapper">
							<span class="material-symbols-outlined contact-info-card__icon">location_on</span>
						</div>
						<h3 class="contact-info-card__title"><?php echo esc_html__('Nos encontrarás en', 'farmacia-queiles'); ?></h3>
						<?php if (!empty($address_url)) : ?>
							<a class="contact-info-card__link" href="<?php echo esc_url($address_url); ?>" rel="noopener noreferrer" target="_blank" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																																						?>>
								<?php echo esc_html($address_text); ?>
							</a>
						<?php else : ?>
							<address class="contact-info-card__text">
								<?php echo esc_html($address_text); ?>
							</address>
						<?php endif; ?>
						<p class="contact-info-card__detail">
							<span aria-label="<?php echo esc_attr__('Horario de atención', 'farmacia-queiles'); ?>">
								<?php echo esc_html($schedule_text); ?>
							</span>
						</p>
					</div>

					<!-- Teléfono -->
					<div class="contact-info-card">
						<div class="contact-info-card__icon-wrapper">
							<span class="material-symbols-outlined contact-info-card__icon">call</span>
						</div>
						<h3 class="contact-info-card__title"><?php echo esc_html__('Llámanos', 'farmacia-queiles'); ?></h3>
						<?php if (!empty($phone_url)) : ?>
							<a class="contact-info-card__link" href="<?php echo esc_url($phone_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																											?>>
								<span itemprop="telephone"><?php echo esc_html($phone_text); ?></span>
							</a>
						<?php else : ?>
							<p class="contact-info-card__text">
								<span itemprop="telephone"><?php echo esc_html($phone_text); ?></span>
							</p>
						<?php endif; ?>
					</div>

					<!-- WhatsApp -->
					<div class="contact-info-card">
						<div class="contact-info-card__icon-wrapper">
							<svg class="contact-info-card__icon-whatsapp" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<title>WhatsApp icon</title>
								<path fill="#52b2e1" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
							</svg>
						</div>
						<h3 class="contact-info-card__title"><?php echo esc_html__('Mensaje directo', 'farmacia-queiles'); ?></h3>
						<?php if (!empty($whatsapp_url)) : ?>
							<a class="contact-info-card__link" href="<?php echo esc_url($whatsapp_url); ?>" rel="noopener noreferrer" target="_blank" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($whatsapp_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																																						?>>
								<?php echo esc_html__('Contactar por WhatsApp', 'farmacia-queiles'); ?>
							</a>
						<?php endif; ?>
						<p class="contact-info-card__detail">
							<?php echo esc_html__('Respuesta inmediata', 'farmacia-queiles'); ?>
						</p>
					</div>
				</div>
			</section>

			<!-- Contact Form Section -->
			<section class="contact-form-section" aria-label="<?php echo esc_attr__('Formulario de contacto', 'farmacia-queiles'); ?>">
				<div class="contact-form-section__wrapper">
					<div class="contact-form-section__content">
						<div class="contact-form-section__content_text">
							<h2 class="contact-form-section__title"><?php echo esc_html__('Envíanos tu consulta', 'farmacia-queiles'); ?></h2>
							<p class="contact-form-section__description"><?php echo esc_html__('Completa el formulario y nuestro equipo te responderá en la mayor brevedad posible.', 'farmacia-queiles'); ?></p>
						</div>
						<div>
							<?php
							if (function_exists('pll_current_language')) {
								$current_lang = pll_current_language();

								if ('en' === $current_lang) {
									echo do_shortcode('[contact-form-7 id="1ced5b6" title="Formulario de contacto 1"]');
								} else {
									echo do_shortcode('[contact-form-7 id="1ced5b6" title="Formulario de contacto 1"]');
								}
							} else {
								echo do_shortcode('[contact-form-7 id="1ced5b6" title="Formulario de contacto 1"]');
							}

							?>
						</div>
					</div>


				</div>
			</section>

		</main>
	</div>
</div>

<?php
get_footer();
