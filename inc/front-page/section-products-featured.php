<?php

if (!defined('ABSPATH')) {
	exit;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_featured_products_cached_payload() : null;
$products = is_array($cached_payload['products'] ?? null) ? $cached_payload['products'] : [];

$fq_fp_limit = (int) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_featured_products_limit', 10 );
$fq_fp_limit = max( 4, min( 20, $fq_fp_limit ) );

if (empty($products)) {
	$posts = get_posts([
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $fq_fp_limit,
		'meta_query'          => [[
			'key'   => '_fq_featured_product',
			'value' => '1',
		]],
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	]);

	if (!empty($posts)) {
		foreach ($posts as $post) {
			$product = wc_get_product($post->ID);
			if (!$product instanceof WC_Product) {
				continue;
			}

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
				$brand_terms = get_the_terms($post->ID, 'product_brand');
				if (is_array($brand_terms)) {
					foreach ($brand_terms as $bt) {
						$brands[] = wp_strip_all_tags($bt->name);
					}
				}
			}

			$products[] = [
				'id'              => (int) $post->ID,
				'name'            => wp_strip_all_tags(get_the_title($post)),
				'url'             => get_permalink($post),
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
}

if (empty($products)) {
	return;
}

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/tienda/');
?>
<section class="home-featured-products">
	<div class="container container--wide">

		<div class="home-featured-products__header">
			<div class="home-featured-products__header-left">
				<?php
				$fq_fp_kicker = (string) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_featured_products_kicker', __( 'Lo mejor para ti', 'farmacia-queiles' ) );
				$fq_fp_title  = (string) Farmacia_Queiles_Theme::get_setting( 'farmacia_queiles_home_featured_products_title', __( 'Productos Destacados', 'farmacia-queiles' ) );
				?>
				<span class="home-featured-products__kicker"><?php echo esc_html( $fq_fp_kicker ); ?></span>
				<h2 class="home-featured-products__title"><?php echo esc_html( $fq_fp_title ); ?></h2>
			</div>
			<div class="home-featured-products__header-right">
				<div class="home-featured-products__arrows">
					<button class="home-featured-products__arrow" type="button" data-fp-prev aria-label="<?php echo esc_attr__('Producto anterior', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">chevron_left</span>
					</button>
					<button class="home-featured-products__arrow" type="button" data-fp-next aria-label="<?php echo esc_attr__('Siguiente producto', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">chevron_right</span>
					</button>
				</div>
			</div>
		</div>

		<div class="home-featured-products__viewport" data-fp-carousel>
			<div class="home-featured-products__track" data-fp-track>
				<?php foreach ($products as $item) : ?>
					<article class="fp-card" data-fq-card-url="<?php echo esc_url($item['url']); ?>">

						<div class="fp-card__image-wrap">
							<?php if ( $item['is_on_sale'] && '' !== $item['regular_price'] && '' !== $item['sale_price'] && (float) $item['regular_price'] > 0 ) :
								$discount_pct = round( ( 1 - (float) $item['sale_price'] / (float) $item['regular_price'] ) * 100 );
							?>
								<span class="fp-card__badge fp-card__badge--discount">-<?php echo $discount_pct; ?>%</span>
							<?php endif; ?>
							<img class="fp-card__image"
							     src="<?php echo esc_url($item['image']); ?>"
							     alt="<?php echo esc_attr($item['name']); ?>"
							     loading="lazy">
							<button class="fq-fav-btn" type="button" data-fq-fav="<?php echo esc_attr((string) $item['id']); ?>" aria-pressed="false" aria-label="<?php echo esc_attr__('Guardar en favoritos', 'farmacia-queiles'); ?>">
								<span class="material-symbols-outlined" aria-hidden="true">favorite</span>
							</button>
						</div>

						<div class="fp-card__body">
							<div class="fp-card__brand-wrap">
								<span class="fp-card__brand"><?php echo esc_html($item['brand'] ?? ''); ?></span>
							</div>

							<h3 class="fp-card__name">
								<a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['name']); ?></a>
							</h3>

							<p class="fp-card__desc"><?php echo esc_html($item['description'] ?? ''); ?></p>

							<div class="fp-card__price-wrap">
								<div class="fp-card__price-row">
									<?php if ($item['is_on_sale'] && '' !== $item['sale_price']) : ?>
										<span class="fp-card__price-current"><?php echo wp_kses_post(wc_price((float) $item['sale_price'])); ?></span>
										<s class="fp-card__price-old"><?php echo wp_kses_post(wc_price((float) $item['regular_price'])); ?></s>
									<?php elseif ('' !== $item['regular_price']) : ?>
										<span class="fp-card__price-current"><?php echo wp_kses_post(wc_price((float) $item['regular_price'])); ?></span>
									<?php endif; ?>
								</div>
								<span class="fp-card__price-tax"><?php echo esc_html__('IVA INC', 'farmacia-queiles'); ?></span>
							</div>

							<a class="fp-card__cta add_to_cart_button ajax_add_to_cart"
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

		<div class="home-featured-products__footer">
			<a class="home-featured-products__all-link" href="<?php echo esc_url($shop_url); ?>">
				<span class="home-featured-products__all-link-text--full"><?php echo esc_html__('Ver todos los productos destacados', 'farmacia-queiles'); ?></span>
				<span class="home-featured-products__all-link-text--short"><?php echo esc_html__('Ver más', 'farmacia-queiles'); ?></span>
				<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
			</a>
		</div>
	</div>
</section>
