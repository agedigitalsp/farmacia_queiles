<?php
/**
 * Ficha de producto — Farmacia Queiles
 * Basada en diseño/ficha-producto.html
 *
 * @package Farmacia_Queiles
 * @version 3.6.0 (WooCommerce compatibility)
 */

defined( 'ABSPATH' ) || exit;

global $product;

/** @var WC_Product $product */

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

/* ── Datos del producto ─────────────────────────────────────────── */
$product_id      = (int) $product->get_id();
$product_name    = (string) $product->get_name();
$product_url     = (string) get_permalink( $product_id );
$short_desc      = (string) $product->get_short_description();
$regular_price   = (string) $product->get_regular_price();
$sale_price      = (string) $product->get_sale_price();
$is_on_sale      = (bool) $product->is_on_sale();
$stock_status    = (string) $product->get_stock_status();
$stock_qty       = $product->get_stock_quantity();
$is_purchasable  = (bool) $product->is_purchasable();
$in_stock        = 'instock' === $stock_status;

/* Galería de imágenes */
$main_image_id       = (int) $product->get_image_id();
$gallery_image_ids   = (array) $product->get_gallery_image_ids();
$all_image_ids       = $main_image_id > 0
	? array_merge( [ $main_image_id ], $gallery_image_ids )
	: $gallery_image_ids;

/* Marca / laboratorio */
$brand = '';
if ( taxonomy_exists( 'product_brand' ) ) {
	$brand_terms = get_the_terms( $product_id, 'product_brand' );
	if ( is_array( $brand_terms ) && ! empty( $brand_terms ) ) {
		$brand = (string) $brand_terms[0]->name;
	}
}

/* Descuento */
$discount_pct = '';
if ( $is_on_sale && '' !== $regular_price && '' !== $sale_price ) {
	$reg = (float) $regular_price;
	$sal = (float) $sale_price;
	if ( $reg > 0 ) {
		$discount_pct = '-' . round( ( ( $reg - $sal ) / $reg ) * 100 ) . '%';
	}
}

