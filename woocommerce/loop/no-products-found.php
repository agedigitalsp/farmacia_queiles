<?php
/**
 * Displayed when no products are found matching the current query
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

?>
<section class="fq-no-products">
	<div class="fq-no-products__icon" aria-hidden="true">
		<span class="material-symbols-outlined">search_off</span>
	</div>
	<p class="fq-no-products__title"><?php echo esc_html__( 'No hemos encontrado productos', 'farmacia-queiles' ); ?></p>
	<p class="fq-no-products__text"><?php echo esc_html__( 'Prueba a cambiar los filtros o vuelve al inicio.', 'farmacia-queiles' ); ?></p>
	<div class="fq-no-products__actions">
		<a class="fq-no-products__button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo esc_html__( 'Ir al inicio', 'farmacia-queiles' ); ?>
		</a>
	</div>
</section>
