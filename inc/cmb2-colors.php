<?php
/**
 * Panel de Gestión del Tema con CMB2
 *
 * Estructura modular con pestañas funcionales:
 * - Colores (primario, oscuro, secundario, etc.)
 * - Topbar (contacto, horarios)
 * - Header (búsqueda)
 * - Footer (contacto, redes, email)
 *
 * Al guardar, genera automáticamente theme-settings.json en uploads
 *
 * @package faramacia-queiles
 */

error_log( '[THEME-SETTINGS] cmb2-colors.php loaded' );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Cargar personalizador del panel
require_once get_template_directory() . '/inc/cmb2-panel-custom.php';

// Cargar JS en admin_footer
add_action( 'admin_footer', 'faramacia_queiles_load_tabs_script' );

function faramacia_queiles_load_tabs_script() {
	global $pagenow;

	if ( $pagenow !== 'themes.php' || ! isset( $_GET['page'] ) || $_GET['page'] !== 'faramacia_theme_options' ) {
		return;
	}

	?>
	<script src="<?php echo get_template_directory_uri() . '/inc/cmb2-tabs.js'; ?>"></script>
	<?php
}

function faramacia_queiles_admin_panel_css() {
	return "
		.faramacia-tabs-wrapper {
			margin: 20px 0;
		}

		.faramacia-tabs-nav {
			display: flex;
			gap: 0;
			border-bottom: 2px solid #e0e0e0;
			margin-bottom: 20px;
			flex-wrap: wrap;
			padding: 0;
			list-style: none;
			background: transparent;
		}

		.faramacia-tabs-nav li {
			margin: 0;
			padding: 0;
		}

		.faramacia-tab-button {
			padding: 15px 25px;
			background: transparent;
			border: none;
			border-bottom: 3px solid transparent;
			cursor: pointer;
			transition: all 0.3s ease;
			font-weight: 500;
			font-size: 14px;
			color: #999;
			white-space: nowrap;
			user-select: none;
		}

		.faramacia-tab-button:hover {
			color: #666;
		}

		.faramacia-tab-button.active {
			color: #23282d;
			border-bottom-color: #e67e22;
			font-weight: 700;
		}


		@keyframes slideIn {
			from {
				opacity: 0;
				transform: translateY(10px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.cmb2-fieldtype-title {
			padding: 18px 0 12px 0;
			margin: 24px 0 12px 0;
			border-top: 2px solid #f0f0f0;
			font-size: 13px;
			font-weight: 700;
			color: #23282d;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.cmb2-fieldtype-title:first-of-type {
			border-top: none;
			margin-top: 0;
			padding-top: 0;
		}

		.cmb2-fieldtype-title .cmb2-metabox-description {
			color: #666;
			font-size: 12px;
			margin-top: 4px;
			font-weight: normal;
			text-transform: none;
			letter-spacing: normal;
		}

		.faramacia-tabs-wrapper .cmb-row {
			margin-bottom: 15px;
		}

		@media (max-width: 782px) {
			.faramacia-tabs-nav {
				gap: 0;
			}

			.cmb2-tab-nav-item {
				padding: 12px 15px;
				font-size: 12px;
			}
		}
	";
}

add_action( 'cmb2_admin_init', 'faramacia_queiles_register_theme_options' );

function faramacia_queiles_register_theme_options() {
	if ( ! function_exists( 'new_cmb2_box' ) ) {
		return;
	}

	// Caja principal
	$cmb = new_cmb2_box( array(
		'id'           => 'faramacia_theme_settings',
		'title'        => esc_html__( 'Configuración del Tema', 'faramacia-queiles'),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'faramacia_theme_options',
		'parent_slug'  => 'themes.php',
		'capability'   => 'manage_options',
	) );

	// ═══════════════════════════════════════════════════════════════════
	// TAB 1: COLORES
	// ═══════════════════════════════════════════════════════════════════

	$cmb->add_field( array(
		'id'   => 'tab_colors',
		'type' => 'tab',
		'name' => esc_html__( '🎨 Colores', 'faramacia-queiles' ),
		'icon' => 'dashicons-admin-appearance',
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Paleta de Colores Principal', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Define los colores principales del tema', 'faramacia-queiles' ),
		'id'   => 'colors_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Primario', 'faramacia-queiles' ),
		'id'      => 'color_primario',
		'type'    => 'colorpicker',
		'default' => '#52b2e1',
		'desc'    => esc_html__( 'Botones, enlaces, acentos principales', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Oscuro', 'faramacia-queiles' ),
		'id'      => 'color_oscuro',
		'type'    => 'colorpicker',
		'default' => '#383e42',
		'desc'    => esc_html__( 'Fondos oscuros (header, footer, topbar)', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Secundario', 'faramacia-queiles' ),
		'id'      => 'color_secundario',
		'type'    => 'colorpicker',
		'default' => '#e67e22',
		'desc'    => esc_html__( 'Acentos y destacados', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Colores de Interfaz', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Colores para elementos secundarios', 'faramacia-queiles' ),
		'id'   => 'ui_colors_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Fondo Claro', 'faramacia-queiles' ),
		'id'      => 'color_fondo_claro',
		'type'    => 'colorpicker',
		'default' => '#e1e7eb',
		'desc'    => esc_html__( 'Fondos claros, inputs, elementos deshabilitados', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Borde', 'faramacia-queiles' ),
		'id'      => 'color_borde',
		'type'    => 'colorpicker',
		'default' => '#d5dde4',
		'desc'    => esc_html__( 'Bordes y divisores', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Colores de Texto', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Define los colores del texto', 'faramacia-queiles' ),
		'id'   => 'text_colors_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Texto Principal', 'faramacia-queiles' ),
		'id'      => 'color_texto_principal',
		'type'    => 'colorpicker',
		'default' => '#383e42',
		'desc'    => esc_html__( 'Texto principal del sitio', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Color Texto Secundario', 'faramacia-queiles' ),
		'id'      => 'color_texto_secundario',
		'type'    => 'colorpicker',
		'default' => '#a3a3a3',
		'desc'    => esc_html__( 'Texto secundario, placeholders, subtítulos', 'faramacia-queiles' ),
	) );

	// ═══════════════════════════════════════════════════════════════════
	// TAB 2: TOPBAR
	// ═══════════════════════════════════════════════════════════════════

	$cmb->add_field( array(
		'id'   => 'tab_topbar',
		'type' => 'tab',
		'name' => esc_html__( '📍 Barra Superior', 'faramacia-queiles' ),
		'icon' => 'dashicons-align-top',
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Información de Contacto - Topbar', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Datos que se muestran en la barra superior del sitio', 'faramacia-queiles' ),
		'id'   => 'topbar_contact_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Teléfono', 'faramacia-queiles' ),
		'id'      => 'topbar_phone',
		'type'    => 'text',
		'default' => '976 642 685',
		'desc'    => esc_html__( 'Número de teléfono mostrado en la barra superior', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Dirección', 'faramacia-queiles' ),
		'id'      => 'topbar_address',
		'type'    => 'text',
		'default' => 'Av. Reino de Aragón 3, Tarazona',
		'desc'    => esc_html__( 'Dirección mostrada en el topbar', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Horario', 'faramacia-queiles' ),
		'id'      => 'topbar_schedule',
		'type'    => 'text',
		'default' => 'L-V 9:00-13:45 · 16:30-20:00',
		'desc'    => esc_html__( 'Horario de apertura', 'faramacia-queiles' ),
	) );

	// ═══════════════════════════════════════════════════════════════════
	// TAB 3: HEADER
	// ═══════════════════════════════════════════════════════════════════

	$cmb->add_field( array(
		'id'   => 'tab_header',
		'type' => 'tab',
		'name' => esc_html__( '🔍 Encabezado', 'faramacia-queiles' ),
		'icon' => 'dashicons-migrate',
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Buscador', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Configuración del campo de búsqueda en el header', 'faramacia-queiles' ),
		'id'   => 'header_search_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Texto Botón Buscar', 'faramacia-queiles' ),
		'id'      => 'header_search_button',
		'type'    => 'text',
		'default' => 'Buscar',
		'desc'    => esc_html__( 'Texto del botón de búsqueda', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Placeholder Búsqueda', 'faramacia-queiles' ),
		'id'      => 'header_search_placeholder',
		'type'    => 'text',
		'default' => 'Busca por producto, laboratorio, necesidad...',
		'desc'    => esc_html__( 'Texto que aparece dentro del input de búsqueda', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Comportamiento', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Opciones de visualización y funcionalidad', 'faramacia-queiles' ),
		'id'   => 'header_behavior_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Mostrar Menú Móvil', 'faramacia-queiles' ),
		'id'      => 'header_mobile_menu',
		'type'    => 'checkbox',
		'default' => true,
		'desc'    => esc_html__( 'Mostrar botón de menú en dispositivos móviles', 'faramacia-queiles' ),
	) );

	// ═══════════════════════════════════════════════════════════════════
	// TAB 4: FOOTER
	// ═══════════════════════════════════════════════════════════════════

	$cmb->add_field( array(
		'id'   => 'tab_footer',
		'type' => 'tab',
		'name' => esc_html__( '🔗 Pie de Página', 'faramacia-queiles' ),
		'icon' => 'dashicons-menu',
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Información de Contacto', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Datos de contacto que aparecen en el footer', 'faramacia-queiles' ),
		'id'   => 'footer_contact_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Dirección', 'faramacia-queiles' ),
		'id'      => 'footer_address',
		'type'    => 'text',
		'default' => 'Av. Reino de Aragón 3, 50500 Tarazona',
		'desc'    => esc_html__( 'Dirección mostrada en el pie de página', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Teléfono Principal', 'faramacia-queiles' ),
		'id'      => 'footer_phone',
		'type'    => 'text',
		'default' => '976 642 685',
		'desc'    => esc_html__( 'Teléfono mostrado en el footer', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'WhatsApp/Teléfono Secundario', 'faramacia-queiles' ),
		'id'      => 'footer_whatsapp',
		'type'    => 'text',
		'default' => '689 123 456',
		'desc'    => esc_html__( 'Número de WhatsApp o teléfono secundario', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Horario de Atención', 'faramacia-queiles' ),
		'id'      => 'footer_schedule',
		'type'    => 'textarea',
		'default' => "L-V: 9:00-13:45 · 16:30-20:00\nSáb: 9:00-13:45",
		'desc'    => esc_html__( 'Horario de atención (usa saltos de línea para múltiples líneas)', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Comunicación', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Información para contactar por email', 'faramacia-queiles' ),
		'id'   => 'footer_communication_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Email de Contacto', 'faramacia-queiles' ),
		'id'      => 'footer_email',
		'type'    => 'text_email',
		'default' => 'info@farmaciaqueles.es',
		'desc'    => esc_html__( 'Email de contacto (aparece en redes y footer)', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Redes Sociales', 'faramacia-queiles' ),
		'desc' => esc_html__( 'Enlaces a perfiles en redes sociales', 'faramacia-queiles' ),
		'id'   => 'footer_social_title',
		'type' => 'title',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Instagram', 'faramacia-queiles' ),
		'id'      => 'footer_instagram',
		'type'    => 'text_url',
		'default' => '#',
		'desc'    => esc_html__( 'URL completa de tu perfil de Instagram', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Facebook', 'faramacia-queiles' ),
		'id'      => 'footer_facebook',
		'type'    => 'text_url',
		'default' => '#',
		'desc'    => esc_html__( 'URL completa de tu página de Facebook', 'faramacia-queiles' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Twitter/X', 'faramacia-queiles' ),
		'id'      => 'footer_twitter',
		'type'    => 'text_url',
		'default' => '#',
		'desc'    => esc_html__( 'URL completa de tu perfil en Twitter/X', 'faramacia-queiles' ),
	) );
}

// Hook que se ejecuta después de guardar CMB2
add_action( 'cmb2_save_options-page_values', 'faramacia_queiles_save_settings_json', 10, 4 );

function faramacia_queiles_save_settings_json( $object_id, $updated, $cmb, $object_data ) {
	// Solo ejecutar para nuestra opción
	$option_key = 'faramacia_theme_options';
	$cmb_option_key = $cmb->prop( 'option_key' );
	if ( isset( $cmb_option_key ) && $cmb_option_key !== $option_key ) {
		return;
	}

	error_log( '[THEME-SETTINGS] faramacia_queiles_save_settings_json called' );
	error_log( '[THEME-SETTINGS]   object_id: ' . print_r( $object_id, true ) );
	error_log( '[THEME-SETTINGS]   updated: ' . print_r( $updated, true ) );
	error_log( '[THEME-SETTINGS]   cmb option_key: ' . print_r( $cmb->prop( 'option_key' ), true ) );
	error_log( '[THEME-SETTINGS]   object_data keys: ' . print_r( array_keys( $object_data ), true ) );

	$theme_options = get_option( $option_key, array() );
	
	error_log( '[THEME-SETTINGS] Retrieved theme options from DB: ' . print_r( $theme_options, true ) );

	// Also try to get the sanitized values from CMB2
	$cmb_data = $cmb->get_data();
	error_log( '[THEME-SETTINGS] CMB data: ' . print_r( $cmb_data, true ) );

	// Use the CMB data if available, otherwise fallback to get_option
	$data_to_use = ! empty( $cmb_data ) ? $cmb_data : $theme_options;
	
	error_log( '[THEME-SETTINGS] Using data source: ' . ( ! empty( $cmb_data ) ? 'CMB' : 'DB' ) );

	// Estructura de datos estática
	$settings = array(
		'colors' => array(
			'primario'         => $data_to_use['color_primario'] ?? '#52b2e1',
			'oscuro'           => $data_to_use['color_oscuro'] ?? '#383e42',
			'secundario'       => $data_to_use['color_secundario'] ?? '#e67e22',
			'fondo_claro'      => $data_to_use['color_fondo_claro'] ?? '#e1e7eb',
			'borde'            => $data_to_use['color_borde'] ?? '#d5dde4',
			'texto_principal'  => $data_to_use['color_texto_principal'] ?? '#383e42',
			'texto_secundario' => $data_to_use['color_texto_secundario'] ?? '#a3a3a3',
		),
		'topbar' => array(
			'phone'    => $data_to_use['topbar_phone'] ?? '976 642 685',
			'address'  => $data_to_use['topbar_address'] ?? 'Av. Reino de Aragón 3, Tarazona',
			'schedule' => $data_to_use['topbar_schedule'] ?? 'L-V 9:00-13:45 · 16:30-20:00',
		),
		'header' => array(
			'search_button'     => $data_to_use['header_search_button'] ?? 'Buscar',
			'search_placeholder' => $data_to_use['header_search_placeholder'] ?? 'Busca por producto, laboratorio, necesidad...',
			'mobile_menu'       => $data_to_use['header_mobile_menu'] ?? true,
		),
		'footer' => array(
			'address'  => $data_to_use['footer_address'] ?? 'Av. Reino de Aragón 3, 50500 Tarazona',
			'phone'    => $data_to_use['footer_phone'] ?? '976 642 685',
			'whatsapp' => $data_to_use['footer_whatsapp'] ?? '689 123 456',
			'schedule' => $data_to_use['footer_schedule'] ?? "L-V: 9:00-13:45 · 16:30-20:00\nSáb: 9:00-13:45",
			'email'    => $data_to_use['footer_email'] ?? 'info@farmaciaqueles.es',
			'instagram' => $data_to_use['footer_instagram'] ?? '#',
			'facebook' => $data_to_use['footer_facebook'] ?? '#',
			'twitter'  => $data_to_use['footer_twitter'] ?? '#',
		),
	);

	error_log( '[THEME-SETTINGS] Settings array to encode: ' . print_r( $settings, true ) );

	// Obtener ruta de uploads
	$uploads_dir = wp_upload_dir();
	
	if ( false === $uploads_dir ) {
		error_log( '[THEME-SETTINGS] ERROR: wp_upload_dir() returned false' );
		return;
	}
	
	if ( ! isset( $uploads_dir['basedir'] ) ) {
		error_log( '[THEME-SETTINGS] ERROR: wp_upload_dir() missing basedin key. Result: ' . print_r( $uploads_dir, true ) );
		return;
	}
	
	$cache_file  = $uploads_dir['basedir'] . '/theme-settings.json';
	
	error_log( '[THEME-SETTINGS] Uploads basedir: ' . $uploads_dir['basedir'] );
	error_log( '[THEME-SETTINGS] Cache file path: ' . $cache_file );

	// Check if uploads directory is writable
	if ( ! is_writable( $uploads_dir['basedir'] ) ) {
		error_log( '[THEME-SETTINGS] ERROR: Uploads directory is not writable: ' . $uploads_dir['basedir'] );
		return;
	}

	// Generar JSON
	$json_content = wp_json_encode( $settings, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	
	if ( $json_content === false ) {
		error_log( '[THEME-SETTINGS] ERROR: wp_json_encode failed' );
		return;
	}
	
	error_log( '[THEME-SETTINGS] JSON content generated (length: ' . strlen( $json_content ) . ')' );

	$result = file_put_contents( $cache_file, $json_content );
	
	if ( $result === false ) {
		error_log( '[THEME-SETTINGS] ERROR: file_put_contents failed for ' . $cache_file );
		error_log( '[THEME-SETTINGS] PHP error: ' . print_r( error_get_last(), true ) );
		return;
	}
	
	error_log( '[THEME-SETTINGS] SUCCESS: File written. Bytes written: ' . $result );
	error_log( '[THEME-SETTINGS] File exists after write: ' . ( file_exists( $cache_file ) ? 'yes' : 'no' ) );
}

// Función para obtener un color (lee del JSON, fallback a BD)
function faramacia_get_color( $color_key ) {
	$uploads_dir = wp_upload_dir();
	$cache_file  = $uploads_dir['basedir'] . '/theme-settings.json';

	// Intentar leer del JSON
	if ( file_exists( $cache_file ) ) {
		$json_content = file_get_contents( $cache_file );
		$settings     = json_decode( $json_content, true );

		if ( isset( $settings['colors'][ $color_key ] ) ) {
			return $settings['colors'][ $color_key ];
		}
	}

	// Fallback: leer de la BD
	$theme_options = get_option( 'faramacia_theme_options', array() );
	$color_key_map = 'color_' . $color_key;

	return $theme_options[ $color_key_map ] ?? '';
}

// Función para obtener la URL de caché JSON (fallback a BD)
function faramacia_get_theme_data( $key, $default = '' ) {
	$uploads_dir = wp_upload_dir();
	$cache_file  = $uploads_dir['basedir'] . '/theme-settings.json';

	// Intentar leer del JSON
	if ( file_exists( $cache_file ) ) {
		$json_content = file_get_contents( $cache_file );
		$settings     = json_decode( $json_content, true );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}
	}

	// Fallback: leer de la BD
	$theme_options = get_option( 'faramacia_theme_options', array() );
	return $theme_options[ $key ] ?? $default;
}

