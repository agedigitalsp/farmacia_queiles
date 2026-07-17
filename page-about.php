<?php

/**
 * Template Name: Sobre Nosotros
 * Description: Plantilla de la página "Quiénes somos"
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

$page_id = (int) get_the_ID();

// Imagen de cabecera del hero: campo propio (URL) → imagen destacada → default.
$header_image_url = (string) get_post_meta($page_id, '_fq_page_header_image', true);
if ('' === $header_image_url) {
	$thumb_id = (int) get_post_thumbnail_id($page_id);
	if ($thumb_id > 0) {
		$header_image_url = (string) wp_get_attachment_image_url($thumb_id, 'full');
	}
}
if ('' === $header_image_url) {
	$header_image_url = get_template_directory_uri() . '/assets/img/category-default.webp';
}

$contact_url = home_url('/contacto/');

get_header();
?>

<div class="fq-about">

	<!-- ══ HERO ══════════════════════════════════════════════════ -->
	<header class="fq-about-hero" style="background-image:url('<?php echo esc_url($header_image_url); ?>');">
		<div class="fq-about-hero__overlay"></div>
		<div class="container container--wide fq-about-hero__inner">
			<h1 class="fq-about-hero__title"><?php echo esc_html__('Quiénes somos', 'farmacia-queiles'); ?></h1>
			<p class="fq-about-hero__subtitle"><?php echo esc_html__('Atención farmacéutica integral en Tarazona', 'farmacia-queiles'); ?></p>
		</div>
	</header>

	<!-- ══ INTRODUCCIÓN ═════════════════════════════════════════ -->
	<section class="fq-about-intro container container--wide" aria-label="<?php echo esc_attr__('Introducción', 'farmacia-queiles'); ?>">
		<div class="fq-about-intro__inner">
			<p class="fq-about-intro__text">
				<?php echo esc_html__('Farmacia Queiles es un espacio pensado para ofrecer a Tarazona un servicio farmacéutico de calidad, cercano y especializado. Nuestra prioridad absoluta es cuidar de la salud y el bienestar integral de cada paciente, ofreciendo atención profesional personalizada que va más allá de dispensar medicamentos.', 'farmacia-queiles'); ?>
			</p>
			<p class="fq-about-intro__text">
				<?php echo esc_html__('Con una amplia trayectoria como farmacéuticos adjuntos, entendimos que era el momento de dar un paso al frente y aportar nuestra propia visión. Así nació Farmacia Queiles, un proyecto donde aplicamos toda nuestra experiencia para asesorarte especialmente en áreas clave como la cosmética natural y ecológica, soluciones ortopédicas adaptadas a cada persona y el cuidado especializado de los más pequeños.', 'farmacia-queiles'); ?>
			</p>
			<a class="fq-about-btn fq-about-btn--primary" href="<?php echo esc_url($contact_url); ?>">
				<?php echo esc_html__('Contactar', 'farmacia-queiles'); ?>
			</a>
		</div>
	</section>

	<!-- ══ VALORES ══════════════════════════════════════════════ -->
	<section class="fq-about-values" aria-label="<?php echo esc_attr__('Nuestros valores', 'farmacia-queiles'); ?>">
		<div class="container container--wide fq-about-values__inner">
			<h2 class="fq-about-values__title"><?php echo esc_html__('Escuchar, entender, acompañar', 'farmacia-queiles'); ?></h2>
			<p class="fq-about-values__text">
				<?php echo esc_html__('Creemos firmemente en un modelo de atención en el que cada consulta es única, y donde escuchar con empatía es tan fundamental como ofrecer soluciones prácticas y eficaces. Queremos ser tu farmacia de confianza en Tarazona, el lugar donde siempre encuentres un asesoramiento honesto, especializado y útil.', 'farmacia-queiles'); ?>
			</p>
			<p class="fq-about-values__text">
				<?php echo esc_html__('Porque sabemos que la salud no solo depende de productos, sino también de sentirte acompañado en cada paso, te garantizamos cercanía, claridad y compromiso con tu bienestar diario. Así es como entendemos la farmacia, y así es como trabajamos cada día en Farmacia Queiles.', 'farmacia-queiles'); ?>
			</p>
		</div>
	</section>

	<!-- ══ CIERRE / CTA FINAL ══════════════════════════════════════ -->
	<section class="fq-about-closing" aria-label="<?php echo esc_attr__('Contacta con nosotros', 'farmacia-queiles'); ?>">
		<div class="container container--wide fq-about-closing__inner">
			<h2 class="fq-about-closing__title"><?php echo esc_html__('Estamos aquí para cuidarte', 'farmacia-queiles'); ?></h2>
			<p class="fq-about-closing__text">
				<?php echo esc_html__('En Farmacia Queiles, nuestra prioridad eres tú. Ofrecemos asesoramiento farmacéutico personalizado, cercanía y soluciones eficaces para cada etapa de tu vida. Descubre una atención que marca la diferencia en Tarazona.', 'farmacia-queiles'); ?>
			</p>
			<a class="fq-about-btn fq-about-btn--secondary" href="<?php echo esc_url($contact_url); ?>">
				<?php echo esc_html__('¡Me interesa!', 'farmacia-queiles'); ?>
			</a>
		</div>
	</section>

</div>

<?php
get_footer();
