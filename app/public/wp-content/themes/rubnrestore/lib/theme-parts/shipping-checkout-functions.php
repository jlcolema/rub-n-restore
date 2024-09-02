<?Php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//all the customized shipping functions are here
//STAGING SITE


//Get shipping country to use for checking US or CA
//Returns the country code
function rnr_customer_country() {

    global $woocommerce;

    $customer_country = $woocommerce->customer->get_shipping_country();

    return $customer_country;

}

//Setup non-virtual total function
function rnr_non_virtual_total($arg) { //only use this function in the cart or checkout!
    $virtual_total = 0;
    $non_virtual_total = 0;
    $non_virtual_subtotal = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {

        $product_id = $cart_item['product_id']; //store the product id
        if (!$cart_item['data']->is_virtual() && $product_id != '66707') { // swatches are not virtual so they can have shipping, but don't count to free shipping
            $non_virtual_total += $cart_item['line_total']; //after discounts
            $non_virtual_subtotal += $cart_item['line_subtotal']; //before discounts
            
        }

        if ($cart_item['data']->is_virtual() || $product_id == '66707') {

            $virtual_total += $cart_item['line_total'];
        }

    }
    if ($arg == "virtual") { //virtual products (services)
        return $virtual_total;

    }

    if ($arg == "subtotal") { //before discounts
        return $non_virtual_subtotal;

    }

    if ($arg == "total") { //after discounts
        return $non_virtual_total;
    }

}
/* //discount in cart for swatches if product order is over $15
 //add_action( 'woocommerce_cart_calculate_fees', 'rnr_swatch_discount' ); 
 
 function rnr_swatch_discount(){
	 
	  $non_virtual_subtotal = rnr_non_virtual_total("subtotal");
	  
	  if ($non_virtual_subtotal>="16"){
		  
		  	global $woocommerce;
			
			if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;
		  
		     foreach( WC()->cart->get_cart() as $cart_item ) {
    	 	 $product_in_cart = $cart_item['product_id']; 
     		 if ( $product_in_cart === 66707 ) { // if there is a swatch in the cart
				
				 $quantity =  $cart_item['quantity'];
				 
				 } 
						 				 
  			 }
			 
			 	if ($quantity == 1){
					 $discount= -.5;
					 }
 				if ($quantity == 2){
					 $discount= -1;
					 }
 				if ($quantity == 3){
					 $discount= -1.5;
					 }	
 				if ($quantity >= 4){
					 $discount= -2;
					 }	
		  
			$woocommerce->cart->add_fee( 'Free swatch discount', $discount, false, '' );
		  
		  }
	 
	 }*/

//Exclude virtual products from free shipping
//And set the shipping rates to compensate for virtual products
add_filter('woocommerce_package_rates', 'custom_shipping_option', 20, 2);

