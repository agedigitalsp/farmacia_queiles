// fq-guardia-popup.js
(function ($) {

    var SP_OVERLAY_ID = 'sp-global-overlay';
    var errorTimer    = null;

    function sp_get_overlay() {
        var $overlay = $('#' + SP_OVERLAY_ID);
        if (!$overlay.length) {
            $overlay = $('<div id="' + SP_OVERLAY_ID + '" class="fq-guardia-popup-overlay"></div>');
            $('body').append($overlay);
            $overlay.on('click', function (e) {
                if ($(e.target).is($overlay)) {
                    $('.fq-guardia-popup--visible').each(function () {
                        // BUG FIX: era window.SP_Popup.cerrar() — no existía ese método
                        window.SP_Popup.cerrar_sp_popup($(this).data('fq-guardia-popup-id'));
                    });
                }
            });
        }
        return $overlay;
    }

    // Objeto global — se define ANTES de los aliases para garantizar que exista
    window.SP_Popup = {

        crear_sp_popup: function (id, titulo, htmlContenido, callbackOnReady) {
            $('#fq-guardia-popup-' + id).remove();
            var $overlay = sp_get_overlay();

            var $popup = $(
                '<div class="fq-guardia-popup" id="fq-guardia-popup-' + id + '" data-fq-guardia-popup-id="' + id + '">' +
                    '<div class="fq-guardia-popup__head">' +
                        '<h2 class="fq-guardia-popup__title">' + titulo + '</h2>' +
                        '<button class="fq-guardia-popup__close" type="button">&times;</button>' +
                    '</div>' +
                    '<div class="fq-guardia-popup-error-banner"></div>' +
                    '<div class="fq-guardia-popup__body">' + htmlContenido + '</div>' +
                '</div>'
            );

            $overlay.append($popup);
            $overlay.addClass('fq-guardia-popup-overlay--visible');
            setTimeout(function () { $popup.addClass('fq-guardia-popup--visible'); }, 10);

            // BUG FIX: era window.SP_Popup.cerrar(id) — método inexistente
            $popup.find('.fq-guardia-popup__close').on('click', function () {
                window.SP_Popup.cerrar_sp_popup(id);
            });

            $(document).off('keydown.sp_popup_' + id).on('keydown.sp_popup_' + id, function (e) {
                if (e.key === 'Escape') window.SP_Popup.cerrar_sp_popup(id);
            });

            if (typeof callbackOnReady === 'function') callbackOnReady();
        },

        cerrar_sp_popup: function (id) {
            var $popup = $('#fq-guardia-popup-' + id);
            if (!$popup.length) return;
            $popup.removeClass('fq-guardia-popup--visible');
            $(document).off('keydown.sp_popup_' + id);
            setTimeout(function () {
                $popup.remove();
                if ($('.fq-guardia-popup--visible').length === 0) {
                    sp_get_overlay().removeClass('fq-guardia-popup-overlay--visible');
                }
            }, 220);
        },

        sp_actualizar_popup: function (id, titulo, htmlContenido, callback) {
            var $popup = $('#fq-guardia-popup-' + id);
            if (!$popup.length) return;

            $popup.find('.fq-guardia-popup__title').text(titulo);
            $popup.find('.fq-guardia-popup-error-banner')
                  .removeClass('fq-guardia-popup-error-banner--visible')
                  .hide();

            var $body = $popup.find('.fq-guardia-popup__body');
            $body.css({ opacity: 0 });
            setTimeout(function () {
                $body.html(htmlContenido).css({ opacity: 1 });
                if (typeof callback === 'function') callback();
            }, 150);
        },

        sp_mostrar_error: function (id, mensaje) {
            var $popup = $('#fq-guardia-popup-' + id);
            if (!$popup.length) return;

            var $banner = $popup.find('.fq-guardia-popup-error-banner');
            clearTimeout(errorTimer);

            $banner.html(
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                '<span>' + mensaje + '</span>'
            ).addClass('fq-guardia-popup-error-banner--visible');

            errorTimer = setTimeout(function () {
                $banner.removeClass('fq-guardia-popup-error-banner--visible');
                setTimeout(function () { $banner.hide(); }, 300);
            }, 4000);
        }
    };

    // Aliases globales — se definen DESPUÉS de SP_Popup para garantizar que existan
    window.crear_sp_popup      = function (id, titulo, html, cb) { window.SP_Popup.crear_sp_popup(id, titulo, html, cb); };
    window.cerrar_sp_popup     = function (id)                   { window.SP_Popup.cerrar_sp_popup(id); };
    window.sp_actualizar_popup = function (titulo, html, cb)     { window.SP_Popup.sp_actualizar_popup('principal', titulo, html, cb); };
    window.sp_mostrar_error    = function (msg)                  { window.SP_Popup.sp_mostrar_error('principal', msg); };

    // Mobile dropdown toggle
    $(function() {
        var $toggle = $('.preheader-cta-mobile-toggle');
        var $dropdown = $('.preheader-cta-mobile-dropdown');

        $toggle.on('click', function(e) {
            e.preventDefault();
            $toggle.toggleClass('active');
            $dropdown.toggleClass('active');
        });

        // Cerrar dropdown al clickear un item
        $dropdown.on('click', 'a', function() {
            $toggle.removeClass('active');
            $dropdown.removeClass('active');
        });

        // Cerrar dropdown al clickear fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.site-preheader__right').length) {
                $toggle.removeClass('active');
                $dropdown.removeClass('active');
            }
        });
    });

    // Evento para abrir popup de Farmacias de Guardia
    $(function() {
        $(document).on('click', '[data-open-guardia-popup="true"]', function(e) {
            e.preventDefault();
            var iframeHtml = '<iframe src="https://farmaciasguardia.farmaceuticos.com/web_guardias/publico/Provincia_pNew.asp?id=50"></iframe>';
            window.SP_Popup.crear_sp_popup('guardia', 'Farmacias de Guardia', iframeHtml);
        });
    });

}(jQuery));
