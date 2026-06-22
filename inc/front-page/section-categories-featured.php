<?php

if (!defined('ABSPATH')) {
	exit;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_featured_cats_cached_payload() : null;
$cats = is_array($cached_payload['cats'] ?? null) ? $cached_payload['cats'] : [];

if (empty($cats)) {
	$terms = get_terms([
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_query' => [
			[
				'key'   => '_fq_featured_product_cat',
				'value' => '1',
			],
		],
		'number'     => 5,
	]);

	if (is_wp_error($terms) || empty($terms)) {
		return;
	}

	foreach ($terms as $term) {
		$thumbnail_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
		$image_url    = '';

		if ($thumbnail_id > 0) {
			$from_id   = wp_get_attachment_image_url($thumbnail_id, 'fq-featured-cat');
			$image_url = is_string($from_id) ? $from_id : '';
		}

		$url = get_term_link($term);
		if (is_wp_error($url)) {
			continue;
		}

		$cats[] = [
			'id'        => (int) $term->term_id,
			'name'      => wp_strip_all_tags($term->name),
			'url'       => $url,
			'image'     => $image_url,
			'bg_color'  => (string) get_term_meta($term->term_id, '_fq_cat_bg_color', true) ?: '#dbeeff',
			'bg_color2' => (string) get_term_meta($term->term_id, '_fq_cat_bg_color2', true) ?: '#ffffff',
		];
	}
}

if (empty($cats)) {
	return;
}

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/tienda/');
?>
<section class="home-featured-cats">
	<div class="container container--wide">
		<div class="home-featured-cats__header">
			<h2 class="home-featured-cats__title"><?php echo esc_html__('Categorías Destacadas', 'farmacia-queiles'); ?></h2>
			<a class="home-featured-cats__all-link" href="<?php echo esc_url($shop_url); ?>">
				<?php echo esc_html__('Ver todas las categorías', 'farmacia-queiles'); ?>
				<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
			</a>
		</div>

		<div class="home-featured-cats__grid">
			<?php foreach ($cats as $cat) : ?>
				<a class="home-featured-cats__card"
				   href="<?php echo esc_url($cat['url']); ?>"
				   aria-label="<?php echo esc_attr($cat['name']); ?>"
				   style="background:linear-gradient(200deg,<?php echo esc_attr($cat['bg_color'] ?? '#dbeeff'); ?> 0%,<?php echo esc_attr($cat['bg_color2'] ?? '#ffffff'); ?> 100%)">

					<img class="home-featured-cats__card-img"
					     src="<?php echo esc_url($cat['image']); ?>"
					     alt="<?php echo esc_attr($cat['name']); ?>"
					     loading="lazy">

					<div class="home-featured-cats__card-overlay"></div>

					<div class="home-featured-cats__card-content">
						<h3 class="home-featured-cats__card-name"><?php echo esc_html($cat['name']); ?></h3>
						<div class="home-featured-cats__card-cta" aria-hidden="true">
							<span class="home-featured-cats__card-cta-label"><?php echo esc_html__('Explorar', 'farmacia-queiles'); ?></span>
							<span class="material-symbols-outlined">arrow_right_alt</span>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