function custom_shipping_option($rates, $package) {

    //if (! is_admin()){
    //Check if a coupon has applied free shipping
    $hasfreeShipcoupon = '';

    $applied_coupons = WC()->cart->get_applied_coupons();

    foreach ($applied_coupons as $coupon_code) {

        $coupon = new WC_Coupon($coupon_code);

        if ($coupon->get_free_shipping()) {
            $hasfreeShipcoupon = "true";

            break;

        }

    }

    // Get the cart content total excluding virtual products and assign to $non_virtual_total
    $non_virtual_total = rnr_non_virtual_total("total");

    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");

    /*$non_virtual_total = 0;
    
    foreach( WC()->cart->get_cart() as $cart_item ){//moved this into it's own function so it can be re-used
    
    $product_id = $cart_item['product_id']; //store the product id
    
     if( ! $cart_item['data']->is_virtual() && $product_id != '66707' ){ // swatches are not virtual so they can have shipping, but don't count to free shipping
    
        $non_virtual_total += $cart_item['line_total']; //after discounts
    $non_virtual_subtotal += $cart_item['line_subtotal']; //before discounts
    }
    
    }*/

    $cart_has_swatch = false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_in_cart = $cart_item['product_id'];
        if ($product_in_cart === 66707) $cart_has_swatch = "true"; // if there is a swatch in the cart
        
    }

    if (rnr_customer_country() == 'AS' || rnr_customer_country() == 'GU' || rnr_customer_country() == 'MP' || rnr_customer_country() == 'PR' || rnr_customer_country() == 'UM' || rnr_customer_country() == 'VI') {
        $USterritory = "yes";
    }

    else {
        $USterritory = "no";
    }

    // Disabling methods based on non_virtual_total for US
    if (rnr_customer_country() == 'US') {

        if ($non_virtual_total < 45 && $hasfreeShipcoupon == "true") {

            //Change the free shipping text when coupon gives free shipping
            foreach ($rates as $rate_key => $rate) {

                if ('free_shipping' == $rate->method_id) {

                    $rates[$rate_key]->label = __('Free shipping - From your coupon.', 'woocommerce'); // New label name
                    
                }

            }

        }

        if ($cart_has_swatch === "true" && ($non_virtual_total === 0 || $non_virtual_total === "")) {

            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id)

                    unset($rates[$rate_key]);
                }

                //We need to remove the other shipping options as they are set to show always.
                if ('flexible_shipping_single:29' == $rate_key) { //$7 rate
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:30' == $rate_key) { //$5 rate
                    unset($rates[$rate_key]);

                }

                if ('ups:17:12' == $rate_key) { //3day select
                    unset($rates[$rate_key]);

                }

                if ('ups:17:03' == $rate_key) { //UPS Ground
                    unset($rates[$rate_key]);

                }
                if ('ups:17:02' == $rate_key) { //2nd day air
                    unset($rates[$rate_key]);

                }
                if ('ups:17:01' == $rate_key) { //Next day air
                    unset($rates[$rate_key]);

                }
                if ('ups:17:14' == $rate_key) { //Next day air early
                    unset($rates[$rate_key]);

                }

            }

        }

        else if ($non_virtual_total < 25) {

            //echo $non_virtual_total;
            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id)

                    unset($rates[$rate_key]);
                }

                //We need to remove the $5 shipping option here it is set to show always.
                if ('flexible_shipping_single:30' == $rate_key) { //$5 ship
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:31' == $rate_key) { //swatch shipping rate
                    //  $rates[$rate_key]->label = __( $cart_has_swatch . ', ' .$non_virtual_total .', ' .$product_id, 'woocommerce' ); // Test variable output as $2 shipping option
                    unset($rates[$rate_key]);

                }

            }

        }

        else if ($non_virtual_total >= 25 && $non_virtual_total < 45) {

            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id) {

                        unset($rates[$rate_key]);

                    }

                }

                //We need to remove the $7 shipping option here it is set to show always.
                if ('flexible_shipping_single:29' == $rate_key) {

                    unset($rates[$rate_key]);

                }

                if ('flexible_shipping_single:31' == $rate_key) { //swatch shipping rate
                    unset($rates[$rate_key]);

                }

            }

        }

        //If the cart total is over $45
        else if ($non_virtual_total >= 45) {

            foreach ($rates as $rate_key => $rate) {

                $initial_cost = $rates[$rate_key]->cost;

                if ('flexible_shipping_single:29' == $rate_key) { //$7
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:30' == $rate_key) { //$5
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:31' == $rate_key) { //swatch shipping rate
                    unset($rates[$rate_key]);

                }

                //Discount UPS rates by $8 to compensate for free shipping
                if ('ups:17:12' == $rate_key) { //3day select
                    

                    $rates['ups:17:12']->cost = $initial_cost - 8;

                }

                if ('ups:17:03' == $rate_key) { //UPS Ground
                    $discounted_cost = $initial_cost - 8;

                    if ($discounted_cost < 0) {
                        $rates['ups:17:03']->cost = 0;
                    } //low ups rates were giving a discount. Free is as low as it should go.
                    else {

                        $rates['ups:17:03']->cost = $initial_cost - 8;

                    }

                }

                if ('ups:17:02' == $rate_key) { //2nd day air
                    $rates['ups:17:02']->cost = $initial_cost - 8;

                }
                if ('ups:17:01' == $rate_key) { //Next day air
                    $rates['ups:17:01']->cost = $initial_cost - 8;

                }
                if ('ups:17:14' == $rate_key) { //Next day air early
                    $rates['ups:17:14']->cost = $initial_cost - 8;

                }
                //change free shipping text
                if ('free_shipping' == $rate->method_id) {

                    $rates[$rate_key]->label = __('Free shipping on product orders over $45. Or use one of the discounted UPS rates below:   ', 'woocommerce'); // New label name
                    
                }
            }

        }

    }

    else if ($USterritory == 'yes') { // US territories shipping methods same as US
        if ($non_virtual_total < 45 && $hasfreeShipcoupon == "true") {

            //Change the free shipping text when coupon gives free shipping
            foreach ($rates as $rate_key => $rate) {

                if ('free_shipping' == $rate->method_id) {

                    $rates[$rate_key]->label = __('Free shipping - From your coupon.', 'woocommerce'); // New label name
                    
                }

            }

        }

        if ($cart_has_swatch === "true" && ($non_virtual_total === 0 || $non_virtual_total === "")) {

            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id)

                    unset($rates[$rate_key]);
                }

                //We need to remove the other shipping options as they are set to show always.
                if ('flexible_shipping_single:37' == $rate_key) { //$7 rate
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:38' == $rate_key) { //$5 rate
                    unset($rates[$rate_key]);

                }

                if ('ups:41:03' == $rate_key) { //UPS Ground
                    unset($rates[$rate_key]);

                }

                if ('ups:41:12' == $rate_key) { //3day select
                    unset($rates[$rate_key]);

                }

                if ('ups:41:02' == $rate_key) { //2nd day air
                    unset($rates[$rate_key]);

                }
                if ('ups:41:01' == $rate_key) { //Next day air
                    unset($rates[$rate_key]);

                }
                if ('ups:41:14' == $rate_key) { //Next day air early
                    unset($rates[$rate_key]);

                }

            }

        }

        else if ($non_virtual_total < 25) {

            //echo $non_virtual_total;
            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id)

                    unset($rates[$rate_key]);
                }

                //We need to remove the $5 shipping option here it is set to show always.
                if ('flexible_shipping_single:38' == $rate_key) { //$5 ship
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:39' == $rate_key) { //swatch shipping rate
                    //  $rates[$rate_key]->label = __( $cart_has_swatch . ', ' .$non_virtual_total .', ' .$product_id, 'woocommerce' ); // Test variable output as $2 shipping option
                    unset($rates[$rate_key]);

                }

            }

        }

        else if ($non_virtual_total >= 25 && $non_virtual_total < 45) {

            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id) {

                        unset($rates[$rate_key]);

                    }

                }

                //We need to remove the $7 shipping option here it is set to show always.
                if ('flexible_shipping_single:37' == $rate_key) {

                    unset($rates[$rate_key]);

                }

                if ('flexible_shipping_single:39' == $rate_key) { //swatch shipping rate
                    unset($rates[$rate_key]);

                }

            }

        }

        //If the cart total is over $45 then just unset the $7 option
        else if ($non_virtual_total >= 45) {

            foreach ($rates as $rate_key => $rate) {

                $initial_cost = $rates[$rate_key]->cost;

                if ('flexible_shipping_single:37' == $rate_key) { //$7
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:38' == $rate_key) { //$5
                    unset($rates[$rate_key]);

                }
                if ('flexible_shipping_single:39' == $rate_key) { //swatch shipping rate
                    unset($rates[$rate_key]);

                }

                //Discount UPS rates by $8 to compensate for free shipping
                if ('ups:41:12' == $rate_key) { //3day select
                    

                    $rates['ups:41:12']->cost = $initial_cost - 8;

                }

                if ('ups:41:03' == $rate_key) { //UPS Ground
                    $discounted_cost = $initial_cost - 8;

                    if ($discounted_cost < 0) {
                        $rates['ups:41:03']->cost = 0;
                    } //low ups rates were giving a discount. Free is as low as it should go.
                    else {

                        $rates['ups:41:03']->cost = $initial_cost - 8;

                    }

                }

                if ('ups:41:02' == $rate_key) { //2nd day air
                    $rates['ups:41:02']->cost = $initial_cost - 8;

                }
                if ('ups:41:01' == $rate_key) { //Next day air
                    $rates['ups:41:01']->cost = $initial_cost - 8;

                }
                if ('ups:41:14' == $rate_key) { //Next day air early
                    $rates['ups:41:14']->cost = $initial_cost - 8;

                }
                //change free shipping text
                if ('free_shipping' == $rate->method_id) {

                    $rates[$rate_key]->label = __('Free shipping on product orders over $45. Or use one of the discounted UPS rates below:   ', 'woocommerce'); // New label name
                    
                }
            }

        }

    }

    // Disabling methods based on non_virtual_total for CA and MX & setup swatch shipping rate // Gree shipping method has been removed from CA and MX
    else if (rnr_customer_country() == 'CA' || rnr_customer_country() == 'MX') {

        if ($cart_has_swatch === "true" && ($non_virtual_total === 0 || $non_virtual_total === "")) {

            foreach ($rates as $rate_key => $rate) {

                if ($hasfreeShipcoupon == "true") {

                    if ('flexible_shipping_single:32' == $rate_key) { //swatch shipping rate (CA & MX)
                        

                        $rates['flexible_shipping_single:32']->cost = 0; // your amount
                        $rates['flexible_shipping_single:32']->label = __('Free shipping - From your coupon.', 'woocommerce'); // New label name
                        
                    }
                }

                if ('ups:18:07' == $rate_key) { //Worldwide express
                    unset($rates[$rate_key]);

                }

                if ('ups:18:08' == $rate_key) { //Worldwide expedited satandard
                    unset($rates[$rate_key]);

                }
                if ('ups:18:65' == $rate_key) { //Worldwide saver
                    unset($rates[$rate_key]);

                }

            }

        }
        else {
            foreach ($rates as $rate_key => $rate) {

                if ('flexible_shipping_single:32' == $rate_key) { //swatch shipping rate (CA & MX)
                    

                    unset($rates[$rate_key]);

                }

            }

        }

    }

    //The rest of the world
    else if (rnr_customer_country() != 'CA' && rnr_customer_country() != 'MX' && rnr_customer_country() != 'US' && $USterritory != "yes") {

        if ($cart_has_swatch === "true" && ($non_virtual_total === 0 || $non_virtual_total === "")) {

            foreach ($rates as $rate_key => $rate) {

                if ($hasfreeShipcoupon == "true") {

                    if ('flexible_shipping_single:33' == $rate_key) { //swatch shipping rate (CA & MX)
                        

                        $rates['flexible_shipping_single:33']->cost = 0; // your amount
                        $rates['flexible_shipping_single:33']->label = __('Free shipping - From your coupon.', 'woocommerce'); // New label name
                        
                    }
                }

                if ('ups:18:07' == $rate_key) { //Worldwide express
                    unset($rates[$rate_key]);

                }

                if ('ups1965' == $rate_key) { //Worldwide expedited satandard
                    unset($rates[$rate_key]);

                }
                if ('ups1908' == $rate_key) { //Worldwide saver
                    unset($rates[$rate_key]);

                }

                if ('ups:19:08' == $rate_key) { //Worldwide Expidited Standard
                    unset($rates[$rate_key]);

                }

                if ('ups:19:65' == $rate_key) { //Worldwid
                    unset($rates[$rate_key]);

                }
            }

        }

        else {

            foreach ($rates as $rate_key => $rate) {

                if ('flexible_shipping_single:33' == $rate_key) { //swatch shipping rate (CA & MX)
                    unset($rates[$rate_key]);

                }

            }
        }
    }

    //}
    return $rates;

}

