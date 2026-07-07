<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$product_id = $product->get_id();
$product_url = get_permalink( $product_id );
$product_name = $product->get_name();
$image_id = (int) $product->get_image_id();
$image_url = $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : '';
$image_url = is_string( $image_url ) && '' !== $image_url ? $image_url : wc_placeholder_img_src( 'woocommerce_single' );
$description = wp_strip_all_tags( $product->get_short_description() );
$brand = '';

if ( taxonomy_exists( 'product_brand' ) ) {
	$brand_terms = get_the_terms( $product_id, 'product_brand' );
	if ( is_array( $brand_terms ) && ! empty( $brand_terms ) ) {
		$brand_names = array_map(
			static fn( $term ) => wp_strip_all_tags( $term->name ),
			$brand_terms
		);
		$brand = implode( ', ', $brand_names );
	}
}

$is_on_sale = $product->is_on_sale();
$regular_price = (string) $product->get_regular_price();
$sale_price = (string) $product->get_sale_price();
$add_to_cart_classes = implode(
	' ',
	array_filter(
		[
			'fp-card__cta',
			'button',
			$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			'product_type_' . $product->get_type(),
			$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
		]
	)
);
?>
<li <?php wc_product_class( '', $product ); ?>>
	<article class="fp-card">
		<div class="fp-card__image-wrap">
			<?php
			$badge_cat = '';
			$badge_slug = '';
			if ( $is_on_sale ) {
				$terms = get_the_terms( $product_id, 'product_cat' );
				if ( is_array( $terms ) && ! empty( $terms ) ) {
					$badge_cat = esc_html( $terms[0]->name );
					$badge_slug = esc_attr( $terms[0]->slug );
				}
			}
			if ( '' !== $badge_cat ) : ?>
				<span class="fp-card__badge" data-cat-slug="<?php echo $badge_slug; ?>"><?php echo $badge_cat; ?></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( $product_url ); ?>" aria-label="<?php echo esc_attr( $product_name ); ?>">
				<img class="fp-card__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" loading="lazy">
			</a>
			<button class="fq-fav-btn" type="button" data-fq-fav="<?php echo esc_attr( (string) $product_id ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr__( 'Guardar en favoritos', 'farmacia-queiles' ); ?>">
				<span class="material-symbols-outlined" aria-hidden="true">favorite</span>
			</button>
		</div>

		<div class="fp-card__body">
			<div class="fp-card__brand-wrap">
				<span class="fp-card__brand"><?php echo esc_html( $brand ?? '' ); ?></span>
			</div>

			<h2 class="fp-card__name">
				<a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a>
			</h2>

			<p class="fp-card__desc"><?php echo esc_html( $description ?? '' ); ?></p>

			<div class="fp-card__price-wrap">
				<div class="fp-card__price-row">
					<?php if ( $is_on_sale && '' !== $sale_price ) : ?>
						<span class="fp-card__price-current"><?php echo wp_kses_post( wc_price( (float) $sale_price ) ); ?></span>
						<s class="fp-card__price-old"><?php echo wp_kses_post( wc_price( (float) $regular_price ) ); ?></s>
					<?php elseif ( '' !== $regular_price ) : ?>
						<span class="fp-card__price-current"><?php echo wp_kses_post( wc_price( (float) $regular_price ) ); ?></span>
					<?php endif; ?>
				</div>
				<span class="fp-card__price-tax"><?php echo esc_html__( 'IVA INC', 'farmacia-queiles' ); ?></span>
			</div>

			<a class="<?php echo esc_attr( $add_to_cart_classes ); ?>"
				href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
				data-product_id="<?php echo esc_attr( (string) $product_id ); ?>"
				data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
				data-quantity="1"
				aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
				rel="nofollow">
				<?php echo esc_html__( 'Comprar', 'farmacia-queiles' ); ?>
			</a>
		</div>
	</article>
</li>
