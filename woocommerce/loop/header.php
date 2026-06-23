<?php
/**
 * Product taxonomy archive header
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/header.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php
$queried_object = get_queried_object();
$product_cat_term = null;

if ( is_tax( 'product_cat' ) && $queried_object instanceof WP_Term && 'product_cat' === $queried_object->taxonomy ) {
	$product_cat_term = $queried_object;
}
?>

<?php if ( $product_cat_term ) : ?>
	<?php
	$breadcrumb_items = class_exists( 'Farmacia_Queiles_Theme' ) ? Farmacia_Queiles_Theme::get_product_cat_breadcrumb_items( (int) $product_cat_term->term_id ) : [];
	$term_description = (string) term_description( $product_cat_term, 'product_cat' );
	$header_image_url = (string) get_term_meta( (int) $product_cat_term->term_id, '_fq_product_cat_header_image', true );
	$header_style = '';

	if ( '' === $header_image_url ) {
		$header_image_url = get_template_directory_uri() . '/assets/img/category-default.webp';
	}

	if ( is_string( $header_image_url ) && '' !== $header_image_url ) {
		$header_style = "background-image:linear-gradient(rgba(255,255,255,0.72),rgba(255,255,255,0.72)),url('" . esc_url( $header_image_url ) . "');";
	}

	$subcategories = get_terms(
		[
			'taxonomy' => 'product_cat',
			'parent' => (int) $product_cat_term->term_id,
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		]
	);
	$subcategories = is_wp_error( $subcategories ) ? [] : $subcategories;
	$enable_mobile_subcats_carousel = count( $subcategories ) > 4;
	?>

	<div class="fq-product-cat-header__top">
		<div class="container container--wide">
			<?php if ( ! empty( $breadcrumb_items ) ) : ?>
				<nav class="fq-product-cat-breadcrumb" aria-label="<?php echo esc_attr__( 'Migas de pan', 'farmacia-queiles' ); ?>">
					<ol class="fq-product-cat-breadcrumb__list">
						<?php foreach ( $breadcrumb_items as $index => $crumb ) : ?>
							<?php
							$is_last = $index === count( $breadcrumb_items ) - 1;
							$crumb_name = isset( $crumb['name'] ) ? (string) $crumb['name'] : '';
							$crumb_url = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
							?>
							<li class="fq-product-cat-breadcrumb__item<?php echo $is_last ? ' is-current' : ''; ?>"<?php echo $is_last ? ' aria-current="page"' : ''; ?>>
								<?php if ( ! $is_last && '' !== $crumb_url ) : ?>
									<a href="<?php echo esc_url( $crumb_url ); ?>"><?php echo esc_html( $crumb_name ); ?></a>
								<?php else : ?>
									<span><?php echo esc_html( $crumb_name ); ?></span>
								<?php endif; ?>
							</li>
							<?php if ( ! $is_last ) : ?>
								<li class="fq-product-cat-breadcrumb__sep" aria-hidden="true">
									<span class="material-symbols-outlined">chevron_right</span>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
		</div>
	</div>

	<header class="woocommerce-products-header fq-product-cat-header"<?php echo '' !== $header_style ? ' style="' . esc_attr( $header_style ) . '"' : ''; ?>>
		<div class="container container--wide fq-product-cat-header__inner">
			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
				<h1 class="fq-product-cat-header__title"><?php echo esc_html( $product_cat_term->name ); ?></h1>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $term_description ) ) ) : ?>
				<div class="fq-product-cat-header__description-wrap" data-fq-desc>
					<div class="fq-product-cat-header__description is-collapsed" data-fq-desc-content><?php echo wp_kses_post( $term_description ); ?></div>
					<button class="fq-product-cat-header__toggle" type="button" data-fq-desc-toggle hidden aria-expanded="false">
						<?php echo esc_html__( 'Ver más', 'farmacia-queiles' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( ! empty( $subcategories ) ) : ?>
		<section class="fq-product-cat-subcats" aria-label="<?php echo esc_attr__( 'Subcategorías', 'farmacia-queiles' ); ?>">
			<div class="container container--wide">
				<div class="fq-product-cat-subcats__viewport<?php echo $enable_mobile_subcats_carousel ? ' has-mobile-carousel' : ''; ?>"<?php echo $enable_mobile_subcats_carousel ? ' data-fq-subcats' : ''; ?>>
					<div class="fq-product-cat-subcats__grid<?php echo $enable_mobile_subcats_carousel ? ' has-mobile-carousel' : ''; ?>"<?php echo $enable_mobile_subcats_carousel ? ' data-fq-subcats-track' : ''; ?>>
					<?php foreach ( $subcategories as $subcategory ) : ?>
						<?php
						$subcategory_url = get_term_link( $subcategory );
						if ( is_wp_error( $subcategory_url ) ) {
							continue;
						}

						$subcategory_thumbnail_id = (int) get_term_meta( (int) $subcategory->term_id, 'thumbnail_id', true );
						$subcategory_thumbnail_url = $subcategory_thumbnail_id > 0 ? wp_get_attachment_image_url( $subcategory_thumbnail_id, 'medium' ) : '';
						?>
						<a class="fq-product-cat-subcats__item" href="<?php echo esc_url( $subcategory_url ); ?>">
							<span class="fq-product-cat-subcats__media" aria-hidden="true">
								<span class="fq-product-cat-subcats__media-inner">
									<?php if ( is_string( $subcategory_thumbnail_url ) && '' !== $subcategory_thumbnail_url ) : ?>
										<span class="fq-product-cat-subcats__img" style="background-image:url('<?php echo esc_url( $subcategory_thumbnail_url ); ?>');"></span>
									<?php else : ?>
										<span class="fq-product-cat-subcats__img-placeholder"></span>
									<?php endif; ?>
								</span>
							</span>
							<span class="fq-product-cat-subcats__label"><?php echo esc_html( $subcategory->name ); ?></span>
						</a>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>
<?php else : ?>
	<header class="woocommerce-products-header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php do_action( 'woocommerce_archive_description' ); ?>
	</header>
<?php endif; ?>
