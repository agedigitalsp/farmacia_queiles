<?php

if (!defined('ABSPATH')) {
	exit;
}

$farmacia_queiles_cmb2_init = __DIR__ . '/lib/CMB2-2.11.0/init.php';
if (!defined('CMB2_LOADED') && file_exists($farmacia_queiles_cmb2_init)) {
	require_once $farmacia_queiles_cmb2_init;
}

if (class_exists('WP_Customize_Control') && !class_exists('Farmacia_Queiles_Material_Icon_Control')) {
	final class Farmacia_Queiles_Material_Icon_Control extends WP_Customize_Control
	{
		public $type = 'farmacia_queiles_material_icon';

		public function render_content(): void
		{
			$value = (string) $this->value();
			$current_label = '' !== $value ? ucwords(str_replace('_', ' ', $value)) : '';
?>
			<div class="fq-material-icon-control">
				<?php if (!empty($this->label)) : ?>
					<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
				<?php endif; ?>

				<div class="fq-material-icon-control__picker">
					<span class="fq-material-icon-control__preview" aria-hidden="true">
						<span class="material-symbols-outlined"><?php echo esc_html($value); ?></span>
					</span>
					<input type="hidden" class="fq-material-icon-input" value="<?php echo esc_attr($value); ?>" <?php $this->link(); ?>>
					<select class="fq-material-icon-select" data-value="<?php echo esc_attr($value); ?>" data-placeholder="<?php echo esc_attr__('Buscar icono...', 'farmacia-queiles'); ?>">
						<option value=""></option>
						<?php if ('' !== $value) : ?>
							<option value="<?php echo esc_attr($value); ?>" selected="selected"><?php echo esc_html($current_label); ?></option>
						<?php endif; ?>
					</select>
				</div>

				<?php if (!empty($this->description)) : ?>
					<p class="description customize-control-description"><?php echo esc_html($this->description); ?></p>
				<?php endif; ?>
			</div>
		<?php
		}
	}
}

final class Farmacia_Queiles_Theme
{
	private const CMB2_THEME_OPTIONS_KEY = 'farmacia_queiles_theme_options';
	private const CMB2_HOME_OPTIONS_KEY = 'farmacia_queiles_home_options';
	private const HOME_LABS_CACHE_VERSION = 2;
	private const HOME_FEATURED_CATS_CACHE_VERSION = 1;
	private const HOME_FEATURED_PRODUCTS_CACHE_VERSION = 1;
	private const HOME_BEST_SELLERS_CACHE_VERSION = 2;
	private string $version;
	private ?array $material_symbols_icon_choices = null;

	public function __construct()
	{
		$version = wp_get_theme()->get('Version');
		$this->version = is_string($version) && $version !== '' ? $version : '1.0.0';

		add_action('after_setup_theme', [$this, 'setup']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		add_action('wp_enqueue_scripts', static function () {
			wp_dequeue_style('sp-item-product-filter-css');
			wp_deregister_style('sp-item-product-filter-css');
		}, 99);
		add_action('widgets_init', [$this, 'widgets_init']);
		add_action('customize_register', [$this, 'customize_register']);
		add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_customizer_control_assets']);
		add_action('wp_head', [$this, 'render_schema_markup'], 20);
		add_action('wp', [$this, 'maybe_adjust_woocommerce_product_cat_archive']);
		add_filter('template_include', [$this, 'force_theme_product_cat_template'], 100);
		// add_action('wp_footer', [$this, 'render_cart_drawer']); // Deshabilitado: usamos el mini cart de Superplus
		// add_filter('woocommerce_add_to_cart_fragments', [$this, 'update_cart_fragments']); // Deshabilitado: Superplus maneja esto
		add_filter('nav_menu_link_attributes', [$this, 'filter_nav_menu_link_attributes'], 10, 4);
		add_filter('woocommerce_structured_data_breadcrumblist', [$this, 'filter_wc_structured_data_breadcrumblist'], 10, 2);
		add_action('pre_get_posts', [$this, 'apply_product_cat_custom_order']);
		add_action('product_cat_add_form_fields', [$this, 'render_featured_product_cat_add_field']);
		add_action('product_cat_edit_form_fields', [$this, 'render_featured_product_cat_edit_field']);
		add_action('created_product_cat', [$this, 'save_featured_product_cat_meta']);
		add_action('edited_product_cat', [$this, 'save_featured_product_cat_meta']);
		add_action('product_brand_add_form_fields', [$this, 'render_featured_product_brand_add_field']);
		add_action('product_brand_edit_form_fields', [$this, 'render_featured_product_brand_edit_field']);
		add_action('created_product_brand', [$this, 'save_featured_product_brand_meta']);
		add_action('edited_product_brand', [$this, 'save_featured_product_brand_meta']);
		add_action('created_product_brand', [$this, 'maybe_regenerate_home_labs_json_on_term_change']);
		add_action('edited_product_brand', [$this, 'maybe_regenerate_home_labs_json_on_term_change']);
		add_action('delete_product_brand', [$this, 'maybe_regenerate_home_labs_json_on_term_change']);
		add_action('created_product_cat', [$this, 'maybe_regenerate_home_featured_cats_json_on_term_change']);
		add_action('edited_product_cat', [$this, 'maybe_regenerate_home_featured_cats_json_on_term_change']);
		add_action('delete_product_cat', [$this, 'maybe_regenerate_home_featured_cats_json_on_term_change']);
		add_filter('manage_edit-product_cat_columns', [$this, 'add_featured_product_cat_column']);
		add_filter('manage_edit-product_brand_columns', [$this, 'add_featured_product_brand_column']);
		add_filter('manage_product_cat_custom_column', [$this, 'render_featured_product_cat_column'], 10, 3);
		add_filter('manage_product_brand_custom_column', [$this, 'render_featured_product_brand_column'], 10, 3);
		add_action('wp_ajax_fq_toggle_featured_term', [$this, 'ajax_toggle_featured_term']);
		add_action('woocommerce_product_options_general_product_data', [$this, 'render_featured_product_field']);
		add_action('save_post_product', [$this, 'save_featured_product_meta'], 20, 3);
		add_action('woocommerce_process_product_meta', [$this, 'save_featured_product_meta_simple'], 20, 1);
		add_action('save_post_product', [$this, 'maybe_regenerate_home_featured_products_json'], 20, 3);
		add_action('deleted_post', [$this, 'maybe_regenerate_home_featured_products_json_on_delete'], 10, 2);
		add_action('trashed_post', [$this, 'maybe_regenerate_home_featured_products_json_on_delete'], 10, 2);
		add_action('untrashed_post', [$this, 'maybe_regenerate_home_featured_products_json_on_delete'], 10, 2);
		add_action('admin_init', [$this, 'maybe_bootstrap_home_featured_products_json']);
		add_action('farmacia_queiles_regenerate_best_sellers_cron', [$this, 'regenerate_home_best_sellers_json']);
		add_filter('cron_schedules', [$this, 'add_best_sellers_cron_interval']);

		add_action('save_post_product', [$this, 'maybe_regenerate_home_best_sellers_json'], 20, 3);
		add_action('deleted_post', [$this, 'maybe_regenerate_home_best_sellers_json_on_delete'], 10, 2);
		add_action('trashed_post', [$this, 'maybe_regenerate_home_best_sellers_json_on_delete'], 10, 2);
		add_action('untrashed_post', [$this, 'maybe_regenerate_home_best_sellers_json_on_delete'], 10, 2);
		add_action('admin_init', [$this, 'maybe_bootstrap_home_best_sellers_json']);

		// Favoritos
		add_action('wp_ajax_fq_favorites', [$this, 'ajax_fq_favorites']);
		add_action('wp_ajax_nopriv_fq_favorites', [$this, 'ajax_fq_favorites']);
		add_action('woocommerce_before_add_to_cart_button', [$this, 'render_single_product_fav_button']);

		add_action('init', [$this, 'register_opiniones_cpt']); // cpt opiniones
		add_action('init', [$this, 'register_promociones_cpt']);
		add_action('init', [$this, 'customize_brand_taxonomy'], 99);
		add_action('cmb2_admin_init', [$this, 'register_cmb2_boxes']);
		add_filter('woocommerce_product_tabs', [$this, 'register_product_custom_tabs'], 20);
		add_action('woocommerce_after_single_product', [$this, 'render_product_rutina_section'], 5);
		add_filter('woocommerce_output_related_products_args', static function (array $args): array {
			$args['posts_per_page'] = 5;
			$args['columns']        = 5;
			return $args;
		});
		// Mover relacionados fuera del grid de tabs, al bloque full-width
		add_action('wp', static function (): void {
			if (!is_product()) {
				return;
			}
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
			add_action('woocommerce_after_single_product', 'woocommerce_output_related_products', 10);
		});
		add_filter('woocommerce_related_products', [$this, 'maybe_use_manual_related_products'], 10, 3);
		add_action('rest_api_init', [$this, 'register_promociones_rest_routes']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_promociones_admin_assets']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_term_featured_admin_assets']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_product_tabs_admin_assets']);
		add_filter('manage_promociones_posts_columns', [$this, 'add_promociones_admin_columns']);
		add_action('manage_promociones_posts_custom_column', [$this, 'render_promociones_admin_columns'], 10, 2);
		add_action('after_switch_theme', [$this, 'schedule_rewrite_flush']);
		add_action('admin_init', [$this, 'maybe_flush_rewrite_rules']);
		add_action('admin_init', [$this, 'maybe_bootstrap_home_promotions_json']);
		add_action('admin_init', [$this, 'maybe_bootstrap_home_labs_json']);
		add_action('admin_init', [$this, 'maybe_bootstrap_home_featured_cats_json']);
		add_action('wp', [$this, 'bootstrap_missing_home_json_files']);
		add_filter('wp_insert_post_data', [$this, 'validate_promociones_subtitle'], 10, 2);
		add_filter('redirect_post_location', [$this, 'add_promociones_subtitle_notice']);
		add_action('admin_notices', [$this, 'render_promociones_subtitle_notice']);
		add_action('add_meta_boxes', [$this, 'register_promociones_featured_meta_box']);
		add_action('save_post_promociones', [$this, 'save_promociones_meta'], 10, 2);
		add_action('save_post_promociones', [$this, 'maybe_regenerate_home_promotions_json'], 30, 3);
		add_action('deleted_post', [$this, 'maybe_regenerate_home_promotions_json_on_delete'], 10, 2);
		add_action('trashed_post', [$this, 'maybe_regenerate_home_promotions_json_on_delete'], 10, 2);
		add_action('untrashed_post', [$this, 'maybe_regenerate_home_promotions_json_on_delete'], 10, 2);
		add_filter( 'woocommerce_my_account_my_orders_actions', [$this, 'agregar_boton_repetir_pedido_en_lista'], 10, 2 );
		add_filter('excerpt_length', function ($length) {
			return 12;
		});

		add_filter('excerpt_more', function ($more) {
			return '...';
		});

		add_filter('wpseo_breadcrumb_separator', function () {
			return '<span class="yoast-breadcrumb__separator">&gt;</span>';
		});

		add_filter('wpseo_breadcrumb_links', [$this, 'forzar_yoast_seo']);

		add_action('category_add_form_fields', [$this, 'render_blog_cat_header_image_field']);
		add_action('category_edit_form_fields', [$this, 'render_blog_cat_header_image_edit_field']);
		add_action('created_category', [$this, 'save_blog_cat_header_image']);
		add_action('edited_category', [$this, 'save_blog_cat_header_image']);




		// ===== INICIO: Deshabilitar Coming Soon de WooCommerce =====
		// add_filter('pre_option_woocommerce_coming_soon', '__return_zero');
		// add_filter('pre_option_woocommerce_store_pages_only', '__return_zero');
		// ===== FIN: Deshabilitar Coming Soon de WooCommerce =====
	}

	public static function get_setting(string $key, $default = '')
	{
		$option_groups = str_starts_with($key, 'farmacia_queiles_home_')
			? [self::CMB2_HOME_OPTIONS_KEY, self::CMB2_THEME_OPTIONS_KEY]
			: [self::CMB2_THEME_OPTIONS_KEY, self::CMB2_HOME_OPTIONS_KEY];

		foreach ($option_groups as $option_group) {
			$option_values = get_option($option_group, []);

			if (is_array($option_values) && array_key_exists($key, $option_values)) {
				return $option_values[$key];
			}
		}

		$theme_mod = get_theme_mod($key, null);

		return null !== $theme_mod ? $theme_mod : $default;
	}

	public function setup(): void
	{
		load_theme_textdomain('farmacia-queiles', get_template_directory() . '/languages');

		add_theme_support('title-tag');
		add_theme_support(
			'custom-logo',
			[
				'height' => 56,
				'width' => 220,
				'flex-height' => true,
				'flex-width' => true,
			]
		);
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
				'services' => __('Menú servicios', 'farmacia-queiles'),
				'footer_explore' => __('Footer - Explorar', 'farmacia-queiles'),
				'footer_support' => __('Footer - Soporte', 'farmacia-queiles'),
				'footer' => __('Menú pie', 'farmacia-queiles'),
				'footer_legal' => __('Menú legal', 'farmacia-queiles'),
			]
		);

		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');

