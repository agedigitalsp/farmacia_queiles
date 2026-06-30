<?php
/**
 * Single Product tabs — Farmacia Queiles
 *
 * @package Farmacia_Queiles
 * @version 9.8.0 (WooCommerce compatibility)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $product_tabs */
$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );

if ( empty( $product_tabs ) ) {
	return;
}

/* Iconos por nombre de tab */
$tab_icons = [
	'description'            => 'description',
	'additional_information' => 'science',
	'fq_composicion'         => 'science',
	'fq_modo_empleo'         => 'touch_app',
	'fq_faqs'                => 'help_outline',
];

$first_key = (string) array_key_first( $product_tabs );
?>

<div class="fq-sp-tabs" data-fq-tabs>

	<!-- Cabecera de tabs -->
	<div class="fq-sp-tabs__nav" role="tablist">
		<?php foreach ( $product_tabs as $key => $tab ) :
			$is_first = ( $key === $first_key );
			$icon     = $tab_icons[ $key ] ?? 'article';
			$title    = apply_filters( 'woocommerce_product_' . $key . '_tab_title', $tab['title'], $key );
		?>
		<button
			class="fq-sp-tabs__btn<?php echo $is_first ? ' is-active' : ''; ?>"
			role="tab"
			id="fq-tab-btn-<?php echo esc_attr( $key ); ?>"
			aria-controls="fq-tab-panel-<?php echo esc_attr( $key ); ?>"
			aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
			data-tab="<?php echo esc_attr( $key ); ?>"
			type="button"
		>
			<span class="material-symbols-outlined"><?php echo esc_html( $icon ); ?></span>
			<?php echo wp_kses_post( $title ); ?>
		</button>
		<?php endforeach; ?>
	</div>

	<!-- Contenido de tabs -->
	<?php foreach ( $product_tabs as $key => $tab ) :
		$is_first = ( $key === $first_key );
	?>
	<div
		class="fq-sp-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
		role="tabpanel"
		id="fq-tab-panel-<?php echo esc_attr( $key ); ?>"
		aria-labelledby="fq-tab-btn-<?php echo esc_attr( $key ); ?>"
		<?php echo $is_first ? '' : 'hidden'; ?>
	>
		<?php
		if ( isset( $tab['callback'] ) ) {
			call_user_func( $tab['callback'], $key, $tab );
		}
		?>
	</div>
	<?php endforeach; ?>

	<?php do_action( 'woocommerce_product_after_tabs' ); ?>
</div>
