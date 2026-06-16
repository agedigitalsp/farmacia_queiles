<?php

if (!defined('ABSPATH')) {
	exit;
}

do_action('farmacia_queiles_before_home_sections');
require get_template_directory() . '/inc/front-page/section-hero-promotions.php';
///Aqui van las seciones 2/3/4
require get_template_directory() . '/inc/front-page/section-labs-stories.php';
do_action('farmacia_queiles_home_sections');
do_action('farmacia_queiles_after_home_sections');
