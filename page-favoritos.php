<?php
/**
 * Template Name: Favoritos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Obtener IDs favoritos: user_meta si logado, cookie si no
$fq_fav_ids = [];
if ( is_user_logged_in() ) {
	$stored = get_user_meta( get_current_user_id(), '_fq_favorites', true );
	$fq_fav_ids = is_array( $stored ) ? array_map( 'intval', $stored ) : [];
} else {
	$cookie_raw = isset( $_COOKIE['fq_favorites'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['fq_favorites'] ) ) : '';
	if ( '' !== $cookie_raw ) {
		$decoded = json_decode( urldecode( $cookie_raw ), true );
		$fq_fav_ids = is_array( $decoded ) ? array_map( 'intval', $decoded ) : [];
	}
}
$fq_fav_ids = array_values( array_filter( $fq_fav_ids, static fn( $id ) => $id > 0 ) );

$fq_header_img = get_template_directory_uri() . '/assets/img/category-default.webp';
$fq_header_style = "background-image:linear-gradient(rgba(255,255,255,0.72),rgba(255,255,255,0.72)),url('" . esc_url( $fq_header_img ) . "');";
?>

<!-- Migas -->
<div class="fq-product-cat-header__top">
	<div class="container container--wide">
		<nav class="fq-product-cat-breadcrumb" aria-label="<?php echo esc_attr__( 'Migas de pan', 'farmacia-queiles' ); ?>">
			<ol class="fq-product-cat-breadcrumb__list">
				<li class="fq-product-cat-breadcrumb__item">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Inicio', 'farmacia-queiles' ); ?></a>
				</li>
				<li class="fq-product-cat-breadcrumb__sep" aria-hidden="true">
					<span class="material-symbols-outlined">chevron_right</span>
				</li>
				<li class="fq-product-cat-breadcrumb__item is-current" aria-current="page">
					<span><?php echo esc_html__( 'Mis favoritos', 'farmacia-queiles' ); ?></span>
				</li>
			</ol>
		</nav>
	</div>
</div>

<!-- Header -->
<header class="woocommerce-products-header fq-product-cat-header" style="<?php echo esc_attr( $fq_header_style ); ?>">
	<div class="container container--wide fq-product-cat-header__inner">
		<h1 class="fq-product-cat-header__title"><?php echo esc_html__( 'Mis favoritos', 'farmacia-queiles' ); ?></h1>
		<?php if ( ! empty( $fq_fav_ids ) ) : ?>
			<p class="fq-product-cat-header__subtitle">
				<?php echo esc_html( sprintf(
					_n( '%d producto guardado', '%d productos guardados', count( $fq_fav_ids ), 'farmacia-queiles' ),
					count( $fq_fav_ids )
				) ); ?>
			</p>
		<?php endif; ?>
	</div>
</header>

<div class="content">
	<div class="container">
		<main id="primary" class="site-main fq-favorites-page">
			<?php if ( empty( $fq_fav_ids ) ) : ?>
				<section class="fq-favorites-empty">
					<span class="material-symbols-outlined" aria-hidden="true">favorite</span>
					<p class="fq-favorites-empty__title"><?php echo esc_html__( 'Aún no tienes favoritos', 'farmacia-queiles' ); ?></p>
					<p class="fq-favorites-empty__text"><?php echo esc_html__( 'Guarda productos pulsando el corazón y aparecerán aquí.', 'farmacia-queiles' ); ?></p>
					<a class="fq-favorites-empty__button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
						<?php echo esc_html__( 'Ir a la tienda', 'farmacia-queiles' ); ?>
					</a>
				</section>
			<?php else : ?>
				<!-- Barra de visualización -->
				<div class="fq-favorites-toolbar">
					<style id="fq-fav-grid-style"></style>
					<div id="fq-fav-grid-controls">
						<span><?php echo esc_html__( 'Visualización:', 'farmacia-queiles' ); ?></span>
						<div class="fq-fav-grid-btns">
							<button class="fq-fav-grid-btn" type="button" data-fav-grid="2" aria-label="<?php echo esc_attr__( '2 columnas', 'farmacia-queiles' ); ?>">
								<svg viewBox="0 0 100 100" width="28" height="28"><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m45 10v80h-37v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m92 10v80h-37v-80z"/></svg>
							</button>
							<button class="fq-fav-grid-btn" type="button" data-fav-grid="3" aria-label="<?php echo esc_attr__( '3 columnas', 'farmacia-queiles' ); ?>">
								<svg viewBox="0 0 100 100" width="28" height="28"><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m62 10v80h-23v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m92 10v80h-23v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m32 10v80h-23v-80z"/></svg>
							</button>
							<button class="fq-fav-grid-btn" type="button" data-fav-grid="4" aria-label="<?php echo esc_attr__( '4 columnas', 'farmacia-queiles' ); ?>">
								<svg viewBox="0 0 100 100" width="28" height="28"><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m94 10v80h-18v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m71 10v80h-18v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m48 10v80h-18v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m25 10v80h-18v-80z"/></svg>
							</button>
							<button class="fq-fav-grid-btn active" type="button" data-fav-grid="5" aria-label="<?php echo esc_attr__( '5 columnas', 'farmacia-queiles' ); ?>">
								<svg viewBox="0 0 100 100" width="28" height="28"><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m96 10v80h-13v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m79 10v80h-13v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m62 10v80h-13v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m45 10v80h-13v-80z"/><path fill-rule="evenodd" fill="none" stroke="currentColor" stroke-width="6" d="m28 10v80h-13v-80z"/></svg>
							</button>
						</div>
					</div>
				</div>

				<?php
				$fq_query = new WP_Query( [
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post__in'       => $fq_fav_ids,
					'orderby'        => 'post__in',
				] );

				if ( $fq_query->have_posts() ) {
					wc_set_loop_prop( 'columns', 5 );
					woocommerce_product_loop_start();
					while ( $fq_query->have_posts() ) {
						$fq_query->the_post();
						wc_get_template_part( 'content', 'product' );
					}
					woocommerce_product_loop_end();
					wp_reset_postdata();
				}
				?>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php get_footer(); ?>
