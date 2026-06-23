<?php
/**
 * The Template for displaying products in a product category. Simply includes the archive template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/taxonomy-product-cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( shortcode_exists( 'filter_sp' ) ) {
	$promo_image_url = '';
	$queried_term = get_queried_object();

	if ( $queried_term instanceof WP_Term && 'product_cat' === $queried_term->taxonomy ) {
		$promo_image_url = (string) get_term_meta( (int) $queried_term->term_id, '_fq_product_cat_promo_image', true );
	}

	get_header( 'shop' );

	do_action( 'woocommerce_before_main_content' );

	wc_get_template( 'loop/header.php' );

	if ( '' !== $promo_image_url ) :
		?>
		<div class="fq-product-cat-promo-source" data-fq-product-cat-promo hidden>
			<aside class="fq-product-cat-promo" aria-label="<?php echo esc_attr__( 'Promoción de categoría', 'farmacia-queiles' ); ?>">
				<img class="fq-product-cat-promo__image" src="<?php echo esc_url( $promo_image_url ); ?>" alt="<?php echo esc_attr( $queried_term instanceof WP_Term ? $queried_term->name : '' ); ?>" loading="lazy">
			</aside>
		</div>
		<?php
	endif;

	echo do_shortcode( '[filter_sp]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	do_action( 'woocommerce_after_main_content' );

	get_footer( 'shop' );

	return;
}

wc_get_template( 'archive-product.php' );
