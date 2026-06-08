<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package faramacia-queiles
 */

?>

</div>
<footer id="colophon" class="bg-[var(--color-oscuro)] text-white site-footer">
	<div class="max-w-[1450px] mx-auto px-4 md:px-8 py-12 md:py-16">
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-8 md:gap-10 mb-10 md:mb-12">

			<div class="sm:col-span-2 lg:col-span-4">
				<div class="flex items-center gap-3 mb-5">
					<?php
					$custom_logo_id = get_theme_mod('custom_logo');
					$logo_url       = wp_get_attachment_image_src($custom_logo_id, 'full');

					if ($logo_url) {
						echo '<img class="w-11 h-11 object-contain" src="' . esc_url($logo_url[0]) . '" alt="' . esc_attr(get_bloginfo('name', 'display')) . '">';
					}
					?>
					<span class="text-xl font-normal tracking-widest"><?php echo esc_html(get_bloginfo('name')); ?></span>
				</div>
				<p class="text-sm text-white/60 leading-relaxed max-w-xs"><?php echo bloginfo( 'description' );?></p>
				<?php
				$footer_data = faramacia_get_theme_data( 'footer' );
				$footer_email = isset( $footer_data['email'] ) ? $footer_data['email'] : '#';
				$footer_instagram = isset( $footer_data['instagram'] ) ? $footer_data['instagram'] : '#';
				$footer_facebook = isset( $footer_data['facebook'] ) ? $footer_data['facebook'] : '#';
				?>
				<div class="flex items-center gap-3 mt-5">
					<a href="<?php echo esc_url( 'mailto:' . $footer_email ); ?>" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-[var(--color-primario)] transition-colors" title="Email">
						<span class="material-symbols-outlined text-sm">alternate_email</span>
					</a>
					<a href="<?php echo esc_url( $footer_instagram ); ?>" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-[var(--color-primario)] transition-colors" title="Instagram">
						<span class="material-symbols-outlined text-sm">camera_alt</span>
					</a>
					<a href="<?php echo esc_url( $footer_facebook ); ?>" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-[var(--color-primario)] transition-colors" title="Facebook">
						<span class="material-symbols-outlined text-sm">groups</span>
					</a>
				</div>
			</div>

			<?php
			$footer_data = faramacia_get_theme_data( 'footer' );
			$footer_address = isset( $footer_data['address'] ) ? $footer_data['address'] : 'Av. Reino de Aragón 3, 50500 Tarazona';
			$footer_phone = isset( $footer_data['phone'] ) ? $footer_data['phone'] : '976 642 685';
			$footer_whatsapp = isset( $footer_data['whatsapp'] ) ? $footer_data['whatsapp'] : '689 123 456';
			$footer_schedule = isset( $footer_data['schedule'] ) ? $footer_data['schedule'] : "L-V: 9:00-13:45 · 16:30-20:00\nSáb: 9:00-13:45";
			?>
			<div class="lg:col-span-3">
				<h4 class="text-[10px] font-bold uppercase tracking-widestst mb-5 text-white/80">Contacto</h4>
				<ul class="space-y-3 text-sm text-white/60">
					<li class="flex items-start gap-3"><span class="material-symbols-outlined text-base text-[var(--color-primario)] shrink-0 mt-0.5">location_on</span><?php echo esc_html( $footer_address ); ?></li>
					<li class="flex items-center gap-3"><span class="material-symbols-outlined text-base text-[var(--color-primario)] shrink-0">call</span><?php echo esc_html( $footer_phone ); ?></li>
					<li class="flex items-center gap-3"><span class="material-symbols-outlined text-base text-[var(--color-primario)] shrink-0">forum</span><?php echo esc_html( $footer_whatsapp ); ?> (WhatsApp)</li>
					<li class="flex items-start gap-3"><span class="material-symbols-outlined text-base text-[var(--color-primario)] shrink-0 mt-0.5">schedule</span><?php echo nl2br( esc_html( $footer_schedule ) ); ?></li>
				</ul>
			</div>

			<div class="lg:col-span-2">
				<h4 class="text-[10px] font-bold uppercase tracking-widestst mb-5 text-white/80">Información</h4>
				<ul class="space-y-2 text-sm">
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Quiénes Somos</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Servicios</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Blog</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Contacto</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Condiciones de compra</a></li>
				</ul>
			</div>

			<div class="lg:col-span-3">
				<h4 class="text-[10px] font-bold uppercase tracking-widestst mb-5 text-white/80">Legal</h4>
				<ul class="space-y-2 text-sm">
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Aviso Legal</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Política de Privacidad</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Política de Cookies</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Política de Accesibilidad</a></li>
					<li><a class="text-white/60 hover:text-white transition-colors" href="#">Envíos y Devoluciones</a></li>
				</ul>
			</div>

		</div>

		<div class="border-t border-white/10 pt-6 md:pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-white/40">
			<p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. Todos los derechos reservados.</p>
			<div class="flex items-center gap-3">
				<span class="material-symbols-outlined text-xl">payments</span>
				<span class="material-symbols-outlined text-xl">credit_card</span>
				<span class="text-[10px] font-bold border border-current px-2 py-1 rounded">BIZUM</span>
				<span class="text-[10px] font-bold border border-current px-2 py-1 rounded">PAYPAL</span>
			</div>
		</div>
	</div>
</footer>
</div>


<div id="acc-overlay" class="acc-overlay" onclick="closeAccPanel()"></div>
<div id="acc-panel" class="acc-panel">
	<div class="acc-panel-header">
		<span class="material-symbols-outlined" style="font-size:20px">accessibility_new</span>
		Accesibilidad
	</div>
	<div class="acc-panel-body">
		<div class="acc-row">
			<span class="acc-label">Tamaño texto</span>
			<div style="display:flex;gap:4px">
				<button class="acc-btn-sm" onclick="setTextSize('sm')" title="Reducir">A-</button>