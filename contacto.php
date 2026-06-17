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
$contact_title = __('Contacta con Farmacia Queiles', 'farmacia-queiles');
$contact_description = __('Nuestro equipo de farmacéuticos está disponible para resolver tus dudas sobre dermocosmética, salud y bienestar.', 'farmacia-queiles');
$site_name = get_bloginfo('name');
$site_url = get_home_url();

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
	<div class="container container--wide">
		<main id="primary" class="site-main">
			<!-- Schema.org markup para LocalBusiness -->
			<script type="application/ld+json">
				<?php echo wp_json_encode($schema_data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</script>

			<!-- Hero Section -->
			<section class="contact-hero" aria-label="<?php echo esc_attr__('Encabezado de contacto', 'farmacia-queiles'); ?>">
				<div class="contact-hero__content">
					<h1 class="contact-hero__title"><?php echo esc_html($contact_title); ?></h1>
					<p class="contact-hero__subtitle"><?php echo esc_html($contact_description); ?></p>
				</div>
			</section>

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
							<a class="contact-info-card__link" href="<?php echo esc_url($address_url); ?>" rel="noopener noreferrer" target="_blank" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($address_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
						<h3 class="contact-info-card__title"><?php echo esc_html__('Llamanos', 'farmacia-queiles'); ?></h3>
						<?php if (!empty($phone_url)) : ?>
							<a class="contact-info-card__link" href="<?php echo esc_url($phone_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($phone_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
							<span class="material-symbols-outlined contact-info-card__icon">chat</span>
						</div>
						<h3 class="contact-info-card__title"><?php echo esc_html__('Mensaje directo', 'farmacia-queiles'); ?></h3>
						<?php if (!empty($whatsapp_url)) : ?>
							<a class="contact-info-card__link" href="<?php echo esc_url($whatsapp_url); ?>" rel="noopener noreferrer" target="_blank" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($whatsapp_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
						<h2 class="contact-form-section__title"><?php echo esc_html__('Envíanos tu consulta', 'farmacia-queiles'); ?></h2>
						<p class="contact-form-section__description"><?php echo esc_html__('Completa el formulario y nuestro equipo te responderá en la mayor brevedad posible.', 'farmacia-queiles'); ?></p>

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
			</section>

		</main>
	</div>
</div>

<?php
get_footer();
