<?php

if (!defined('ABSPATH')) {
	exit;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_best_sellers_cached_payload() : null;
$products = is_array($cached_payload['products'] ?? null) ? $cached_payload['products'] : [];

$fq_bs_limit = (int) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_bestsellers_limit', 10 );
$fq_bs_limit = max( 4, min( 20, $fq_bs_limit ) );

if (empty($products)) {
	$products_raw = wc_get_products([
		'orderby'  => 'meta_value_num',
		'meta_key' => 'total_sales',
		'order'    => 'DESC',
		'limit'    => $fq_bs_limit,
		'status'   => 'publish',
	]);

	if (empty($products_raw)) {
		return;
	}

	$position = 0;
	foreach ($products_raw as $product) {
		$position++;

		$image_id  = (int) $product->get_image_id();
		$image_url = '';
		if ($image_id > 0) {
			$src = wp_get_attachment_image_url($image_id, 'woocommerce_single');
			$image_url = is_string($src) ? $src : '';
		}
		if ('' === $image_url) {
			$image_url = wc_placeholder_img_src('woocommerce_single');
		}

		$brands = [];
		if (taxonomy_exists('product_brand')) {
			$brand_terms = get_the_terms($product->get_id(), 'product_brand');
			if (is_array($brand_terms)) {
				foreach ($brand_terms as $bt) {
					$brands[] = wp_strip_all_tags($bt->name);
				}
			}
		}

		$products[] = [
			'position'        => $position,
			'id'              => $product->get_id(),
			'name'            => wp_strip_all_tags($product->get_name()),
			'url'             => $product->get_permalink(),
			'image'           => $image_url,
			'brand'           => implode(', ', $brands),
			'description'     => wp_strip_all_tags($product->get_short_description()),
			'regular_price'   => (string) $product->get_regular_price(),
			'sale_price'      => (string) $product->get_sale_price(),
			'is_on_sale'      => $product->is_on_sale(),
			'add_to_cart_url' => $product->add_to_cart_url(),
		];
	}
}

if (empty($products)) {
	return;
}

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/tienda/');
?>
<section class="home-best-sellers">
	<div class="container container--wide">

		<div class="home-best-sellers__header">
			<div class="home-best-sellers__header-left">
				<?php
				$fq_bs_kicker = (string) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_bestsellers_kicker', __( 'Los más populares', 'farmacia-queiles' ) );
				$fq_bs_title  = (string) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_bestsellers_title', __( 'Más Vendidos', 'farmacia-queiles' ) );
				?>
				<span class="home-best-sellers__kicker"><?php echo esc_html( $fq_bs_kicker ); ?></span>
				<h2 class="home-best-sellers__title"><?php echo esc_html( $fq_bs_title ); ?></h2>
			</div>
			<div class="home-best-sellers__header-right">
				<div class="home-best-sellers__controls">
					<div class="home-best-sellers__arrows">
						<button class="home-best-sellers__arrow" type="button" data-bs-prev aria-label="<?php echo esc_attr__('Producto anterior', 'farmacia-queiles'); ?>">
							<span class="material-symbols-outlined">chevron_left</span>
						</button>
						<button class="home-best-sellers__arrow" type="button" data-bs-next aria-label="<?php echo esc_attr__('Siguiente producto', 'farmacia-queiles'); ?>">
							<span class="material-symbols-outlined">chevron_right</span>
						</button>
					</div>
					<a class="home-best-sellers__all-link" href="<?php echo esc_url($shop_url); ?>">
						<?php echo esc_html__('Ver todos los productos', 'farmacia-queiles'); ?>
						<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
					</a>
				</div>
			</div>
		</div>

		<div class="home-best-sellers__viewport splide" data-bs-carousel>
			<div class="home-best-sellers__track splide__track" data-bs-track>
				<div class="splide__list">
				<?php foreach ($products as $item) : ?>
					<article class="bs-card splide__slide" data-fq-card-url="<?php echo esc_url($item['url']); ?>">

						<div class="bs-card__image-wrap">
							<span class="bs-card__badge">TOP</span>
							<img class="bs-card__image"
							     src="<?php echo esc_url($item['image']); ?>"
							     alt="<?php echo esc_attr($item['name']); ?>"
							     loading="lazy">
							<button class="fq-fav-btn" type="button" data-fq-fav="<?php echo esc_attr((string) $item['id']); ?>" aria-pressed="false" aria-label="<?php echo esc_attr__('Guardar en favoritos', 'farmacia-queiles'); ?>">
								<span class="material-symbols-outlined" aria-hidden="true">favorite</span>
							</button>
						</div>

						<div class="bs-card__body">
							<div class="bs-card__brand-wrap">
								<span class="bs-card__brand"><?php echo esc_html($item['brand'] ?? ''); ?></span>
							</div>

							<h3 class="bs-card__name">
								<a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['name']); ?></a>
							</h3>

							<p class="bs-card__desc"><?php echo esc_html($item['description'] ?? ''); ?></p>

							<div class="bs-card__price-wrap">
								<div class="bs-card__price-row">
									<?php if ($item['is_on_sale'] && '' !== $item['sale_price']) : ?>
										<span class="bs-card__price-current"><?php echo wp_kses_post(wc_price((float) $item['sale_price'])); ?></span>
										<s class="bs-card__price-old"><?php echo wp_kses_post(wc_price((float) $item['regular_price'])); ?></s>
									<?php elseif ('' !== $item['regular_price']) : ?>
										<span class="bs-card__price-current"><?php echo wp_kses_post(wc_price((float) $item['regular_price'])); ?></span>
									<?php endif; ?>
								</div>
								<span class="bs-card__price-tax"><?php echo esc_html__('IVA INC', 'farmacia-queiles'); ?></span>
							</div>

							<a class="bs-card__cta add_to_cart_button ajax_add_to_cart"
							   href="<?php echo esc_url($item['add_to_cart_url']); ?>"
							   data-product_id="<?php echo esc_attr((string) $item['id']); ?>"
							   data-quantity="1"
							   rel="nofollow">
								<?php echo esc_html__('Comprar', 'farmacia-queiles'); ?>
							</a>
						</div>

					</article>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