/* Clases add-to-cart */
$add_to_cart_classes = implode( ' ', array_filter( [
	'fq-sp-btn fq-sp-btn--cart',
	'add_to_cart_button',
	'ajax_add_to_cart',
	$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
] ) );
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'fq-single-product', $product ); ?>>

	<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

	<!-- ══ MIGAS DE PAN ══════════════════════════════════════════ -->
	<?php
	$fq_sp_crumbs = [
		[ 'name' => __( 'Inicio', 'farmacia-queiles' ), 'url' => home_url( '/' ) ],
	];
	$fq_sp_cats = get_the_terms( $product_id, 'product_cat' );
	if ( is_array( $fq_sp_cats ) && ! empty( $fq_sp_cats ) ) {
		// Categoría principal: la de mayor profundidad
		usort( $fq_sp_cats, static fn( $a, $b ) => $b->parent <=> $a->parent );
		$fq_sp_cat = $fq_sp_cats[0];
		// Ancestros de la categoría, de más general a más específico
		$fq_sp_ancestors = array_reverse( get_ancestors( (int) $fq_sp_cat->term_id, 'product_cat' ) );
		foreach ( $fq_sp_ancestors as $ancestor_id ) {
			$ancestor_term = get_term( $ancestor_id, 'product_cat' );
			if ( $ancestor_term instanceof WP_Term ) {
				$fq_sp_crumbs[] = [
					'name' => $ancestor_term->name,
					'url'  => (string) get_term_link( $ancestor_term ),
				];
			}
		}
		$fq_sp_crumbs[] = [
			'name' => $fq_sp_cat->name,
			'url'  => (string) get_term_link( $fq_sp_cat ),
		];
	}
	$fq_sp_crumbs[] = [ 'name' => $product_name, 'url' => '' ];
	?>
	<nav class="fq-product-cat-breadcrumb fq-sp-breadcrumb" aria-label="<?php echo esc_attr__( 'Migas de pan', 'farmacia-queiles' ); ?>">
		<ol class="fq-product-cat-breadcrumb__list">
			<?php foreach ( $fq_sp_crumbs as $fq_i => $fq_crumb ) :
				$fq_is_last = $fq_i === count( $fq_sp_crumbs ) - 1;
			?>
			<li class="fq-product-cat-breadcrumb__item<?php echo $fq_is_last ? ' is-current' : ''; ?>"<?php echo $fq_is_last ? ' aria-current="page"' : ''; ?>>
				<?php if ( ! $fq_is_last && '' !== $fq_crumb['url'] ) : ?>
					<a href="<?php echo esc_url( $fq_crumb['url'] ); ?>"><?php echo esc_html( $fq_crumb['name'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $fq_crumb['name'] ); ?></span>
				<?php endif; ?>
			</li>
			<?php if ( ! $fq_is_last ) : ?>
			<li class="fq-product-cat-breadcrumb__sep" aria-hidden="true">
				<span class="material-symbols-outlined">chevron_right</span>
			</li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ol>
	</nav>

	<!-- ══ HERO: Galería + Info ══════════════════════════════════ -->
	<div class="fq-sp-hero">

		<!-- Galería -->
		<div class="fq-sp-gallery">
			<div class="fq-sp-gallery__main" id="fq-sp-main-wrap">

				<?php
				$main_src = $main_image_id > 0
					? (string) wp_get_attachment_image_url( $main_image_id, 'woocommerce_single' )
					: wc_placeholder_img_src( 'woocommerce_single' );
				$main_alt = $main_image_id > 0
					? (string) get_post_meta( $main_image_id, '_wp_attachment_image_alt', true )
					: $product_name;
				?>
				<img
					id="fq-sp-main-img"
					class="fq-sp-gallery__img"
					src="<?php echo esc_url( $main_src ); ?>"
					alt="<?php echo esc_attr( $main_alt ); ?>"
					loading="eager"
				>
			</div>

			<?php if ( count( $all_image_ids ) > 1 ) : ?>
			<div class="fq-sp-gallery__thumbs" role="list">
				<?php foreach ( $all_image_ids as $img_id ) :
					$thumb_src  = (string) wp_get_attachment_image_url( $img_id, 'thumbnail' );
					$full_src   = (string) wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
					$thumb_alt  = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
					$is_active  = ( $img_id === $main_image_id ) ? ' is-active' : '';
				?>
				<button
					class="fq-sp-gallery__thumb<?php echo esc_attr( $is_active ); ?>"
					data-full="<?php echo esc_url( $full_src ); ?>"
					aria-label="<?php echo esc_attr( $thumb_alt ?: $product_name ); ?>"
					role="listitem"
					type="button"
				>
					<img src="<?php echo esc_url( $thumb_src ); ?>" alt="<?php echo esc_attr( $thumb_alt ?: $product_name ); ?>" loading="lazy">
				</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div><!-- /fq-sp-gallery -->

		<!-- Info + Compra -->
		<div class="fq-sp-info">

			<?php if ( '' !== $brand ) : ?>
				<span class="fq-sp-info__brand"><?php echo esc_html( $brand ); ?></span>
			<?php endif; ?>

			<h1 class="fq-sp-info__title"><?php echo esc_html( $product_name ); ?></h1>

			<?php if ( '' !== $short_desc ) : ?>
				<div class="fq-sp-info__excerpt"><?php echo wp_kses_post( $short_desc ); ?></div>
			<?php endif; ?>

			<!-- Precio -->
			<div class="fq-sp-buybox">
				<div class="fq-sp-price">
					<?php if ( $is_on_sale && '' !== $sale_price ) : ?>
						<div class="fq-sp-price__old-row">
							<s class="fq-sp-price__old"><?php echo wp_kses_post( wc_price( (float) $regular_price ) ); ?></s>
							<?php if ( '' !== $discount_pct ) : ?>
								<span class="fq-sp-price__badge"><?php echo esc_html( $discount_pct ); ?></span>
							<?php endif; ?>
						</div>
						<span class="fq-sp-price__current"><?php echo wp_kses_post( wc_price( (float) $sale_price ) ); ?></span>
					<?php elseif ( '' !== $regular_price ) : ?>
						<span class="fq-sp-price__current"><?php echo wp_kses_post( wc_price( (float) $regular_price ) ); ?></span>
					<?php else : ?>
						<span class="fq-sp-price__current"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					<?php endif; ?>
					<span class="fq-sp-price__tax"><?php echo esc_html__( 'IVA incluido', 'farmacia-queiles' ); ?></span>
				</div>

				<!-- Cantidad -->
				<?php if ( $is_purchasable && $in_stock ) : ?>
				<div class="fq-sp-qty">
					<label class="fq-sp-qty__label" for="fq-sp-qty-<?php echo esc_attr( (string) $product_id ); ?>">
						<?php echo esc_html__( 'Cantidad', 'farmacia-queiles' ); ?>
					</label>
					<div class="fq-sp-qty__wrap">
						<?php
						woocommerce_quantity_input( [
							'input_id'   => 'fq-sp-qty-' . $product_id,
							'input_name' => 'quantity',
							'input_value'=> 1,
							'min_value'  => 1,
							'max_value'  => $stock_qty ?: '',
							'classes'    => [ 'fq-sp-qty__input' ],
						] );
						?>
					</div>
					<?php if ( $stock_qty !== null ) : ?>
						<span class="fq-sp-qty__stock is-in-stock">
							<span class="fq-sp-qty__stock-dot"></span>
							<?php
							/* translators: %d: stock quantity */
							printf( esc_html__( 'Stock: %d uds.', 'farmacia-queiles' ), (int) $stock_qty );
							?>
						</span>
					<?php else : ?>
						<span class="fq-sp-qty__stock is-in-stock">
							<span class="fq-sp-qty__stock-dot"></span>
							<?php echo esc_html__( 'En stock', 'farmacia-queiles' ); ?>
						</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- Botones CTA -->
				<div class="fq-sp-cta">
					<?php if ( $is_purchasable && $in_stock ) : ?>
					<a
						href="<?php echo esc_url( add_query_arg( [ 'add-to-cart' => $product_id, 'quantity' => 1 ], wc_get_checkout_url() ) ); ?>"
						class="fq-sp-btn fq-sp-btn--buynow"
						aria-label="<?php echo esc_attr__( 'Comprar ahora', 'farmacia-queiles' ); ?>"
					>
						<?php echo esc_html__( 'Comprar ahora', 'farmacia-queiles' ); ?>
					</a>
					<a
						href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
						class="<?php echo esc_attr( $add_to_cart_classes ); ?> fq-sp-btn--primary"
						data-product_id="<?php echo esc_attr( (string) $product_id ); ?>"
						data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
						data-quantity="1"
						aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
						rel="nofollow"
					>
						<span class="material-symbols-outlined">shopping_bag</span>
						<?php echo esc_html__( 'Añadir al carrito', 'farmacia-queiles' ); ?>
					</a>
					<?php else : ?>
					<span class="fq-sp-btn fq-sp-btn--disabled">
						<?php echo esc_html__( 'Sin stock', 'farmacia-queiles' ); ?>
					</span>
					<?php endif; ?>
				</div>
			</div><!-- /fq-sp-buybox -->

			<!-- Envío gratis -->
			<div class="fq-sp-shipping">
				<span class="material-symbols-outlined">local_shipping</span>
				<span><?php echo esc_html__( 'Envío gratis para pedidos superiores a 50€', 'farmacia-queiles' ); ?></span>
			</div>

			<!-- Trust badges -->
			<div class="fq-sp-trust">
				<div class="fq-sp-trust__item">
					<span class="material-symbols-outlined">lock</span>
					<span><?php echo esc_html__( 'Pago seguro', 'farmacia-queiles' ); ?></span>
				</div>
				<div class="fq-sp-trust__item">
					<span class="material-symbols-outlined">store</span>
					<span><?php echo esc_html__( 'Farmacia física', 'farmacia-queiles' ); ?></span>
				</div>
				<div class="fq-sp-trust__item">
					<span class="material-symbols-outlined">assignment_return</span>
					<span><?php echo esc_html__( 'Devolución fácil', 'farmacia-queiles' ); ?></span>
				</div>
				<div class="fq-sp-trust__item">
					<span class="material-symbols-outlined">medical_services</span>
					<span><?php echo esc_html__( 'Atención sanitaria', 'farmacia-queiles' ); ?></span>
				</div>
			</div>

			<!-- Logos de pago -->
			<div class="fq-sp-payments">
				<?php
				$payment_logos = [
					'visa'       => 'visa.svg',
					'mastercard' => 'mastercard.svg',
					'bizum'      => 'bizum.svg',
					'Apple Pay'  => 'apple-pay.svg',
					'Google Pay' => 'google-pay.svg',
				];
				foreach ( $payment_logos as $alt => $file ) :
					$file_path = get_template_directory() . '/assets/img/payments/' . $file;
					if ( file_exists( $file_path ) ) :
				?>
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/payments/' . $file ); ?>"
						alt="<?php echo esc_attr( $alt ); ?>"
						class="fq-sp-payments__logo"
						loading="lazy"
					>
				<?php
					endif;
				endforeach;
				?>
			</div>

			<!-- Meta WooCommerce (SKU, categorías, etiquetas) -->
			<?php //do_action( 'woocommerce_product_meta_start' ); ?>
			<?php //wc_get_template( 'single-product/meta.php' ); ?>
			<?php //do_action( 'woocommerce_product_meta_end' ); ?>

			<!-- Schema WooCommerce (JSON-LD de producto) -->

		</div><!-- /fq-sp-info -->
	</div><!-- /fq-sp-hero -->

	<!-- ══ TABS + CONSEJO ════════════════════════════════════════ -->
	<!-- ══ TABS + CONSEJO (grid 2 col) ══════════════════════════════ -->
	<div class="fq-sp-lower">
		<div class="fq-sp-lower__tabs">
			<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
		</div>

		<!-- Consejo de la farmacéutica — sticky junto a los tabs -->
		<aside class="fq-sp-advice" aria-label="<?php echo esc_attr__( 'Consejo de la farmacéutica', 'farmacia-queiles' ); ?>">
			<div class="fq-sp-advice__avatar">
				<span class="material-symbols-outlined">person_4</span>
			</div>
			<div class="fq-sp-advice__meta">
				<h4 class="fq-sp-advice__name"><?php echo esc_html__( 'El consejo de María', 'farmacia-queiles' ); ?></h4>
				<p class="fq-sp-advice__role"><?php echo esc_html__( 'Tu farmacéutica', 'farmacia-queiles' ); ?></p>
			</div>
			<p class="fq-sp-advice__text">
				<?php echo esc_html__( 'Consulta con nosotros si tienes dudas sobre este producto. Estamos aquí para orientarte de forma personalizada según tu tipo de piel y necesidades.', 'farmacia-queiles' ); ?>
			</p>
			<?php
			$consulting_url = (string) get_option( 'farmacia_queiles_home_consulting_whatsapp_url', '' );
			if ( '' === $consulting_url ) {
				$consulting_url = (string) get_option( 'farmacia_queiles_footer_whatsapp_url', '#' );
			}
			?>
			<a class="fq-sp-advice__cta" href="<?php echo esc_url( $consulting_url ); ?>" target="_blank" rel="noopener noreferrer nofollow">
				<?php echo esc_html__( 'Consultar dudas personalizadas', 'farmacia-queiles' ); ?>
				<span class="material-symbols-outlined">arrow_forward</span>
			</a>
		</aside>
	</div><!-- /fq-sp-lower -->

</div><!-- #product-xxx -->

<!-- ══ SECCIONES FULL-WIDTH (rutina + relacionados) ══════════════ -->
<div class="fq-sp-fullwidth">
	<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>
