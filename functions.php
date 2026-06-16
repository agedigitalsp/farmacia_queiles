<?php

if (!defined('ABSPATH')) {
	exit;
}

final class Farmacia_Queiles_Theme
{
	private string $version;

	public function __construct()
	{
		$version = wp_get_theme()->get('Version');
		$this->version = is_string($version) && $version !== '' ? $version : '1.0.0';

		add_action('after_setup_theme', [$this, 'setup']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		add_action('widgets_init', [$this, 'widgets_init']);
		add_action('customize_register', [$this, 'customize_register']);
		add_action('wp_head', [$this, 'render_schema_markup'], 20);
		// add_action('wp_footer', [$this, 'render_cart_drawer']); // Deshabilitado: usamos el mini cart de Superplus
		// add_filter('woocommerce_add_to_cart_fragments', [$this, 'update_cart_fragments']); // Deshabilitado: Superplus maneja esto
		add_filter('nav_menu_link_attributes', [$this, 'filter_nav_menu_link_attributes'], 10, 4);
		add_action('product_cat_add_form_fields', [$this, 'render_featured_product_cat_add_field']);
		add_action('product_cat_edit_form_fields', [$this, 'render_featured_product_cat_edit_field']);
		add_action('created_product_cat', [$this, 'save_featured_product_cat_meta']);
		add_action('edited_product_cat', [$this, 'save_featured_product_cat_meta']);
		add_action('product_brand_add_form_fields', [$this, 'render_featured_product_brand_add_field']);
		add_action('product_brand_edit_form_fields', [$this, 'render_featured_product_brand_edit_field']);
		add_action('created_product_brand', [$this, 'save_featured_product_brand_meta']);
		add_action('edited_product_brand', [$this, 'save_featured_product_brand_meta']);
		add_filter('manage_edit-product_cat_columns', [$this, 'add_featured_product_cat_column']);
		add_filter('manage_edit-product_brand_columns', [$this, 'add_featured_product_brand_column']);
		add_filter('manage_product_cat_custom_column', [$this, 'render_featured_product_cat_column'], 10, 3);
		add_filter('manage_product_brand_custom_column', [$this, 'render_featured_product_brand_column'], 10, 3);
		add_action('wp_ajax_fq_toggle_featured_term', [$this, 'ajax_toggle_featured_term']);
		add_action('init', [$this, 'register_promociones_cpt']);
		add_action('init', [$this, 'customize_brand_taxonomy'], 99);
		add_action('rest_api_init', [$this, 'register_promociones_rest_routes']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_promociones_admin_assets']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_term_featured_admin_assets']);
		add_action('add_meta_boxes', [$this, 'register_promociones_meta_boxes']);
		add_action('save_post_promociones', [$this, 'save_promociones_meta'], 10, 2);
		add_filter('manage_promociones_posts_columns', [$this, 'add_promociones_admin_columns']);
		add_action('manage_promociones_posts_custom_column', [$this, 'render_promociones_admin_columns'], 10, 2);
		add_action('after_switch_theme', [$this, 'schedule_rewrite_flush']);
		add_action('admin_init', [$this, 'maybe_flush_rewrite_rules']);
		add_filter('wp_insert_post_data', [$this, 'validate_promociones_subtitle'], 10, 2);
		add_filter('redirect_post_location', [$this, 'add_promociones_subtitle_notice']);
		add_action('admin_notices', [$this, 'render_promociones_subtitle_notice']);
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
			]
		);

		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');
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
			wp_enqueue_script(
				'farmacia-queiles-home-hero',
				get_template_directory_uri() . '/assets/js/home-hero-promotions.min.js',
				[],
				$this->version,
				true
			);
			wp_enqueue_script(
				'farmacia-queiles-home-labs',
				get_template_directory_uri() . '/assets/js/home-labs-stories.min.js',
				[],
				$this->version,
				true
			);
		}

		// Deshabilitado: Superplus maneja todo el carrito
		// if (class_exists('WooCommerce')) {
		//	wp_enqueue_script('wc-cart-fragments');
		//	wp_add_inline_script('wc-cart-fragments', $this->get_cart_drawer_script());
		// }
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
		$wp_customize->add_setting(
			'farmacia_queiles_footer_logo',
			[
				'default' => 0,
				'sanitize_callback' => 'absint',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'farmacia_queiles_footer_logo',
				[
					'label' => __('Imagen del footer', 'farmacia-queiles'),
					'section' => 'title_tagline',
					'mime_type' => 'image',
				]
			)
		);

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
			'farmacia_queiles_home_labs',
			[
				'title' => __('Home - Laboratorios de confianza', 'farmacia-queiles'),
				'priority' => 33,
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
				<?php echo $this->get_cart_drawer_content_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo $this->get_cart_drawer_footer_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
		<?php
	}

	public function save_featured_product_cat_meta(int $term_id): void
	{
		$this->save_featured_term_meta($term_id, 'product_cat', '_fq_featured_product_cat', 'fq_featured_product_cat');
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
		<?php
	}

	public function render_featured_product_brand_edit_field(WP_Term $term): void
	{
		$is_featured = '1' === (string) get_term_meta($term->term_id, '_fq_featured_product_brand', true);
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
		if ('edit-tags.php' !== $hook) {
			return;
		}

		$screen = get_current_screen();

		if (!$screen || !in_array($screen->taxonomy, ['product_cat', 'product_brand'], true)) {
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
			wp_send_json_success(['value' => '1']);
		}

		delete_term_meta($term_id, $meta_key);
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
			return;
		}

		update_term_meta($term_id, $meta_key, '1');
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
		$phone_text = (string) get_theme_mod('farmacia_queiles_phone_text', '976 642 685');
		$address_text = (string) get_theme_mod('farmacia_queiles_address_text', 'Av. Reino de Aragón 3, Tarazona');
		$address_url = (string) get_theme_mod('farmacia_queiles_address_url', '');
		$schedule_text = (string) get_theme_mod('farmacia_queiles_schedule_text', 'L-V 9:00-13:45 · 16:30-20:00');
		$brand_text = (string) get_theme_mod('farmacia_queiles_footer_brand_text', '');
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

		$graph[] = $webpage;

		$schema = [
			'@context' => 'https://schema.org',
			'@graph' => $graph,
		];

		echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
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

		return [
			'featured' => $featured_terms,
			'more' => $remaining_terms,
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
					data-placeholder="<?php echo esc_attr__('Busca productos...', 'farmacia-queiles'); ?>"
				>
					<?php foreach ($products as $product) : ?>
						<option value="<?php echo esc_attr((string) $product->ID); ?>"<?php selected(in_array((int) $product->ID, $selected_products, true)); ?>>
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

		if (!isset($_POST['farmacia_queiles_promociones_nonce']) || !wp_verify_nonce((string) $_POST['farmacia_queiles_promociones_nonce'], 'farmacia_queiles_promociones_save')) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$subtitle = isset($_POST['fq_promo_subtitle']) ? sanitize_text_field((string) $_POST['fq_promo_subtitle']) : '';
		$description = isset($_POST['fq_promo_description']) ? sanitize_textarea_field((string) $_POST['fq_promo_description']) : '';
		$product_cat = isset($_POST['fq_promo_product_cat']) ? sanitize_text_field((string) $_POST['fq_promo_product_cat']) : '';
		$product_brand = isset($_POST['fq_promo_product_brand']) ? sanitize_text_field((string) $_POST['fq_promo_product_brand']) : '';
		$featured_1 = isset($_POST['fq_promo_featured_1']) ? '1' : '';
		$featured_2 = isset($_POST['fq_promo_featured_2']) ? '1' : '';
		$products = isset($_POST['fq_promo_products']) && is_array($_POST['fq_promo_products']) ? array_map('intval', (array) $_POST['fq_promo_products']) : [];
		$products = array_values(array_filter($products, static fn($id) => $id > 0));

		update_post_meta($post_id, '_fq_promo_subtitle', $subtitle);
		update_post_meta($post_id, '_fq_promo_description', $description);
		update_post_meta($post_id, '_fq_promo_product_cat', $product_cat);
		update_post_meta($post_id, '_fq_promo_product_brand', $product_brand);
		update_post_meta($post_id, '_fq_promo_featured_1', $featured_1);
		update_post_meta($post_id, '_fq_promo_featured_2', $featured_2);
		update_post_meta($post_id, '_fq_promo_products', $products);
	}

	public function validate_promociones_subtitle(array $data, array $postarr): array
	{
		if (($data['post_type'] ?? '') !== 'promociones') {
			return $data;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $data;
		}

		$subtitle = isset($_POST['fq_promo_subtitle']) ? sanitize_text_field((string) $_POST['fq_promo_subtitle']) : '';
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
		$subtitle = isset($_POST['fq_promo_subtitle']) ? sanitize_text_field((string) $_POST['fq_promo_subtitle']) : '';

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

		$footer_logo_id = (int) get_theme_mod('farmacia_queiles_footer_logo', 0);

		if ($footer_logo_id > 0) {
			$logo_url = wp_get_attachment_image_url($footer_logo_id, 'full');

			if (is_string($logo_url) && '' !== $logo_url) {
				return $logo_url;
			}
		}

		return '';
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

}

new Farmacia_Queiles_Theme();
