<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="page">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package faramacia-queiles
 */

$url_assets = get_stylesheet_directory_uri();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<script src="<?php echo esc_url('https://cdn.tailwindcss.com?plugins=forms,container-queries') ?>"></script>

	<?php if (has_site_icon()) { ?>
		<link rel="shortcut icon" href="<?php echo esc_url(get_site_icon_url()) ?>" type="image/x-icon">
	<?php } ?>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="<?php echo esc_url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap')?>" rel="stylesheet">
	<link href="<?php echo esc_url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap')?>" rel="stylesheet">

	<link rel="stylesheet" href="<?php echo esc_url($url_assets . '/assets/css/styles.css'); ?>">

	<style>
		:root {
			--color-primario: <?php echo esc_attr( faramacia_get_color( 'primario' ) ); ?>;
			--color-oscuro: <?php echo esc_attr( faramacia_get_color( 'oscuro' ) ); ?>;
			--color-secundario: <?php echo esc_attr( faramacia_get_color( 'secundario' ) ); ?>;
			--color-fondo-claro: <?php echo esc_attr( faramacia_get_color( 'fondo_claro' ) ); ?>;
			--color-borde: <?php echo esc_attr( faramacia_get_color( 'borde' ) ); ?>;
			--color-texto-principal: <?php echo esc_attr( faramacia_get_color( 'texto_principal' ) ); ?>;
			--color-texto-secundario: <?php echo esc_attr( faramacia_get_color( 'texto_secundario' ) ); ?>;
		}
	</style>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'faramacia-queiles' ); ?></a>

	<?php 
	/**
	 * Renderizamos el Topbar y el Header Maquetado.
	 * Al llamarlo aquí, se mantendrá global en toda la tienda online.
	 */
	get_template_part('topbar'); 
	?>

	<div id="content" class="site-content">