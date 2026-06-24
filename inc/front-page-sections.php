<?php

if (!defined('ABSPATH')) {
	exit;
}

do_action('farmacia_queiles_before_home_sections');
require get_template_directory() . '/inc/front-page/section-hero-promotions.php';
///Aqui van las seciones 2/3/4
require get_template_directory() . '/inc/front-page/section-categories-featured.php';
require get_template_directory() . '/inc/front-page/section-products-featured.php';
require get_template_directory() . '/inc/front-page/section-best-selling-products.php';
require get_template_directory() . '/inc/front-page/section-labs-stories.php';
require get_template_directory() . '/inc/front-page/section-health-commitment.php';
require get_template_directory() . '/inc/front-page/section-consulting-cta.php';
require get_template_directory() . '/inc/front-page/section-faqs.php';
do_action('farmacia_queiles_home_sections');
do_action('farmacia_queiles_after_home_sections');