//Show messages when virtual products are in the cart
add_action('woocommerce_before_cart', 'rnr_check_category_in_cart');

function rnr_check_category_in_cart() {

    // Set $cat_in_cart to false
    $cat_in_cart = false;

    // Loop through all products in the Cart
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {

        // If Cart has category "download", set $cat_in_cart to true
        if (has_term('color-matching-services', 'product_cat', $cart_item['product_id'])) {
            $cat_in_cart = true;
            break;
        }
    }

    if ($cat_in_cart) {

        wc_print_notice('Note: The Color Matching Sevices in your cart do not add to your free shipping total. ', 'notice');

    }

}

//Show the Virtual and Regular subtotals in the cart and checkout totals table
add_action('woocommerce_cart_totals_before_shipping', 'display_cart_virtual_total', 0, 1);

add_action('woocommerce_review_order_before_shipping', 'display_cart_virtual_total', 0, 1);

function display_cart_virtual_total() {

    //  $non_virtual_total = 0;
    // $virtual_total = 0;
    // $non_virtual_subtotal = 0;
    setlocale(LC_MONETARY, 'en_US');
    $virtual_total = rnr_non_virtual_total("virtual");
    $non_virtual_total = rnr_non_virtual_total("total");
    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");

    //Check if a coupon has applied free shipping
    $hasfreeShipcoupon = '';

    $applied_coupons = WC()->cart->get_applied_coupons();

    foreach ($applied_coupons as $coupon_code) {

        $coupon = new WC_Coupon($coupon_code);

        if ($coupon->get_free_shipping()) {
            $hasfreeShipcoupon = "true";

            break;

        }

    }

    // Get the cart content total excluding virtual products
    //Depreciated in favor of running the rnr_non_virtual_total() function
    /*  foreach( WC()->cart->get_cart() as $cart_item ){
    
     $product_id = $cart_item['product_id']; //store the product id
    
        if( ! $cart_item['data']->is_virtual() && $product_id != '66707' ){ //don't apply swatches to non-virtual total
    
     	   $non_virtual_total += $cart_item['line_total']; //after discounts
    $non_virtual_subtotal += $cart_item['line_subtotal']; //before discounts
    	 }
    
    if ($cart_item['data']->is_virtual( ) || $product_id == '66707'){
       		$virtual_total += $cart_item['line_total'];
    }
    
    }*/

    $free_shipping_us = 45 - $non_virtual_total;

    $free_shipping_ca = 150 - $non_virtual_total;

    $free_shipping_message = "";

    $hasaCoupon = "";

    if (count(WC()->cart->get_applied_coupons()) > 0) {
        $hasaCoupon = "true";
    }

    //USA
    if (rnr_customer_country() == 'US') {

        if ($hasaCoupon == "true" && $hasfreeShipcoupon != "true" && $non_virtual_subtotal >= 45 && $non_virtual_subtotal - $non_virtual_total < 45) {
            $free_shipping_message = 'Your coupon lowered your product order below the free shipping cutoff. <br>';
        }
        if ($free_shipping_us <= 45 && $free_shipping_us > 0 && $hasfreeShipcoupon != "true") {

            $free_shipping_message .= 'Add $' . number_format($free_shipping_us, 2) . "\n" . ' to your cart for free shipping!';

        }

        else if ($hasfreeShipcoupon == "true") {

            $free_shipping_message = "Your coupon gives you free shipping!";

        }

        else if ($non_virtual_total >= 45) {

            $free_shipping_message = "Your order qualifies for free shipping!";

        }

        //If they have virtual and non-virtual products
        if ($non_virtual_total > 0 && $virtual_total > 0) {

            if ($virtual_total > 0) {

                // The Output
                echo ' <tr class="products-virtual-subtotal" style="font-size:14px" >
            <th>' . __("Services Subtotal ", "woocommerce") . '</th>
            <td data-title="virtual-total">' . number_format($virtual_total, 2) . "\n" . '<br><em>Services do not count toward free shipping. </em></td>
        </tr>';
            }

            if ($non_virtual_total > 0) {

                // The Output
                echo ' <tr class="cart-virtual-total"  style="font-size:14px">
            <th>' . __("Products Subtotal ", "woocommerce") . '</th>
            <td data-title="non-virtual-total"><em>' . number_format($non_virtual_total, 2) . "\n" . '<br>' . $free_shipping_message . '</em> </td>
        </tr>';
            }
        }

        //If there are non-virtual products only and a free shipping coupon is not included
        if ($non_virtual_total > 0 && $virtual_total == 0 && $free_shipping_us > 0) {
            echo ' <tr class="cart-products-total"  style="font-size:14px">
				<th>' . __("", "woocommerce") . '</th>
				<td data-title="free-shipping-message"><em>' . $free_shipping_message . '</em> </td>
			</tr>';
        }

    }

    //CANADA & Mexico DISABLED FREE SHIPPING TO CA MX 4.16-2020
    

    
}

