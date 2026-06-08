/**
 * Sistema de Pestañas para CMB2
 * Control simple: data-tab-field hace el mapping, CSS maneja display
 */

(function() {
	'use strict';

	function init() {
		const buttons = document.querySelectorAll('.faramacia-tab-button');
		const wrapper = document.querySelector('.faramacia-tabs-wrapper');

		if (!buttons.length || !wrapper) {
			setTimeout(init, 100);
			return;
		}

		// Click en botones de tab
		buttons.forEach(btn => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();

				const tabId = this.getAttribute('data-tab');

				// Actualizar clase activa en botones
				buttons.forEach(b => b.classList.remove('active'));
				this.classList.add('active');

				// Cambiar data-active en wrapper (CSS controla qué se muestra)
				wrapper.setAttribute('data-active-tab', tabId);
			});
		});	

		// Inicializar con primer tab
		wrapper.setAttribute('data-active-tab', 'tab-colores');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		setTimeout(init, 200);
	}
})();
