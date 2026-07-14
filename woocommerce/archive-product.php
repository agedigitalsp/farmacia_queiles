<?php
/**
 * The Template for displaying product archives, including the main shop page.
 *
 * @package Farmacia_Queiles
 * @version 8.6.0 (WooCommerce compatibility)
 */

defined( 'ABSPATH' ) || exit;

/* ── Tienda: mismo flujo que taxonomy-product-cat.php ──────────── */
if ( is_shop() && shortcode_exists( 'filter_sp' ) ) {
	get_header( 'shop' );

	do_action( 'woocommerce_before_main_content' );

	wc_get_template( 'loop/header.php' );

	echo do_shortcode( '[filter_sp]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	do_action( 'woocommerce_after_main_content' );

	get_footer( 'shop' );

	return;
}

/* ── Fallback: comportamiento estándar WooCommerce ──────────────── */
get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );
do_action( 'woocommerce_shop_loop_header' );

if ( woocommerce_product_loop() ) {
	do_action( 'woocommerce_before_shop_loop' );
	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();
	do_action( 'woocommerce_after_shop_loop' );
} else {
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );