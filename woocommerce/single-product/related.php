<?php
/**
 * Related Products — Farmacia Queiles
 * Scroll horizontal con tarjetas fp-card.
 *
 * @package Farmacia_Queiles
 * @version 10.3.0 (WooCommerce compatibility)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $related_products ) ) {
	return;
}

/* Asegurar lazy loading correcto */
if ( function_exists( 'wp_increase_content_media_count' ) ) {
	$content_media_count = wp_increase_content_media_count( 0 );
	if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
		wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
	}
}
?>

<section class="fq-sp-related" aria-label="<?php echo esc_attr__( 'Productos relacionados', 'farmacia-queiles' ); ?>">

	<div class="fq-sp-related__head">
		<div class="fq-sp-related__heading">
			<span class="fq-sp-related__kicker"><?php echo esc_html__( 'También te puede interesar', 'farmacia-queiles' ); ?></span>
			<h2 class="fq-sp-related__title">
				<?php echo esc_html( apply_filters( 'woocommerce_product_related_products_heading', __( 'Productos relacionados', 'farmacia-queiles' ) ) ); ?>
			</h2>
		</div>
		<div class="fq-sp-related__arrows" aria-hidden="true">
			<button class="fq-sp-related__arrow fq-sp-related__arrow--prev" type="button" aria-label="<?php echo esc_attr__( 'Anterior', 'farmacia-queiles' ); ?>">
				<span class="material-symbols-outlined">chevron_left</span>
			</button>
			<button class="fq-sp-related__arrow fq-sp-related__arrow--next" type="button" aria-label="<?php echo esc_attr__( 'Siguiente', 'farmacia-queiles' ); ?>">
				<span class="material-symbols-outlined">chevron_right</span>
			</button>
		</div>
	</div>

	<div class="fq-sp-related__viewport" data-fq-related-track>
		<?php foreach ( $related_products as $related_product ) :
			$post_object = get_post( $related_product->get_id() );
			setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			/** @var WC_Product $related_product */
			$rp_id           = (int) $related_product->get_id();
			$rp_name         = (string) $related_product->get_name();
			$rp_url          = (string) get_permalink( $rp_id );
			$rp_image_id     = (int) $related_product->get_image_id();
			$rp_image_url    = $rp_image_id > 0
				? (string) wp_get_attachment_image_url( $rp_image_id, 'woocommerce_thumbnail' )
				: wc_placeholder_img_src( 'woocommerce_thumbnail' );
			$rp_image_alt    = $rp_image_id > 0
				? (string) get_post_meta( $rp_image_id, '_wp_attachment_image_alt', true )
				: $rp_name;
			$rp_regular      = (string) $related_product->get_regular_price();
			$rp_sale         = (string) $related_product->get_sale_price();
			$rp_on_sale      = (bool) $related_product->is_on_sale();
			$rp_in_stock     = 'instock' === (string) $related_product->get_stock_status();

			/* Marca */
			$rp_brand = '';
			if ( taxonomy_exists( 'product_brand' ) ) {
				$rp_brand_terms = get_the_terms( $rp_id, 'product_brand' );
				if ( is_array( $rp_brand_terms ) && ! empty( $rp_brand_terms ) ) {
					$rp_brand = (string) $rp_brand_terms[0]->name;
				}
			}
		?>
		<article class="fp-card fq-sp-related__item">
			<div class="fp-card__image-wrap">
				<?php if ( $rp_on_sale ) : ?>
					<span class="fp-card__badge"><?php echo esc_html__( 'Oferta', 'farmacia-queiles' ); ?></span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $rp_url ); ?>" aria-label="<?php echo esc_attr( $rp_name ); ?>">
					<img class="fp-card__image" src="<?php echo esc_url( $rp_image_url ); ?>" alt="<?php echo esc_attr( $rp_image_alt ); ?>" loading="lazy">
				</a>
			</div>
			<div class="fp-card__body">
				<?php if ( '' !== $rp_brand ) : ?>
					<div class="fp-card__brand-wrap">
						<span class="fp-card__brand"><?php echo esc_html( $rp_brand ); ?></span>
					</div>
				<?php endif; ?>
				<h3 class="fp-card__name">
					<a href="<?php echo esc_url( $rp_url ); ?>"><?php echo esc_html( $rp_name ); ?></a>
				</h3>
				<div class="fp-card__price-wrap">
					<div class="fp-card__price-row">
						<?php if ( $rp_on_sale && '' !== $rp_sale ) : ?>
							<span class="fp-card__price-current"><?php echo wp_kses_post( wc_price( (float) $rp_sale ) ); ?></span>
							<s class="fp-card__price-old"><?php echo wp_kses_post( wc_price( (float) $rp_regular ) ); ?></s>
						<?php elseif ( '' !== $rp_regular ) : ?>
							<span class="fp-card__price-current"><?php echo wp_kses_post( wc_price( (float) $rp_regular ) ); ?></span>
						<?php endif; ?>
					</div>
					<span class="fp-card__price-tax"><?php echo esc_html__( 'IVA INC', 'farmacia-queiles' ); ?></span>
				</div>
				<?php if ( $rp_in_stock ) : ?>
				<a
					class="fp-card__cta add_to_cart_button ajax_add_to_cart"
					href="<?php echo esc_url( $related_product->add_to_cart_url() ); ?>"
					data-product_id="<?php echo esc_attr( (string) $rp_id ); ?>"
					data-product_sku="<?php echo esc_attr( $related_product->get_sku() ); ?>"
					data-quantity="1"
					aria-label="<?php echo esc_attr( $related_product->add_to_cart_description() ); ?>"
					rel="nofollow"
				>
					<?php echo esc_html__( 'Comprar', 'farmacia-queiles' ); ?>
				</a>
				<?php endif; ?>
			</div>
		</article>
		<?php endforeach; ?>
	</div>

</section>

<?php wp_reset_postdata(); ?>
