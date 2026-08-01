<?php

if (!defined('ABSPATH')) {
	exit;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_promotions_cached_payload() : null;

if (is_array($cached_payload)) {
	$hero_slides = is_array($cached_payload['hero_slides'] ?? null) ? $cached_payload['hero_slides'] : [];
	$side_promotions = is_array($cached_payload['side_promotions'] ?? null) ? $cached_payload['side_promotions'] : [null, null];
} elseif (class_exists('sp_promo_hero_cpt')) {
	$payload = sp_promo_hero_cpt::build_home_payload();
	$hero_slides = $payload['hero_slides'];
	$side_promotions = $payload['side_promotions'];
} else {
	$hero_slides = [];
	$side_promotions = [null, null];
}

// Sin ninguna promoción (ni slider ni destacadas): se inyecta un slide por defecto
// con el mismo formato exacto que una promoción real, configurable en Ajustes Home.
if (empty($hero_slides) && empty(array_filter($side_promotions))) {
	$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
	$get_setting = static function (string $key, string $default) {
		return class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_setting($key, $default) : $default;
	};

	// La imagen puede venir de Ajustes Home (CMB2, URL directa) o del Customizer
	// (id de adjunto); se prueban en ese orden antes de caer a la imagen por defecto.
	$empty_image = (string) $get_setting('farmacia_queiles_home_hero_empty_image', '');
	if ('' === $empty_image) {
		$empty_image_id = (int) $get_setting('farmacia_queiles_home_hero_empty_image_id', '0');
		if ($empty_image_id > 0) {
			$empty_image = (string) wp_get_attachment_image_url($empty_image_id, 'full');
		}
	}
	if ('' === $empty_image) {
		$empty_image = get_template_directory_uri() . '/assets/img/category-default.webp';
	}

	$hero_slides = [
		[
			'id' => 0,
			'title' => $get_setting('farmacia_queiles_home_hero_empty_title', __('Descubre nuestros productos', 'farmacia-queiles')),
			'subtitle' => $get_setting('farmacia_queiles_home_hero_empty_subtitle', ''),
			'description' => $get_setting('farmacia_queiles_home_hero_empty_text', __('Explora nuestro catálogo completo de farmacia y parafarmacia.', 'farmacia-queiles')),
			'url' => $shop_url,
			'image' => $empty_image,
			'cta_text' => $get_setting('farmacia_queiles_home_hero_empty_cta_text', __('Ir a la tienda', 'farmacia-queiles')),
		],
	];
}
?>
<?php
// Construir lista unificada para el slider móvil: destacados primero, luego generales
$mobile_slides = [];
foreach ( array_filter( $side_promotions ) as $promo ) {
	$mobile_slides[] = array_merge( $promo, [ 'is_featured' => true ] );
}
foreach ( $hero_slides as $slide ) {
	$mobile_slides[] = array_merge( $slide, [ 'is_featured' => false ] );
}
$mobile_total = count( $mobile_slides );
?>
<section class="home-hero-promotions">

	<!-- ── SLIDER MÓVIL (solo visible en ≤767px) ───────────────── -->
	<?php if ( $mobile_total > 0 ) : ?>
	<div class="home-hero-promotions__mobile-slider" data-mobile-slider>
		<?php foreach ( $mobile_slides as $i => $slide ) : ?>
			<article class="home-hero-promotions__mobile-slide<?php echo 0 === $i ? ' is-active' : ''; ?><?php echo $slide['is_featured'] ? ' is-featured' : ''; ?>" data-mobile-slide>
				<?php if ( ! empty( $slide['image'] ) ) : ?>
					<img class="home-hero-promotions__image" src="<?php echo esc_url( $slide['image'] ); ?>" alt="<?php echo esc_attr( $slide['title'] ); ?>" loading="lazy">
				<?php endif; ?>
				<div class="home-hero-promotions__overlay">
					<?php if ( ! empty( $slide['subtitle'] ) ) : ?>
						<span class="home-hero-promotions__eyebrow"><?php echo esc_html( $slide['subtitle'] ); ?></span>
					<?php endif; ?>
					<div class="home-hero-promotions__content">
						<h2 class="home-hero-promotions__title"><?php echo esc_html( $slide['title'] ); ?></h2>
						<a class="home-hero-promotions__button" href="<?php echo esc_url( $slide['url'] ); ?>">
							<?php echo esc_html( $slide['cta_text'] ?? __( 'Ver promoción', 'farmacia-queiles' ) ); ?>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
		<?php if ( $mobile_total > 1 ) : ?>
		<div class="home-hero-promotions__nav">
			<button class="home-hero-promotions__arrow" type="button" data-mobile-prev aria-label="<?php esc_attr_e( 'Anterior', 'farmacia-queiles' ); ?>">
				<span class="material-symbols-outlined">chevron_left</span>
			</button>
			<div class="home-hero-promotions__dots" data-mobile-dots>
				<?php for ( $d = 0; $d < $mobile_total; $d++ ) : ?>
					<button class="home-hero-promotions__dot<?php echo 0 === $d ? ' is-active' : ''; ?>" type="button" data-mobile-dot="<?php echo $d; ?>" aria-label="Slide <?php echo $d + 1; ?>"></button>
				<?php endfor; ?>
			</div>
			<button class="home-hero-promotions__arrow" type="button" data-mobile-next aria-label="<?php esc_attr_e( 'Siguiente', 'farmacia-queiles' ); ?>">
				<span class="material-symbols-outlined">chevron_right</span>
			</button>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php $has_side_promotions = !empty(array_filter($side_promotions)); ?>
	<!-- ── GRID ESCRITORIO (oculto en ≤767px) ──────────────────── -->
	<div class="home-hero-promotions__grid<?php echo $has_side_promotions ? '' : ' home-hero-promotions__grid--full'; ?>">
		<div class="home-hero-promotions__main">
			<?php if (!empty($hero_slides)) : ?>
				<div class="home-hero-promotions__slider" data-hero-slider>
					<?php foreach ($hero_slides as $index => $slide) : ?>
						<article class="home-hero-promotions__slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-hero-slide>
							<?php if (!empty($slide['image'])) : ?>
								<img class="home-hero-promotions__image" src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr($slide['title']); ?>">
							<?php endif; ?>
							<div class="home-hero-promotions__overlay">
								<?php if (!empty($slide['subtitle'])) : ?>
									<span class="home-hero-promotions__eyebrow"><?php echo esc_html($slide['subtitle']); ?></span>
								<?php endif; ?>
								<div class="home-hero-promotions__content">
									<h2 class="home-hero-promotions__title"><?php echo esc_html($slide['title']); ?></h2>
									<?php if (!empty($slide['description'])) : ?>
										<p class="home-hero-promotions__description"><?php echo esc_html($slide['description']); ?></p>
									<?php endif; ?>
									<a class="home-hero-promotions__button" href="<?php echo esc_url($slide['url']); ?>">
										<?php echo esc_html($slide['cta_text'] ?? __('Ver promoción', 'farmacia-queiles')); ?>
									</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
					<?php if (count($hero_slides) > 1) : ?>
						<div class="home-hero-promotions__nav">
							<button class="home-hero-promotions__arrow" type="button" data-hero-prev aria-label="<?php echo esc_attr__('Anterior promoción', 'farmacia-queiles'); ?>">
								<span class="material-symbols-outlined">chevron_left</span>
							</button>
							<button class="home-hero-promotions__arrow" type="button" data-hero-next aria-label="<?php echo esc_attr__('Siguiente promoción', 'farmacia-queiles'); ?>">
								<span class="material-symbols-outlined">chevron_right</span>
							</button>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ($has_side_promotions) : ?>
			<div class="home-hero-promotions__side">
				<?php foreach ($side_promotions as $index => $promotion) : ?>
					<?php if (!$promotion) : continue; endif; ?>
					<article class="home-hero-promotions__card home-hero-promotions__card--<?php echo 0 === $index ? 'featured-1' : 'featured-2'; ?>">
						<?php if (!empty($promotion['image'])) : ?>
							<img class="home-hero-promotions__card-image" src="<?php echo esc_url($promotion['image']); ?>" alt="<?php echo esc_attr($promotion['title']); ?>">
						<?php endif; ?>
						<div class="home-hero-promotions__card-overlay"></div>
						<div class="home-hero-promotions__card-content">
							<?php if (!empty($promotion['subtitle'])) : ?>
								<span class="home-hero-promotions__card-eyebrow"><?php echo esc_html($promotion['subtitle']); ?></span>
							<?php endif; ?>
							<h3 class="home-hero-promotions__card-title"><?php echo esc_html($promotion['title']); ?></h3>
							<a class="home-hero-promotions__card-button" href="<?php echo esc_url($promotion['url']); ?>">
								<?php echo esc_html__('Ver promoción', 'farmacia-queiles'); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
