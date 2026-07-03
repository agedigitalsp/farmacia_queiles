<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header()
?>

<section class="blog-hero">
    <div class="container container--wide">
        <?php if (function_exists('yoast_breadcrumb')) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
        <div class="blog-hero__card">
            <div class="blog-hero__content">
                <span class="sp-faqs-home-label blog-hero__kicker">
                   Archivo
                </span>

                <h1 class="sp-faqs-home-title blog-hero__heading">
                    <?php the_archive_title(); ?>
                </h1>

                <?php the_archive_description('<p class="blog-hero__description">', '</p>'); ?>
            </div>
        </div>
    </div>
</section>

<div class="container container--wide blog-layout">
    <main class="blog-main">
        <?php if (have_posts()): ?>
            <div class="blog-grid">
                <?php while (have_posts()): the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-card'); ?>>
                        <a href="<?php the_permalink(); ?>" style="display:block;height:100%;text-decoration:none;color:inherit;position:relative">
                            <?php
                            $cats = get_the_category();
                            $badge_cats = array_values(array_filter($cats, function ($cat) {
                                return !in_array($cat->slug, ['uncategorized', 'sin-categoria']);
                            }));
                            if (!empty($badge_cats)):
                            ?>
                                <span class="blog-card__badge"><?php echo esc_html($badge_cats[0]->name); ?></span>
                            <?php endif; ?>

                            <span class="blog-card__image-link">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php endif; ?>
                            </span>

                            <div class="blog-card__body">
                                <h2 class="blog-card__title"><?php the_title(); ?></h2>
                                <p class="blog-card__excerpt"><?php the_excerpt(); ?></p>
                                <span class="blog-card__link">
                                    Leer más
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </span>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => __('Anterior', 'farmacia-queiles'),
                'next_text' => __('Siguiente', 'farmacia-queiles'),
            ));
            ?>
        <?php else: ?>
            <p class="blog-empty"><?php esc_html_e('Lo siento, no hay entradas que coincidan con estos criterios.', 'farmacia-queiles'); ?></p>
        <?php endif; ?>
    </main>

    <aside class="blog-sidebar">
        <?php if (is_active_sidebar('sidebar-1')) : ?>
            <?php dynamic_sidebar('sidebar-1'); ?>
        <?php endif; ?>
    </aside>
</div>

<?php get_footer() ?>