//add international shipping notification
add_action('woocommerce_checkout_before_order_review', 'rnr_echo_notice_shipping');

function rnr_echo_notice_shipping() {
    echo '<div class="shipping-notice" style="display:none">Rub \'n Restore, Inc. is not responsible for international duties or taxes. Please read about <a href="https://rubnrestore.com/shipping-info/" target="_blank" title="shipping">international shipments.</a></div>';
}

// Part 2
// Show or hide message based on billing country
// The "display:none" hides it by default
add_action('woocommerce_after_checkout_form', 'rnr_show_notice_shipping');

function rnr_show_notice_shipping() {

?>
<script>
        jQuery(document).ready(function($){
  
            // Set the country code (That will hide the message)
            var countryCode = 'US';
			
			var activecountryCode = $( "select#billing_country" ).val();
			
			 if( activecountryCode != countryCode ){
                    $('.shipping-notice').show();
                }
                else {
                    $('.shipping-notice').hide();
                }
  
            $('select#billing_country').change(function(){
  
                selectedCountry = $('select#billing_country').val();
                  
                if( selectedCountry != countryCode ){
                    $('.shipping-notice').show();
                }
                else {
                    $('.shipping-notice').hide();
                }
            });
  
        });
    </script>
<?php
}

//Hook newsletter subcribe into Thank you on checkout
add_action('woocommerce_thankyou', 'rnr_newsletter_subscribe_and_pinterest', 1);

