/**
 * Sistema de Pestañas CMB2 + Inyección de estilos
 */

(function() {
	'use strict';

	const fieldsByTab = {
		'tab-colores': [
			'color_primario', 'color_oscuro', 'color_secundario',
			'color_fondo_claro', 'color_borde', 'color_texto_principal', 'color_texto_secundario'
		],
		'tab-topbar': [
			'topbar_phone', 'topbar_address', 'topbar_schedule'
		],
		'tab-header': [
			'header_search_button', 'header_search_placeholder', 'header_mobile_menu'
		],
		'tab-footer': [
			'footer_address', 'footer_phone', 'footer_whatsapp', 'footer_schedule',
			'footer_email', 'footer_instagram', 'footer_facebook', 'footer_twitter'
		]
	};

	function injectStyles() {
		const style = document.createElement('style');
		style.textContent = `
			.faramacia-tabs-nav-wrapper {
				display: flex !important;
				background: linear-gradient(135deg, #f8f9fa 0%, #f0f1f3 100%) !important;
				border-radius: 12px 12px 0 0 !important;
				margin: 40px 0 0 0 !important;
				gap: 8px !important;
				padding: 8px !important;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
				border: none !important;
			}
			.faramacia-tab-button {
				padding: 14px 24px !important;
				background: transparent !important;
				border: none !important;
				border-radius: 10px !important;
				cursor: pointer !important;
				font-weight: 500 !important;
				font-size: 14px !important;
				color: #000000ff !important;
				transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
				margin: 0 !important;
				white-space: nowrap !important;
				display: flex !important;
				align-items: center !important;
				gap: 6px !important;
			}
			.faramacia-tab-button:hover {
				background-color: rgba(59, 130, 246, 0.08) !important;
				color: #252525ff !important;
			}
			.faramacia-tab-button.active {
				background: linear-gradient(135deg, #f0cdcd2d 0%, #dabdbd34 100%) !important;
				color: black !important;
				font-weight: 600 !important;
				box-shadow: 0 4px 12px rgba(182, 182, 182, 0.3) !important;
			}
			.faramacia-tabs-wrapper {
				background: white !important;
				border-radius: 0 0 12px 12px !important;
				padding: 32px !important;
				box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05), 0 -2px 8px rgba(0, 0, 0, 0.02) !important;
				border: none !important;
				margin: 0 !important;
			}
			.faramacia-tabs-wrapper .cmb2-wrap {
				background: transparent !important;
				border: none !important;
				box-shadow: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			.faramacia-tabs-wrapper .cmb2-metabox {
				background: transparent !important;
				border: none !important;
				box-shadow: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			.faramacia-tabs-wrapper .cmb-row {
				animation: fadeIn 0.3s ease-out !important;
				border-bottom: 1px solid #f0f0f0 !important;
			}
			.faramacia-tabs-wrapper .cmb-row:last-child {
				border-bottom: none !important;
			}
			@keyframes fadeIn {
				from {
					opacity: 0;
					transform: translateY(-4px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
		`;
		document.head.appendChild(style);
	}

	function findInput(fieldId) {
		let input = document.getElementById(fieldId);
		if (input) return input;

		input = document.getElementById(fieldId + '_cmb');
		if (input) return input;

		input = document.querySelector(`[name*="${fieldId}"]`);
		if (input) return input;

		return null;
	}

	function init() {
		const buttons = document.querySelectorAll('.faramacia-tab-button');
		const wrapper = document.querySelector('.faramacia-tabs-wrapper');

		if (!buttons.length || !wrapper) {
			setTimeout(init, 100);
			return;
		}

		// Inyectar estilos AHORA
		injectStyles();

		// Ocultar TODO
		const allRows = wrapper.querySelectorAll('.cmb-row');
		allRows.forEach(row => row.style.display = 'none');

		// Mostrar solo tab-colores al inicio
		showTab('tab-colores');

		// Event listeners
		buttons.forEach(btn => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				const tabId = this.getAttribute('data-tab');

				// Actualizar botones activos
				buttons.forEach(b => b.classList.remove('active'));
				this.classList.add('active');

				// Ocultar todo
				allRows.forEach(row => row.style.display = 'none');

				// Mostrar solo el tab seleccionado
				showTab(tabId);
			});
		});
	}

	function showTab(tabId) {
		const wrapper = document.querySelector('.faramacia-tabs-wrapper');
		const fields = fieldsByTab[tabId];
		fields.forEach(fieldId => {
			const input = findInput(fieldId);
			if (input) {
				const row = input.closest('.cmb-row');
				if (row) {
					row.style.display = 'table-row';
				}
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		setTimeout(init, 200);
	}
})();
