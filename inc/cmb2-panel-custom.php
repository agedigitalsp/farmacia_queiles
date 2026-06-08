<?php
/**
 * Renderizador personalizado para el panel de opciones del tema
 *
 * @package faramacia-queiles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'cmb2_before_form', 'faramacia_queiles_render_tabs_header', 10, 4 );

function faramacia_queiles_render_tabs_header( $cmb_id, $object_id, $object_type, $cmb ) {
	if ( 'faramacia_theme_settings' !== $cmb_id ) {
		return;
	}
	?>
	<style>
		.faramacia-tabs-nav-wrapper {
			display: flex;
			border-bottom: 2px solid #e0e0e0;
			margin: 20px 0;
			gap: 0;
		}
		.faramacia-tab-button {
			padding: 15px 25px;
			background: transparent;
			border: none;
			border-bottom: 3px solid transparent;
			cursor: pointer;
			font-weight: 500;
			font-size: 14px;
			color: #999;
			transition: all 0.3s ease;
		}
		.faramacia-tab-button:hover {
			color: #666;
		}
		.faramacia-tab-button.active {
			color: #23282d;
			border-bottom-color: #e67e22;
			font-weight: 700;
		}
	</style>
	<div class="faramacia-tabs-nav-wrapper">
		<button class="faramacia-tab-button active" data-tab="tab-colores">Colores</button>
		<button class="faramacia-tab-button" data-tab="tab-topbar">Barra Superior</button>
		<button class="faramacia-tab-button" data-tab="tab-header">Encabezado</button>
		<button class="faramacia-tab-button" data-tab="tab-footer">Pie de Página</button>
	</div>
	<div class="faramacia-tabs-wrapper">
	<?php
}

add_action( 'cmb2_after_form', 'faramacia_queiles_render_tabs_footer', 10, 4 );

function faramacia_queiles_render_tabs_footer( $cmb_id, $object_id, $object_type, $cmb ) {
	if ( 'faramacia_theme_settings' !== $cmb_id ) {
		return;
	}
	?>
	</div>
	<?php
}