function rnr_newsletter_subscribe_and_pinterest($order_id) {

    echo do_shortcode('[fl_builder_insert_layout id="73611"]');

    /*//Pinterest tracking - switched to pixel manager
    $order          = wc_get_order( $order_id );
    $order_total    = $order->get_total();
    $order_quantity = $order->get_item_count();
    $order_currency = $order->get_currency();
    
    $order_items_data = array();
    
    foreach ( $order->get_items() as $item_id => $item ) {
        $order_items_data[] = array(
            'product_id'       => $item->get_product_id(),
            'product_name'     => $item->get_name(),
            'product_price'    => $item->get_total(),
            'product_quantity' => $item->get_quantity(),
        );
    }
    
    printf(
        '<script>
        pintrk("track", "checkout", {
            value: %1$s,
            order_quantity: %2$s,
            currency: "%3$s",
            line_items: %4$s
        });
        </script>',
        esc_attr( $order_total ),
        esc_attr( $order_quantity ),
        esc_attr( $order_currency ),
        wp_json_encode( $order_items_data )
    );*/

}

// Auto uncheck ship to different address
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');

//force shipping on all products
add_filter('woocommerce_cart_needs_shipping_address', '__return_true', 50);

//Add custom content to the order completed email
add_action('woocommerce_email_before_order_table', 'rnr_add_content_specific_email', 20, 4);

