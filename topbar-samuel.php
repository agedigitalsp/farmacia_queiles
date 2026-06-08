<?php
$topbar_data = faramacia_get_theme_data( 'topbar' );
$topbar_phone = isset( $topbar_data['phone'] ) ? $topbar_data['phone'] : '976 642 685';
$topbar_address = isset( $topbar_data['address'] ) ? $topbar_data['address'] : 'Av. Reino de Aragón 3, Tarazona';
$topbar_schedule = isset( $topbar_data['schedule'] ) ? $topbar_data['schedule'] : 'L-V 9:00-13:45 · 16:30-20:00';
?>
<div class="bg-[var(--color-oscuro)] text-white text-[11px] font-medium py-2.5">
    <div class="max-w-[1450px] mx-auto px-4 md:px-8 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">call</span><?php echo esc_html( $topbar_phone ); ?></span>
            <span class="hidden sm:flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">location_on</span><?php echo esc_html( $topbar_address ); ?></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">schedule</span><?php echo esc_html( $topbar_schedule ); ?></span>
            <a href="#" class="bg-[var(--color-primario)] hover:bg-[var(--color-primario)] text-white text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">Contáctanos</a>
        </div>
    </div>
</div>
<!-- ═══ HEADER ═══ -->
<header class="w-full bg-white border-b border-[var(--color-borde)] sticky top-0 z-50">
    <div class="max-w-[1450px] mx-auto px-4 md:px-8 py-3 flex items-center justify-between gap-4">
        <button id="menu-toggle" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg hover:bg-[var(--color-fondo-claro)] transition-colors" onclick="toggleMenu()">
            <span class="material-symbols-outlined text-[var(--color-texto-principal)]">menu</span>
        </button>
        <a class="flex items-center gap-3 shrink-0" href="#">
            <?php
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo_url       = wp_get_attachment_image_src($custom_logo_id, 'full');

            if ($logo_url) {
                echo '<img class="w-11 h-11 object-contain" src="' . esc_url($logo_url[0]) . '" alt="' . get_bloginfo('name') . '">';
            } 
            ?>
            <span class="text-xl font-normal tracking-widest text-[var(--color-texto-principal)] hidden sm:block"><?php echo bloginfo('name')?></span>
        </a>
        <div class="hidden lg:flex flex-1 max-w-xl relative">
            <div class="flex items-center bg-[var(--color-fondo-claro)] border border-[var(--color-borde)] rounded-full overflow-hidden focus-within:border-[var(--color-primario)] transition-colors w-full">
                <span class="material-symbols-outlined text-[var(--color-texto-secundario)] ml-4 text-lg shrink-0">search</span>
                <input class="w-full bg-transparent border-none focus:ring-0 px-3 py-2.5 text-sm text-[var(--color-texto-principal)] placeholder:text-[var(--color-texto-secundario)]" placeholder="Busca por producto, laboratorio, necesidad..." type="text">
                <button class="bg-[var(--color-primario)] hover:bg-[var(--color-primario)] text-white px-5 py-2.5 text-sm font-semibold transition-colors rounded-full border-2 border-[var(--color-primario)] hover:border-[var(--color-primario)] shrink-0">Buscar</button>
            </div>
        </div>
        <div class="flex items-center gap-4 md:gap-5">
            <a class="flex flex-col items-center text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">
                <span class="material-symbols-outlined text-lg">person</span>
                <span class="text-[9px] font-semibold uppercase mt-0.5 hidden md:block">Cuenta</span>
            </a>
            <a class="flex flex-col items-center text-[var(--color-texto-principal)] hover:text-red-500 transition-colors relative" href="#">
                <span class="material-symbols-outlined text-lg">favorite</span>
                <span class="text-[9px] font-semibold uppercase mt-0.5 hidden md:block">Me encanta</span>
            </a>
            <a class="flex flex-col items-center text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors relative" href="#">
                <span class="material-symbols-outlined text-lg">shopping_bag</span>
                <span class="text-[9px] font-semibold uppercase mt-0.5 hidden md:block">Carrito</span>
                <span class="absolute -top-1 -right-1 bg-[var(--color-primario)] text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">2</span>
            </a>
        </div>
    </div>
    <div class="hidden lg:block border-t border-[var(--color-borde)]">
        <div class="max-w-[1450px] mx-auto px-4 md:px-8 py-2 flex items-center gap-6 text-sm font-medium overflow-x-auto lg:overflow-visible scroll-hide">
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Dermocosmética</a>
            <a class="whitespace-nowrap bg-[#fff4e0] text-[var(--color-secundario)] px-3 py-1 rounded-full font-semibold" href="#">Solar</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Facial</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Corporal</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Infantil</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Higiene</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Cabello</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Salud</a>
            <a class="whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors" href="#">Marcas</a>
            <div class="relative group">
                <button class="flex items-center gap-1 whitespace-nowrap text-[var(--color-texto-principal)] hover:text-[var(--color-primario)] transition-colors text-sm font-medium">
                    <span class="material-symbols-outlined text-base">category</span> Más categorías <span class="material-symbols-outlined text-base">expand_more</span>
                </button>
                <div class="absolute top-full left-0 mt-2 w-56 bg-white border border-[var(--color-borde)] rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                    <div class="p-2 space-y-1">
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Bucodental</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Nutrición</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Ortopedia</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Salud Digestiva</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Cosmética Coreana</a>
                    </div>
                </div>
            </div>
            <span class="ml-auto text-[var(--color-borde)]">|</span>
            <div class="relative group">
                <button class="flex items-center gap-1 whitespace-nowrap border-2 border-[var(--color-primario)] text-[var(--color-primario)] hover:bg-[var(--color-primario)] hover:text-white px-3 py-1 rounded-full text-sm font-medium transition-all">
                    <span class="material-symbols-outlined text-base">category</span> Servicios <span class="material-symbols-outlined text-base">expand_more</span>
                </button>
                <div class="absolute top-full left-0 mt-2 w-56 bg-white border border-[var(--color-borde)] rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                    <div class="p-2 space-y-1">
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Asesoramiento farmacéutico</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Ortopedia personalizada</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Espacio Senior</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Cuidado infantil</a>
                        <a class="block px-3 py-2 rounded-lg text-sm text-[var(--color-texto-principal)] hover:bg-[var(--color-fondo-claro)] hover:text-[var(--color-primario)] transition-colors" href="#">Cosmética natural</a>
                    </div>
                </div>
            </div>
            <a class="whitespace-nowrap bg-[var(--color-primario)] text-white px-4 py-1.5 rounded-full font-semibold" href="#">Promociones</a>
        </div>
    </div>
</header>