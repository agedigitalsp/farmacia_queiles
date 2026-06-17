<?php

if (!defined('ABSPATH')) {
	exit;
}

$allowed_basic_html = [
	'span' => ['class' => true],
	'em' => [],
	'strong' => [],
	'b' => [],
	'i' => [],
	'br' => [],
];

$image_id = (int) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_image_id', 0);
$image_url = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_image', '');
$kicker = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_kicker', __('Consultoría profesional', 'farmacia-queiles'));
$title_html = (string) Farmacia_Queiles_Theme::get_setting(
	'farmacia_queiles_home_consulting_title_html',
	'¿Necesitas <span class="home-consulting-cta__title-accent">asesoramiento</span> farmacéutico?'
);
$text_html = (string) Farmacia_Queiles_Theme::get_setting(
	'farmacia_queiles_home_consulting_text_html',
	'Nuestro equipo de farmacéuticos expertos en <strong>dermocosmética</strong>, <strong>cuidado infantil</strong> y <strong>ortopedia personalizada</strong> está disponible para resolver tus dudas de forma gratuita y personalizada.'
);

$cta_text = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_cta_text', __('Contactar por WhatsApp', 'farmacia-queiles'));
$cta_url_default = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_footer_whatsapp_url', '');
$cta_url = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_cta_url', $cta_url_default);
$cta_icon = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_cta_icon', 'chat');

$status_enabled = (int) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_status_enabled', 1) === 1;
$status_text = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_consulting_status_text', __('Respuesta inmediata', 'farmacia-queiles'));

$has_image = $image_id > 0 || '' !== trim($image_url);
$has_cta = '' !== trim($cta_url);
?>
<section class="home-consulting-cta" aria-label="<?php echo esc_attr__('Consultoría farmacéutica', 'farmacia-queiles'); ?>">
	<div class="container container--wide">
		<div class="home-consulting-cta__card">
			<?php if ($has_image) : ?>
				<div class="home-consulting-cta__media" aria-hidden="true">
					<?php if ($image_id > 0) : ?>
						<?php echo wp_get_attachment_image($image_id, 'full', false, ['class' => 'home-consulting-cta__image']); ?>
					<?php elseif ('' !== trim($image_url)) : ?>
						<img class="home-consulting-cta__image" src="<?php echo esc_url($image_url); ?>" alt="">
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="home-consulting-cta__content">
				<span class="home-consulting-cta__kicker"><?php echo esc_html($kicker); ?></span>
				<h2 class="home-consulting-cta__title"><?php echo wp_kses($title_html, $allowed_basic_html); ?></h2>
				<p class="home-consulting-cta__text"><?php echo wp_kses($text_html, $allowed_basic_html); ?></p>

				<div class="home-consulting-cta__actions">
					<?php if ($has_cta) : ?>
						<a class="home-consulting-cta__button" href="<?php echo esc_url($cta_url); ?>" <?php echo Farmacia_Queiles_Theme::get_seo_link_attributes($cta_url); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<span class="material-symbols-outlined home-consulting-cta__button-icon" aria-hidden="true"><?php echo esc_html($cta_icon); ?></span>
							<span><?php echo esc_html($cta_text); ?></span>
						</a>
					<?php endif; ?>

					<?php if ($status_enabled && '' !== trim($status_text)) : ?>
						<span class="home-consulting-cta__status">
							<span class="home-consulting-cta__status-dot" aria-hidden="true"></span>
							<span><?php echo esc_html($status_text); ?></span>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
