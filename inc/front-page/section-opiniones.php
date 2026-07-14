<?php

if (!defined('ABSPATH')) {
    exit;
}

$args = [
    'post_type'      => 'opiniones',
    'posts_per_page' => 15,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$opiniones_query = new WP_Query($args);
$is_slider = $opiniones_query->post_count > 4;
$section_kicker = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_opiniones_kicker', __('Opiniones reales', 'farmacia-queiles'));
$section_title  = (string) Farmacia_Queiles_Theme::get_setting('farmacia_queiles_home_opiniones_title', __('Lo que dicen de nosotros', 'farmacia-queiles'));
?>

<section class="home-labs-stories home-opiniones-stories" style="padding: 30px 0; background: #f9fbfd;">
    <div class="container container--wide">

        <div class="home-opiniones-header-wrapper">

            <header class="home-labs-stories__header home-opiniones-stories__header">
                <div class="home-opiniones__header-left">
                    <span class="home-labs-stories__kicker home-opiniones-stories__kicker"><?php echo esc_html($section_kicker); ?></span>
                    <h2 class="home-labs-stories__title home-opiniones-stories__title"><?php echo esc_html($section_title); ?></h2>
                </div>


                <?php if ($is_slider) : ?>
                    <div class="home-opiniones-arrows-top">
                        <button class="home-labs-stories__arrow home-labs-stories__arrow--prev" type="button" id="fq-prev-opiniones" aria-label="<?php echo esc_attr__('Opinión anterior', 'farmacia-queiles'); ?>">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button class="home-labs-stories__arrow home-labs-stories__arrow--next" type="button" id="fq-next-opiniones" aria-label="<?php echo esc_attr__('Siguiente opinión', 'farmacia-queiles'); ?>">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                <?php endif; ?>
            </header>
        </div>

        <div class="home-opiniones-carousel">
            <div class="home-opiniones-viewport splide" data-opiniones-carousel>
                <div class="home-opiniones-track splide__track">
                    <div class="splide__list">

                        <?php
                        if ($opiniones_query->have_posts()) :
                            while ($opiniones_query->have_posts()) : $opiniones_query->the_post();
                        ?>
                                <div class="tarjeta-test splide__slide">
                                    <div>
                                        <div class="estrellas-test">
                                            <?php
                                            $num_estrellas = get_post_meta(get_the_ID(), '_fq_valoracion', true);
                                            $num_estrellas = $num_estrellas ? intval($num_estrellas) : 5;

                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $num_estrellas) ? '★' : '☆';
                                            }
                                            ?>
                                        </div>

                                        <div class="contenido-test" style="font-style: italic; color: #475569; line-height: 1.6; margin-bottom: 20px; font-size: 0.95rem;">
                                            <?php the_content(); ?>
                                        </div>
                                    </div>

                                    <h3 class="autor-test" style="font-size: 0.9rem; color: #0f172a; margin: 0; font-weight: 600; border-top: 1px dashed #e2e8f0; padding-top: 12px;">
                                        - <?php the_title(); ?>
                                    </h3>
                                </div>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                            echo '<p>No se encontraron opiniones.</p>';
                        endif;
                        ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Splide === 'undefined') return;
        var e = document.querySelector('[data-opiniones-carousel]');
        if (!e) return;
        var s = new Splide(e, {
            type: 'slide',
            perPage: 4,
            perMove: 1,
            gap: '20px',
            arrows: false,
            pagination: false,
            breakpoints: {
                1024: {
                    perPage: 2
                },
                768: {
                    perPage: 1
                }
            }
        });
        s.mount();
        var p = document.getElementById('fq-prev-opiniones'),
            n = document.getElementById('fq-next-opiniones');
        if (p) p.addEventListener('click', function() {
            s.go('<')
        });
        if (n) n.addEventListener('click', function() {
            s.go('>')
        });
    });
</script>