function rnr_add_content_specific_email($order, $sent_to_admin, $plain_text, $email) {
    if ($email->id == 'customer_completed_order') {

        echo get_field('customer_message', 374);

        //echo '<p><strong>Please secure your package in a timely fashion to avoid freezing during winter months. </strong></p> //<p>Your order comes with instructions, but <a href="https://rubnrestore.com/how-to/" title="How-to Library">greater details and videos can be found in our How To & FAQs</a></p>';
        
    }
}

// Add shiping delay message for USPS
add_action('woocommerce_cart_totals_before_shipping', 'rnr_USPS_notification');

function rnr_USPS_notification() {

    echo '<p><!--Edit this in shipping-checkout-functions.php--><small>* We process your order same-day or next business-day. Delivery times (except UPS Next Day Air) are not guaranteed due to COVID. International customers may be assessed duties and taxes. <a href="' . get_permalink(3019) . '" target="_blank" title="Shipping Policy">More shipping info.</a></small></p> ';

}
//* Add a message to shipping calculator on checkout, woocommerce
add_action('woocommerce_after_shipping_calculator', 'rnr_zip_notification');
function rnr_zip_notification() {

    echo '<p><!--Edit this in shipping-checkout-functions.php--><small>Note: A zip/postal code is required to calculate shipping. If you live in a location without a postal code, enter "00000" to get rates. International duties and taxes may apply.</small></p> ';

}

