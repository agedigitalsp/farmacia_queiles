<?php

if (!defined('ABSPATH')) {
	exit;
}

$cached_payload = class_exists('Farmacia_Queiles_Theme') ? Farmacia_Queiles_Theme::get_home_promotions_cached_payload() : null;

if (is_array($cached_payload)) {
	$hero_slides = is_array($cached_payload['hero_slides'] ?? null) ? $cached_payload['hero_slides'] : [];
	$side_promotions = is_array($cached_payload['side_promotions'] ?? null) ? $cached_payload['side_promotions'] : [null, null];
} else {
	$featured_promo_1 = get_posts(
		[
			'post_type' => 'promociones',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_key' => '_fq_promo_featured_1',
			'meta_value' => '1',
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		]
	);
	$featured_promo_2 = get_posts(
		[
			'post_type' => 'promociones',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_key' => '_fq_promo_featured_2',
			'meta_value' => '1',
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		]
	);

	$excluded_ids = array_filter(
		[
			isset($featured_promo_1[0]) ? (int) $featured_promo_1[0]->ID : 0,
			isset($featured_promo_2[0]) ? (int) $featured_promo_2[0]->ID : 0,
		]
	);

	$hero_promotions = get_posts(
		[
			'post_type' => 'promociones',
			'post_status' => 'publish',
			'posts_per_page' => 8,
			'post__not_in' => $excluded_ids,
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
		]
	);

	$hero_slides = [];
	foreach ($hero_promotions as $promotion) {
		$hero_slides[] = [
			'id' => (int) $promotion->ID,
			'title' => get_the_title($promotion),
			'subtitle' => (string) get_post_meta($promotion->ID, '_fq_promo_subtitle', true),
			'description' => (string) get_post_meta($promotion->ID, '_fq_promo_description', true),
			'url' => get_permalink($promotion),
			'image' => get_the_post_thumbnail_url($promotion, 'full'),
		];
	}

	$side_promotions = [];
	foreach ([$featured_promo_1[0] ?? null, $featured_promo_2[0] ?? null] as $promotion) {
		if (!$promotion instanceof WP_Post) {
			$side_promotions[] = null;
			continue;
		}

		$side_promotions[] = [
			'id' => (int) $promotion->ID,
			'title' => get_the_title($promotion),
			'subtitle' => (string) get_post_meta($promotion->ID, '_fq_promo_subtitle', true),
			'description' => (string) get_post_meta($promotion->ID, '_fq_promo_description', true),
			'url' => get_permalink($promotion),
			'image' => get_the_post_thumbnail_url($promotion, 'full'),
		];
	}
}

if (empty($hero_slides) && empty(array_filter($side_promotions))) {
	return;
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
							<?php esc_html_e( 'Ver promoción', 'farmacia-queiles' ); ?>
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

	<!-- ── GRID ESCRITORIO (oculto en ≤767px) ──────────────────── -->
	<div class="home-hero-promotions__grid">
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
										<?php echo esc_html__('Ver promoción', 'farmacia-queiles'); ?>
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
	</div>
</section>
