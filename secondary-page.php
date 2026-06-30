<?php
/**
 * Template Name: Páginas Secundarias
 * Post Type: page
 */

get_header();

$page_id    = (int) get_the_ID();
$page_title = (string) get_the_title();

// Imagen de cabecera: campo propio (URL) → imagen destacada → default
$header_image_url = (string) get_post_meta( $page_id, '_fq_page_header_image', true );
if ( '' === $header_image_url ) {
	$thumb_id = (int) get_post_thumbnail_id( $page_id );
	if ( $thumb_id > 0 ) {
		$header_image_url = (string) wp_get_attachment_image_url( $thumb_id, 'full' );
	}
}
if ( '' === $header_image_url ) {
	$header_image_url = get_template_directory_uri() . '/assets/img/category-default.webp';
}

$header_style = "background-image:linear-gradient(rgba(255,255,255,0.72),rgba(255,255,255,0.72)),url('" . esc_url( $header_image_url ) . "');";
?>

<div class="fq-secondary-page">

	<!-- ══ MIGAS DE PAN ══════════════════════════════════════════ -->
	<div class="fq-product-cat-header__top">
		<div class="container container--wide">
			<nav class="fq-product-cat-breadcrumb fq-sp-breadcrumb" aria-label="<?php echo esc_attr__( 'Migas de pan', 'farmacia-queiles' ); ?>">
				<ol class="fq-product-cat-breadcrumb__list">
					<li class="fq-product-cat-breadcrumb__item">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Inicio', 'farmacia-queiles' ); ?></a>
					</li>
					<li class="fq-product-cat-breadcrumb__sep" aria-hidden="true">
						<span class="material-symbols-outlined">chevron_right</span>
					</li>
					<li class="fq-product-cat-breadcrumb__item is-current" aria-current="page">
						<span><?php echo esc_html( $page_title ); ?></span>
					</li>
				</ol>
			</nav>
		</div>
	</div>

	<!-- ══ HERO ══════════════════════════════════════════════════ -->
	<header class="fq-secondary-page__hero" style="<?php echo esc_attr( $header_style ); ?>">
		<div class="container container--wide fq-secondary-page__hero-inner">
			<h1 class="fq-secondary-page__title"><?php echo esc_html( $page_title ); ?></h1>
		</div>
	</header>

	<!-- ══ CONTENIDO ════════════════════════════════════════════ -->
	<div class="fq-secondary-page__content container container--wide">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	</div>

</div>

<?php get_footer();