//*****************************************************
//Customize wooommerce no shipping message
add_filter('woocommerce_no_shipping_available_html', 'my_custom_no_shipping_message');

//add_filter( 'woocommerce_cart_no_shipping_available_html', 'my_custom_no_shipping_message' );
function my_custom_no_shipping_message($message) {
    return __('The shipping calculator does not understand your address. Please ensure that your address has been entered correctly with a zip code. Contact us if you need any help. <a href="https://www.rubnrestore.com/shipping-info/" target="_blank">Shipping Policy</a>');
}

//*******************************************
//Force postcode to be non optional
function sv_require_wc_company_field($fields) {
    $fields['postcode']['required'] = true;
    return $fields;
}
add_filter('woocommerce_default_address_fields', 'sv_require_wc_company_field');

//check if Evaluation is in cart and show a message
add_action('woocommerce_before_single_product', 'rnr_find_product_in_cart');

function rnr_find_product_in_cart() {

    if (is_single(76219) && is_product()) {

        $product_id = 76219;

        $in_cart = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_in_cart = $cart_item['product_id'];
            if ($product_in_cart === $product_id) $in_cart = true;
        }

        if ($in_cart) {

            echo '<div style="text-align: center;padding: 20px; margin: 20px 100px; font-size: 26px;">You can only have one Project Evaluation in the cart. Please proceed to <a href= "' . get_permalink(374) . '"> checkout </a> to submit it. Thanks!</div>';

        }

    }

}

//stop multiple evals in cart
add_filter('woocommerce_add_to_cart_sold_individually_found_in_cart', function ($found, $product_id) {

    $product = wc_get_product($product_id);
    if ($product->is_sold_individually()) {
        foreach (WC()->cart->get_cart() as $item) {
            if ($item['data']->get_id() == $product_id) {
                $found = true;
                break;
            }
        }
    }

    return $found;
}
, 5, 2);

//auto redirect to cart when eval is added
//Redirect users after add to cart.
function my_custom_add_to_cart_redirect($url) {

    if (!isset($_REQUEST['add-to-cart']) || !is_numeric($_REQUEST['add-to-cart'])) {
        return $url;
    }

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['add-to-cart']));

    // Only redirect the product IDs in the array to the checkout
    if (in_array($product_id, array(
        76219
    ))) {
        $url = WC()->cart->get_checkout_url();
    }

    return $url;

}
add_filter('woocommerce_add_to_cart_redirect', 'my_custom_add_to_cart_redirect');

//////////////////////////////
//Apply swatch coupon to cart
add_action('woocommerce_update_cart_action_cart_updated', 'rnr_apply_coupon_if_swatch');
add_action('woocommerce_before_cart', 'rnr_apply_coupon_if_swatch');

