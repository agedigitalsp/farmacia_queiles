<?php
/**
 * Customer Review Order page.
 *
 * Theme-overridable. Copy to `yourtheme/woocommerce/order/customer-review-order.php`.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.8.0
 *
 * @var WC_Order $order Order being reviewed.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order instanceof WC_Order ) {
	return;
}

$meta_parts = \Automattic\WooCommerce\Internal\OrderReviews\Meta::parts_for_order( $order );

/**
 * Filter the eligible items rendered on the Review Order page.
 *
 * Defaults to the order's line items. Extensions can use this to hide items
 * that have already been reviewed or are otherwise ineligible.
 *
 * @since 10.8.0
 *
 * @param WC_Order_Item[] $items Order line items.
 * @param WC_Order        $order The order being reviewed.
 */
$items = (array) apply_filters( 'woocommerce_review_order_eligible_items', $order->get_items(), $order );

// Batched lookup; without this each decide() call would issue its own query.
\Automattic\WooCommerce\Internal\OrderReviews\ItemEligibility::preload_for_items( $items, $order );

// Skipped rows are counted so the disabled-products notice can render above the form.
$decisions          = array();
$has_unreviewed_row = false;
$skipped_count      = 0;
foreach ( $items as $item ) {
	if ( ! $item instanceof WC_Order_Item_Product ) {
		continue;
	}
	$product = $item->get_product();
	if ( ! $product instanceof WC_Product ) {
		continue;
	}

	$decision = \Automattic\WooCommerce\Internal\OrderReviews\ItemEligibility::decide( $item, $order );
	if ( \Automattic\WooCommerce\Internal\OrderReviews\ItemEligibility::STATUS_SKIP === $decision['status'] ) {
		++$skipped_count;
		continue;
	}

	if ( ! ( $decision['comment'] instanceof WP_Comment ) ) {
		$has_unreviewed_row = true;
	}

	$decisions[] = array(
		'item'     => $item,
		'product'  => $product,
		'decision' => $decision,
	);
}//end foreach

// Empty-state: no actionable rows remain.
if ( ! $has_unreviewed_row ) {
	$reviewed_count = 0;
	foreach ( $decisions as $entry ) {
		if ( $entry['decision']['comment'] instanceof WP_Comment ) {
			++$reviewed_count;
		}
	}

	wc_get_template(
		'order/customer-review-order-empty.php',
		array(
			'order'          => $order,
			'reviewed_count' => $reviewed_count,
		)
	);
	return;
}//end if

$order_key       = (string) $order->get_order_key();
$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
?>
