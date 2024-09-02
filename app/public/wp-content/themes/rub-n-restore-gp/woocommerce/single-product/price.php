<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Only modify the price display if it's a variable product
if ( $product->is_type( 'variable' ) ) {
	$prices = $product->get_variation_prices( true );

	if ( ! empty( $prices['price'] ) ) {
			$min_price = current( $prices['price'] );
			$max_price = end( $prices['price'] );

			// Check if there's a price range
			if ( $min_price !== $max_price ) {
					// Get the price including tax, if applicable
					$min_price_html = '<b>' . wc_price( wc_get_price_including_tax( $product, array( 'price' => $min_price ) ) );
					$max_price_html = wc_price( wc_get_price_including_tax( $product, array( 'price' => $max_price ) ) ) . '</b>';

					// Prepend "As low as" to the minimum price HTML
					$price_html = sprintf( __( 'As low as %s &ndash; %s', 'woocommerce' ), $min_price_html, $max_price_html );
			} else {
					// If there's no range, just show the regular price
					$price_html = '<b>' . wc_price( $min_price ) . '</b>';
			}
	} else {
			// If there are no prices available, don't show anything
			$price_html = '';
	}
} else {
	// For non-variable products, show the regular price HTML
	$price_html = '<b>' . $product->get_price_html() . '</b>';
}

?>
<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?>">
    <?php echo $price_html; // Note that we're now using our modified $price_html variable ?>
</p>
