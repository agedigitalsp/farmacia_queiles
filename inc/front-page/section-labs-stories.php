<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!taxonomy_exists('product_brand')) {
	return;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_labs_cached_payload() : null;
$labs = is_array($cached_payload['labs'] ?? null) ? $cached_payload['labs'] : [];

if (empty($labs)) {
	$terms = get_terms(
		[
			'taxonomy' => 'product_brand',
			'hide_empty' => false,
			'meta_query' => [
				[
					'key' => '_fq_featured_product_brand',
					'value' => '1',
				],
			],
			'orderby' => 'name',
			'order' => 'ASC',
		]
	);

	if (is_wp_error($terms) || empty($terms)) {
		return;
	}

	foreach ($terms as $term) {
		$home_image_id = (int) get_term_meta((int) $term->term_id, '_fq_product_brand_home_image_id', true);
		$home_image = (string) get_term_meta((int) $term->term_id, '_fq_product_brand_home_image', true);

		if ($home_image_id > 0) {
			$from_id = wp_get_attachment_image_url($home_image_id, 'full');
			$home_image = is_string($from_id) ? $from_id : $home_image;
		}

		if ('' === $home_image) {
			continue;
		}

		$url = get_term_link($term);
		if (is_wp_error($url)) {
			continue;
		}

		$labs[] = [
			'id' => (int) $term->term_id,
			'name' => $term->name,
			'url' => $url,
			'home_image' => $home_image,
		];
	}
}

if (empty($labs)) {
	return;
}

$is_slider = count($labs) > 5;
$render_labs = $is_slider ? $labs : array_slice($labs, 0, 5);
$section_kicker = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_labs_kicker', __('Nuestros laboratorios', 'farmacia-queiles'));
$section_title_html = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_labs_title_html', 'Laboratorios de <span class="home-labs-stories__title-accent">Confianza</span>');
?>
<section class="home-labs-stories">
	<div class="container container--wide">
		<header class="home-labs-stories__header">
			<span class="home-labs-stories__kicker"><?php echo esc_html($section_kicker); ?></span>
			<h2 class="home-labs-stories__title"><?php echo wp_kses($section_title_html, ['span' => ['class' => true], 'em' => [], 'strong' => [], 'b' => [], 'i' => [], 'br' => []]); ?></h2>
		</header>

		<div class="home-labs-stories__carousel<?php echo $is_slider ? ' is-slider' : ''; ?>"<?php echo $is_slider ? ' data-labs-carousel data-labs-delay="2000"' : ''; ?>>
			<?php if ($is_slider) : ?>
				<button class="home-labs-stories__arrow home-labs-stories__arrow--prev" type="button" data-labs-prev aria-label="<?php echo esc_attr__('Laboratorio anterior', 'farmacia-queiles'); ?>">
					<span class="material-symbols-outlined">chevron_left</span>
				</button>
			<?php endif; ?>

			<div class="home-labs-stories__viewport">
				<div class="home-labs-stories__track" data-labs-track>
					<?php foreach ($render_labs as $lab) : ?>
						<a class="lab-story" href="<?php echo esc_url($lab['url']); ?>" aria-label="<?php echo esc_attr($lab['name']); ?>">
							<span class="lab-story__media" style="background-image:url('<?php echo esc_url($lab['home_image']); ?>');"></span>
							<span class="lab-story__label"><?php echo esc_html($lab['name']); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ($is_slider) : ?>
				<button class="home-labs-stories__arrow home-labs-stories__arrow--next" type="button" data-labs-next aria-label="<?php echo esc_attr__('Siguiente laboratorio', 'farmacia-queiles'); ?>">
					<span class="material-symbols-outlined">chevron_right</span>
				</button>
			<?php endif; ?>
		</div>
	</div>
</section>
