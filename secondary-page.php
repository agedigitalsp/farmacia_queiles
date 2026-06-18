<?php
/**
 * Template Name: Páginas Secundarias
 * Post Type: page
 */

get_header();
?>

<div class="breadcrumbs-secondary-page">
    <div class="breadcrumbs-container">
        <?php
        // Detecta automáticamente si la agencia usa Yoast SEO o Rank Math
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
        }
        else {
            // Si no hay plugin de SEO, pinta una ruta básica estructurada
            echo '<a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a> / <span class="current-crumb">' . get_the_title() . '</span>';
        }
        ?>
    </div>
</div>
<div class="secondary-page-banner">
    <div class="title-secondary-page-banner">
        <h1><?php echo get_the_title(); ?></h1>
    </div>
</div>

<div class="content-secondary-page">
    <?php
    // Este bucle de PHP carga el texto legal que tú escribas en WordPress
    while ( have_posts() ) : the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php 
get_footer();