		add_image_size('fq-featured-cat', 480, 600, true);
	}

	public function enqueue_assets(): void
	{
		wp_enqueue_style(
			'farmacia-queiles-fonts',
			'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
			[],
			null
		);
		wp_enqueue_style(
			'farmacia-queiles-material-symbols',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@300..700,0..1&display=swap',
			[],
			null
		);
		wp_enqueue_style('farmacia-queiles-style', get_stylesheet_uri(), [], $this->version);
		wp_enqueue_script(
			'farmacia-queiles-header-mobile',
			get_template_directory_uri() . '/assets/js/header-mobile.min.js',
			[],
			$this->version,
			true
		);

		// SP Popup - CSS y JS para el popup de farmacias de guardia
		wp_enqueue_style(
			'sp-popup',
			get_template_directory_uri() . '/assets/css/sp-popup.css',
			['farmacia-queiles-style'],
			time()
		);
		wp_enqueue_script(
			'sp-popup',
			get_template_directory_uri() . '/assets/js/sp-popup.js',
			['jquery'],
			time(),
			true
		);

		if (is_front_page()) {
			wp_enqueue_style(
				'farmacia-queiles-home-hero',
				get_template_directory_uri() . '/assets/css/home-hero-promotions.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-labs',
				get_template_directory_uri() . '/assets/css/home-labs-stories.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-health-commitment',
				get_template_directory_uri() . '/assets/css/home-health-commitment.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-consulting-cta',
				get_template_directory_uri() . '/assets/css/home-consulting-cta.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-featured-cats',
				get_template_directory_uri() . '/assets/css/home-featured-categories.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-featured-products',
				get_template_directory_uri() . '/assets/css/home-featured-products.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-home-best-sellers',
				get_template_directory_uri() . '/assets/css/home-best-sellers.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_script(
				'farmacia-queiles-home-hero',
				get_template_directory_uri() . '/assets/js/home-hero-promotions.min.js',
				[],
				$this->version,
				true
			);

			// Splide: reutilizar la librería del plugin superplus (mismos handles
			// 'splide-js'/'splide-css' para no cargarla dos veces). Los carruseles
			// de la home dependen de ella.
			if (defined('SP_WSV_PRO_URL')) {
				if (!wp_style_is('splide-css', 'registered') && !wp_style_is('splide-css', 'enqueued')) {
					wp_enqueue_style('splide-css', SP_WSV_PRO_URL . 'assets/css/splide.min.css', [], $this->version);
				} else {
					wp_enqueue_style('splide-css');
				}
				if (!wp_script_is('splide-js', 'registered') && !wp_script_is('splide-js', 'enqueued')) {
					wp_enqueue_script('splide-js', SP_WSV_PRO_URL . 'assets/js/splide.min.js', [], $this->version, true);
				} else {
					wp_enqueue_script('splide-js');
				}
			}
			$splide_dep = wp_script_is('splide-js', 'registered') || wp_script_is('splide-js', 'enqueued') ? ['splide-js'] : [];

			wp_enqueue_script(
				'farmacia-queiles-home-labs',
				get_template_directory_uri() . '/assets/js/home-labs-stories.min.js',
				$splide_dep,
				$this->version,
				true
			);
			wp_enqueue_script(
				'farmacia-queiles-home-featured-products',
				get_template_directory_uri() . '/assets/js/home-featured-products.min.js',
				$splide_dep,
				$this->version,
				true
			);
			wp_enqueue_script(
				'farmacia-queiles-home-best-sellers',
				get_template_directory_uri() . '/assets/js/home-best-sellers.min.js',
				$splide_dep,
				$this->version,
				true
			);
			wp_enqueue_script(
				'farmacia-queiles-home-featured-cats',
				get_template_directory_uri() . '/assets/js/home-featured-categories.min.js',
				$splide_dep,
				$this->version,
				true
			);
		}

		if (is_page_template('contacto.php')) {
			wp_enqueue_style(
				'farmacia-queiles-contact',
				get_template_directory_uri() . '/assets/css/contact-page.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
		}

		if (class_exists('WooCommerce') && (is_front_page() || is_account_page() || is_tax('product_cat') || is_tax('product_brand') || is_shop())) {
			wp_enqueue_style(
				'farmacia-queiles-home-featured-products',
				get_template_directory_uri() . '/assets/css/home-featured-products.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-product-cat-header',
				get_template_directory_uri() . '/assets/css/product-cat-header.min.css',
				['farmacia-queiles-style', 'farmacia-queiles-home-featured-products'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-product-cat-filters',
				get_template_directory_uri() . '/assets/css/product-cat-filters.min.css',
				['farmacia-queiles-product-cat-header'],
				$this->version
			);
			wp_enqueue_script(
				'farmacia-queiles-product-cat-header',
				get_template_directory_uri() . '/assets/js/product-cat-header.min.js',
				[],
				$this->version,
				true
			);
			wp_enqueue_script(
				'farmacia-queiles-product-cat-filters',
				get_template_directory_uri() . '/assets/js/product-cat-filters.min.js',
				[],
				$this->version,
				true
			);

			if (is_shop()) {
				wp_enqueue_style(
					'farmacia-queiles-shop-slider',
					get_template_directory_uri() . '/assets/css/shop-slider.min.css',
					['farmacia-queiles-product-cat-header'],
					$this->version
				);
				wp_enqueue_script(
					'farmacia-queiles-shop-slider',
					get_template_directory_uri() . '/assets/js/shop-slider.min.js',
					[],
					$this->version,
					true
				);
			}
		}

		// Assets para ficha de producto individual
		if (class_exists('WooCommerce') && is_singular('product')) {
			wp_enqueue_style(
				'farmacia-queiles-home-featured-products',
				get_template_directory_uri() . '/assets/css/home-featured-products.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-product-cat-header',
				get_template_directory_uri() . '/assets/css/product-cat-header.min.css',
				['farmacia-queiles-style', 'farmacia-queiles-home-featured-products'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-product-cat-filters',
				get_template_directory_uri() . '/assets/css/product-cat-filters.min.css',
				['farmacia-queiles-product-cat-header'],
				$this->version
			);
			wp_enqueue_style(
				'farmacia-queiles-single-product',
				get_template_directory_uri() . '/assets/css/single-product.min.css',
				['farmacia-queiles-style', 'farmacia-queiles-home-featured-products'],
				$this->version
			);
			wp_enqueue_script(
				'farmacia-queiles-single-product',
				get_template_directory_uri() . '/assets/js/single-product.min.js',
				[],
				$this->version,
				true
			);
		}

		wp_enqueue_style(
			'farmacia-queiles-blog-page',
			get_template_directory_uri() . '/assets/css/blog-page.min.css',
			['farmacia-queiles-style'],
			$this->version
		);
		
		wp_enqueue_style(
			'farmacia-queiles-blog-single-page',
			get_template_directory_uri() . '/assets/css/blog-single-page.min.css',
			['farmacia-queiles-style'],
			$this->version
		);

		wp_enqueue_script(
			'farmacia-queiles-blog-page',
			get_template_directory_uri() . '/assets/js/blog-page.min.js',
			[],
			$this->version,
			true
		);


		// Deshabilitado: Superplus maneja todo el carrito
		// if (class_exists('WooCommerce')) {
		//	wp_enqueue_script('wc-cart-fragments');
		//	wp_add_inline_script('wc-cart-fragments', $this->get_cart_drawer_script());
		// }

		// Favoritos: JS siempre (badge en header en todas las páginas)
		wp_enqueue_script(
			'farmacia-queiles-fq-favorites',
			get_template_directory_uri() . '/assets/js/fq-favorites.min.js',
			[],
			$this->version,
			true
		);
		wp_localize_script('farmacia-queiles-fq-favorites', 'fqFav', [
			'ajax'   => admin_url('admin-ajax.php'),
			'nonce'  => wp_create_nonce('fq_favorites'),
			'logged' => is_user_logged_in() ? '1' : '0',
		]);

		// Favoritos: CSS global (el popup de búsqueda aparece en cualquier página)
		if (class_exists('WooCommerce')) {
			wp_enqueue_style(
				'farmacia-queiles-fq-favorites',
				get_template_directory_uri() . '/assets/css/fq-favorites.min.css',
				['farmacia-queiles-style'],
				$this->version
			);
			if (is_page_template('page-favoritos.php')) {
				wp_enqueue_style(
					'farmacia-queiles-product-cat-header',
					get_template_directory_uri() . '/assets/css/product-cat-header.min.css',
					['farmacia-queiles-style'],
					$this->version
				);
				wp_enqueue_style(
					'farmacia-queiles-product-cat-filters',
					get_template_directory_uri() . '/assets/css/product-cat-filters.min.css',
					['farmacia-queiles-product-cat-header'],
					$this->version
				);
				wp_enqueue_style(
					'farmacia-queiles-home-featured-products',
					get_template_directory_uri() . '/assets/css/home-featured-products.min.css',
					['farmacia-queiles-style'],
					$this->version
				);
				wp_enqueue_script(
					'farmacia-queiles-product-cat-header',
					get_template_directory_uri() . '/assets/js/product-cat-header.min.js',
					[],
					$this->version,
					true
				);
			}
		}
	}

	public function enqueue_customizer_control_assets(): void
	{
		wp_enqueue_style(
			'farmacia-queiles-material-symbols-customizer',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@300..700,0..1&display=swap',
			[],
			null
		);
		wp_enqueue_style(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/css/select2.min.css',
			[],
			'4.1.0-rc.0'
		);
		wp_enqueue_style(
			'farmacia-queiles-customizer-material-icons',
			get_template_directory_uri() . '/assets/css/admin/customizer-material-icons.min.css',
			['farmacia-queiles-select2'],
			$this->version
		);
		wp_enqueue_script(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/js/select2.min.js',
			['jquery'],
			'4.1.0-rc.0',
			true
		);
		wp_enqueue_script(
			'farmacia-queiles-customizer-material-icons',
			get_template_directory_uri() . '/assets/js/admin/customizer-material-icons.min.js',
			['jquery', 'customize-controls', 'farmacia-queiles-select2'],
			$this->version,
			true
		);
		wp_localize_script(
			'farmacia-queiles-customizer-material-icons',
			'FQMaterialIcons',
			[
				'icons' => $this->get_material_symbols_icon_dataset(),
				'placeholder' => __('Buscar icono...', 'farmacia-queiles'),
				'noResults' => __('No se encontraron iconos', 'farmacia-queiles'),
			]
		);
	}

	public function widgets_init(): void
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

	public function customize_register(WP_Customize_Manager $wp_customize): void
	{
		$wp_customize->add_section(
			'farmacia_queiles_header_contact',
			[
				'title' => __('Cabecera - Contacto', 'farmacia-queiles'),
				'priority' => 30,
			]
		);

		$wp_customize->add_section(
			'farmacia_queiles_header_links',
			[
				'title' => __('Cabecera - Enlaces', 'farmacia-queiles'),
				'priority' => 31,
			]
		);

		$wp_customize->add_section(
			'farmacia_queiles_footer',
			[
				'title' => __('Footer', 'farmacia-queiles'),
				'priority' => 32,
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_cats',
			[
				'title' => __('Home - Categorías Destacadas', 'farmacia-queiles'),
				'priority' => 32,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_bestsellers',
			[
				'title' => __('Home - Más Vendidos', 'farmacia-queiles'),
				'priority' => 32,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_featured_products',
			[
				'title' => __('Home - Productos Destacados', 'farmacia-queiles'),
				'priority' => 32,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_labs',
			[
				'title' => __('Home - Laboratorios de confianza', 'farmacia-queiles'),
				'priority' => 33,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_commitment',
			[
				'title' => __('Home - Compromiso sanitario', 'farmacia-queiles'),
				'priority' => 34,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);
		$wp_customize->add_section(
			'farmacia_queiles_home_consulting',
			[
				'title' => __('Home - Consultoría', 'farmacia-queiles'),
				'priority' => 35,
				'active_callback' => [$this, 'is_front_page_customizer'],
			]
		);

		$settings = [
			'farmacia_queiles_phone_text' => [
				'label' => __('Teléfono - texto', 'farmacia-queiles'),
				'default' => '976 642 685',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_phone_url' => [
				'label' => __('Teléfono - URL', 'farmacia-queiles'),
				'default' => 'tel:+34976642685',
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_address_text' => [
				'label' => __('Dirección - texto', 'farmacia-queiles'),
				'default' => 'Av. Reino de Aragón 3, Tarazona',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_address_url' => [
				'label' => __('Dirección - URL', 'farmacia-queiles'),
				'default' => '',
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_schedule_text' => [
				'label' => __('Horario', 'farmacia-queiles'),
				'default' => 'L-V 9:00-13:45 · 16:30-20:00',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_contact_url' => [
				'label' => __('Página de contacto - URL', 'farmacia-queiles'),
				'default' => home_url('/contacto'),
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
		];

		$link_settings = [
			'farmacia_queiles_my_account_url' => [
				'label' => __('Mi cuenta - URL', 'farmacia-queiles'),
				'default' => class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url(),
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_favorites_url' => [
				'label' => __('Favoritos - URL', 'farmacia-queiles'),
				'default' => home_url('/favoritos'),
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
		];

		foreach ($settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_header_contact',
					'type' => $args['type'] ?? ('url' === substr($setting_id, -3) ? 'url' : 'text'),
				]
			);
		}

		foreach ($link_settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_header_links',
					'type' => 'url',
				]
			);
		}

		$footer_settings = [
			'farmacia_queiles_footer_newsletter_title' => [
				'label' => __('Newsletter - Título', 'farmacia-queiles'),
				'default' => __('Únete a nuestra comunidad', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_newsletter_text' => [
				'label' => __('Newsletter - Texto', 'farmacia-queiles'),
				'default' => __('Recibe consejos farmacéuticos exclusivos y descubre antes que nadie nuestras novedades botánicas.', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_textarea'],
				'type' => 'textarea',
			],
			'farmacia_queiles_footer_newsletter_placeholder' => [
				'label' => __('Newsletter - Placeholder', 'farmacia-queiles'),
				'default' => __('Tu correo electrónico', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_newsletter_button' => [
				'label' => __('Newsletter - Botón', 'farmacia-queiles'),
				'default' => __('Suscribirme', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_brand_text' => [
				'label' => __('Marca - Descripción', 'farmacia-queiles'),
				'default' => __('Donde la ciencia farmacéutica se encuentra con el bienestar profundo. Cuidamos tu piel y tu salud con el rigor de un boticario y la sensibilidad de quien valora la vida.', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_textarea'],
				'type' => 'textarea',
			],
			'farmacia_queiles_footer_address_text' => [
				'label' => __('Contacto - Dirección (texto)', 'farmacia-queiles'),
				'default' => 'Av. Reino de Aragón 3, 50500 Tarazona',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_address_url' => [
				'label' => __('Contacto - Dirección (URL)', 'farmacia-queiles'),
				'default' => '',
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_footer_phone_text' => [
				'label' => __('Contacto - Teléfono (texto)', 'farmacia-queiles'),
				'default' => '976 642 685',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_phone_url' => [
				'label' => __('Contacto - Teléfono (URL)', 'farmacia-queiles'),
				'default' => 'tel:+34976642685',
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_footer_whatsapp_text' => [
				'label' => __('Contacto - WhatsApp (texto)', 'farmacia-queiles'),
				'default' => 'WhatsApp: 689 123 456',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_whatsapp_url' => [
				'label' => __('Contacto - WhatsApp (URL)', 'farmacia-queiles'),
				'default' => '',
				'sanitize_callback' => [$this, 'sanitize_url'],
			],
			'farmacia_queiles_footer_schedule_title' => [
				'label' => __('Contacto - Título horario', 'farmacia-queiles'),
				'default' => __('Nuestra Botica:', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_footer_schedule_text' => [
				'label' => __('Contacto - Horario', 'farmacia-queiles'),
				'default' => "L-V: 9:00 - 13:45 | 16:30 - 20:00\nSábados: 9:00 - 13:45",
				'sanitize_callback' => [$this, 'sanitize_textarea'],
				'type' => 'textarea',
			],
			'farmacia_queiles_footer_copyright' => [
				'label' => __('Subfooter - Copyright', 'farmacia-queiles'),
				'default' => '© {year} {site}. ELEVATING PHARMACEUTICAL CARE.',
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
		];

		foreach ($footer_settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_footer',
					'type' => $args['type'] ?? ('url' === substr($setting_id, -3) ? 'url' : 'text'),
				]
			);
		}

		$home_cats_settings = [
			'farmacia_queiles_home_cats_kicker' => [
				'label'             => __('Texto superior (kicker)', 'farmacia-queiles'),
				'default'           => __('Explora nuestra selección', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_home_cats_title' => [
				'label'             => __('Título de la sección', 'farmacia-queiles'),
				'default'           => __('Categorías Destacadas', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
		];
		foreach ($home_cats_settings as $setting_id => $args) {
			$wp_customize->add_setting($setting_id, ['default' => $args['default'], 'sanitize_callback' => $args['sanitize_callback']]);
			$wp_customize->add_control($setting_id, ['label' => $args['label'], 'section' => 'farmacia_queiles_home_cats', 'type' => 'text']);
		}

		$home_bestsellers_settings = [
			'farmacia_queiles_home_bestsellers_kicker' => [
				'label'             => __('Texto superior (kicker)', 'farmacia-queiles'),
				'default'           => __('Los más populares', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type'              => 'text',
			],
			'farmacia_queiles_home_bestsellers_title' => [
				'label'             => __('Título de la sección', 'farmacia-queiles'),
				'default'           => __('Más Vendidos', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type'              => 'text',
			],
			'farmacia_queiles_home_bestsellers_limit' => [
				'label'             => __('Número de productos (4–20)', 'farmacia-queiles'),
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'type'              => 'number',
			],
		];
		foreach ($home_bestsellers_settings as $setting_id => $args) {
			$wp_customize->add_setting($setting_id, ['default' => $args['default'], 'sanitize_callback' => $args['sanitize_callback']]);
			$wp_customize->add_control($setting_id, ['label' => $args['label'], 'section' => 'farmacia_queiles_home_bestsellers', 'type' => $args['type'] ?? 'text']);
		}

		$home_featured_products_settings = [
			'farmacia_queiles_home_featured_products_kicker' => [
				'label'             => __('Texto superior (kicker)', 'farmacia-queiles'),
				'default'           => __('Lo mejor para ti', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type'              => 'text',
			],
			'farmacia_queiles_home_featured_products_title' => [
				'label'             => __('Título de la sección', 'farmacia-queiles'),
				'default'           => __('Productos Destacados', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type'              => 'text',
			],
			'farmacia_queiles_home_featured_products_limit' => [
				'label'             => __('Número de productos (4–20)', 'farmacia-queiles'),
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'type'              => 'number',
			],
		];
		foreach ($home_featured_products_settings as $setting_id => $args) {
			$wp_customize->add_setting($setting_id, ['default' => $args['default'], 'sanitize_callback' => $args['sanitize_callback']]);
			$wp_customize->add_control($setting_id, ['label' => $args['label'], 'section' => 'farmacia_queiles_home_featured_products', 'type' => $args['type'] ?? 'text']);
		}

		$home_labs_settings = [
			'farmacia_queiles_home_labs_kicker' => [
				'label' => __('Home Labs - Texto superior', 'farmacia-queiles'),
				'default' => __('Nuestros laboratorios', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
			],
			'farmacia_queiles_home_labs_title_html' => [
				'label' => __('Home Labs - Título HTML', 'farmacia-queiles'),
				'default' => 'Laboratorios de <span class="home-labs-stories__title-accent">Confianza</span>',
				'sanitize_callback' => [$this, 'sanitize_basic_html'],
				'type' => 'textarea',
			],
		];

		foreach ($home_labs_settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_home_labs',
					'type' => 'text',
				]
			);
		}

		$home_commitment_settings = [
			'farmacia_queiles_home_commitment_kicker' => [
				'label' => __('Texto superior', 'farmacia-queiles'),
				'default' => __('Compromiso farmacéutico', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
			'farmacia_queiles_home_commitment_title' => [
				'label' => __('Título de la sección', 'farmacia-queiles'),
				'default' => __('Nuestro Compromiso Sanitario', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
		];

		for ($index = 1; $index <= 4; $index++) {
			$item_defaults = match ($index) {
				1 => [
					'icon' => 'lock',
					'title' => __('Pago Seguro', 'farmacia-queiles'),
					'text' => __('Tarjeta y Bizum 100% protegidos', 'farmacia-queiles'),
					'note' => __('Entorno cifrado bajo protocolo SSL', 'farmacia-queiles'),
				],
				2 => [
					'icon' => 'store',
					'title' => __('Farmacia Física', 'farmacia-queiles'),
					'text' => __('Respaldo sanitario real en Tarazona', 'farmacia-queiles'),
					'note' => __('Atención y presencia física real', 'farmacia-queiles'),
				],
				3 => [
					'icon' => 'local_shipping',
					'title' => __('Envío Gratis', 'farmacia-queiles'),
					'text' => __('A partir de 50€ sin IVA', 'farmacia-queiles'),
					'note' => __('Fiel a las condiciones comerciales estipuladas', 'farmacia-queiles'),
				],
				default => [
					'icon' => 'forum',
					'title' => __('Atención Sanitaria', 'farmacia-queiles'),
					'text' => __('Asesoramiento y soporte directo por WhatsApp', 'farmacia-queiles'),
					'note' => __('Resolución de dudas por farmacéuticos online', 'farmacia-queiles'),
				],
			};

			$home_commitment_settings["farmacia_queiles_home_commitment_item_{$index}_icon"] = [
				'label' => sprintf(__('Bloque %d - Icono Material', 'farmacia-queiles'), $index),
				'default' => $item_defaults['icon'],
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			];
			$home_commitment_settings["farmacia_queiles_home_commitment_item_{$index}_title"] = [
				'label' => sprintf(__('Bloque %d - Título', 'farmacia-queiles'), $index),
				'default' => $item_defaults['title'],
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			];
			$home_commitment_settings["farmacia_queiles_home_commitment_item_{$index}_text"] = [
				'label' => sprintf(__('Bloque %d - Texto principal', 'farmacia-queiles'), $index),
				'default' => $item_defaults['text'],
				'sanitize_callback' => [$this, 'sanitize_textarea'],
				'type' => 'textarea',
			];
			$home_commitment_settings["farmacia_queiles_home_commitment_item_{$index}_note"] = [
				'label' => sprintf(__('Bloque %d - Texto secundario', 'farmacia-queiles'), $index),
				'default' => $item_defaults['note'],
				'sanitize_callback' => [$this, 'sanitize_textarea'],
				'type' => 'textarea',
			];
		}

		foreach ($home_commitment_settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			if (str_ends_with($setting_id, '_icon') && class_exists('Farmacia_Queiles_Material_Icon_Control')) {
				$wp_customize->add_control(
					new Farmacia_Queiles_Material_Icon_Control(
						$wp_customize,
						$setting_id,
						[
							'label' => $args['label'],
							'description' => __('Busca por nombre y elige el icono con vista previa.', 'farmacia-queiles'),
							'section' => 'farmacia_queiles_home_commitment',
						]
					)
				);
				continue;
			}

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_home_commitment',
					'type' => $args['type'],
				]
			);
		}

		$wp_customize->add_setting(
			'farmacia_queiles_home_consulting_image_id',
			[
				'default' => 0,
				'sanitize_callback' => 'absint',
			]
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'farmacia_queiles_home_consulting_image_id',
				[
					'label' => __('Imagen (lado izquierdo)', 'farmacia-queiles'),
					'section' => 'farmacia_queiles_home_consulting',
					'mime_type' => 'image',
				]
			)
		);

		$home_consulting_settings = [
			'farmacia_queiles_home_consulting_kicker' => [
				'label' => __('Texto superior', 'farmacia-queiles'),
				'default' => __('Consultoría profesional', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
			'farmacia_queiles_home_consulting_title_html' => [
				'label' => __('Título HTML', 'farmacia-queiles'),
				'default' => '¿Necesitas <span class="home-consulting-cta__title-accent">asesoramiento</span> farmacéutico?',
				'sanitize_callback' => [$this, 'sanitize_basic_html'],
				'type' => 'textarea',
			],
			'farmacia_queiles_home_consulting_text_html' => [
				'label' => __('Texto HTML', 'farmacia-queiles'),
				'default' => 'Nuestro equipo de farmacéuticos expertos en <strong>dermocosmética</strong>, <strong>cuidado infantil</strong> y <strong>ortopedia personalizada</strong> está disponible para resolver tus dudas de forma gratuita y personalizada.',
				'sanitize_callback' => [$this, 'sanitize_basic_html'],
				'type' => 'textarea',
			],
			'farmacia_queiles_home_consulting_cta_text' => [
				'label' => __('Botón - Texto', 'farmacia-queiles'),
				'default' => __('Contactar por WhatsApp', 'farmacia-queiles'),
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
			'farmacia_queiles_home_consulting_cta_url' => [
				'label' => __('Botón - URL', 'farmacia-queiles'),
				'default' => (string) get_theme_mod('farmacia_queiles_footer_whatsapp_url', ''),
				'sanitize_callback' => [$this, 'sanitize_url'],
				'type' => 'url',
			],
			'farmacia_queiles_home_consulting_cta_icon' => [
				'label' => __('Botón - Icono Material', 'farmacia-queiles'),
				'default' => 'chat',
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
			'farmacia_queiles_home_consulting_status_enabled' => [
				'label' => __('Mostrar estado', 'farmacia-queiles'),
				'default' => 0,
				'sanitize_callback' => 'absint',
				'type' => 'checkbox',
			],
			'farmacia_queiles_home_consulting_status_text' => [
				'label' => __('Estado - Texto', 'farmacia-queiles'),
				'default' => '',
				'sanitize_callback' => [$this, 'sanitize_text'],
				'type' => 'text',
			],
		];

		foreach ($home_consulting_settings as $setting_id => $args) {
			$wp_customize->add_setting(
				$setting_id,
				[
					'default' => $args['default'],
					'sanitize_callback' => $args['sanitize_callback'],
				]
			);

			if (str_ends_with($setting_id, '_icon') && class_exists('Farmacia_Queiles_Material_Icon_Control')) {
				$wp_customize->add_control(
					new Farmacia_Queiles_Material_Icon_Control(
						$wp_customize,
						$setting_id,
						[
							'label' => $args['label'],
							'description' => __('Busca por nombre y elige el icono con vista previa.', 'farmacia-queiles'),
							'section' => 'farmacia_queiles_home_consulting',
						]
					)
				);
				continue;
			}

			$wp_customize->add_control(
				$setting_id,
				[
					'label' => $args['label'],
					'section' => 'farmacia_queiles_home_consulting',
					'type' => $args['type'],
				]
			);
		}
	}
	
	# REPETIR PEDIDO
	function agregar_boton_repetir_pedido_en_lista( $actions, $order ) {
		// Si el pedido está completado, añadimos la acción de volver a pedir a la lista
		if ( $order->has_status( 'completed' ) ) {
			$actions['order-again'] = array(
				'url'  => wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ),
				'name' => __( 'Volver a pedir', 'woocommerce' ),
			);
		}
		return $actions;
	}

	 	function forzar_yoast_seo( $links ) {
			// Solo modificamos la ruta si estamos dentro de una entrada de blog individual
			if ( is_single() && get_post_type() === 'post' ) {
				$categories = get_the_category();
				
				if ( ! empty( $categories ) ) {
					// Evitamos categorías por defecto de WordPress
					$valid_categories = array_values(array_filter($categories, function($cat) {
						return !in_array($cat->slug, ['uncategorized', 'sin-categoria']);
					}));

					if ( ! empty( $valid_categories ) ) {
						// Tomamos la primera categoría asignada
						$main_category = $valid_categories[0];
						
						$category_link = array(
							'url'  => get_category_link( $main_category->term_id ),
							'text' => $main_category->name,
						);

						// La inyectamos en la penúltima posición (antes del título de la entrada)
						array_splice( $links, -1, 0, array($category_link) );
					}
				}
			}
			return $links;
		}

	private function get_cmb2_theme_options_fields(): array
	{
		return [
			'header_contact' => [
				[
					'name' => __('Teléfono - texto', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_phone_text',
					'type' => 'text',
					'default' => '976 642 685',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Teléfono - URL', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_phone_url',
					'type' => 'text_url',
					'default' => 'tel:+34976642685',
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Dirección - texto', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_address_text',
					'type' => 'text',
					'default' => 'Av. Reino de Aragón 3, Tarazona',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Dirección - URL', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_address_url',
					'type' => 'text_url',
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Horario', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_schedule_text',
					'type' => 'text',
					'default' => 'L-V 9:00-13:45 · 16:30-20:00',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Página de contacto - URL', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_contact_url',
					'type' => 'text_url',
					'default' => home_url('/contacto'),
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
			],
			'header_links' => [
				[
					'name' => __('Mi cuenta - URL', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_my_account_url',
					'type' => 'text_url',
					'default' => class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url(),
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Favoritos - URL', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_favorites_url',
					'type' => 'text_url',
					'default' => home_url('/favoritos'),
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
			],
			'footer' => [
				[
					'name' => __('Newsletter - Título', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_newsletter_title',
					'type' => 'text',
					'default' => __('Únete a nuestra comunidad', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Newsletter - Texto', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_newsletter_text',
					'type' => 'textarea_small',
					'default' => __('Recibe consejos farmacéuticos exclusivos y descubre antes que nadie nuestras novedades botánicas.', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_textarea_field',
				],
				[
					'name' => __('Newsletter - Placeholder', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_newsletter_placeholder',
					'type' => 'text',
					'default' => __('Tu correo electrónico', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Newsletter - Botón', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_newsletter_button',
					'type' => 'text',
					'default' => __('Suscribirme', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Marca - Descripción', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_brand_text',
					'type' => 'textarea_small',
					'default' => __('Donde la ciencia farmacéutica se encuentra con el bienestar profundo. Cuidamos tu piel y tu salud con el rigor de un boticario y la sensibilidad de quien valora la vida.', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_textarea_field',
				],
				[
					'name' => __('Contacto - Dirección (texto)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_address_text',
					'type' => 'text',
					'default' => 'Av. Reino de Aragón 3, 50500 Tarazona',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Contacto - Dirección (URL)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_address_url',
					'type' => 'text_url',
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Contacto - Teléfono (texto)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_phone_text',
					'type' => 'text',
					'default' => '976 642 685',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Contacto - Teléfono (URL)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_phone_url',
					'type' => 'text_url',
					'default' => 'tel:+34976642685',
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Contacto - WhatsApp (texto)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_whatsapp_text',
					'type' => 'text',
					'default' => 'WhatsApp: 689 123 456',
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Contacto - WhatsApp (URL)', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_whatsapp_url',
					'type' => 'text_url',
					'sanitization_cb' => [$this, 'sanitize_url'],
				],
				[
					'name' => __('Contacto - Título horario', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_schedule_title',
					'type' => 'text',
					'default' => __('Nuestra Botica:', 'farmacia-queiles'),
					'sanitization_cb' => 'sanitize_text_field',
				],
				[
					'name' => __('Contacto - Horario', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_schedule_text',
					'type' => 'textarea_small',
					'default' => "L-V: 9:00 - 13:45 | 16:30 - 20:00\nSábados: 9:00 - 13:45",
					'sanitization_cb' => 'sanitize_textarea_field',
				],
				[
					'name' => __('Subfooter - Copyright', 'farmacia-queiles'),
					'id' => 'farmacia_queiles_footer_copyright',
					'type' => 'text',
					'default' => '© {year} {site}. ELEVATING PHARMACEUTICAL CARE.',
					'sanitization_cb' => 'sanitize_text_field',
				],
			],
		];
	}

	private function get_cmb2_home_options_sections(): array
	{
		$icon_options = $this->get_material_symbols_icon_choices();
		$sections = [
			[
				'id' => 'fq_home_heading_labs',
				'title' => __('Home - Laboratorios de confianza', 'farmacia-queiles'),
				'fields' => [
					[
						'name' => __('Texto superior', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_labs_kicker',
						'type' => 'text',
						'default' => __('Nuestros laboratorios', 'farmacia-queiles'),
						'sanitization_cb' => 'sanitize_text_field',
					],
					[
						'name' => __('Título HTML', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_labs_title_html',
						'type' => 'textarea_small',
						'default' => 'Laboratorios de <span class="home-labs-stories__title-accent">Confianza</span>',
						'sanitization_cb' => [$this, 'sanitize_basic_html'],
					],
				],
			],
			[
				'id' => 'fq_home_heading_commitment',
				'title' => __('Home - Compromiso sanitario', 'farmacia-queiles'),
				'fields' => [
					[
						'name' => __('Texto superior', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_commitment_kicker',
						'type' => 'text',
						'default' => __('Compromiso farmacéutico', 'farmacia-queiles'),
						'sanitization_cb' => 'sanitize_text_field',
					],
					[
						'name' => __('Título de la sección', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_commitment_title',
						'type' => 'text',
						'default' => __('Nuestro Compromiso Sanitario', 'farmacia-queiles'),
						'sanitization_cb' => 'sanitize_text_field',
					],
				],
			],
			[
				'id' => 'fq_home_heading_consulting',
				'title' => __('Home - Consultoría', 'farmacia-queiles'),
				'fields' => [
					[
						'name' => __('Imagen (URL)', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_image',
						'type' => 'file',
						'options' => ['url' => true],
						'text' => ['add_upload_file_text' => __('Seleccionar imagen', 'farmacia-queiles')],
						'sanitization_cb' => [$this, 'sanitize_url'],
					],
					[
						'name' => __('Texto superior', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_kicker',
						'type' => 'text',
						'default' => __('Consultoría profesional', 'farmacia-queiles'),
						'sanitization_cb' => 'sanitize_text_field',
					],
					[
						'name' => __('Título HTML', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_title_html',
						'type' => 'textarea_small',
						'default' => '¿Necesitas <span class="home-consulting-cta__title-accent">asesoramiento</span> farmacéutico?',
						'sanitization_cb' => [$this, 'sanitize_basic_html'],
					],
					[
						'name' => __('Texto HTML', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_text_html',
						'type' => 'textarea_small',
						'default' => 'Nuestro equipo de farmacéuticos expertos en <strong>dermocosmética</strong>, <strong>cuidado infantil</strong> y <strong>ortopedia personalizada</strong> está disponible para resolver tus dudas de forma gratuita y personalizada.',
						'sanitization_cb' => [$this, 'sanitize_basic_html'],
					],
					[
						'name' => __('Botón - Texto', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_cta_text',
						'type' => 'text',
						'default' => __('Contactar por WhatsApp', 'farmacia-queiles'),
						'sanitization_cb' => 'sanitize_text_field',
					],
					[
						'name' => __('Botón - URL', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_cta_url',
						'type' => 'text_url',
						'sanitization_cb' => [$this, 'sanitize_url'],
					],
					[
						'name' => __('Botón - Icono Material', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_cta_icon',
						'type' => 'select',
						'options' => $icon_options,
						'default' => 'chat',
						'sanitization_cb' => 'sanitize_text_field',
					],
					[
						'name' => __('Mostrar estado', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_status_enabled',
						'type' => 'checkbox',
					],
					[
						'name' => __('Estado - Texto', 'farmacia-queiles'),
						'id' => 'farmacia_queiles_home_consulting_status_text',
						'type' => 'text',
						'default' => '',
						'sanitization_cb' => 'sanitize_text_field',
					],
				],
			],
		];

		for ($index = 1; $index <= 4; $index++) {
			$defaults = match ($index) {
				1 => ['icon' => 'lock', 'title' => __('Pago Seguro', 'farmacia-queiles'), 'text' => __('Tarjeta y Bizum 100% protegidos', 'farmacia-queiles'), 'note' => __('Entorno cifrado bajo protocolo SSL', 'farmacia-queiles')],
				2 => ['icon' => 'store', 'title' => __('Farmacia Física', 'farmacia-queiles'), 'text' => __('Respaldo sanitario real en Tarazona', 'farmacia-queiles'), 'note' => __('Atención y presencia física real', 'farmacia-queiles')],
				3 => ['icon' => 'local_shipping', 'title' => __('Envío Gratis', 'farmacia-queiles'), 'text' => __('A partir de 50€ sin IVA', 'farmacia-queiles'), 'note' => __('Fiel a las condiciones comerciales estipuladas', 'farmacia-queiles')],
				default => ['icon' => 'forum', 'title' => __('Atención Sanitaria', 'farmacia-queiles'), 'text' => __('Asesoramiento y soporte directo por WhatsApp', 'farmacia-queiles'), 'note' => __('Resolución de dudas por farmacéuticos online', 'farmacia-queiles')],
			};

			$sections[1]['fields'][] = [
				'name' => sprintf(__('Bloque %d - Icono Material', 'farmacia-queiles'), $index),
				'id' => "farmacia_queiles_home_commitment_item_{$index}_icon",
				'type' => 'select',
				'options' => $icon_options,
				'default' => $defaults['icon'],
				'sanitization_cb' => 'sanitize_text_field',
			];
			$sections[1]['fields'][] = [
				'name' => sprintf(__('Bloque %d - Título', 'farmacia-queiles'), $index),
				'id' => "farmacia_queiles_home_commitment_item_{$index}_title",
				'type' => 'text',
				'default' => $defaults['title'],
				'sanitization_cb' => 'sanitize_text_field',
			];
			$sections[1]['fields'][] = [
				'name' => sprintf(__('Bloque %d - Texto principal', 'farmacia-queiles'), $index),
				'id' => "farmacia_queiles_home_commitment_item_{$index}_text",
				'type' => 'textarea_small',
				'default' => $defaults['text'],
				'sanitization_cb' => 'sanitize_textarea_field',
			];
			$sections[1]['fields'][] = [
				'name' => sprintf(__('Bloque %d - Texto secundario', 'farmacia-queiles'), $index),
				'id' => "farmacia_queiles_home_commitment_item_{$index}_note",
				'type' => 'textarea_small',
				'default' => $defaults['note'],
				'sanitization_cb' => 'sanitize_textarea_field',
			];
		}

		return $sections;
	}

	private function get_material_symbols_icon_dataset(): array
	{
		$dataset = [];

		foreach ($this->get_material_symbols_icon_choices() as $value => $label) {
			$dataset[] = [
				'id' => $value,
				'text' => $label,
			];
		}

		return $dataset;
	}

	private function get_material_symbols_icon_choices(): array
	{
		if (is_array($this->material_symbols_icon_choices)) {
			return $this->material_symbols_icon_choices;
		}

		$file_path = get_template_directory() . '/assets/data/material-symbols-rounded.codepoints';
		$lines = is_readable($file_path) ? file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;

		if (!is_array($lines)) {
			$this->material_symbols_icon_choices = [
				'lock' => 'Lock',
				'store' => 'Store',
				'local_shipping' => 'Local Shipping',
				'forum' => 'Forum',
			];

			return $this->material_symbols_icon_choices;
		}

		$choices = [];

		foreach ($lines as $line) {
			$parts = preg_split('/\s+/', trim($line));
			$icon_name = isset($parts[0]) ? (string) $parts[0] : '';

			if ('' === $icon_name || isset($choices[$icon_name])) {
				continue;
			}

			$choices[$icon_name] = $this->format_material_symbol_label($icon_name);
		}

		$this->material_symbols_icon_choices = $choices;

		return $this->material_symbols_icon_choices;
	}

	private function format_material_symbol_label(string $icon_name): string
	{
		return ucwords(str_replace('_', ' ', $icon_name));
	}

	public function is_front_page_customizer($control = null): bool
	{
		unset($control);

		if (is_front_page()) {
			return true;
		}

		$front_page_id = (int) get_option('page_on_front');

		if ($front_page_id < 1 || !is_customize_preview()) {
			return false;
		}

		global $wp_customize;

		if (!isset($wp_customize) || !($wp_customize instanceof WP_Customize_Manager)) {
			return false;
		}

		$previewed_url = $wp_customize->get_preview_url();

		if (!is_string($previewed_url) || '' === $previewed_url) {
			return false;
		}

		return untrailingslashit($previewed_url) === untrailingslashit(get_permalink($front_page_id));
	}

	public function render_cart_drawer(): void
	{
		if (!class_exists('WooCommerce')) {
			return;
		}
		?>
		<div id="site-cart-drawer" class="site-cart-drawer" aria-hidden="true">
			<div class="site-cart-drawer__overlay" data-close-site-cart="true"></div>
			<aside class="site-cart-drawer__panel" aria-label="<?php echo esc_attr__('Carrito', 'farmacia-queiles'); ?>">
				<div class="site-cart-drawer__header">
					<h2 class="site-cart-drawer__title"><?php echo esc_html__('Tu carrito', 'farmacia-queiles'); ?></h2>
					<button type="button" class="site-cart-drawer__close" data-close-site-cart="true" aria-label="<?php echo esc_attr__('Cerrar carrito', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">close</span>
					</button>
				</div>
				<?php echo $this->get_cart_drawer_content_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
				<?php echo $this->get_cart_drawer_footer_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</aside>
		</div>
	<?php
	}

	public function update_cart_fragments(array $fragments): array
	{
		if (!class_exists('WooCommerce')) {
			return $fragments;
		}

		$fragments['.cart-count-fragment'] = $this->get_cart_count_markup();
		$fragments['.site-cart-drawer__content'] = $this->get_cart_drawer_content_markup();
		$fragments['.site-cart-drawer__footer'] = $this->get_cart_drawer_footer_markup();

		return $fragments;
	}

	public function sanitize_text(string $value): string
	{
		return sanitize_text_field($value);
	}

	public function sanitize_textarea(string $value): string
	{
		return sanitize_textarea_field($value);
	}

	public function sanitize_basic_html(string $value): string
	{
		return wp_kses(
			$value,
			[
				'span' => [
					'class' => true,
				],
				'em' => [],
				'strong' => [],
				'b' => [],
				'i' => [],
				'br' => [],
			]
		);
	}

	public function sanitize_url(string $value): string
	{
		return esc_url_raw($value);
	}

	public function render_featured_product_field(): void
	{
		woocommerce_wp_checkbox([
			'id'          => '_fq_featured_product',
			'label'       => __('Producto destacado en portada', 'farmacia-queiles'),
			'description' => __('Marca este producto para mostrarlo en la sección destacada de la portada.', 'farmacia-queiles'),
		]);
	}

	public function save_featured_product_meta(int $post_id, WP_Post $post, bool $update): void
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$value = isset($_POST['_fq_featured_product']) ? '1' : '0';
		update_post_meta($post_id, '_fq_featured_product', $value);
	}

	public function save_featured_product_meta_simple(int $post_id): void
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$value = isset($_POST['_fq_featured_product']) ? '1' : '0';
		update_post_meta($post_id, '_fq_featured_product', $value);
	}

	public function render_featured_product_cat_add_field(string $taxonomy): void
	{
		unset($taxonomy);
		wp_nonce_field('fq_featured_term_meta', 'fq_featured_term_meta_nonce');
	?>
		<div class="form-field">
			<label for="fq_featured_product_cat">
				<input type="checkbox" name="fq_featured_product_cat" id="fq_featured_product_cat" value="1">
				<?php echo esc_html__('Categoría destacada', 'farmacia-queiles'); ?>
			</label>
		</div>
		<div class="form-field" id="fq-cat-bg-color-row" style="display:none">
			<label for="fq_cat_bg_color"><?php echo esc_html__('Color de fondo 1', 'farmacia-queiles'); ?></label>
			<input type="color" name="fq_cat_bg_color" id="fq_cat_bg_color" value="#dbeeff">
			<p class="description"><?php echo esc_html__('Color inicial del degradado.', 'farmacia-queiles'); ?></p>
		</div>
		<div class="form-field" id="fq-cat-bg-color2-row" style="display:none">
			<label for="fq_cat_bg_color2"><?php echo esc_html__('Color de fondo 2', 'farmacia-queiles'); ?></label>
			<input type="color" name="fq_cat_bg_color2" id="fq_cat_bg_color2" value="#ffffff">
			<p class="description"><?php echo esc_html__('Color final del degradado. Blanco por defecto.', 'farmacia-queiles'); ?></p>
		</div>
		<script>
			(function() {
				var cb = document.getElementById('fq_featured_product_cat');
				if (!cb) {
					return;
				}

				function toggle() {
					var show = cb.checked;
					var thumbnailWrap = document.querySelector('.term-thumbnail-wrap');
					var colorRow1 = document.getElementById('fq-cat-bg-color-row');
					var colorRow2 = document.getElementById('fq-cat-bg-color2-row');
					if (thumbnailWrap) {
						thumbnailWrap.style.display = show ? '' : 'none';
					}
					if (colorRow1) {
						colorRow1.style.display = show ? '' : 'none';
					}
					if (colorRow2) {
						colorRow2.style.display = show ? '' : 'none';
					}
				}
				cb.addEventListener('change', toggle);
				document.addEventListener('DOMContentLoaded', toggle);
			}());
		</script>
	<?php
	}

	public function render_featured_product_cat_edit_field(WP_Term $term): void
	{
		$is_featured = '1' === (string) get_term_meta($term->term_id, '_fq_featured_product_cat', true);

		wp_nonce_field('fq_featured_term_meta', 'fq_featured_term_meta_nonce');
	?>
		<tr class="form-field">
			<th scope="row">
				<label for="fq_featured_product_cat"><?php echo esc_html__('Categoría destacada', 'farmacia-queiles'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="fq_featured_product_cat" id="fq_featured_product_cat" value="1" <?php checked($is_featured); ?>>
					<?php echo esc_html__('Mostrar como destacada', 'farmacia-queiles'); ?>
				</label>
			</td>
		</tr>
		<tr class="form-field" id="fq-cat-bg-color-row">
			<th scope="row">
				<label for="fq_cat_bg_color">Color de fondo 1</label>
			</th>
			<td>
				<input type="color"
					name="fq_cat_bg_color"
					id="fq_cat_bg_color"
					value="<?php echo esc_attr(get_term_meta($term->term_id, '_fq_cat_bg_color', true) ?: '#dbeeff'); ?>">
				<p class="description">Color inicial del degradado (esquina superior derecha).</p>
			</td>
		</tr>
		<tr class="form-field" id="fq-cat-bg-color2-row">
			<th scope="row">
				<label for="fq_cat_bg_color2">Color de fondo 2</label>
			</th>
			<td>
				<input type="color"
					name="fq_cat_bg_color2"
					id="fq_cat_bg_color2"
					value="<?php echo esc_attr(get_term_meta($term->term_id, '_fq_cat_bg_color2', true) ?: '#ffffff'); ?>">
				<p class="description">Color final del degradado (esquina inferior izquierda). Blanco por defecto.</p>
			</td>
		</tr>
		<script>
			(function() {
				var cb = document.getElementById('fq_featured_product_cat');
				if (!cb) {
					return;
				}

				function toggle() {
					var show = cb.checked;
					var thumbnailRow = document.querySelector('.term-thumbnail-wrap');
					var colorRow1 = document.getElementById('fq-cat-bg-color-row');
					var colorRow2 = document.getElementById('fq-cat-bg-color2-row');
					if (thumbnailRow) {
						thumbnailRow.style.display = show ? '' : 'none';
					}
					if (colorRow1) {
						colorRow1.style.display = show ? '' : 'none';
					}
					if (colorRow2) {
						colorRow2.style.display = show ? '' : 'none';
					}
				}
				cb.addEventListener('change', toggle);
				document.addEventListener('DOMContentLoaded', toggle);
			}());
		</script>
	<?php
	}

	public function save_featured_product_cat_meta(int $term_id): void
	{
		$this->save_featured_term_meta($term_id, 'product_cat', '_fq_featured_product_cat', 'fq_featured_product_cat');
		delete_term_meta($term_id, '_fq_featured_cat_image_size');

		if (isset($_POST['fq_cat_bg_color'])) {
			$color = sanitize_hex_color($_POST['fq_cat_bg_color']);
			if ($color) {
				update_term_meta($term_id, '_fq_cat_bg_color', $color);
			}
		}

		if (isset($_POST['fq_cat_bg_color2'])) {
			$color2 = sanitize_hex_color($_POST['fq_cat_bg_color2']);

			if ($color2) {
				update_term_meta($term_id, '_fq_cat_bg_color2', $color2);
			}
		}
	}

	public function render_featured_product_brand_add_field(string $taxonomy): void
	{
		unset($taxonomy);
		wp_nonce_field('fq_featured_term_meta', 'fq_featured_term_meta_nonce');
	?>
		<div class="form-field">
			<label for="fq_featured_product_brand">
				<input type="checkbox" name="fq_featured_product_brand" id="fq_featured_product_brand" value="1">
				<?php echo esc_html__('Laboratorio destacado', 'farmacia-queiles'); ?>
			</label>
		</div>
		<div class="form-field">
			<label><?php echo esc_html__('Imagen Home', 'farmacia-queiles'); ?></label>
			<div class="fq-brand-image" data-fq-brand-image="home">
				<input type="hidden" name="fq_product_brand_home_image_id" value="">
				<input type="hidden" name="fq_product_brand_home_image" value="">
				<div class="fq-brand-image__preview" aria-hidden="true"></div>
				<div class="fq-brand-image__actions">
					<button type="button" class="button fq-brand-image__upload"><?php echo esc_html__('Subir/Añadir imagen', 'farmacia-queiles'); ?></button>
					<button type="button" class="button fq-brand-image__remove fq-brand-image__remove--hidden"><?php echo esc_html__('Quitar', 'farmacia-queiles'); ?></button>
				</div>
			</div>
			<p class="description"><?php echo esc_html__('Recomendada para Home. Max. 180x180 px. Proporción 1:1.', 'farmacia-queiles'); ?></p>
		</div>
		<div class="form-field">
			<label><?php echo esc_html__('Imagen Hero', 'farmacia-queiles'); ?></label>
			<div class="fq-brand-image" data-fq-brand-image="hero">
				<input type="hidden" name="fq_product_brand_hero_image_id" value="">
				<input type="hidden" name="fq_product_brand_hero_image" value="">
				<div class="fq-brand-image__preview" aria-hidden="true"></div>
				<div class="fq-brand-image__actions">
					<button type="button" class="button fq-brand-image__upload"><?php echo esc_html__('Subir/Añadir imagen', 'farmacia-queiles'); ?></button>
					<button type="button" class="button fq-brand-image__remove fq-brand-image__remove--hidden"><?php echo esc_html__('Quitar', 'farmacia-queiles'); ?></button>
				</div>
			</div>
			<p class="description"><?php echo esc_html__('Recomendada para hero o banners. Max. 1920x1080 px. Proporción 16:9.', 'farmacia-queiles'); ?></p>
		</div>
	<?php
	}

	public function render_featured_product_brand_edit_field(WP_Term $term): void
	{
		$is_featured = '1' === (string) get_term_meta($term->term_id, '_fq_featured_product_brand', true);
		$home_image = (string) get_term_meta($term->term_id, '_fq_product_brand_home_image', true);
		$hero_image = (string) get_term_meta($term->term_id, '_fq_product_brand_hero_image', true);
		$home_image_id = (int) get_term_meta($term->term_id, '_fq_product_brand_home_image_id', true);
		$hero_image_id = (int) get_term_meta($term->term_id, '_fq_product_brand_hero_image_id', true);
		wp_nonce_field('fq_featured_term_meta', 'fq_featured_term_meta_nonce');
	?>
		<tr class="form-field">
			<th scope="row">
				<label for="fq_featured_product_brand"><?php echo esc_html__('Laboratorio destacado', 'farmacia-queiles'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="fq_featured_product_brand" id="fq_featured_product_brand" value="1" <?php checked($is_featured); ?>>
					<?php echo esc_html__('Mostrar como destacado', 'farmacia-queiles'); ?>
				</label>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label><?php echo esc_html__('Imagen Home', 'farmacia-queiles'); ?></label>
			</th>
			<td>
				<div class="fq-brand-image" data-fq-brand-image="home">
					<input type="hidden" name="fq_product_brand_home_image_id" value="<?php echo esc_attr((string) $home_image_id); ?>">
					<input type="hidden" name="fq_product_brand_home_image" value="<?php echo esc_attr($home_image); ?>">
					<div class="fq-brand-image__preview" aria-hidden="true" <?php echo $home_image !== '' ? ' data-fq-src="' . esc_attr($home_image) . '"' : ''; ?>></div>
					<div class="fq-brand-image__actions">
						<button type="button" class="button fq-brand-image__upload"><?php echo esc_html__('Subir/Añadir imagen', 'farmacia-queiles'); ?></button>
						<button type="button" class="button fq-brand-image__remove<?php echo ($home_image === '' && $home_image_id < 1) ? ' fq-brand-image__remove--hidden' : ''; ?>"><?php echo esc_html__('Quitar', 'farmacia-queiles'); ?></button>
					</div>
				</div>
				<p class="description"><?php echo esc_html__('Recomendada para Home. Max. 180x180 px. Proporción 1:1.', 'farmacia-queiles'); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label><?php echo esc_html__('Imagen Hero', 'farmacia-queiles'); ?></label>
			</th>
			<td>
				<div class="fq-brand-image" data-fq-brand-image="hero">
					<input type="hidden" name="fq_product_brand_hero_image_id" value="<?php echo esc_attr((string) $hero_image_id); ?>">
					<input type="hidden" name="fq_product_brand_hero_image" value="<?php echo esc_attr($hero_image); ?>">
					<div class="fq-brand-image__preview" aria-hidden="true" <?php echo $hero_image !== '' ? ' data-fq-src="' . esc_attr($hero_image) . '"' : ''; ?>></div>
					<div class="fq-brand-image__actions">
						<button type="button" class="button fq-brand-image__upload"><?php echo esc_html__('Subir/Añadir imagen', 'farmacia-queiles'); ?></button>
						<button type="button" class="button fq-brand-image__remove<?php echo ($hero_image === '' && $hero_image_id < 1) ? ' fq-brand-image__remove--hidden' : ''; ?>"><?php echo esc_html__('Quitar', 'farmacia-queiles'); ?></button>
					</div>
				</div>
				<p class="description"><?php echo esc_html__('Recomendada para hero o banners. Max. 1920x1080 px. Proporción 16:9.', 'farmacia-queiles'); ?></p>
			</td>
		</tr>
	<?php
	}

	public function save_featured_product_brand_meta(int $term_id): void
	{
		$this->save_featured_term_meta($term_id, 'product_brand', '_fq_featured_product_brand', 'fq_featured_product_brand');
	}

	public function add_featured_product_cat_column(array $columns): array
	{
		return $this->add_featured_term_column($columns, __('Destacada', 'farmacia-queiles'));
	}

	public function add_featured_product_brand_column(array $columns): array
	{
		return $this->add_featured_term_column($columns, __('Destacado', 'farmacia-queiles'));
	}

	public function render_featured_product_cat_column(string $content, string $column_name, int $term_id): string
	{
		return $this->render_featured_term_column($content, $column_name, $term_id, 'product_cat', '_fq_featured_product_cat');
	}

	public function render_featured_product_brand_column(string $content, string $column_name, int $term_id): string
	{
		return $this->render_featured_term_column($content, $column_name, $term_id, 'product_brand', '_fq_featured_product_brand');
	}

	public function enqueue_term_featured_admin_assets(string $hook): void
	{
		if (!in_array($hook, ['edit-tags.php', 'term.php'], true)) {
			return;
		}

		$screen = get_current_screen();

		if (!$screen || !in_array($screen->taxonomy, ['product_cat', 'product_brand', 'category'], true)) {
			return;
		}

		wp_enqueue_script(
			'farmacia-queiles-term-featured-toggle',
			get_template_directory_uri() . '/assets/js/admin/term-featured-toggle.min.js',
			[],
			$this->version,
			true
		);
		wp_localize_script(
			'farmacia-queiles-term-featured-toggle',
			'FQTermFeatured',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('fq_term_featured_toggle'),
			]
		);

		if (in_array($screen->taxonomy, ['product_cat', 'product_brand', 'category'], true)) {
			wp_enqueue_media();

			wp_enqueue_style(
				'farmacia-queiles-term-brand-images',
				get_template_directory_uri() . '/assets/css/admin/term-brand-images.min.css',
				[],
				$this->version
			);

			wp_enqueue_script(
				'farmacia-queiles-term-brand-images',
				get_template_directory_uri() . '/assets/js/admin/term-brand-images.min.js',
				['jquery', 'media-editor'],
				$this->version,
				true
			);

			if ('category' === $screen->taxonomy) {
				wp_add_inline_script('farmacia-queiles-term-brand-images', '
					(function($) {
						$(function() {
							var $preview = $("#fq-blog-cat-header-preview");
							var $input = $("#fq-blog-cat-header-image");
							var $upload = $("#fq-blog-cat-header-upload");
							var $remove = $("#fq-blog-cat-header-remove");

							function setPreview(url) {
								$preview.empty();
								if (url) {
									$preview.append($("<img>").attr("src", url).css({maxWidth:"200px",height:"auto",borderRadius:"6px",display:"block"}));
									$remove.show();
								} else {
									$remove.hide();
								}
							}

							$upload.on("click", function(e) {
								e.preventDefault();
								var frame = wp.media({ title:"Seleccionar imagen", button:{text:"Usar esta imagen"}, multiple:false });
								frame.on("select", function() {
									var att = frame.state().get("selection").first().toJSON();
									$input.val(att.url);
									setPreview(att.url);
								});
								frame.open();
							});

							$remove.on("click", function(e) {
								e.preventDefault();
								$input.val("");
								setPreview("");
							});
						});
					})(window.jQuery);
				');
			}
		}
	}

	public function ajax_toggle_featured_term(): void
	{
		check_ajax_referer('fq_term_featured_toggle', 'nonce');

		$term_id = isset($_POST['term_id']) ? absint((string) $_POST['term_id']) : 0;
		$taxonomy = isset($_POST['taxonomy']) ? sanitize_key((string) $_POST['taxonomy']) : '';
		$value = isset($_POST['value']) ? sanitize_text_field((string) $_POST['value']) : '';

		$meta_key = match ($taxonomy) {
			'product_cat' => '_fq_featured_product_cat',
			'product_brand' => '_fq_featured_product_brand',
			default => '',
		};

		if ($term_id < 1 || '' === $meta_key || !taxonomy_exists($taxonomy)) {
			wp_send_json_error(['message' => 'invalid_request']);
		}

		$taxonomy_object = get_taxonomy($taxonomy);

		if (!$taxonomy_object || !isset($taxonomy_object->cap->manage_terms) || !current_user_can($taxonomy_object->cap->manage_terms)) {
			wp_send_json_error(['message' => 'forbidden']);
		}

		if ('1' === $value) {
			update_term_meta($term_id, $meta_key, '1');
			if ('product_brand' === $taxonomy) {
				$this->regenerate_home_labs_json();
			}
			if ('product_cat' === $taxonomy) {
				$this->regenerate_home_featured_cats_json();
			}
			wp_send_json_success(['value' => '1']);
		}

		delete_term_meta($term_id, $meta_key);
		if ('product_brand' === $taxonomy) {
			$this->regenerate_home_labs_json();
		}
		if ('product_cat' === $taxonomy) {
			$this->regenerate_home_featured_cats_json();
		}
		wp_send_json_success(['value' => '']);
	}

	private function add_featured_term_column(array $columns, string $label): array
	{
		$updated_columns = [];

		foreach ($columns as $key => $value) {
			$updated_columns[$key] = $value;

			if ('name' === $key) {
				$updated_columns['fq_featured'] = $label;
			}
		}

		if (!isset($updated_columns['fq_featured'])) {
			$updated_columns['fq_featured'] = $label;
		}

		return $updated_columns;
	}

	private function render_featured_term_column(string $content, string $column_name, int $term_id, string $taxonomy, string $meta_key): string
	{
		if ('fq_featured' !== $column_name) {
			return $content;
		}

		$is_featured = '1' === (string) get_term_meta($term_id, $meta_key, true);

		return sprintf(
			'<input type="checkbox" class="fq-term-featured-toggle" data-term-id="%1$s" data-taxonomy="%2$s" %3$s>',
			esc_attr((string) $term_id),
			esc_attr($taxonomy),
			checked($is_featured, true, false)
		);
	}

	private function save_featured_term_meta(int $term_id, string $taxonomy, string $meta_key, string $post_key): void
	{
		$nonce = isset($_POST['fq_featured_term_meta_nonce']) ? (string) $_POST['fq_featured_term_meta_nonce'] : '';

		if ('' === $nonce || !wp_verify_nonce($nonce, 'fq_featured_term_meta')) {
			return;
		}

		$taxonomy_object = get_taxonomy($taxonomy);

		if (!$taxonomy_object || !isset($taxonomy_object->cap->manage_terms)) {
			return;
		}

		if (!current_user_can($taxonomy_object->cap->manage_terms)) {
			return;
		}

		$value = isset($_POST[$post_key]) ? '1' : '';

		if ('' === $value) {
			delete_term_meta($term_id, $meta_key);
		} else {
			update_term_meta($term_id, $meta_key, '1');
		}

		if ('product_brand' === $taxonomy) {
			$home_image_id = isset($_POST['fq_product_brand_home_image_id']) ? absint((string) $_POST['fq_product_brand_home_image_id']) : 0;
			$hero_image_id = isset($_POST['fq_product_brand_hero_image_id']) ? absint((string) $_POST['fq_product_brand_hero_image_id']) : 0;
			$home_image = isset($_POST['fq_product_brand_home_image']) ? $this->sanitize_url((string) $_POST['fq_product_brand_home_image']) : '';
			$hero_image = isset($_POST['fq_product_brand_hero_image']) ? $this->sanitize_url((string) $_POST['fq_product_brand_hero_image']) : '';

			if ($home_image_id < 1) {
				delete_term_meta($term_id, '_fq_product_brand_home_image_id');
			} else {
				update_term_meta($term_id, '_fq_product_brand_home_image_id', $home_image_id);
			}

			if ($hero_image_id < 1) {
				delete_term_meta($term_id, '_fq_product_brand_hero_image_id');
			} else {
				update_term_meta($term_id, '_fq_product_brand_hero_image_id', $hero_image_id);
			}

			if ('' === $home_image) {
				delete_term_meta($term_id, '_fq_product_brand_home_image');
			} else {
				update_term_meta($term_id, '_fq_product_brand_home_image', $home_image);
			}

			if ('' === $hero_image) {
				delete_term_meta($term_id, '_fq_product_brand_hero_image');
			} else {
				update_term_meta($term_id, '_fq_product_brand_hero_image', $hero_image);
			}
		}
	}

	public static function is_external_http_url(string $url): bool
	{
		if ('' === $url) {
			return false;
		}

		$parsed_url = wp_parse_url($url);
		$scheme = isset($parsed_url['scheme']) ? strtolower((string) $parsed_url['scheme']) : '';

		if (!in_array($scheme, ['http', 'https'], true)) {
			return false;
		}

		$link_host = isset($parsed_url['host']) ? strtolower((string) $parsed_url['host']) : '';
		$home_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));

		if ('' === $link_host || '' === $home_host) {
			return false;
		}

		return $link_host !== $home_host;
	}

	public static function get_seo_link_attributes(string $url): string
	{
		if (!self::is_external_http_url($url)) {
			return '';
		}

		return ' target="_blank" rel="noopener noreferrer nofollow"';
	}

	public function filter_nav_menu_link_attributes(array $atts, WP_Post $item, stdClass $args, int $depth): array
	{
		unset($item, $args, $depth);

		$url = isset($atts['href']) ? (string) $atts['href'] : '';

		if (!self::is_external_http_url($url)) {
			return $atts;
		}

		$atts['target'] = '_blank';
		$atts['rel'] = 'noopener noreferrer nofollow';

		return $atts;
	}

	public function render_schema_markup(): void
	{
		if (is_admin() || wp_doing_ajax() || is_feed()) {
			return;
		}

		$home_url = trailingslashit(home_url('/'));
		$site_name = get_bloginfo('name');
		$logo_url = $this->get_schema_logo_url();
		$phone_text = (string) self::get_setting('farmacia_queiles_phone_text', '976 642 685');
		$address_text = (string) self::get_setting('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, Tarazona');
		$address_url = (string) self::get_setting('farmacia_queiles_address_url', '');
		$schedule_text = (string) self::get_setting('farmacia_queiles_schedule_text', 'L-V 9:00-13:45 · 16:30-20:00');
		$brand_text = (string) self::get_setting('farmacia_queiles_footer_brand_text', '');
		$current_url = $this->get_schema_current_url();

		$graph = [];

		$organization = [
			'@type' => 'Pharmacy',
			'@id' => $home_url . '#organization',
			'name' => $site_name,
			'url' => $home_url,
		];

		if ('' !== $brand_text) {
			$organization['description'] = wp_strip_all_tags($brand_text);
		}

		if ('' !== $logo_url) {
			$organization['logo'] = $logo_url;
			$organization['image'] = $logo_url;
		}

		if ('' !== $phone_text) {
			$organization['telephone'] = wp_strip_all_tags($phone_text);
			$organization['contactPoint'] = [
				[
					'@type' => 'ContactPoint',
					'telephone' => wp_strip_all_tags($phone_text),
					'contactType' => 'customer service',
					'availableLanguage' => ['es'],
				],
			];
		}

		if ('' !== $address_text) {
			$organization['address'] = [
				'@type' => 'PostalAddress',
				'streetAddress' => wp_strip_all_tags($address_text),
			];
		}

		if ('' !== $address_url) {
			$organization['hasMap'] = esc_url_raw($address_url);
		}

		if ('' !== $schedule_text) {
			$organization['openingHours'] = [wp_strip_all_tags($schedule_text)];
		}

		$graph[] = $organization;

		$website = [
			'@type' => 'WebSite',
			'@id' => $home_url . '#website',
			'url' => $home_url,
			'name' => $site_name,
			'inLanguage' => get_bloginfo('language'),
			'publisher' => [
				'@id' => $home_url . '#organization',
			],
			'potentialAction' => [
				'@type' => 'SearchAction',
				'target' => [
					'@type' => 'EntryPoint',
					'urlTemplate' => esc_url_raw(add_query_arg('s', '{search_term_string}', $home_url)),
				],
				'query-input' => 'required name=search_term_string',
			],
		];

		$graph[] = $website;

		$webpage = [
			'@type' => 'WebPage',
			'@id' => $current_url . '#webpage',
			'url' => $current_url,
			'name' => wp_get_document_title(),
			'isPartOf' => [
				'@id' => $home_url . '#website',
			],
			'about' => [
				'@id' => $home_url . '#organization',
			],
			'inLanguage' => get_bloginfo('language'),
		];

		if (class_exists('WooCommerce') && is_tax('product_cat')) {
			$breadcrumb_items = self::get_product_cat_breadcrumb_items();

			if (!empty($breadcrumb_items)) {
				$breadcrumb_id = $current_url . '#breadcrumb';
				$webpage['breadcrumb'] = [
					'@id' => $breadcrumb_id,
				];

				$list_items = [];
				$position = 1;

				foreach ($breadcrumb_items as $crumb) {
					$name = isset($crumb['name']) ? (string) $crumb['name'] : '';
					$url = isset($crumb['url']) ? (string) $crumb['url'] : '';

					if ('' === $name || '' === $url) {
						continue;
					}

					$list_items[] = [
						'@type' => 'ListItem',
						'position' => $position,
						'name' => $name,
						'item' => esc_url_raw($url),
					];

					$position += 1;
				}

				if (!empty($list_items)) {
					$graph[] = [
						'@type' => 'BreadcrumbList',
						'@id' => $breadcrumb_id,
						'itemListElement' => $list_items,
					];
				}
			}
		}

		// BreadcrumbList para producto individual
		if (class_exists('WooCommerce') && is_singular('product')) {
			global $post;

			$crumb_items   = [];
			$crumb_items[] = ['name' => __('Inicio', 'farmacia-queiles'), 'url' => home_url('/')];

			// Categorías del producto (tomamos la principal / la de mayor profundidad)
			$product_cats = get_the_terms((int) $post->ID, 'product_cat');
			if (is_array($product_cats) && !empty($product_cats)) {
				// Encontrar la categoría de mayor profundidad
				$deepest_term = null;
				$max_depth    = -1;
				foreach ($product_cats as $cat) {
					$ancestors = get_ancestors((int) $cat->term_id, 'product_cat');
					$depth     = count($ancestors);
					if ($depth > $max_depth) {
						$max_depth    = $depth;
						$deepest_term = $cat;
					}
				}
				if ($deepest_term !== null) {
					// Añadir ancestros
					$ancestor_ids = array_reverse(get_ancestors((int) $deepest_term->term_id, 'product_cat'));
					foreach ($ancestor_ids as $anc_id) {
						$anc_term = get_term((int) $anc_id, 'product_cat');
						if ($anc_term instanceof WP_Term) {
							$crumb_items[] = [
								'name' => (string) $anc_term->name,
								'url'  => (string) get_term_link($anc_term),
							];
						}
					}
					$crumb_items[] = [
						'name' => (string) $deepest_term->name,
						'url'  => (string) get_term_link($deepest_term),
					];
				}
			}

			// Producto actual (sin URL — es el ítem actual)
			$crumb_items[] = ['name' => (string) get_the_title((int) $post->ID), 'url' => (string) get_permalink((int) $post->ID)];

			$bc_id     = $current_url . '#breadcrumb';
			$list_items = [];
			$position   = 1;
			foreach ($crumb_items as $crumb) {
				$list_items[] = [
					'@type'    => 'ListItem',
					'position' => $position,
					'name'     => (string) $crumb['name'],
					'item'     => esc_url_raw((string) $crumb['url']),
				];
				$position += 1;
			}

			if (!empty($list_items)) {
				$webpage['breadcrumb'] = ['@id' => $bc_id];
				$graph[] = [
					'@type'           => 'BreadcrumbList',
					'@id'             => $bc_id,
					'itemListElement' => $list_items,
				];
			}
		}

		$graph[] = $webpage;

		$schema = [
			'@context' => 'https://schema.org',
			'@graph' => $graph,
		];

		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
	}

	public function maybe_adjust_woocommerce_product_cat_archive(): void
	{
		if (!class_exists('WooCommerce')) {
			return;
		}

		if (is_admin() || wp_doing_ajax()) {
			return;
		}

		if (is_tax('product_cat') || is_tax('product_brand') || is_shop()) {
			remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
		}

		if (is_shop() || is_tax('product_brand')) {
			// SuperPlus reemplaza archive-product.php a prioridad 99.
			// Su template llama do_action('woocommerce_archive_description') — lo usamos
			// para inyectar el header completo (migas + título + slider) desde el tema.
			add_action('woocommerce_archive_description', function () {
				wc_get_template('loop/header.php');
			}, 5);
		}
	}

	private function register_product_cat_cmb2_box(): void
	{
		if (!taxonomy_exists('product_cat')) {
			return;
		}

		$box = new_cmb2_box(
			[
				'id' => 'farmacia_queiles_product_cat_design_cmb2',
				'title' => __('Diseño de categoría', 'farmacia-queiles'),
				'object_types' => ['term'],
				'taxonomies' => ['product_cat'],
				'new_term_section' => true,
			]
		);

		$box->add_field(
			[
				'name' => __('Imagen de cabecera', 'farmacia-queiles'),
				'id' => '_fq_product_cat_header_image',
				'type' => 'file',
				'options' => [
					'url' => false,
				],
				'query_args' => [
					'type' => ['image/jpeg', 'image/png', 'image/webp'],
				],
				'preview_size' => [320, 120],
				'text' => [
					'add_upload_file_text' => __('Subir/Añadir imagen', 'farmacia-queiles'),
				],
				'desc' => __('Imagen de fondo para la cabecera de la categoría. Recomendado: 1920x520 px o superior, proporción aproximada 3.7:1.', 'farmacia-queiles'),
				'sanitization_cb' => [$this, 'sanitize_url'],
			]
		);

		$box->add_field(
			[
				'name' => __('Imagen promoción', 'farmacia-queiles'),
				'id' => '_fq_product_cat_promo_image',
				'type' => 'file',
				'options' => [
					'url' => false,
				],
				'query_args' => [
					'type' => ['image/jpeg', 'image/png', 'image/webp'],
				],
				'preview_size' => [320, 120],
				'text' => [
					'add_upload_file_text' => __('Subir/Añadir imagen', 'farmacia-queiles'),
				],
				'desc' => __('Imagen promocional que aparece a la derecha, encima de los productos. Recomendado: 1200x260 px o superior, proporción aproximada 4.6:1.', 'farmacia-queiles'),
				'sanitization_cb' => [$this, 'sanitize_url'],
			]
		);
	}

	public function force_theme_product_cat_template(string $template): string
	{
		if (!class_exists('WooCommerce')) {
			return $template;
		}

		if (is_admin() || wp_doing_ajax()) {
			return $template;
		}

		if (!is_tax('product_cat')) {
			return $template;
		}

		$theme_template = get_theme_file_path('woocommerce/taxonomy-product-cat.php');

		if (is_string($theme_template) && '' !== $theme_template && file_exists($theme_template)) {
			return $theme_template;
		}

		return $template;
	}

	/**
	 * Gestiona el orden en archivos product_cat:
	 *
	 * Orden por defecto (sin selección del usuario):
	 *   1. Destacados/promocionados primero (_featured = yes).
	 *   2. Con stock antes que sin stock (_stock_status = instock).
	 *   3. Más vendidos (total_sales DESC).
	 *   4. Sin stock al final (automático por el punto 2).
	 *
	 * Cuando el usuario elige un orden en el select del plugin,
	 * el plugin gestiona price/date/popularity. Nosotros solo
	 * garantizamos que sin stock quede siempre al final.
	 */
	public function apply_product_cat_custom_order(\WP_Query $query): void
	{
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (!function_exists('is_tax') || !is_tax('product_cat')) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sp_order = isset($_GET['sp_filter_order']) ? sanitize_key($_GET['sp_filter_order']) : '';

		$meta_query = (array) $query->get('meta_query');

		// Siempre registramos la cláusula de stock para poder ordenar por ella.
		$meta_query['stock_clause'] = [
			'key'     => '_stock_status',
			'value'   => 'instock',
			'compare' => '=',
		];

		if ('' === $sp_order) {
			// ── Orden por defecto ──────────────────────────────────────────
			// 1. Destacados/promocionados (_featured = yes → 1, resto → 0).
			$meta_query['featured_clause'] = [
				'key'     => '_featured',
				'value'   => 'yes',
				'compare' => '=',
			];

			// 2 & 3. Con stock + más vendidos.
			$meta_query['sales_clause'] = [
				'key'     => 'total_sales',
				'type'    => 'NUMERIC',
				'compare' => 'EXISTS',
			];

			$query->set('meta_query', $meta_query);

			// Orden: destacados ↓ · con stock ↓ · más vendidos ↓ · fecha ↓
			$query->set('orderby', [
				'featured_clause' => 'DESC',
				'stock_clause'    => 'DESC',
				'sales_clause'    => 'DESC',
				'date'            => 'DESC',
			]);
		} else {
			// ── Orden elegido por el usuario ───────────────────────────────
			// El plugin gestiona price/date/popularity en su propio switch.
			// Solo añadimos stock_clause al final para que sin stock quede último.
			$query->set('meta_query', $meta_query);

			$current_orderby = (array) $query->get('orderby');
			if (!isset($current_orderby['stock_clause'])) {
				$current_orderby['stock_clause'] = 'DESC';
				$query->set('orderby', $current_orderby);
			}
		}
	}

	public function filter_wc_structured_data_breadcrumblist(array $markup, $breadcrumb): array
	{
		unset($breadcrumb);

		if (is_tax('product_cat')) {
			return [];
		}

		return $markup;
	}

	public static function get_product_cat_breadcrumb_items(int $term_id = 0): array
	{
		if (!taxonomy_exists('product_cat')) {
			return [];
		}

		$term = null;

		if ($term_id > 0) {
			$candidate = get_term($term_id, 'product_cat');
			$term = $candidate instanceof WP_Term && !is_wp_error($candidate) ? $candidate : null;
		} else {
			$queried_object = get_queried_object();
			$term = $queried_object instanceof WP_Term && 'product_cat' === $queried_object->taxonomy ? $queried_object : null;
		}

		if (!$term) {
			return [];
		}

		$items = [
			[
				'name' => __('Inicio', 'farmacia-queiles'),
				'url' => trailingslashit(home_url('/')),
			],
		];

		$ancestor_ids = array_reverse(get_ancestors((int) $term->term_id, 'product_cat'));

		foreach ($ancestor_ids as $ancestor_id) {
			$ancestor = get_term((int) $ancestor_id, 'product_cat');

			if (!$ancestor instanceof WP_Term || is_wp_error($ancestor)) {
				continue;
			}

			$ancestor_url = get_term_link($ancestor);
			if (is_wp_error($ancestor_url)) {
				continue;
			}

			$items[] = [
				'name' => $ancestor->name,
				'url' => (string) $ancestor_url,
			];
		}

		$term_url = get_term_link($term);

		if (!is_wp_error($term_url)) {
			$items[] = [
				'name' => $term->name,
				'url' => (string) $term_url,
			];
		}

		return $items;
	}

	public static function get_header_product_categories(int $limit = 5): array
	{
		if (!taxonomy_exists('product_cat')) {
			return [
				'featured' => [],
				'more' => [],
			];
		}

		$exclude = [];
		$default_product_cat = (int) get_option('default_product_cat');

		if ($default_product_cat > 0) {
			$exclude[] = $default_product_cat;
		}

		$terms = get_terms(
			[
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent' => 0,
				'exclude' => $exclude,
				'orderby' => 'name',
				'order' => 'ASC',
			]
		);

		if (is_wp_error($terms) || empty($terms)) {
			return [
				'featured' => [],
				'more' => [],
			];
		}

		$featured_terms = [];
		$other_terms = [];

		foreach ($terms as $term) {
			$is_featured = '1' === (string) get_term_meta((int) $term->term_id, '_fq_featured_product_cat', true);

			if ($is_featured) {
				$featured_terms[] = $term;
				continue;
			}

			$other_terms[] = $term;
		}

		$featured_terms = array_slice($featured_terms, 0, max(0, $limit));
		$featured_ids = array_map(static fn($term) => (int) $term->term_id, $featured_terms);
		$remaining_terms = array_values(
			array_filter(
				$terms,
				static fn($term) => !in_array((int) $term->term_id, $featured_ids, true)
			)
		);

		// Adjuntamos las subcategorías (hijos directos) a cada categoría padre,
		// para poder mostrar un mega-desplegable en el menú.
		$attach_children = static function (array $parents): array {
			foreach ($parents as $parent) {
				$children = get_terms(
					[
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'parent'     => (int) $parent->term_id,
						'orderby'    => 'name',
						'order'      => 'ASC',
					]
				);
				$parent->fq_children = (is_wp_error($children) || empty($children)) ? [] : $children;
			}
			return $parents;
		};

		return [
			'featured' => $attach_children($featured_terms),
			'more'     => $attach_children($remaining_terms),
		];
	}

	public function customize_brand_taxonomy(): void
	{
		global $wp_taxonomies;

		if (!isset($wp_taxonomies['product_brand'])) {
			return;
		}

		$taxonomy = $wp_taxonomies['product_brand'];

		$taxonomy->labels->name = __('Laboratorios', 'farmacia-queiles');
		$taxonomy->labels->singular_name = __('Laboratorio', 'farmacia-queiles');
		$taxonomy->labels->menu_name = __('Laboratorios', 'farmacia-queiles');
		$taxonomy->labels->all_items = __('Todos los laboratorios', 'farmacia-queiles');
		$taxonomy->labels->edit_item = __('Editar laboratorio', 'farmacia-queiles');
		$taxonomy->labels->view_item = __('Ver laboratorio', 'farmacia-queiles');
		$taxonomy->labels->update_item = __('Actualizar laboratorio', 'farmacia-queiles');
		$taxonomy->labels->add_new_item = __('Añadir nuevo laboratorio', 'farmacia-queiles');
		$taxonomy->labels->new_item_name = __('Nuevo laboratorio', 'farmacia-queiles');
		$taxonomy->labels->parent_item = __('Laboratorio superior', 'farmacia-queiles');
		$taxonomy->labels->parent_item_colon = __('Laboratorio superior:', 'farmacia-queiles');
		$taxonomy->labels->search_items = __('Buscar laboratorios', 'farmacia-queiles');
		$taxonomy->labels->popular_items = __('Laboratorios populares', 'farmacia-queiles');
		$taxonomy->labels->separate_items_with_commas = __('Separa laboratorios con comas', 'farmacia-queiles');
		$taxonomy->labels->add_or_remove_items = __('Añadir o quitar laboratorios', 'farmacia-queiles');
		$taxonomy->labels->choose_from_most_used = __('Elegir entre los laboratorios más usados', 'farmacia-queiles');
		$taxonomy->labels->not_found = __('No se han encontrado laboratorios.', 'farmacia-queiles');
		$taxonomy->labels->back_to_items = __('Volver a laboratorios', 'farmacia-queiles');

		$taxonomy->label = __('Laboratorios', 'farmacia-queiles');
		$taxonomy->rewrite = [
			'slug' => 'laboratorio',
			'with_front' => false,
			'hierarchical' => true,
		];
	}

	public function schedule_rewrite_flush(): void
	{
		update_option('farmacia_queiles_flush_rewrite_rules', '1');
	}

	public function maybe_flush_rewrite_rules(): void
	{
		if ('1' !== get_option('farmacia_queiles_flush_rewrite_rules')) {
			return;
		}

		delete_option('farmacia_queiles_flush_rewrite_rules');
		flush_rewrite_rules();
	}
	/* ==========================================================================
   INICIO CPT OPINIONES
   ========================================================================== */

	public function register_opiniones_cpt(): void
	{
		$labels = [
			'name' 					=> __('Opiniones', 'farmacia-queiles'),
			'singular_name' 		=> __('Opinión', 'farmacia-queiles'),
			'menu_name' 			=> __('Opiniones', 'farmacia-queiles'),
			'add_new' 				=> __('Añadir nueva', 'farmacia-queiles'),
			'add_new_item' 			=> __('Añadir nueva opinión', 'farmacia-queiles'),
			'edit_item' 			=> __('Editar opinión', 'farmacia-queiles'),
			'new_item' 				=> __('Nueva opinión', 'farmacia-queiles'),
			'view_item' 			=> __('Ver opinión', 'farmacia-queiles'),
			'search_items' 			=> __('Buscar opiniones', 'farmacia-queiles'),
			'not_found' 			=> __('No se encontraron opiniones.', 'farmacia-queiles'),
			'not_found_in_trash' 	=> __('No se encontraron opiniones en la papelera.', 'farmacia-queiles'),

		];

		$args = [
			'labels' 				=> $labels,
			'public' 				=> true,
			'publicly_queryable' 	=> false,
			'has_archive' 			=> false,
			'query_var'          	=> false,
			'show_in_rest' 			=> true,
			'supports' 				=> ['title', 'editor', 'thumbnail'],
			'menu_icon' 			=> 'dashicons-editor-quote',

		];

		register_post_type('opiniones', $args);
	}

	/* ==========================================================================
   FIN CPT OPINIONES
   ========================================================================== */
	public function register_promociones_cpt(): void
	{
		$labels = [
			'name' => __('Promociones', 'farmacia-queiles'),
			'singular_name' => __('Promoción', 'farmacia-queiles'),
			'add_new' => __('Añadir nueva', 'farmacia-queiles'),
			'add_new_item' => __('Añadir nueva promoción', 'farmacia-queiles'),
			'edit_item' => __('Editar promoción', 'farmacia-queiles'),
			'new_item' => __('Nueva promoción', 'farmacia-queiles'),
			'view_item' => __('Ver promoción', 'farmacia-queiles'),
			'search_items' => __('Buscar promociones', 'farmacia-queiles'),
			'not_found' => __('No se encontraron promociones.', 'farmacia-queiles'),
			'not_found_in_trash' => __('No se encontraron promociones en la papelera.', 'farmacia-queiles'),
			'menu_name' => __('Promociones', 'farmacia-queiles'),
		];

		register_post_type(
			'promociones',
			[
				'labels' => $labels,
				'public' => true,
				'has_archive' => true,
				'show_in_rest' => true,
				'menu_icon' => 'dashicons-megaphone',
				'rewrite' => ['slug' => 'promociones'],
				'supports' => ['title', 'thumbnail'],
			]
		);
	}

	public static function get_home_promotions_cached_payload(): ?array
	{
		$file_path = get_template_directory() . '/assets/data/home-promotions.json';

		if (!is_readable($file_path)) {
			return null;
		}

		$content = file_get_contents($file_path);
		if (!is_string($content) || '' === trim($content)) {
			return null;
		}

		$data = json_decode($content, true);
		if (!is_array($data)) {
			return null;
		}

		$hero_slides = $data['hero_slides'] ?? null;
		$side_promotions = $data['side_promotions'] ?? null;

		if (!is_array($hero_slides) || !is_array($side_promotions)) {
			return null;
		}

		$generated_at = isset($data['generated_at']) ? (int) $data['generated_at'] : 0;

		if ($generated_at < 1 && empty($hero_slides) && empty(array_filter($side_promotions))) {
			return null;
		}

		return [
			'generated_at' => $generated_at,
			'hero_slides' => $hero_slides,
			'side_promotions' => $side_promotions,
		];
	}

	public function maybe_regenerate_home_promotions_json(int $post_id, WP_Post $post, bool $update): void
	{
		if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$this->regenerate_home_promotions_json();
	}

	public function maybe_regenerate_home_promotions_json_on_delete(int $post_id, ?WP_Post $post = null): void
	{
		$post = $post instanceof WP_Post ? $post : get_post($post_id);
		if (!$post instanceof WP_Post) {
			return;
		}

		if ('promociones' !== $post->post_type) {
			return;
		}

		$this->regenerate_home_promotions_json();
	}

	public function maybe_bootstrap_home_promotions_json(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$payload = self::get_home_promotions_cached_payload();
		if (is_array($payload) && ($payload['generated_at'] ?? 0) > 0) {
			return;
		}

		$this->regenerate_home_promotions_json();
	}

	private function regenerate_home_promotions_json(): void
	{
		$payload = $this->build_home_promotions_payload();

		$dir = get_template_directory() . '/assets/data';
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		$file_path = $dir . '/home-promotions.json';
		$json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			return;
		}

		file_put_contents($file_path, $json);
	}

	public function add_best_sellers_cron_interval(array $schedules): array
	{
		$schedules['every_48_hours'] = [
			'interval' => 2 * DAY_IN_SECONDS,
			'display'  => __('Cada 48 horas', 'farmacia-queiles'),
		];
		return $schedules;

	}

	private function build_home_promotions_payload(): array
	{
		$featured_promo_1 = get_posts(
			[
				'post_type' => 'promociones',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'meta_key' => '_fq_promo_featured_1',
				'meta_value' => '1',
				'orderby' => 'date',
				'order' => 'DESC',
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
				'orderby' => 'date',
				'order' => 'DESC',
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
				'orderby' => 'date',
				'order' => 'DESC',
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
			]
		);

		$hero_slides = [];
		foreach ($hero_promotions as $promotion) {
			$hero_slides[] = $this->format_promotion_payload($promotion);
		}

		$side_promotions = [];
		foreach ([$featured_promo_1[0] ?? null, $featured_promo_2[0] ?? null] as $promotion) {
			$side_promotions[] = $promotion instanceof WP_Post ? $this->format_promotion_payload($promotion) : null;
		}

		return [
			'generated_at' => time(),
			'hero_slides' => $hero_slides,
			'side_promotions' => $side_promotions,
		];
	}

	private function format_promotion_payload(WP_Post $promotion): array
	{
		$title = wp_strip_all_tags(get_the_title($promotion));

		return [
			'id' => (int) $promotion->ID,
			'title' => $title,
			'subtitle' => (string) get_post_meta($promotion->ID, '_fq_promo_subtitle', true),
			'description' => (string) get_post_meta($promotion->ID, '_fq_promo_description', true),
			'url' => get_permalink($promotion),
			'image' => (string) get_the_post_thumbnail_url($promotion, 'full'),
		];
	}

	public static function get_home_labs_cached_payload(): ?array
	{
		$file_path = get_template_directory() . '/assets/data/home-labs.json';

		if (!is_readable($file_path)) {
			return null;
		}

		$content = file_get_contents($file_path);
		if (!is_string($content) || '' === trim($content)) {
			return null;
		}

		$data = json_decode($content, true);
		if (!is_array($data)) {
			return null;
		}

		$labs = $data['labs'] ?? null;
		if (!is_array($labs)) {
			return null;
		}

		$cache_version = isset($data['version']) ? (int) $data['version'] : 0;
		if ($cache_version !== self::HOME_LABS_CACHE_VERSION) {
			return null;
		}

		$generated_at = isset($data['generated_at']) ? (int) $data['generated_at'] : 0;
		if ($generated_at < 1 && empty($labs)) {
			return null;
		}

		return [
			'version' => $cache_version,
			'generated_at' => $generated_at,
			'labs' => $labs,
		];
	}

	public function maybe_regenerate_home_labs_json_on_term_change(int $term_id): void
	{
		if ($term_id < 1) {
			return;
		}

		$this->regenerate_home_labs_json();
	}

	public function maybe_bootstrap_home_labs_json(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$payload = self::get_home_labs_cached_payload();
		if (is_array($payload) && ($payload['generated_at'] ?? 0) > 0) {
			return;
		}

		$this->regenerate_home_labs_json();
	}

	private function regenerate_home_labs_json(): void
	{
		$payload = $this->build_home_labs_payload();
		$dir = get_template_directory() . '/assets/data';

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		$json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			return;
		}

		file_put_contents($dir . '/home-labs.json', $json);
	}

	private function build_home_labs_payload(): array
	{
		if (!taxonomy_exists('product_brand')) {
			return [
				'version' => self::HOME_LABS_CACHE_VERSION,
				'generated_at' => time(),
				'labs' => [],
			];
		}

		$terms = get_terms(
			[
				'taxonomy' => 'product_brand',
				'hide_empty' => false,
				'meta_query' => [
					[
						'key' => '_fq_featured_product_brand',
						'value' => '1',
					],
				],
				'orderby' => 'name',
				'order' => 'ASC',
			]
		);

		if (is_wp_error($terms) || empty($terms)) {
			return [
				'version' => self::HOME_LABS_CACHE_VERSION,
				'generated_at' => time(),
				'labs' => [],
			];
		}

		$labs = [];
		foreach ($terms as $term) {
			$item = $this->format_lab_payload($term);
			if (null !== $item) {
				$labs[] = $item;
			}
		}

		return [
			'version' => self::HOME_LABS_CACHE_VERSION,
			'generated_at' => time(),
			'labs' => $labs,
		];
	}

	private function format_lab_payload(WP_Term $term): ?array
	{
		$home_image_id = (int) get_term_meta((int) $term->term_id, '_fq_product_brand_home_image_id', true);
		$hero_image_id = (int) get_term_meta((int) $term->term_id, '_fq_product_brand_hero_image_id', true);
		$home_image = (string) get_term_meta((int) $term->term_id, '_fq_product_brand_home_image', true);
		$hero_image = (string) get_term_meta((int) $term->term_id, '_fq_product_brand_hero_image', true);

		if ($home_image_id > 0) {
			$from_id = wp_get_attachment_image_url($home_image_id, 'full');
			$home_image = is_string($from_id) ? $from_id : $home_image;
		}

		if ($hero_image_id > 0) {
			$from_id = wp_get_attachment_image_url($hero_image_id, 'full');
			$hero_image = is_string($from_id) ? $from_id : $hero_image;
		}

		if ('' === $hero_image) {
			$hero_image = $home_image;
		}

		if ('' === $home_image) {
			return null;
		}

		$url = get_term_link($term);
		if (is_wp_error($url)) {
			return null;
		}

		return [
			'id' => (int) $term->term_id,
			'name' => wp_strip_all_tags($term->name),
			'url' => $url,
			'home_image' => $home_image,
			'hero_image' => $hero_image,
		];
	}

	public static function get_home_featured_cats_cached_payload(): ?array
	{
		$file_path = get_template_directory() . '/assets/data/home-featured-cats.json';

		if (!is_readable($file_path)) {
			return null;
		}

		$content = file_get_contents($file_path);
		if (!is_string($content) || '' === trim($content)) {
			return null;
		}

		$data = json_decode($content, true);
		if (!is_array($data)) {
			return null;
		}

		$cats = $data['cats'] ?? null;
		if (!is_array($cats)) {
			return null;
		}

		$cache_version = isset($data['version']) ? (int) $data['version'] : 0;
		if ($cache_version !== self::HOME_FEATURED_CATS_CACHE_VERSION) {
			return null;
		}

		$generated_at = isset($data['generated_at']) ? (int) $data['generated_at'] : 0;
		if ($generated_at < 1 && empty($cats)) {
			return null;
		}

		return [
			'version'      => $cache_version,
			'generated_at' => $generated_at,
			'cats'         => $cats,
		];
	}

	public function maybe_regenerate_home_featured_cats_json_on_term_change(int $term_id): void
	{
		if ($term_id < 1) {
			return;
		}

		$this->regenerate_home_featured_cats_json();
	}

	public function maybe_bootstrap_home_featured_cats_json(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$payload = self::get_home_featured_cats_cached_payload();
		if (is_array($payload) && ($payload['generated_at'] ?? 0) > 0) {
			return;
		}

		$this->regenerate_home_featured_cats_json();
	}

	public function bootstrap_missing_home_json_files(): void
	{
		$dir = get_template_directory() . '/assets/data';
		if (is_dir($dir) && !is_writable($dir)) {
			return;
		}

		$payload = self::get_home_promotions_cached_payload();
		if (!is_array($payload) || ($payload['generated_at'] ?? 0) < 1) {
			$this->regenerate_home_promotions_json();
		}

		$payload = self::get_home_labs_cached_payload();
		if (!is_array($payload) || ($payload['generated_at'] ?? 0) < 1) {
			$this->regenerate_home_labs_json();
		}

		$payload = self::get_home_featured_cats_cached_payload();
		if (!is_array($payload) || ($payload['generated_at'] ?? 0) < 1) {
			$this->regenerate_home_featured_cats_json();
		}

		$payload = self::get_home_featured_products_cached_payload();
		if (!is_array($payload) || ($payload['generated_at'] ?? 0) < 1) {
			$this->regenerate_home_featured_products_json();
		}

		$payload = self::get_home_best_sellers_cached_payload();
		if (!is_array($payload) || ($payload['generated_at'] ?? 0) < 1) {
			$this->regenerate_home_best_sellers_json();
		}
	}

	private function regenerate_home_featured_cats_json(): void
	{
		$payload = $this->build_home_featured_cats_payload();
		$dir = get_template_directory() . '/assets/data';

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		$json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			return;
		}

		file_put_contents($dir . '/home-featured-cats.json', $json);
	}

	private function build_home_featured_cats_payload(): array
	{
		$empty = [
			'version'      => self::HOME_FEATURED_CATS_CACHE_VERSION,
			'generated_at' => time(),
			'cats'         => [],

		];

		if (!taxonomy_exists('product_cat')) {
			return $empty;
		}

		$terms = get_terms([
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'meta_query' => [
				[
					'key'   => '_fq_featured_product_cat',
					'value' => '1',
				],
			],
			'number'     => 5,
		]);

		if (is_wp_error($terms) || empty($terms)) {
			return $empty;
		}

		$cats = [];
		foreach ($terms as $term) {
			$item = $this->format_featured_cat_payload($term);
			if (null !== $item) {
				$cats[] = $item;
			}
		}

		return [
			'version'      => self::HOME_FEATURED_CATS_CACHE_VERSION,
			'generated_at' => time(),
			'cats'         => $cats,

		];
	}

	private function format_featured_cat_payload(WP_Term $term): ?array
	{
		$thumbnail_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
		$image_url    = '';

		if ($thumbnail_id > 0) {
			$size_key  = (string) get_term_meta($term->term_id, '_fq_featured_cat_image_size', true) ?: 'medium';
			$size_map  = ['small' => 'medium', 'medium' => 'fq-featured-cat', 'large' => 'full'];
			$wp_size   = $size_map[$size_key] ?? 'fq-featured-cat';
			$from_id   = wp_get_attachment_image_url($thumbnail_id, $wp_size);
			$image_url = is_string($from_id) ? $from_id : '';
		}

		$url = get_term_link($term);
		if (is_wp_error($url)) {
			return null;
		}

		$bg_color  = (string) get_term_meta($term->term_id, '_fq_cat_bg_color', true) ?: '#dbeeff';
		$bg_color2 = (string) get_term_meta($term->term_id, '_fq_cat_bg_color2', true) ?: '#ffffff';

		return [
			'id'        => (int) $term->term_id,
			'name'      => wp_strip_all_tags($term->name),
			'url'       => $url,
			'image'     => $image_url,
			'bg_color'  => $bg_color,
			'bg_color2' => $bg_color2,
		];
	}

	// ───── Caché: Productos Destacados ─────

	public static function get_home_featured_products_cached_payload(): ?array
	{
		$file_path = get_template_directory() . '/assets/data/home-featured-products.json';

		if (!is_readable($file_path)) {
			return null;
		}

		$content = file_get_contents($file_path);
		if (!is_string($content) || '' === trim($content)) {
			return null;
		}

		$data = json_decode($content, true);
		if (!is_array($data)) {
			return null;
		}

		$products = $data['products'] ?? null;
		if (!is_array($products)) {
			return null;
		}

		$cache_version = isset($data['version']) ? (int) $data['version'] : 0;
		if ($cache_version !== self::HOME_FEATURED_PRODUCTS_CACHE_VERSION) {
			return null;
		}

		return [
			'version'      => $cache_version,
			'generated_at' => isset($data['generated_at']) ? (int) $data['generated_at'] : 0,
			'products'     => $products,
		];
	}

	public function maybe_regenerate_home_featured_products_json(int $post_id, WP_Post $post, bool $update): void
	{
		unset($update);
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if ('product' !== $post->post_type) {
			return;
		}
		$this->regenerate_home_featured_products_json();
	}

	public function maybe_regenerate_home_featured_products_json_on_delete(int $post_id, WP_Post $post): void
	{
		if ('product' !== $post->post_type) {
			return;
		}
		$this->regenerate_home_featured_products_json();
	}

	public function maybe_bootstrap_home_featured_products_json(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$payload = self::get_home_featured_products_cached_payload();
		if (is_array($payload) && ($payload['generated_at'] ?? 0) > 0) {
			return;
		}

		$this->regenerate_home_featured_products_json();
	}

	private function regenerate_home_featured_products_json(): void
	{
		$payload = $this->build_home_featured_products_payload();
		$dir = get_template_directory() . '/assets/data';

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		$json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			return;
		}

		file_put_contents($dir . '/home-featured-products.json', $json);
	}

	private function build_home_featured_products_payload(): array
	{
		$empty = [
			'version'      => self::HOME_FEATURED_PRODUCTS_CACHE_VERSION,
			'generated_at' => time(),
			'products'     => [],
		];

		$fp_limit = max( 4, min( 20, (int) self::get_setting( 'farmacia_queiles_home_featured_products_limit', 10 ) ) );
		$posts = get_posts([
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $fp_limit,
			'meta_query'     => [
				[
					'key'   => '_fq_featured_product',
					'value' => '1',
				],
			],
			'no_found_rows'      => true,
			'ignore_sticky_posts' => true,
		]);

		if (empty($posts)) {
			return $empty;
		}

		$products = [];
		foreach ($posts as $post) {
			$product = wc_get_product($post->ID);
			if (!$product instanceof WC_Product) {
				continue;
			}

			$image_id  = (int) $product->get_image_id();
			$image_url = '';
			if ($image_id > 0) {
				$src = wp_get_attachment_image_url($image_id, 'woocommerce_single');
				$image_url = is_string($src) ? $src : '';
			}
			if ('' === $image_url) {
				$image_url = wc_placeholder_img_src('woocommerce_single');
			}

			$regular_price = (string) $product->get_regular_price();
			$sale_price    = (string) $product->get_sale_price();
			$is_on_sale    = $product->is_on_sale();

			$brands = [];
			if (taxonomy_exists('product_brand')) {
				$brand_terms = get_the_terms($post->ID, 'product_brand');
				if (is_array($brand_terms)) {
					foreach ($brand_terms as $bt) {
						$brands[] = wp_strip_all_tags($bt->name);
					}
				}
			}

			$short_desc = wp_strip_all_tags($product->get_short_description());

			$cat_name = '';
			$cat_terms = get_the_terms($post->ID, 'product_cat');
			if (is_array($cat_terms) && !empty($cat_terms)) {
				$cat_name = wp_strip_all_tags($cat_terms[0]->name);
			}

			$products[] = [
				'id'              => (int) $post->ID,
				'name'            => wp_strip_all_tags(get_the_title($post)),
				'url'             => get_permalink($post),
				'image'           => $image_url,
				'brand'           => implode(', ', $brands),
				'description'     => $short_desc,
				'category'        => $cat_name,
				'regular_price'   => $regular_price,
				'sale_price'      => $sale_price,
				'is_on_sale'      => $is_on_sale,
				'add_to_cart_url' => $product->add_to_cart_url(),
			];
		}

		return [
			'version'      => self::HOME_FEATURED_PRODUCTS_CACHE_VERSION,
			'generated_at' => time(),
			'products'     => $products,
		];
	}

	public static function get_home_best_sellers_cached_payload(): ?array
	{
		$file_path = get_template_directory() . '/assets/data/home-best-sellers.json';

		if (!is_readable($file_path)) {
			return null;
		}

		$content = file_get_contents($file_path);
		if (!is_string($content) || '' === trim($content)) {
			return null;
		}

		$data = json_decode($content, true);
		if (!is_array($data)) {
			return null;
		}

		$products = $data['products'] ?? null;
		if (!is_array($products)) {
			return null;
		}

		$cache_version = isset($data['version']) ? (int) $data['version'] : 0;
		if ($cache_version !== self::HOME_BEST_SELLERS_CACHE_VERSION) {
			return null;
		}

		return [
			'version'      => $cache_version,
			'generated_at' => isset($data['generated_at']) ? (int) $data['generated_at'] : 0,
			'products'     => $products,
		];
	}

	private function build_home_best_sellers_payload(): array
	{
		$empty = [
			'version'      => self::HOME_BEST_SELLERS_CACHE_VERSION,
			'generated_at' => time(),
			'products'     => [],
		];

		$bs_limit = max( 4, min( 20, (int) self::get_setting( 'farmacia_queiles_home_bestsellers_limit', 10 ) ) );
		$products = wc_get_products([
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales',
			'order'    => 'DESC',
			'limit'    => $bs_limit,
			'status'   => 'publish',
		]);

		if (empty($products)) {
			return $empty;
		}

		$items = [];
		$position = 0;

		foreach ($products as $product) {
			$position++;

			$image_id  = (int) $product->get_image_id();
			$image_url = '';
			if ($image_id > 0) {
				$src = wp_get_attachment_image_url($image_id, 'woocommerce_single');
				$image_url = is_string($src) ? $src : '';
			}
			if ('' === $image_url) {
				$image_url = wc_placeholder_img_src('woocommerce_single');
			}

			$regular_price = (string) $product->get_regular_price();
			$sale_price    = (string) $product->get_sale_price();
			$is_on_sale    = $product->is_on_sale();

			$brands = [];
			if (taxonomy_exists('product_brand')) {
				$brand_terms = get_the_terms($product->get_id(), 'product_brand');
				if (is_array($brand_terms)) {
					foreach ($brand_terms as $bt) {
						$brands[] = wp_strip_all_tags($bt->name);
					}
				}
			}

			$short_desc = wp_strip_all_tags($product->get_short_description());

			$items[] = [
				'position'        => $position,
				'id'              => $product->get_id(),
				'name'            => wp_strip_all_tags($product->get_name()),
				'url'             => $product->get_permalink(),
				'image'           => $image_url,
				'brand'           => implode(', ', $brands),
				'description'     => $short_desc,
				'regular_price'   => $regular_price,
				'sale_price'      => $sale_price,
				'is_on_sale'      => $is_on_sale,
				'add_to_cart_url' => $product->add_to_cart_url(),
			];
		}

		return [
			'version'      => self::HOME_BEST_SELLERS_CACHE_VERSION,
			'generated_at' => time(),
			'products'     => $items,
		];
	}

	public function maybe_bootstrap_home_best_sellers_json(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$payload = self::get_home_best_sellers_cached_payload();
		if (is_array($payload) && ($payload['generated_at'] ?? 0) > 0) {
			return;
		}

		$this->regenerate_home_best_sellers_json();
	}

	public function maybe_regenerate_home_best_sellers_json(int $post_id, WP_Post $post, bool $update): void
	{
		unset($update);
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if ('product' !== $post->post_type) {
			return;
		}
		$this->regenerate_home_best_sellers_json();
	}

	public function maybe_regenerate_home_best_sellers_json_on_delete(int $post_id, WP_Post $post): void
	{
		if ('product' !== $post->post_type) {
			return;
		}
		$this->regenerate_home_best_sellers_json();
	}

	private function regenerate_home_best_sellers_json(): void
	{
		$payload = $this->build_home_best_sellers_payload();
		$dir = get_template_directory() . '/assets/data';

		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		$json = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			return;
		}

		file_put_contents($dir . '/home-best-sellers.json', $json);
	}

	public function register_cmb2_boxes(): void
	{
		if (!function_exists('new_cmb2_box')) {
			return;
		}

		$this->register_promociones_cmb2_box();
		$this->register_product_cat_cmb2_box();
		$this->register_theme_options_cmb2_boxes();
		$this->register_product_tabs_cmb2_box();
		$this->register_product_rutina_cmb2_box();
		$this->register_product_related_meta_box();
		$this->register_page_header_cmb2_box();
	}

	private function register_promociones_cmb2_box(): void
	{
		$box = new_cmb2_box(
			[
				'id' => 'farmacia_queiles_promociones_cmb2',
				'title' => __('Datos de la promoción', 'farmacia-queiles'),
				'object_types' => ['promociones'],
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true,
			]
		);

		$box->add_field(
			[
				'name' => __('Subtítulo', 'farmacia-queiles'),
				'id' => '_fq_promo_subtitle',
				'type' => 'text',
				'attributes' => [
					'required' => 'required',
				],
				'desc' => __('Este campo es obligatorio.', 'farmacia-queiles'),
				'sanitization_cb' => 'sanitize_text_field',
			]
		);
		$box->add_field(
			[
				'name' => __('Descripción', 'farmacia-queiles'),
				'id' => '_fq_promo_description',
				'type' => 'textarea',
				'sanitization_cb' => 'sanitize_textarea_field',
			]
		);
		// Los checkboxes featured_1/2 se gestionan en un meta box nativo separado (register_promociones_featured_meta_box)

		if (taxonomy_exists('product_cat')) {
			$box->add_field(
				[
					'name' => __('Categoría (WooCommerce)', 'farmacia-queiles'),
					'id' => '_fq_promo_product_cat',
					'taxonomy' => 'product_cat',
					'type' => 'taxonomy_select',
					'remove_default' => true,
					'query_args' => [
						'hide_empty' => false,
					],
				]
			);
		}

		if (taxonomy_exists('product_brand')) {
			$box->add_field(
				[
					'name' => __('Laboratorio (WooCommerce)', 'farmacia-queiles'),
					'id' => '_fq_promo_product_brand',
					'taxonomy' => 'product_brand',
					'type' => 'taxonomy_select',
					'remove_default' => true,
					'query_args' => [
						'hide_empty' => false,
					],
				]
			);
		}

		if (post_type_exists('product')) {
			$box->add_field(
				[
					'name' => __('Productos (WooCommerce)', 'farmacia-queiles'),
					'id' => '_fq_promo_products',
					'type' => 'select',
					'options_cb' => [$this, 'get_promociones_cmb2_product_options'],
					'sanitization_cb' => [$this, 'sanitize_product_ids_array'],
					'escape_cb' => [$this, 'escape_product_ids_array'],
					'attributes' => [
						'class' => 'widefat fq-promo-products-select',
						'multiple' => 'multiple',
						'data-rest-url' => rest_url('farmacia-queiles/v1/products-search'),
						'data-rest-nonce' => wp_create_nonce('wp_rest'),
						'data-placeholder' => __('Busca productos...', 'farmacia-queiles'),
						'style' => 'width:100%;',
					],
					'desc' => __('Se cargan los primeros 20 productos y luego puedes buscar más por AJAX.', 'farmacia-queiles'),
				]
			);
		}
	}

	private function register_theme_options_cmb2_boxes(): void
	{
		$theme_box = new_cmb2_box(
			[
				'id' => 'farmacia_queiles_theme_options_page',
				'title' => __('Ajustes del tema', 'farmacia-queiles'),
				'object_types' => ['options-page'],
				'option_key' => self::CMB2_THEME_OPTIONS_KEY,
				'icon_url' => 'dashicons-admin-generic',
				'parent_slug' => 'themes.php',
				'capability' => 'manage_options',
				'display_cb' => [$this, 'render_cmb2_options_page'],
			]
		);

		$theme_box->add_field(['name' => __('Cabecera - Contacto', 'farmacia-queiles'), 'id' => 'fq_theme_heading_header_contact', 'type' => 'title']);
		$this->add_cmb2_options_fields($theme_box, $this->get_cmb2_theme_options_fields()['header_contact']);
		$theme_box->add_field(['name' => __('Cabecera - Enlaces', 'farmacia-queiles'), 'id' => 'fq_theme_heading_header_links', 'type' => 'title']);
		$this->add_cmb2_options_fields($theme_box, $this->get_cmb2_theme_options_fields()['header_links']);
		$theme_box->add_field(['name' => __('Footer', 'farmacia-queiles'), 'id' => 'fq_theme_heading_footer', 'type' => 'title']);
		$this->add_cmb2_options_fields($theme_box, $this->get_cmb2_theme_options_fields()['footer']);

		$home_box = new_cmb2_box(
			[
				'id' => 'farmacia_queiles_home_options_page',
				'title' => __('Ajustes Home', 'farmacia-queiles'),
				'object_types' => ['options-page'],
				'option_key' => self::CMB2_HOME_OPTIONS_KEY,
				'icon_url' => 'dashicons-layout',
				'parent_slug' => 'themes.php',
				'capability' => 'manage_options',
				'display_cb' => [$this, 'render_cmb2_options_page'],
			]
		);

		foreach ($this->get_cmb2_home_options_sections() as $section) {
			$home_box->add_field(
				[
					'name' => $section['title'],
					'id' => $section['id'],
					'type' => 'title',
				]
			);
			$this->add_cmb2_options_fields($home_box, $section['fields']);
		}
	}

	private function add_cmb2_options_fields(CMB2 $box, array $fields): void
	{
		foreach ($fields as $field) {
			$box->add_field($field);
		}
	}

	public function render_cmb2_options_page(CMB2_Options_Hookup $options_page): void
	{
	?>
		<div class="wrap cmb2-options-page <?php echo esc_attr($options_page->option_key); ?>">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php cmb2_metabox_form($options_page->cmb->cmb_id, $options_page->option_key); ?>
		</div>
	<?php
	}

	public function get_promociones_cmb2_product_options($field = null): array
	{
		$selected_ids = [];

		if (is_object($field) && method_exists($field, 'object_id')) {
			$object_id = (int) $field->object_id();
			$selected_meta = get_post_meta($object_id, '_fq_promo_products', true);
			$selected_ids = is_array($selected_meta) ? array_map('intval', $selected_meta) : [];
		}

		$options = [];
		foreach ($this->get_promociones_initial_products($selected_ids) as $product) {
			$options[(string) $product->ID] = get_the_title($product);
		}

		return $options;
	}

	public function sanitize_product_ids_array($value): array
	{
		$values = is_array($value) ? $value : [$value];
		$values = array_map('intval', $values);
		$values = array_values(array_filter($values, static fn($id): bool => $id > 0));

		return array_map('strval', $values);
	}

	public function escape_product_ids_array($value): array
	{
		return $this->sanitize_product_ids_array($value);
	}

	public function register_promociones_featured_meta_box(): void
	{
		add_meta_box(
			'fq_promo_featured_box',
			__('Posición destacada en portada', 'farmacia-queiles'),
			[$this, 'render_promociones_featured_meta_box'],
			'promociones',
			'side',
			'high'
		);
	}

	public function render_promociones_featured_meta_box(WP_Post $post): void
	{
		wp_nonce_field('fq_promo_featured_save', 'fq_promo_featured_nonce');
		$featured_1 = (bool) get_post_meta($post->ID, '_fq_promo_featured_1', true);
		$featured_2 = (bool) get_post_meta($post->ID, '_fq_promo_featured_2', true);
		?>
		<p style="margin:0 0 8px">
			<label style="display:flex;align-items:center;gap:8px;cursor:pointer">
				<input type="checkbox" name="fq_promo_featured_1" value="1" <?php checked($featured_1); ?>>
				<strong><?php esc_html_e('Destacada 1 (lateral izquierda)', 'farmacia-queiles'); ?></strong>
			</label>
		</p>
		<p style="margin:0">
			<label style="display:flex;align-items:center;gap:8px;cursor:pointer">
				<input type="checkbox" name="fq_promo_featured_2" value="1" <?php checked($featured_2); ?>>
				<strong><?php esc_html_e('Destacada 2 (lateral derecha)', 'farmacia-queiles'); ?></strong>
			</label>
		</p>
		<p style="margin:8px 0 0;font-size:11px;color:#666"><?php esc_html_e('Solo una promoción puede ocupar cada posición destacada.', 'farmacia-queiles'); ?></p>
		<?php
	}

	public function register_promociones_meta_boxes(): void
	{
		add_meta_box(
			'farmacia_queiles_promociones_data',
			__('Datos de la promoción', 'farmacia-queiles'),
			[$this, 'render_promociones_meta_box'],
			'promociones',
			'normal',
			'high'
		);
	}

	public function register_promociones_rest_routes(): void
	{
		register_rest_route(
			'farmacia-queiles/v1',
			'/products-search',
			[
				'methods' => WP_REST_Server::READABLE,
				'callback' => [$this, 'rest_search_products'],
				'permission_callback' => static function (): bool {
					return current_user_can('edit_posts');
				},
				'args' => [
					'search' => [
						'type' => 'string',
						'required' => false,
					],
					'include' => [
						'type' => 'array',
						'required' => false,
					],
					'page' => [
						'type' => 'integer',
						'required' => false,
						'default' => 1,
					],
				],
			]
		);
	}

	public function enqueue_promociones_admin_assets(string $hook_suffix): void
	{
		if (!in_array($hook_suffix, ['post.php', 'post-new.php'], true)) {
			return;
		}

		$screen = get_current_screen();
		if (!$screen || 'promociones' !== $screen->post_type) {
			return;
		}

		wp_enqueue_style(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/css/select2.min.css',
			[],
			'4.1.0-rc.0'
		);
		wp_enqueue_style(
			'farmacia-queiles-promociones-admin',
			get_template_directory_uri() . '/assets/css/admin/promociones-select2.min.css',
			['farmacia-queiles-select2'],
			$this->version
		);
		wp_enqueue_script(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/js/select2.min.js',
			['jquery'],
			'4.1.0-rc.0',
			true
		);
		wp_enqueue_script(
			'farmacia-queiles-promociones-admin',
			get_template_directory_uri() . '/assets/js/admin/promociones-select2.min.js',
			['jquery', 'farmacia-queiles-select2'],
			$this->version,
			true
		);
	}

	public function render_promociones_meta_box(WP_Post $post): void
	{
		wp_nonce_field('farmacia_queiles_promociones_save', 'farmacia_queiles_promociones_nonce');

		$subtitle = (string) get_post_meta($post->ID, '_fq_promo_subtitle', true);
		$description = (string) get_post_meta($post->ID, '_fq_promo_description', true);
		$selected_cat = (string) get_post_meta($post->ID, '_fq_promo_product_cat', true);
		$selected_brand = (string) get_post_meta($post->ID, '_fq_promo_product_brand', true);
		$selected_products = get_post_meta($post->ID, '_fq_promo_products', true);
		$selected_products = is_array($selected_products) ? array_map('intval', $selected_products) : [];
		$featured_1 = (bool) get_post_meta($post->ID, '_fq_promo_featured_1', true);
		$featured_2 = (bool) get_post_meta($post->ID, '_fq_promo_featured_2', true);

	?>
		<p>
			<label for="fq_promo_subtitle"><strong><?php echo esc_html__('Subtítulo', 'farmacia-queiles'); ?></strong></label><br>
			<input id="fq_promo_subtitle" name="fq_promo_subtitle" type="text" value="<?php echo esc_attr($subtitle); ?>" class="widefat" required>
			<span class="description"><?php echo esc_html__('Este campo es obligatorio.', 'farmacia-queiles'); ?></span>
		</p>
		<p>
			<label for="fq_promo_description"><strong><?php echo esc_html__('Descripción', 'farmacia-queiles'); ?></strong></label><br>
			<textarea id="fq_promo_description" name="fq_promo_description" rows="5" class="widefat"><?php echo esc_textarea($description); ?></textarea>
		</p>
		<p>
			<label>
				<input type="checkbox" name="fq_promo_featured_1" value="1" <?php checked($featured_1); ?>>
				<?php echo esc_html__('Promo destacada 1', 'farmacia-queiles'); ?>
			</label>
			<br>
			<label>
				<input type="checkbox" name="fq_promo_featured_2" value="1" <?php checked($featured_2); ?>>
				<?php echo esc_html__('Promo destacada 2', 'farmacia-queiles'); ?>
			</label>
		</p>
		<hr>
		<?php if (taxonomy_exists('product_cat')) : ?>
			<p>
				<label for="fq_promo_product_cat"><strong><?php echo esc_html__('Categoría (WooCommerce)', 'farmacia-queiles'); ?></strong></label><br>
				<?php
				wp_dropdown_categories(
					[
						'taxonomy' => 'product_cat',
						'hide_empty' => false,
						'name' => 'fq_promo_product_cat',
						'id' => 'fq_promo_product_cat',
						'class' => 'widefat',
						'show_option_none' => __('— Sin categoría —', 'farmacia-queiles'),
						'option_none_value' => '',
						'selected' => $selected_cat,
					]
				);
				?>
			</p>
		<?php else : ?>
			<p><?php echo esc_html__('WooCommerce no está activo o la taxonomía de productos no está disponible.', 'farmacia-queiles'); ?></p>
		<?php endif; ?>

		<?php if (taxonomy_exists('product_brand')) : ?>
			<p>
				<label for="fq_promo_product_brand"><strong><?php echo esc_html__('Laboratorio (WooCommerce)', 'farmacia-queiles'); ?></strong></label><br>
				<?php
				wp_dropdown_categories(
					[
						'taxonomy' => 'product_brand',
						'hide_empty' => false,
						'name' => 'fq_promo_product_brand',
						'id' => 'fq_promo_product_brand',
						'class' => 'widefat',
						'show_option_none' => __('— Sin laboratorio —', 'farmacia-queiles'),
						'option_none_value' => '',
						'selected' => $selected_brand,
					]
				);
				?>
			</p>
		<?php else : ?>
			<p><?php echo esc_html__('La taxonomía de laboratorios no está disponible.', 'farmacia-queiles'); ?></p>
		<?php endif; ?>

		<?php if (post_type_exists('product')) : ?>
			<?php
			$products = $this->get_promociones_initial_products($selected_products);
			?>
			<p>
				<label for="fq_promo_products"><strong><?php echo esc_html__('Productos (WooCommerce)', 'farmacia-queiles'); ?></strong></label><br>
				<select
					id="fq_promo_products"
					name="fq_promo_products[]"
					class="widefat fq-promo-products-select"
					multiple
					data-rest-url="<?php echo esc_url(rest_url('farmacia-queiles/v1/products-search')); ?>"
					data-rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
					data-placeholder="<?php echo esc_attr__('Busca productos...', 'farmacia-queiles'); ?>">
					<?php foreach ($products as $product) : ?>
						<option value="<?php echo esc_attr((string) $product->ID); ?>" <?php selected(in_array((int) $product->ID, $selected_products, true)); ?>>
							<?php echo esc_html(get_the_title($product)); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="fq-promo-help">
					<?php echo esc_html__('Se cargan los primeros 20 productos y luego puedes buscar más por AJAX.', 'farmacia-queiles'); ?>
				</span>
			</p>
		<?php else : ?>
			<p><?php echo esc_html__('WooCommerce no está activo o el tipo de contenido "product" no está disponible.', 'farmacia-queiles'); ?></p>
		<?php endif; ?>
	<?php
	}

	public function save_promociones_meta(int $post_id, WP_Post $post): void
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Guardar featured_1 y featured_2 desde el meta box nativo (sidebar)
		if (isset($_POST['fq_promo_featured_nonce']) && wp_verify_nonce((string) $_POST['fq_promo_featured_nonce'], 'fq_promo_featured_save')) {
			update_post_meta($post_id, '_fq_promo_featured_1', isset($_POST['fq_promo_featured_1']) ? '1' : '');
			update_post_meta($post_id, '_fq_promo_featured_2', isset($_POST['fq_promo_featured_2']) ? '1' : '');
		}
	}

	public function validate_promociones_subtitle(array $data, array $postarr): array
	{
		if (($data['post_type'] ?? '') !== 'promociones') {
			return $data;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $data;
		}

		$subtitle = '';
		if (isset($_POST['_fq_promo_subtitle'])) {
			$subtitle = sanitize_text_field((string) $_POST['_fq_promo_subtitle']);
		} elseif (isset($_POST['fq_promo_subtitle'])) {
			$subtitle = sanitize_text_field((string) $_POST['fq_promo_subtitle']);
		}
		$status = $data['post_status'] ?? '';

		if ($subtitle !== '' || in_array($status, ['auto-draft', 'trash'], true)) {
			return $data;
		}

		if (in_array($status, ['publish', 'future', 'pending'], true)) {
			$data['post_status'] = 'draft';
		}

		return $data;
	}

	public function add_promociones_subtitle_notice(string $location): string
	{
		$post_type = isset($_POST['post_type']) ? sanitize_key((string) $_POST['post_type']) : '';
		$subtitle = '';
		if (isset($_POST['_fq_promo_subtitle'])) {
			$subtitle = sanitize_text_field((string) $_POST['_fq_promo_subtitle']);
		} elseif (isset($_POST['fq_promo_subtitle'])) {
			$subtitle = sanitize_text_field((string) $_POST['fq_promo_subtitle']);
		}

		if ('promociones' !== $post_type || '' !== $subtitle) {
			return $location;
		}

		return add_query_arg('fq_promo_subtitle_required', '1', $location);
	}

	public function render_promociones_subtitle_notice(): void
	{
		if (!is_admin() || !isset($_GET['fq_promo_subtitle_required'])) {
			return;
		}

		$screen = get_current_screen();
		if (!$screen || 'promociones' !== $screen->post_type) {
			return;
		}
	?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html__('El subtítulo es obligatorio para guardar o publicar una promoción.', 'farmacia-queiles'); ?></p>
		</div>
	<?php
	}

	public function add_promociones_admin_columns(array $columns): array
	{
		$updated_columns = [];

		foreach ($columns as $key => $label) {
			$updated_columns[$key] = $label;

			if ('title' === $key) {
				$updated_columns['fq_promo_tipo'] = __('Tipo', 'farmacia-queiles');
			}
		}

		return $updated_columns;
	}

	public function render_promociones_admin_columns(string $column, int $post_id): void
	{
		if ('fq_promo_tipo' !== $column) {
			return;
		}

		$is_featured_1 = '1' === (string) get_post_meta($post_id, '_fq_promo_featured_1', true);
		$is_featured_2 = '1' === (string) get_post_meta($post_id, '_fq_promo_featured_2', true);

		if ($is_featured_1) {
			echo esc_html__('Destacada 1', 'farmacia-queiles');
			return;
		}

		if ($is_featured_2) {
			echo esc_html__('Destacada 2', 'farmacia-queiles');
			return;
		}

		echo esc_html__('General', 'farmacia-queiles');
	}

	public function rest_search_products(WP_REST_Request $request): WP_REST_Response
	{
		$search = sanitize_text_field((string) $request->get_param('search'));
		$page = max(1, (int) $request->get_param('page'));
		$include = $request->get_param('include');
		$include = is_array($include) ? array_values(array_filter(array_map('intval', $include))) : [];

		$args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 20,
			'paged' => $page,
			'orderby' => 'title',
			'order' => 'ASC',
			'fields' => 'ids',
		];

		if ('' !== $search) {
			$args['s'] = $search;
		}

		if (!empty($include) && '' === $search) {
			$args['post__in'] = $include;
			$args['orderby'] = 'post__in';
		}

		$query = new WP_Query($args);
		$results = [];

		foreach ($query->posts as $product_id) {
			$results[] = [
				'id' => (int) $product_id,
				'text' => get_the_title((int) $product_id),
			];
		}

		return new WP_REST_Response(
			[
				'results' => $results,
				'pagination' => [
					'more' => $query->max_num_pages > $page,
				],
			]
		);
	}

	private function get_cart_count_markup(): string
	{
		$count = 0;

		if (function_exists('WC') && WC()->cart) {
			$count = (int) WC()->cart->get_cart_contents_count();
		}

		$classes = 'util-link__badge cart-count-fragment';

		if ($count < 1) {
			$classes .= ' is-empty';
		}

		return sprintf(
			'<span class="%1$s">%2$s</span>',
			esc_attr($classes),
			esc_html((string) $count)
		);
	}

	private function get_cart_drawer_content_markup(): string
	{
		ob_start();
	?>
		<div class="site-cart-drawer__content">
			<?php woocommerce_mini_cart(); ?>
		</div>
	<?php

		return (string) ob_get_clean();
	}

	private function get_cart_drawer_footer_markup(): string
	{
		ob_start();
	?>
		<div class="site-cart-drawer__footer">
			<?php if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) : ?>
				<div class="site-cart-drawer__subtotal">
					<span><?php echo esc_html__('Subtotal', 'farmacia-queiles'); ?></span>
					<strong><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong>
				</div>
				<div class="site-cart-drawer__actions">
					<a class="site-cart-drawer__button site-cart-drawer__button--secondary" href="<?php echo esc_url(wc_get_cart_url()); ?>">
						<?php echo esc_html__('Ver carrito', 'farmacia-queiles'); ?>
					</a>
					<a class="site-cart-drawer__button" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
						<?php echo esc_html__('Finalizar compra', 'farmacia-queiles'); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
<?php

		return (string) ob_get_clean();
	}

	private function get_cart_drawer_script(): string
	{
		return <<<'JS'
(function () {
	const body = document.body;
	const drawer = document.getElementById('site-cart-drawer');

	if (!drawer) {
		return;
	}

	const setExpanded = function (isOpen) {
		document.querySelectorAll('[data-open-site-cart]').forEach(function (trigger) {
			trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	};

	const openDrawer = function () {
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		body.classList.add('site-cart-open');
		setExpanded(true);
	};

	const closeDrawer = function () {
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		body.classList.remove('site-cart-open');
		setExpanded(false);
	};

	document.addEventListener('click', function (event) {
		const openTrigger = event.target.closest('[data-open-site-cart]');
		const closeTrigger = event.target.closest('[data-close-site-cart]');

		if (openTrigger) {
			event.preventDefault();
			openDrawer();
		}

		if (closeTrigger) {
			event.preventDefault();
			closeDrawer();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeDrawer();
		}
	});

	if (window.jQuery) {
		window.jQuery(document.body).on('added_to_cart', function () {
			openDrawer();
		});
	}
})();
JS;
	}

	private function get_promociones_initial_products(array $selected_products): array
	{
		$initial_products = get_posts(
			[
				'post_type' => 'product',
				'numberposts' => 20,
				'post_status' => 'publish',
				'orderby' => 'title',
				'order' => 'ASC',
			]
		);

		if (empty($selected_products)) {
			return $initial_products;
		}

		$selected_posts = get_posts(
			[
				'post_type' => 'product',
				'post__in' => $selected_products,
				'numberposts' => -1,
				'post_status' => 'publish',
				'orderby' => 'post__in',
			]
		);

		$merged = [];

		foreach (array_merge($selected_posts, $initial_products) as $product) {
			$merged[$product->ID] = $product;
		}

		return array_values($merged);
	}

	private function get_schema_logo_url(): string
	{
		$custom_logo_id = (int) get_theme_mod('custom_logo', 0);

		if ($custom_logo_id > 0) {
			$logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');

			if (is_string($logo_url) && '' !== $logo_url) {
				return $logo_url;
			}
		}

		if (file_exists(get_template_directory() . '/assets/img/logo.svg')) {
			return get_template_directory_uri() . '/assets/img/logo.svg';
		}

		return '';
	}

	public function enqueue_product_tabs_admin_assets(string $hook): void
	{
		if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
			return;
		}
		$screen = get_current_screen();
		if (!$screen || 'product' !== $screen->post_type) {
			return;
		}

		// CSS tabs + rutina admin
		wp_enqueue_style(
			'fq-product-tabs-admin',
			get_template_directory_uri() . '/assets/css/admin/product-tabs.min.css',
			[],
			$this->version
		);

		// Select2
		wp_enqueue_style(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/css/select2.min.css',
			[],
			'4.1.0-rc.0'
		);
		wp_enqueue_script(
			'farmacia-queiles-select2',
			get_template_directory_uri() . '/assets/vendor/select2/js/select2.min.js',
			['jquery'],
			'4.1.0-rc.0',
			true
		);

		// JS rutina
		wp_enqueue_script(
			'fq-product-rutina-admin',
			get_template_directory_uri() . '/assets/js/admin/product-rutina.min.js',
			['jquery', 'farmacia-queiles-select2'],
			$this->version,
			true
		);
	}

	// ── Tabs personalizados de ficha de producto ────────────────────

	private function register_product_tabs_cmb2_box(): void
	{
		$box = new_cmb2_box([
			'id'           => 'fq_product_tabs_meta',
			'title'        => __('Contenido de pestañas', 'farmacia-queiles'),
			'object_types' => ['product'],
			'context'      => 'normal',
			'priority'     => 'high',
		]);

		$box->add_field([
			'name' => __('Composición', 'farmacia-queiles'),
			'desc' => __('Ingredientes, fórmula y activos del producto.', 'farmacia-queiles'),
			'id'   => '_fq_composicion',
			'type' => 'wysiwyg',
			'options' => ['textarea_rows' => 6, 'media_buttons' => false],
		]);

		$box->add_field([
			'name' => __('Modo de empleo', 'farmacia-queiles'),
			'desc' => __('Instrucciones de aplicación paso a paso.', 'farmacia-queiles'),
			'id'   => '_fq_modo_empleo',
			'type' => 'wysiwyg',
			'options' => ['textarea_rows' => 6, 'media_buttons' => false],
		]);

		$faqs_group = $box->add_field([
			'id'          => '_fq_faqs',
			'type'        => 'group',
			'description' => __('Añade preguntas y respuestas para la pestaña "Preguntas frecuentes".', 'farmacia-queiles'),
			'options'     => [
				'group_title'   => __('Pregunta #{#}', 'farmacia-queiles'),
				'add_button'    => __('+ Añadir pregunta', 'farmacia-queiles'),
				'remove_button' => __('Eliminar', 'farmacia-queiles'),
				'sortable'      => true,
				'closed'        => false,
			],
		]);

		$box->add_group_field($faqs_group, [
			'name'        => __('Pregunta', 'farmacia-queiles'),
			'id'          => 'fq_faq_pregunta',
			'type'        => 'text',
			'attributes'  => ['placeholder' => __('¿Es apto para pieles grasas?', 'farmacia-queiles')],
		]);

		$box->add_group_field($faqs_group, [
			'name'        => __('Respuesta', 'farmacia-queiles'),
			'id'          => 'fq_faq_respuesta',
			'type'        => 'textarea_small',
			'attributes'  => ['rows' => 3, 'placeholder' => __('Sí, su textura ultra-ligera es no comedogénica.', 'farmacia-queiles')],
		]);
	}

	public function register_product_custom_tabs(array $tabs): array
	{
		global $product;

		// Eliminar tab de valoraciones de WooCommerce
		unset($tabs['reviews']);

		if (!$product instanceof \WC_Product) {
			return $tabs;
		}

		$product_id = (int) $product->get_id();

		$composicion = (string) get_post_meta($product_id, '_fq_composicion', true);
		$modo_empleo = (string) get_post_meta($product_id, '_fq_modo_empleo', true);
		$faqs        = get_post_meta($product_id, '_fq_faqs', true);

		if ('' !== trim($composicion)) {
			$tabs['fq_composicion'] = [
				'title'    => __('Composición', 'farmacia-queiles'),
				'priority' => 20,
				'callback' => [$this, 'render_tab_composicion'],
			];
		}

		if ('' !== trim($modo_empleo)) {
			$tabs['fq_modo_empleo'] = [
				'title'    => __('Modo de empleo', 'farmacia-queiles'),
				'priority' => 30,
				'callback' => [$this, 'render_tab_modo_empleo'],
			];
		}

		if (!empty($faqs) && is_array($faqs)) {
			$has_content = false;
			foreach ($faqs as $faq) {
				if (!empty(trim((string) ($faq['fq_faq_pregunta'] ?? '')))) {
					$has_content = true;
					break;
				}
			}
			if ($has_content) {
				$tabs['fq_faqs'] = [
					'title'    => __('Preguntas frecuentes', 'farmacia-queiles'),
					'priority' => 40,
					'callback' => [$this, 'render_tab_faqs'],
				];
			}
		}

		return $tabs;
	}

	public function render_tab_composicion(string $key, array $tab): void
	{
		global $product;
		if (!$product instanceof \WC_Product) {
			return;
		}
		$content = (string) get_post_meta((int) $product->get_id(), '_fq_composicion', true);
		if ('' !== trim($content)) {
			echo '<div class="fq-sp-tab-content fq-sp-tab-content--composicion">' . wp_kses_post(wpautop($content)) . '</div>';
		}
	}

	public function render_tab_modo_empleo(string $key, array $tab): void
	{
		global $product;
		if (!$product instanceof \WC_Product) {
			return;
		}
		$content = (string) get_post_meta((int) $product->get_id(), '_fq_modo_empleo', true);
		if ('' !== trim($content)) {
			echo '<div class="fq-sp-tab-content fq-sp-tab-content--modo-empleo">' . wp_kses_post(wpautop($content)) . '</div>';
		}
	}

	public function render_tab_faqs(string $key, array $tab): void
	{
		global $product;
		if (!$product instanceof \WC_Product) {
			return;
		}
		$faqs = get_post_meta((int) $product->get_id(), '_fq_faqs', true);
		if (empty($faqs) || !is_array($faqs)) {
			return;
		}
		echo '<div class="fq-sp-tab-content fq-sp-tab-content--faqs"><dl class="fq-sp-faqs">';
		foreach ($faqs as $faq) {
			$question = trim((string) ($faq['fq_faq_pregunta'] ?? ''));
			$answer   = trim((string) ($faq['fq_faq_respuesta'] ?? ''));
			if ('' === $question) {
				continue;
			}
			echo '<div class="fq-sp-faqs__item">'
				. '<dt class="fq-sp-faqs__q">' . esc_html($question) . '</dt>'
				. '<dd class="fq-sp-faqs__a">' . wp_kses_post(nl2br(esc_html($answer))) . '</dd>'
				. '</div>';
		}
		echo '</dl></div>';
	}

	// ── Sección "Completa tu rutina" ────────────────────────────────

	private function register_product_rutina_cmb2_box(): void
	{
		add_meta_box(
			'fq_product_rutina_meta',
			__('Completa tu rutina', 'farmacia-queiles'),
			[$this, 'render_rutina_meta_box'],
			'product',
			'normal',
			'low'
		);
		add_action('save_post_product', [$this, 'save_rutina_meta'], 20);
	}

	public function render_rutina_meta_box(\WP_Post $post): void
	{
		wp_nonce_field('fq_rutina_save', 'fq_rutina_nonce');

		$kicker   = (string) get_post_meta($post->ID, '_fq_rutina_kicker', true);
		$titulo   = (string) get_post_meta($post->ID, '_fq_rutina_titulo', true);
		$sel_ids  = get_post_meta($post->ID, '_fq_rutina_productos', true);
		$sel_ids  = is_array($sel_ids) ? array_filter(array_map('intval', $sel_ids)) : [];

		// Carga los productos ya seleccionados para pre-poblar el select
		$selected_products = [];
		foreach ($sel_ids as $pid) {
			$t = get_the_title($pid);
			if ($t) {
				$selected_products[$pid] = $t;
			}
		}
		?>
		<div class="fq-rutina-admin">
			<p>
				<label for="fq_rutina_kicker"><strong><?php echo esc_html__('Kicker (texto superior en azul)', 'farmacia-queiles'); ?></strong></label><br>
				<input id="fq_rutina_kicker" name="fq_rutina_kicker" type="text"
					value="<?php echo esc_attr($kicker ?: 'CUIDADO INTEGRAL'); ?>"
					placeholder="CUIDADO INTEGRAL" class="widefat">
			</p>
			<p>
				<label for="fq_rutina_titulo"><strong><?php echo esc_html__('Título de la sección', 'farmacia-queiles'); ?></strong></label><br>
				<input id="fq_rutina_titulo" name="fq_rutina_titulo" type="text"
					value="<?php echo esc_attr($titulo ?: 'Completa tu rutina'); ?>"
					placeholder="Completa tu rutina" class="widefat">
			</p>
			<p>
				<label for="fq_rutina_productos"><strong><?php echo esc_html__('Productos de la rutina', 'farmacia-queiles'); ?></strong></label><br>
				<span class="description"><?php echo esc_html__('Añade entre 3 y 9 productos. Se mostrarán 3 aleatorios en el frontend.', 'farmacia-queiles'); ?></span>
			</p>
			<select
				id="fq_rutina_productos"
				name="fq_rutina_productos[]"
				class="fq-rutina-products-select widefat"
				multiple
				data-rest-url="<?php echo esc_url(rest_url('farmacia-queiles/v1/products-search')); ?>"
				data-rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
				data-placeholder="<?php echo esc_attr__('Busca y añade productos...', 'farmacia-queiles'); ?>">
				<?php foreach ($selected_products as $pid => $label) : ?>
					<option value="<?php echo esc_attr((string) $pid); ?>" selected>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	public function save_rutina_meta(int $post_id): void
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!isset($_POST['fq_rutina_nonce']) || !wp_verify_nonce((string) $_POST['fq_rutina_nonce'], 'fq_rutina_save')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$kicker  = isset($_POST['fq_rutina_kicker']) ? sanitize_text_field((string) $_POST['fq_rutina_kicker']) : '';
		$titulo  = isset($_POST['fq_rutina_titulo']) ? sanitize_text_field((string) $_POST['fq_rutina_titulo']) : '';
		$ids_raw = isset($_POST['fq_rutina_productos']) && is_array($_POST['fq_rutina_productos'])
			? array_values(array_filter(array_map('intval', (array) $_POST['fq_rutina_productos'])))
			: [];

		update_post_meta($post_id, '_fq_rutina_kicker', $kicker);
		update_post_meta($post_id, '_fq_rutina_titulo', $titulo);
		update_post_meta($post_id, '_fq_rutina_productos', $ids_raw);
	}

	public function render_product_rutina_section(): void
	{
		global $product;
		if (!$product instanceof \WC_Product) {
			return;
		}

		$product_id  = (int) $product->get_id();
		$ids_raw     = get_post_meta($product_id, '_fq_rutina_productos', true);
		$product_ids = is_array($ids_raw) ? array_filter(array_map('intval', $ids_raw)) : [];

		if (empty($product_ids)) {
			return;
		}

		// Mezclar aleatoriamente y coger 3
		shuffle($product_ids);
		$product_ids = array_slice($product_ids, 0, 3);

		$kicker = (string) get_post_meta($product_id, '_fq_rutina_kicker', true);
		$titulo = (string) get_post_meta($product_id, '_fq_rutina_titulo', true);
		$kicker = '' !== trim($kicker) ? $kicker : 'CUIDADO INTEGRAL';
		$titulo = '' !== trim($titulo) ? $titulo : 'Completa tu rutina';

		$products = array_filter(array_map(fn(int $id) => wc_get_product($id), $product_ids));
		if (empty($products)) {
			return;
		}
		?>
		<section class="fq-sp-rutina">
			<div class="fq-sp-rutina__head">
				<span class="fq-sp-rutina__kicker"><?php echo esc_html($kicker); ?></span>
				<h2 class="fq-sp-rutina__title"><?php echo esc_html($titulo); ?></h2>
				<div class="fq-sp-rutina__arrows" aria-hidden="true">
					<button class="fq-sp-rutina__arrow fq-sp-rutina__arrow--prev" type="button" aria-label="<?php echo esc_attr__('Anterior', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">chevron_left</span>
					</button>
					<button class="fq-sp-rutina__arrow fq-sp-rutina__arrow--next" type="button" aria-label="<?php echo esc_attr__('Siguiente', 'farmacia-queiles'); ?>">
						<span class="material-symbols-outlined">chevron_right</span>
					</button>
				</div>
			</div>
			<div class="fq-sp-rutina__grid" data-fq-rutina-track>
				<?php foreach ($products as $rutina_product) :
					/** @var WC_Product $rutina_product */
					$rp_id       = (int) $rutina_product->get_id();
					$rp_name     = (string) $rutina_product->get_name();
					$rp_url      = (string) get_permalink($rp_id);
					$rp_img      = (int) $rutina_product->get_image_id();
					$rp_img_src  = $rp_img > 0 ? (string) wp_get_attachment_image_url($rp_img, 'woocommerce_thumbnail') : wc_placeholder_img_src();
					$rp_excerpt  = wp_trim_words((string) $rutina_product->get_short_description(), 12, '…');
					$rp_regular  = (string) $rutina_product->get_regular_price();
					$rp_sale     = (string) $rutina_product->get_sale_price();
					$rp_on_sale  = (bool) $rutina_product->is_on_sale();
					$rp_price    = $rp_on_sale && '' !== $rp_sale ? $rp_sale : $rp_regular;

					// Marca
					$rp_brand = '';
					if (taxonomy_exists('product_brand')) {
						$rp_brands = get_the_terms($rp_id, 'product_brand');
						if (is_array($rp_brands) && !empty($rp_brands)) {
							$rp_brand = (string) $rp_brands[0]->name;
						}
					}
				?>
				<article class="fq-sp-rutina__card">
					<a class="fq-sp-rutina__img-wrap" href="<?php echo esc_url($rp_url); ?>" tabindex="-1" aria-hidden="true">
						<?php if ($rp_on_sale) : ?>
							<span class="fq-sp-rutina__badge"><?php echo esc_html__('TOP', 'farmacia-queiles'); ?></span>
						<?php endif; ?>
						<img src="<?php echo esc_url($rp_img_src); ?>" alt="<?php echo esc_attr($rp_name); ?>" loading="lazy">
					</a>
					<div class="fq-sp-rutina__body">
						<?php if ('' !== $rp_brand) : ?>
							<span class="fq-sp-rutina__brand"><?php echo esc_html($rp_brand); ?></span>
						<?php endif; ?>
						<h3 class="fq-sp-rutina__name"><a href="<?php echo esc_url($rp_url); ?>"><?php echo esc_html($rp_name); ?></a></h3>
						<?php if ('' !== $rp_excerpt) : ?>
							<p class="fq-sp-rutina__excerpt"><?php echo esc_html($rp_excerpt); ?></p>
						<?php endif; ?>
						<div class="fq-sp-rutina__price-row">
							<?php if ($rp_on_sale && '' !== $rp_sale) : ?>
								<strong class="fq-sp-rutina__price"><?php echo wp_kses_post(wc_price((float) $rp_sale)); ?></strong>
								<s class="fq-sp-rutina__price-old"><?php echo wp_kses_post(wc_price((float) $rp_regular)); ?></s>
							<?php elseif ('' !== $rp_regular) : ?>
								<strong class="fq-sp-rutina__price"><?php echo wp_kses_post(wc_price((float) $rp_regular)); ?></strong>
							<?php endif; ?>
							<span class="fq-sp-rutina__iva"><?php echo esc_html__('IVA INC', 'farmacia-queiles'); ?></span>
						</div>
						<a
							href="<?php echo esc_url($rutina_product->add_to_cart_url()); ?>"
							class="fq-sp-rutina__cta add_to_cart_button ajax_add_to_cart"
							data-product_id="<?php echo esc_attr((string) $rp_id); ?>"
							data-quantity="1"
							rel="nofollow"
						>
							<span class="material-symbols-outlined">shopping_bag</span>
							<?php echo esc_html__('Añadir al carrito', 'farmacia-queiles'); ?>
						</a>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	// ── Productos relacionados manuales ──────────────────────────────

	public function maybe_use_manual_related_products(array $related_ids, int $product_id, array $args): array
	{
		$manual_ids = get_post_meta($product_id, '_fq_related_productos', true);
		$manual_ids = is_array($manual_ids) ? array_filter(array_map('intval', $manual_ids)) : [];

		if (!empty($manual_ids)) {
			return $manual_ids;
		}

		return $related_ids;
	}

	private function register_product_related_meta_box(): void
	{
		add_meta_box(
			'fq_product_related_meta',
			__('Productos relacionados (manual)', 'farmacia-queiles'),
			[$this, 'render_related_meta_box'],
			'product',
			'normal',
			'low'
		);
		add_action('save_post_product', [$this, 'save_related_meta'], 20);
	}

	public function render_related_meta_box(\WP_Post $post): void
	{
		wp_nonce_field('fq_related_save', 'fq_related_nonce');

		$sel_ids = get_post_meta($post->ID, '_fq_related_productos', true);
		$sel_ids = is_array($sel_ids) ? array_filter(array_map('intval', $sel_ids)) : [];

		$selected_products = [];
		foreach ($sel_ids as $pid) {
			$t = get_the_title($pid);
			if ($t) {
				$selected_products[$pid] = $t;
			}
		}
		?>
		<div class="fq-related-admin">
			<p>
				<label for="fq_related_productos"><strong><?php echo esc_html__('Selecciona productos relacionados manualmente', 'farmacia-queiles'); ?></strong></label><br>
				<span class="description"><?php echo esc_html__('Si añades productos aquí, reemplazarán a los relacionados automáticos de WooCommerce. Déjalo vacío para usar los automáticos.', 'farmacia-queiles'); ?></span>
			</p>
			<select
				id="fq_related_productos"
				name="fq_related_productos[]"
				class="fq-related-products-select widefat"
				multiple
				data-rest-url="<?php echo esc_url(rest_url('farmacia-queiles/v1/products-search')); ?>"
				data-rest-nonce="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
				data-placeholder="<?php echo esc_attr__('Busca y selecciona productos...', 'farmacia-queiles'); ?>">
				<?php foreach ($selected_products as $pid => $label) : ?>
					<option value="<?php echo esc_attr((string) $pid); ?>" selected>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<script>
		(function($){
			if (typeof $.fn.select2 !== 'function') return;
			var $sel = $('#fq_related_productos');
			if (!$sel.length) return;
			$sel.select2({
				width: '100%',
				placeholder: $sel.data('placeholder') || 'Busca productos...',
				allowClear: true,
				ajax: {
					url: $sel.data('rest-url'),
					dataType: 'json',
					delay: 250,
					headers: { 'X-WP-Nonce': $sel.data('rest-nonce') },
					data: function(p) {
						return { search: p.term || '', page: p.page || 1 };
					},
					processResults: function(d) {
						return { results: d.results || [], pagination: d.pagination || { more: false } };
					},
					cache: true
				},
				minimumInputLength: 0,
				language: {
					noResults: function() { return 'Sin resultados'; },
					searching: function() { return 'Buscando...'; },
					loadingMore: function() { return 'Cargando más resultados...'; }
				}
			});
		})(jQuery);
		</script>
		<?php
	}

	public function save_related_meta(int $post_id): void
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!isset($_POST['fq_related_nonce']) || !wp_verify_nonce((string) $_POST['fq_related_nonce'], 'fq_related_save')) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$ids_raw = isset($_POST['fq_related_productos']) && is_array($_POST['fq_related_productos'])
			? array_values(array_filter(array_map('intval', (array) $_POST['fq_related_productos'])))
			: [];

		update_post_meta($post_id, '_fq_related_productos', $ids_raw);
	}

	// ── Imagen de cabecera para páginas secundarias ──────────────────

	private function register_page_header_cmb2_box(): void
	{
		$box = new_cmb2_box([
			'id'           => 'fq_page_header_meta',
			'title'        => __('Cabecera de página', 'farmacia-queiles'),
			'object_types' => ['page', 'post'],
			'context'      => 'side',
			'priority'     => 'low',
		]);

		$box->add_field([
			'name' => __('Imagen de fondo del hero', 'farmacia-queiles'),
			'desc' => __('Si no se selecciona se usará la imagen destacada o la imagen por defecto.', 'farmacia-queiles'),
			'id'   => '_fq_page_header_image',
			'type' => 'file',
			'options' => ['url' => false],
			'text' => [
				'add_upload_file_text' => __('Seleccionar imagen', 'farmacia-queiles'),
			],
			'preview_size' => 'medium',
		]);
	}

	private function get_schema_current_url(): string
	{
		if (is_singular()) {
			$canonical_url = wp_get_canonical_url();

			if (is_string($canonical_url) && '' !== $canonical_url) {
				return $canonical_url;
			}

			$permalink = get_permalink();

			if (is_string($permalink) && '' !== $permalink) {
				return $permalink;
			}
		}

		global $wp;

		if (isset($wp->request) && '' !== $wp->request) {
			return home_url(user_trailingslashit($wp->request));
		}

		return trailingslashit(home_url('/'));
	}

	/* ── Favoritos: AJAX handler ─────────────────────────────── */
	public function ajax_fq_favorites(): void
	{
		check_ajax_referer('fq_favorites', 'nonce');

		$action     = isset($_POST['fq_action']) ? sanitize_text_field((string) $_POST['fq_action']) : '';
		$product_id = isset($_POST['product_id']) ? absint((string) $_POST['product_id']) : 0;

		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$favs    = get_user_meta($user_id, '_fq_favorites', true);
			$favs    = is_array($favs) ? array_map('intval', $favs) : [];

			if ('get' === $action) {
				wp_send_json_success($favs);
			}

			if ('toggle' === $action && $product_id > 0) {
				$idx = array_search($product_id, $favs, true);
				if ($idx === false) {
					$favs[] = $product_id;
					$active = true;
				} else {
					array_splice($favs, (int) $idx, 1);
					$active = false;
				}
				update_user_meta($user_id, '_fq_favorites', array_values($favs));
				wp_send_json_success(['active' => $active, 'ids' => array_values($favs)]);
			}
		}

		// Usuario no logado: la cookie la gestiona el JS en el cliente
		if ('get' === $action) {
			wp_send_json_success([]);
		}

		wp_send_json_error();
	}

	/* ── Favoritos: botón en ficha de producto ───────────────── */
	public function render_single_product_fav_button(): void
	{
		global $product;
		if (!$product instanceof WC_Product) {
			return;
		}
		$pid = (int) $product->get_id();
		?>
		<button class="fq-fav-btn fq-fav-btn--single" type="button"
			data-fq-fav="<?php echo esc_attr((string) $pid); ?>"
			aria-pressed="false"
			aria-label="<?php echo esc_attr__('Guardar en favoritos', 'farmacia-queiles'); ?>">
			<span class="material-symbols-outlined" aria-hidden="true">favorite</span>
		</button>
		<?php
	}

	public function render_blog_cat_header_image_field(string $taxonomy): void
	{
		unset($taxonomy);
		?>
		<div class="form-field">
			<label><?php esc_html_e('Imagen de fondo del hero', 'farmacia-queiles'); ?></label>
			<p id="fq-blog-cat-header-preview" style="margin:0 0 6px;"></p>
			<input type="hidden" name="fq_blog_cat_header_image" id="fq-blog-cat-header-image" value="">
			<button type="button" class="button" id="fq-blog-cat-header-upload"><?php esc_html_e('Seleccionar imagen', 'farmacia-queiles'); ?></button>
			<button type="button" class="button" id="fq-blog-cat-header-remove" style="display:none;"><?php esc_html_e('Eliminar', 'farmacia-queiles'); ?></button>
			<p class="description"><?php esc_html_e('Imagen de fondo para el hero de esta categoría del blog.', 'farmacia-queiles'); ?></p>
		</div>
		<?php
	}

	public function render_blog_cat_header_image_edit_field(WP_Term $term): void
	{
		$image_url = (string) get_term_meta($term->term_id, '_fq_blog_cat_header_image', true);
		?>
		<tr class="form-field">
			<th scope="row">
				<label><?php esc_html_e('Imagen de fondo del hero', 'farmacia-queiles'); ?></label>
			</th>
			<td>
				<p id="fq-blog-cat-header-preview" style="margin:0 0 6px;">
					<?php if ('' !== $image_url) : ?>
						<img src="<?php echo esc_url($image_url); ?>" style="max-width:200px;height:auto;border-radius:6px;display:block;">
					<?php endif; ?>
				</p>
				<input type="hidden" name="fq_blog_cat_header_image" id="fq-blog-cat-header-image" value="<?php echo esc_attr($image_url); ?>">
				<button type="button" class="button" id="fq-blog-cat-header-upload"><?php esc_html_e('Seleccionar imagen', 'farmacia-queiles'); ?></button>
				<button type="button" class="button" id="fq-blog-cat-header-remove" style="<?php echo '' === $image_url ? 'display:none;' : ''; ?>"><?php esc_html_e('Eliminar', 'farmacia-queiles'); ?></button>
				<p class="description"><?php esc_html_e('Imagen de fondo para el hero de esta categoría del blog.', 'farmacia-queiles'); ?></p>
			</td>
		</tr>
		<?php
	}

	public function save_blog_cat_header_image(int $term_id): void
	{
		if (!isset($_POST['fq_blog_cat_header_image'])) {
			return;
		}

		$image_url = esc_url_raw((string) $_POST['fq_blog_cat_header_image']);

		if ('' === $image_url) {
			delete_term_meta($term_id, '_fq_blog_cat_header_image');
		} else {
			update_term_meta($term_id, '_fq_blog_cat_header_image', $image_url);
		}
	}
}

new Farmacia_Queiles_Theme();

add_filter('template_include', function($template){

    if (is_woocommerce()) {
        $new_template = locate_template('woocommerce.php');

        if ($new_template) {
            return $new_template;
        }
    }

    return $template;
}, 99);