function rnr_apply_coupon_if_swatch() {

    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");
    //echo "A: " . $non_virtual_subtotal;
    

    if ($non_virtual_subtotal >= "16") {

        // if product in the cart
        foreach (WC()->cart->get_cart() as $cart_item) {

            $product_in_cart = $cart_item['product_id'];
            $price = 0;
            // $quantity=0;
            if ($product_in_cart === 66707) { // if there is a swatch in the cart
                // $quantity =  $cart_item['quantity'];
                $price = $cart_item['data']->get_price();

                if ($price == .5) {
                    if (!WC()->cart->has_discount('1freeswatch')) {
                        WC()->cart->apply_coupon('1freeswatch');
                    }
                }

                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('1freeswatch');
                    WC()->cart->calculate_totals();
                }
                if ($price == 1) {
                    if (!WC()->cart->has_discount('2freeswatches')) {
                        WC()->cart->apply_coupon('2freeswatches');
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('2freeswatches');
                    WC()->cart->calculate_totals();
                }
                if ($price == 1.5) {
                    if (!WC()->cart->has_discount('3freeswatches')) {
                        WC()->cart->apply_coupon('3freeswatches');
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('3freeswatches');
                    WC()->cart->calculate_totals();
                }
                if ($price >= 2) {
                    if (!WC()->cart->has_discount('4freeswatches')) {
                        WC()->cart->apply_coupon('4freeswatches');
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('4freeswatches');
                    WC()->cart->calculate_totals();
                }

            }
        }
    }

    else { //if less than 16 subtotal
        WC()->cart->remove_coupon('1freeswatch');
        WC()->cart->remove_coupon('2freeswatches');
        WC()->cart->remove_coupon('3freeswatches');
        WC()->cart->remove_coupon('4freeswatches');
        WC()->cart->calculate_totals();
    }
}

// Add colorado retail delivery fee


add_action('woocommerce_cart_calculate_fees', 'rnr_custom_co_tax');

function rnr_custom_co_tax() {

    $non_virtual_total = rnr_non_virtual_total("total");

    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");

    global $woocommerce;

    if (is_admin() && !defined('DOING_AJAX')) return;

    //swatch discount
    /* if ($non_virtual_subtotal >="16"){
       $quantity=0;
       foreach( WC()->cart->get_cart() as $cart_item ) {
    	 	 $product_in_cart = $cart_item['product_id']; 
    
     		 if ( $product_in_cart === 66707 ) { // if there is a swatch in the cart
    
     $quantity =  $cart_item['quantity'];
     $price = $cart_item['data']->get_price();
     
     } 
    		 				 
    	 }
    
    	if ($price == .5){
    	 $discount= -.5;
    	 }
    	if ($price == 1){
    	 $discount= -1;
    	 }
    	if ($price == 1.5){
    	 $discount= -1.5;
    	 }	
    	if ($price >= 2){
    	 $discount= -2;
    	 }	
    	 
    if ($price >0){
    $woocommerce->cart->add_fee( 'Swatch discount', $discount, false, '' );
    }
    
    }*/

    //check if the coupon is applied and then add the co delivery fee
    if (!WC()->cart->has_discount('LocalsClub')) {

        /*	global $woocommerce;
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;*/
        $state = array(
            'CO'
        );
        $tax = .27;
        $customer_state = $woocommerce->customer->get_shipping_state();
        //$non_virtual_total = 0;
        /*  foreach( WC()->cart->get_cart() as $cart_item ){ //moved this into a function
        
        $product_id = $cart_item['product_id']; //store the product id
        
        if( ! $cart_item['data']->is_virtual() ){ // swatches are not virtual so they can have shipping, but don't count    shipping
        
        $non_virtual_total += $cart_item['line_total']; //after discounts
        $non_virtual_subtotal += $cart_item['line_subtotal']; //before discounts
        }
        
        }*/

        if ($non_virtual_total > 0) {

            if (in_array($customer_state, $state)) {

                $woocommerce->cart->add_fee('CO State Retail Delivery Tax', $tax, false, '');

            }
        }
    }
}

//Add paynow button to invoice email
add_action('woocommerce_email_after_order_table', 'rnr_customer_order_invoice', 20, 4);

function rnr_customer_order_invoice($order, $sent_to_admin, $plain_text, $email) {

    if ($email->id == 'customer_invoice') {

        $pay_now_url = esc_url($order->get_checkout_payment_url());

        echo '<a style="
		 
		padding: 10px 20px;
		background: #3341af;
		border: 1px solid;
		border-radius: 6px;
		color: #fff;
		font-size: 20px;
		font-weight: bold;
		margin: 40px 0;
		display: block;
		text-align: center;
		width: 50%;"
		
		href=" ' . $pay_now_url . '">Pay Now</a>';

    }

}

//Allow customer to pay for order without login
//add_filter( 'user_has_cap', 'rnr_order_pay_without_login', 9999, 3 );
function rnr_order_pay_without_login($allcaps, $caps, $args) {
    if (isset($caps[0], $_GET['key'])) {
        if ($caps[0] == 'pay_for_order') {
            $order_id = isset($args[2]) ? $args[2] : null;
            $order = wc_get_order($order_id);
            if ($order) {
                $allcaps['pay_for_order'] = true;
            }
        }
    }
    return $allcaps;
}

