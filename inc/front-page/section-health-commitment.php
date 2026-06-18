<?php

if (!defined('ABSPATH')) {
	exit;
}

$section_kicker = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_commitment_kicker', __('Compromiso farmacéutico', 'farmacia-queiles'));
$section_title = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_commitment_title', __('Nuestro Compromiso Sanitario', 'farmacia-queiles'));
$items = [];

for ($index = 1; $index <= 4; $index++) {
	$items[] = [
		'icon' => (string) Farmacia_Queiles_Theme::get_setting(
			"farmacia_queiles_home_commitment_item_{$index}_icon",
			match ($index) {
				1 => 'lock',
				2 => 'store',
				3 => 'local_shipping',
				default => 'forum',
			}
		),
		'title' => (string) Farmacia_Queiles_Theme::get_setting(
			"farmacia_queiles_home_commitment_item_{$index}_title",
			match ($index) {
				1 => __('Pago Seguro', 'farmacia-queiles'),
				2 => __('Farmacia Física', 'farmacia-queiles'),
				3 => __('Envío Gratis', 'farmacia-queiles'),
				default => __('Atención Sanitaria', 'farmacia-queiles'),
			}
		),
		'text' => (string) Farmacia_Queiles_Theme::get_setting(
			"farmacia_queiles_home_commitment_item_{$index}_text",
			match ($index) {
				1 => __('Tarjeta y Bizum 100% protegidos', 'farmacia-queiles'),
				2 => __('Respaldo sanitario real en Tarazona', 'farmacia-queiles'),
				3 => __('A partir de 50€ sin IVA', 'farmacia-queiles'),
				default => __('Asesoramiento y soporte directo por WhatsApp', 'farmacia-queiles'),
			}
		),
		'note' => (string) Farmacia_Queiles_Theme::get_setting(
			"farmacia_queiles_home_commitment_item_{$index}_note",
			match ($index) {
				1 => __('Entorno cifrado bajo protocolo SSL', 'farmacia-queiles'),
				2 => __('Atención y presencia física real', 'farmacia-queiles'),
				3 => __('Fiel a las condiciones comerciales estipuladas', 'farmacia-queiles'),
				default => __('Resolución de dudas por farmacéuticos online', 'farmacia-queiles'),
			}
		),
	];
}
?>
<section class="home-health-commitment">
	<div class="container container--wide">
		<header class="home-health-commitment__header">
			<?php if ('' !== trim($section_kicker)) : ?>
				<span class="home-health-commitment__kicker"><?php echo esc_html($section_kicker); ?></span>
			<?php endif; ?>
			<h2 class="home-health-commitment__title"><?php echo esc_html($section_title); ?></h2>
		</header>

		<div class="home-health-commitment__grid">
			<?php foreach ($items as $item) : ?>
				<article class="health-commitment-card">
					<div class="health-commitment-card__icon-wrap">
						<span class="material-symbols-outlined health-commitment-card__icon"><?php echo esc_html($item['icon']); ?></span>
					</div>
					<h3 class="health-commitment-card__title"><?php echo esc_html($item['title']); ?></h3>
					<p class="health-commitment-card__text">
						<?php echo esc_html($item['text']); ?>
						<?php if ('' !== trim($item['note'])) : ?>
							<span class="health-commitment-card__note">(<?php echo esc_html($item['note']); ?>)</span>
						<?php endif; ?>
					</p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
