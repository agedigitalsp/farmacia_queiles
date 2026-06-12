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
		add_action('wp_footer', [$this, 'render_cart_drawer']);
		add_filter('woocommerce_add_to_cart_fragments', [$this, 'update_cart_fragments']);
		add_action('init', [$this, 'register_promociones_cpt']);
		add_action('add_meta_boxes', [$this, 'register_promociones_meta_boxes']);
		add_action('save_post_promociones', [$this, 'save_promociones_meta'], 10, 2);
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

		if (class_exists('WooCommerce')) {
			wp_enqueue_script('wc-cart-fragments');
			wp_add_inline_script('wc-cart-fragments', $this->get_cart_drawer_script());
		}
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

	public function sanitize_url(string $value): string
	{
		return esc_url_raw($value);
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

	public function render_promociones_meta_box(WP_Post $post): void
	{
		wp_nonce_field('farmacia_queiles_promociones_save', 'farmacia_queiles_promociones_nonce');

		$subtitle = (string) get_post_meta($post->ID, '_fq_promo_subtitle', true);
		$description = (string) get_post_meta($post->ID, '_fq_promo_description', true);
		$selected_cat = (string) get_post_meta($post->ID, '_fq_promo_product_cat', true);
		$selected_products = get_post_meta($post->ID, '_fq_promo_products', true);
		$selected_products = is_array($selected_products) ? array_map('intval', $selected_products) : [];

		?>
		<p>
			<label for="fq_promo_subtitle"><strong><?php echo esc_html__('Subtítulo', 'farmacia-queiles'); ?></strong></label><br>
			<input id="fq_promo_subtitle" name="fq_promo_subtitle" type="text" value="<?php echo esc_attr($subtitle); ?>" class="widefat">
		</p>
		<p>
			<label for="fq_promo_description"><strong><?php echo esc_html__('Descripción', 'farmacia-queiles'); ?></strong></label><br>
			<textarea id="fq_promo_description" name="fq_promo_description" rows="5" class="widefat"><?php echo esc_textarea($description); ?></textarea>
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

		<?php if (post_type_exists('product')) : ?>
			<?php
			$products = get_posts(
				[
					'post_type' => 'product',
					'numberposts' => 200,
					'post_status' => 'publish',
					'orderby' => 'title',
					'order' => 'ASC',
				]
			);
			?>
			<p>
				<label for="fq_promo_products"><strong><?php echo esc_html__('Productos (WooCommerce)', 'farmacia-queiles'); ?></strong></label><br>
				<select id="fq_promo_products" name="fq_promo_products[]" class="widefat" multiple size="10">
					<?php foreach ($products as $product) : ?>
						<option value="<?php echo esc_attr((string) $product->ID); ?>"<?php selected(in_array((int) $product->ID, $selected_products, true)); ?>>
							<?php echo esc_html(get_the_title($product)); ?>
						</option>
					<?php endforeach; ?>
				</select>
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
		$products = isset($_POST['fq_promo_products']) && is_array($_POST['fq_promo_products']) ? array_map('intval', (array) $_POST['fq_promo_products']) : [];
		$products = array_values(array_filter($products, static fn($id) => $id > 0));

		update_post_meta($post_id, '_fq_promo_subtitle', $subtitle);
		update_post_meta($post_id, '_fq_promo_description', $description);
		update_post_meta($post_id, '_fq_promo_product_cat', $product_cat);
		update_post_meta($post_id, '_fq_promo_products', $products);
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
}

new Farmacia_Queiles_Theme();
