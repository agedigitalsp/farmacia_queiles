<?php

if (!defined('ABSPATH')) {
	exit;
}

function farmacia_queiles_setup(): void
{
	load_theme_textdomain('farmacia-queiles', get_template_directory() . '/languages');

	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support(
		'html5',
		[
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]
	);

	register_nav_menus(
		[
			'primary' => __('Menú principal', 'farmacia-queiles'),
			'footer' => __('Menú pie', 'farmacia-queiles'),
		]
	);

	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'farmacia_queiles_setup');

function farmacia_queiles_enqueue_assets(): void
{
	wp_enqueue_style('farmacia-queiles-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'farmacia_queiles_enqueue_assets');

function farmacia_queiles_widgets_init(): void
{
	register_sidebar(
		[
			'name' => __('Sidebar', 'farmacia-queiles'),
			'id' => 'sidebar-1',
			'description' => __('Área de widgets principal.', 'farmacia-queiles'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		]
	);
}
add_action('widgets_init', 'farmacia_queiles_widgets_init');

function farmacia_queiles_body_open(): void
{
	if (function_exists('wp_body_open')) {
		wp_body_open();
	}
}
