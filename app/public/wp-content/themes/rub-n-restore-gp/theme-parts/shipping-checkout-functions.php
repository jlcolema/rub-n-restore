<?Php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//all the customized shipping functions are here

//LIVE SITE
//Edited 04/24/2023 4:56PM MDT by AJ Designs :
//Notes - this file had been overwritten from the staging site and the changes to shipping had been reverted. This version merges all changes. 
//Major update 10/25/2023 AJ - added cart and checkout overhaul in combination with removal of some templates and addition of elements in the theme for css and js. Also moved some checkout related functions here from Woocommers-functions.php.


	
//Woocommerce	
if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	
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
	$virtual_subtotal = 0;
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
			$virtual_subtotal += $cart_item['line_subtotal']; //before discounts
        }

    }
    if ($arg == "virtual") { //virtual products (services)
        return $virtual_total;

    }
	
	if ($arg == "virtual_subtotal") { //virtual products before discounts
	        return $virtual_subtotal;

    }

    if ($arg == "subtotal") { //before discounts
        return $non_virtual_subtotal;

    }

    if ($arg == "total") { //after discounts
        return $non_virtual_total;
    }

}


//Exclude virtual products from free shipping
//Set the shipping rates to compensate for virtual products
add_filter('woocommerce_package_rates', 'custom_shipping_option', 20, 2);

function custom_shipping_option($rates, $package) {

    //if (! is_admin()){
    //Check if a coupon has applied free shipping
    $hasfreeShipcoupon = '';
	
	//$free_shipping_min = '50'; 
	
	$free_shipping_min = get_field('free_shipping_threshold', 373); //SET THE FREE SHIPPING MAX AMOUNT ON THE CART PAGE

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
	
    if (rnr_customer_country() == 'US' ) {

        if ($non_virtual_total < $free_shipping_min && $hasfreeShipcoupon == "true") {

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
                if ('flexible_shipping_single:29' == $rate_key) {  //This is the USPS flat rate, the only method in use.
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

        else if ($non_virtual_total < $free_shipping_min) { //only show the $6.95 shipping method

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


        //If the cart total is over $$free_shipping_min
        else if ($non_virtual_total >= $free_shipping_min) {

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

                    $rates[$rate_key]->label = __(' Free shipping: Product orders over $'. $free_shipping_min, 'woocommerce'); // New label name
                    
                }
            }

        }

    }

    else if ($USterritory == 'yes') { // US territories shipping methods same as US
        if ($non_virtual_total < $free_shipping_min && $hasfreeShipcoupon == "true") {

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

        else if ($non_virtual_total < $free_shipping_min) { // $6.95 method only

            //echo $non_virtual_total;
            foreach ($rates as $rate_key => $rate) {

                //Remove free shipping if it isn't granted by a coupon
                if ($hasfreeShipcoupon != "true") {

                    if ('free_shipping' == $rate->method_id)

                    unset($rates[$rate_key]);
                }
				 //flexible_shipping_single:44 is the default and will always show 
				 
				//We need to remove the $7 shipping option here it is set to show always.
                if ('flexible_shipping_single:37' == $rate_key) { //$7 ship
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

      

        //If the cart total is over $$free_shipping_min then just unset the $7 option
        else if ($non_virtual_total >= $free_shipping_min) {

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

                    $rates[$rate_key]->label = __('Free shipping on product orders over $'.$free_shipping_min.'. Or use one of the discounted UPS rates below:   ', 'woocommerce'); // New label name
                    
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



///////////////////
//Show the Virtual and Regular subtotals in the cart and checkout totals table
 
 //This is a custom hook inserted in a template override at: yourtheme/woocommerce/cart/cart-totals.php.
add_action('RNR_woocommerce_cart_hook_before_shipping', 'display_cart_virtual_total', 0, 50); 
 
 //This is a custom hook inserted in a template override at: yourtheme/woocommerce/checkout/review-order.php.
 add_action('RNR_woocommerce_checkout_extra_subtotals', 'display_cart_virtual_total', 0, 1);

//old hooks 
//add_action('woocommerce_review_order_before_shipping', 'display_cart_virtual_total', 0, 1);
 //add_action('woocommerce_cart_totals_before_shipping', 'display_cart_virtual_total', 0, 1); // This only fires if shipping is set

function display_cart_virtual_total() {
	
	
	$free_shipping_min = get_field('free_shipping_threshold', 373); //in Cart

    // $non_virtual_total = 0;
    // $virtual_total = 0;
    // $non_virtual_subtotal = 0;
    setlocale(LC_MONETARY, 'en_US');
    $virtual_total = rnr_non_virtual_total("virtual");
	$virtual_subtotal = rnr_non_virtual_total("virtual_subtotal");
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
	//check for swatch
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_in_cart = $cart_item['product_id'];
        if ($product_in_cart === 66707) $cart_has_swatch = "true"; // if there is a swatch in the cart
        
    }	

    $free_shipping_us = $free_shipping_min - $non_virtual_total;

    $free_shipping_ca = 150 - $non_virtual_total;

    $free_shipping_message = "";

    $hasaCoupon = "";

    if (count(WC()->cart->get_applied_coupons()) > 0) {
        $hasaCoupon = "true";
    }

    //USA
    if (rnr_customer_country() == 'US' || rnr_customer_country() == 'default') {
		
       //Depreciated 10-13-23 AJ 
      /*  if ($hasaCoupon == "true" && $hasfreeShipcoupon != "true" && $non_virtual_subtotal >= $free_shipping_min && $non_virtual_subtotal - $non_virtual_total < $free_shipping_min) {
			
            $free_shipping_message = 'Your coupon lowered your product order below the free shipping cutoff. <br>';
        }
        if ($free_shipping_us <= $free_shipping_min && $free_shipping_us > 0 && $hasfreeShipcoupon != "true") {

            $free_shipping_message .= 'Add $' . number_format($free_shipping_us, 2) . "\n" . ' to your cart for free shipping!';

        }

        else if ($hasfreeShipcoupon == "true") {

            $free_shipping_message = "Your coupon gives you free shipping!";

        }

        else if ($non_virtual_total >= $free_shipping_min) {

            $free_shipping_message = "Your order qualifies for free shipping!";

        }*/

        //If they have virtual and non-virtual products
        if ($non_virtual_total > 0 && $virtual_total > 0) {
			
			  if ($non_virtual_total > 0) {//Show products subtotal
			  

                // The Output
                echo ' <tr class="cart-virtual-total custom-subtotals">
            <th>' . __("Products Subtotal ", "woocommerce") . '</th>
            <td data-title="non-virtual-total">$' . number_format($non_virtual_subtotal, 2) . "\n" . '<br><em><span>' . $free_shipping_message . '</span></em> </td>
        </tr>';
            }
        

            if ($virtual_total > 0) {//Show services subtotal

                // The Output
                echo ' <tr class="products-virtual-subtotal custom-subtotals"   >
            <th>' . __("Services Subtotal ", "woocommerce") . ' <br><em class="small"> (Not counted for free shipping)  </em> </th>
            <td data-title="virtual-total">$' . number_format($virtual_subtotal, 2)  . '</td>
        </tr>';
            }
			
		}	

/*      //Depreciated 10-13-23 AJ
        //If there are non-virtual products only and a free shipping coupon is not included
        if ($non_virtual_total > 0 && $virtual_total == 0 && $free_shipping_us > 0) {
            echo ' <tr class="cart-products-total"  style="font-size:16px">
				<th>' . __("", "woocommerce") . '</th>
				<td data-title="free-shipping-message"><em class="small"> ' . $free_shipping_message . ' </em> </td>
			</tr>';
        }*/

    
	}

    //CANADA & Mexico DISABLED FREE SHIPPING TO CA MX 4.16-2020
    

    
}

//////////////////////////////////////////////////////////
//Show message that shipping needs to be added in checkout
add_action('woocommerce_review_order_before_order_total', 'rnr_needs_shipping');
function rnr_needs_shipping(){
		
	$free_shipping_min = get_field('free_shipping_threshold', 373); // in cart
	 
    setlocale(LC_MONETARY, 'en_US');
    $virtual_total = rnr_non_virtual_total("virtual");
	$virtual_subtotal = rnr_non_virtual_total("virtual_subtotal");
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
 
    $free_shipping_us = $free_shipping_min - $non_virtual_total;
      
    if ($non_virtual_total > 0 && $hasfreeShipcoupon !="true" && $non_virtual_total <= $free_shipping_min) { 

	 if( WC()->cart->show_shipping()  ){

	 	}
		
	else {
		 echo '<tr class="checkout-needs-shipping" >
                 <th colspan="2" class="needs_shipping" style="background:#fff; text-align:right">Enter an address to calculate shipping.</th>
				 <td style="padding:.1px !important"></td>
              </tr> ';
	   	 }
    }
}
///////////
//move email below name
add_filter( 'woocommerce_billing_fields', 'rnr_move_checkout_email_field' );
 
function rnr_move_checkout_email_field( $address_fields ) {
    $address_fields['billing_email']['priority'] = 25;
	$fields['company']['priority'] = 8;
    return $address_fields;
}	
///////////////////////////
//Move coupon at checkout

// Hide default woocommerce coupon field
add_action( 'woocommerce_before_checkout_form', 'hide_checkout_coupon_form', 5 );
function hide_checkout_coupon_form() {
    echo '<style>.woocommerce-form-coupon-toggle {display:none;}</style>'; 
}


// Add a custom coupon field before checkout payment section
add_action( 'woocommerce_review_order_before_payment', 'woocommerce_checkout_coupon_form_custom' );
function woocommerce_checkout_coupon_form_custom() {
    echo '<div class="checkout-coupon-toggle"><div class="woocommerce-info">' . sprintf(
        __('<img src="https://rubnrestore.com/wp-content/uploads/2023/10/coupon.png" height="15" width="20">%s'), '<a href="#" class="show-coupon">' . __("Click here to enter a coupon code") . '</a>'
    ) . '</div></div>';

    echo '<div class="coupon-form" style="margin-bottom:20px;" style="display:none !important;">
        
        <p class="form-row form-row-first woocommerce-validated">
            <input type="text" name="new_coupon_code" class="input-text" placeholder="' . __("Coupon code") . '" id="new_oupon_code" value="">
        </p>
        <p class="form-row form-row-last">
            <button type="button" class="button" name="apply_coupon" value="' . __("Apply coupon") . '">' . __("Apply coupon") . '</button>
        </p>
        <div class="clear"></div>
    </div>';
}

// jQuery code for coupon in Checkout JS code element 
 

//order-received jquery //not using element to target order recieved only
//hide eval fields in order received
//add_action('wp_footer', 'order_received_js_script');
function order_received_js_script() {
    // Only on order received" (thankyou)
    if( ! is_wc_endpoint_url('order-received') )
        return; // Exit

 
    ?>
    <script>
  jQuery("strong:contains('Are there stains? Check all that apply::')").remove();
  jQuery("li:contains('Water, wine, coffee, juice or cola stains')").remove();
  jQuery('strong:contains("Check all other options that apply to your project::"),strong:contains("Check all other options that apply to your project::")').parent().hide();  
    </script>
    <?php
}
	

	
/////////////////////////////
//change proceed to cart text
function woocommerce_button_proceed_to_checkout() { ?>
 <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward">
 <?php esc_html_e( 'Proceed to Secure Checkout', 'woocommerce' ); ?>
 </a>
 <?php
}

//////////////////////////
// Change Cart Totals Text
add_filter( 'gettext', 'change_cart_totals_text', 20, 3 );
function change_cart_totals_text( $translated, $text, $domain ) {
    if( is_cart() && $translated == 'Cart totals' ){
        $translated = __('Cart Totals & Shipping ', 'woocommerce');
    }
    return $translated;
	
}
/////////////////////////////
//Change billing details text
add_filter( 'gettext', 'wc_billing_field_strings', 20, 3 );
function wc_billing_field_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Billing details' :
            $translated_text = __( 'Enter Your Billing & Shipping Info', 'woocommerce' );
            break;
			
		case 'Your order' :
            $translated_text = __( 'Order Summary', 'woocommerce' );
            break;	
		case 'Click here to login' :
            $translated_text = __( 'Login', 'woocommerce' );
            break;				
  
	     case 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.' :
            $translated_text = __( 'Customers with accounts may login, otherwise please continue to billing. Guest checkout is always welcome. ', 'woocommerce' );
            break; 
			
		  }	
    return $translated_text;
}

//////////////////////////////////////////////////////
//rename checkout fields

add_filter( 'woocommerce_default_address_fields' , 'rnr_rename_state_province', 9999 );
 
function rnr_rename_state_province( $fields ) {
    $fields['address_1']['label'] = 'Street address or PO box';
    return $fields;
}
////////////////////////////
//Change customer login
 add_filter( 'woocommerce_checkout_login_message', 'rnr_return_customer_message' );
 
function rnr_return_customer_message() {
return 'Customer account: ';
} 

/////////////////////////
// Rename the "Have a Coupon?" message on the checkout page

function woocommerce_rename_coupon_message_on_checkout() {

	return  ' <a href="#" class="showcoupon">' . __( 'Click to add a coupon.', 'woocommerce' ) . '</a>';
}
add_filter( 'woocommerce_checkout_coupon_message', 'woocommerce_rename_coupon_message_on_checkout' );

 
//Add price suffix in cart and checkout
function addPriceSuffix($format, $currency_pos) {
	switch ( $currency_pos ) {
		case 'left' :
			$currency = get_woocommerce_currency();
			$format = '%1$s%2$s&nbsp;' . $currency;
		break;
	}
 
	return $format;
}
 
function addPriceSuffixAction() {
	add_action('woocommerce_price_format', 'addPriceSuffix', 1, 2);
}
 
add_action('woocommerce_before_cart', 'addPriceSuffixAction');
add_action('woocommerce_review_order_before_order_total', 'addPriceSuffixAction');


//Move Shipping Message to top Cart Totals Section

add_action ('woocommerce_cart_totals_before_order_total', 'rnr_ship_notice');

function rnr_ship_notice(){
	$free_shipping_min = get_field('free_shipping_threshold', 373); //in cart
    setlocale(LC_MONETARY, 'en_US');
    $virtual_total = rnr_non_virtual_total("virtual");
    $non_virtual_total = rnr_non_virtual_total("total");
    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");
	$free_shipping_us = $free_shipping_min - $non_virtual_total;
    
    $free_shipping_message = "";
	
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
 //check for swatch
	$cart_has_swatch = "";
	
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_in_cart = $cart_item['product_id'];
        if ($product_in_cart === 66707) $cart_has_swatch = "true"; // if there is a swatch in the cart
        
    }	
	
 	 if( rnr_customer_country() =='default' || rnr_customer_country() =='US' ){
       
	echo '<div class="shipping-threshold">
   		  <div class="threshhold-align">
    	  <div class="threshold-inner">';
		  

		  
	 if ($virtual_total > 0 && $non_virtual_total <= 0 && $cart_has_swatch !='true'){
			
			 echo 'No shipping charges on your order!';
			
			}
			
	else if ($virtual_total > 0 && $non_virtual_total <= 0 && $cart_has_swatch =='true'){
			
			 echo 'Your color swatches will ship via USPS.';
			
			}	
		
		else if ($free_shipping_us <= $free_shipping_min && $free_shipping_us > 0 && $hasfreeShipcoupon != "true") {
	  
      	echo  '  Add <span class="amount-more">' . number_format($free_shipping_us, 2) .' more </span> for <span class="free-shipping"> FREE Shipping </span> to the U.S.';
		 
		
		//if (rnr_customer_country() =='default' && $virtual_total > 0 ){echo '<div style="margin-top: 5px;"><small>(Service items do not count toward free shipping.)</small></div>';}
		 
    	 
	   }
	   else if ($non_virtual_total >= $free_shipping_min) {
		   
		   echo'Congrats! You’re eligible for <span class="free-shipping"> FREE Shipping </span> to the U.S.';
	   }
	   
	   
        else if ($hasfreeShipcoupon == "true") {

            echo 'Your coupon gives you <span class="free-shipping">free shipping!</span>';

        }
		
		
	   
		 echo'</div>
   		 </div>
 		 </div>';
 
	 } // US or not set country
	 
	 else {
	  	echo '<div class="shipping-threshold">
   		  <div class="threshhold-align">
    	  <div class="threshold-inner">';
		  
		   echo '<strong>International duties and fees may apply.</strong> See <a href="'. get_site_url() .'/shipping-info/" target="_blank">Shipping Policy</a>.';
	 
	  echo'</div>
   		 </div>
 		 </div>';
	 }
	
	}//Ship notice
//add checkout to cart
add_action( 'woocommerce_before_cart', 'rnr_add_checkout_button' );
function rnr_add_checkout_button( $checkout ) {
    echo '<div class="mobile-checkout-btn text-right"><a href="' . esc_url( WC()->cart->get_checkout_url() ) . '" class="checkout-button button alt wc-forward" >' . __( 'Proceed to Secure Checkout', 'woocommerce' ) . '</a></div>';
}		
		
//add category to services in cart


add_filter( 'woocommerce_cart_item_name', 'custom_text_cart_item_name2', 10, 3 );
function custom_text_cart_item_name2( $item_name, $cart_item, $cart_item_key ) {
global $woocommerce;

$product_id = $cart_item['product_id'];

if( has_term( array('color-matching-services'), 'product_cat', $cart_item['product_id'] ) ) {
return '<div class="cart-service" style="font-size:13px;margin-bottom:5px">Service:</div>' . $item_name ;

}

else {
return $item_name;
}


}	

//Add image to order received and checkout pages
add_filter( 'woocommerce_order_item_name', 'rnr_product_image_on_thankyou', 10, 3 );
  
function rnr_product_image_on_thankyou( $name, $item, $visible ) {
 
    /* Return if not thankyou/order-received page */
    if ( ! is_order_received_page()   ) {
        return $name;
    }
     
    /* Get product id */
    $product_id = $item->get_product_id();
      
    /* Get product object */
    $_product = wc_get_product( $product_id );
  
    /* Get product thumbnail */
    $thumbnail = $_product->get_image();
  
    /* Add wrapper to image and add some css */
 //Add wrapper to image and add some css 
    $image = '<span class="db-product-image" style="vertical-align: middle;
    padding-right: 10px;">'
                . $thumbnail .
            '</span>'; 
    $name = '<span class="db-product-name">' . $name . '</span>';
  
    /* Prepend image to name and return it */
    return $image . $name;
}

//Add images to checkout items

 add_filter( 'woocommerce_cart_item_name', 'rnr_product_image_on_checkout', 10, 3 );
 
function rnr_product_image_on_checkout( $name, $cart_item, $cart_item_key ) {
     
    // Return if not checkout page 
    if (   is_checkout()  ) {
     
    // Get product object 
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
 
    //Get product thumbnail 
    $thumbnail = $_product->get_image( array( 40, 40 ) );
 
    //Add wrapper to image and add some css 
    $image = '<span class="db-product-image" style="vertical-align: middle;
    padding-right: 10px;">'
                . $thumbnail .
            '</span>'; 
    $name = '<span class="db-product-name">' . $name . '</span>';
    // Prepend image to name and return it
    return $image . $name;
	}
	else  { return $name;   }
} 

//Change swatch coupon removed message
function rnr_coupon_error_message_change($err, $err_code, $WC_Coupon) {
    switch ( $err_code ) {

//CHANGE HIGHLIGHTED COUPON CODE

        case $WC_Coupon::E_WC_COUPON_INVALID_REMOVED:

            $err = '';
    }
    return $err;
}

add_filter( 'woocommerce_coupon_error','rnr_coupon_error_message_change',10,3 );


//add international shipping notification
//Removed 10-9-2023 AJ
/*add_action('woocommerce_checkout_before_order_review', 'rnr_echo_notice_shipping');

function rnr_echo_notice_shipping() {
    echo '<div class="shipping-notice" style="display:none">Rub \'n Restore, Inc. is not responsible for international duties or taxes. Please read about <a href="https://rubnrestore.com/shipping-info/" target="_blank" title="shipping">international shipments.</a></div>';
}*/

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

   // echo do_shortcode('[fl_builder_insert_layout id="73611"]');
   
   echo '<div class="checkout-gform"> 
   
   <h3>Subscribe to our newsletter?</h3>
   <p>We don\'t automatically add you, so sign up to stay abreast of new how-to videos and special coupon codes.</p>';
	
	echo do_shortcode('[gravityform id="15" title="false"]');
	
	echo '</div>';

}
//automatically update cart total when quantity is changed
//Added 9-19-2023 - AJ 
	
add_action( 'wp_head', 'rnr_cart_refresh_update_hide_button' );
function rnr_cart_refresh_update_hide_button() { 
	?>
	<style>
		.woocommerce button[name="update_cart"],
		.woocommerce input[name="update_cart"] { display: none; }
	</style>
	<?php
}

add_action( 'wp_footer', 'rnr_cart_refresh_update_qty' ); 
function rnr_cart_refresh_update_qty() { 
	if ( is_cart() || ( is_cart() && is_checkout() ) ) {
		?>
		<script>
			jQuery( function( $ ) {
				let timeout;
				jQuery('.woocommerce').on('change', 'input.qty', function(){
					if ( timeout !== undefined ) {
						clearTimeout( timeout );
					}
					timeout = setTimeout(function() {
						jQuery("[name='update_cart']").trigger("click"); // trigger cart update
					}, 300 ); //  (500) seems comfortable too
				});
			} );
		</script>
		<?php
	}
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
//Hidden top shipping
//add_action('woocommerce_cart_totals_before_shipping', 'rnr_USPS_notification');

/*function rnr_USPS_notification() {

    echo '<div class="shipping-policy"><!--Edit this in shipping-checkout-functions.php--><small>*We process your order same-day or next-business-day. However, due to COVID, carriers are not guaranteeing delivery times. See <a href="' . get_permalink(3019) . '" target="_blank" title="Shipping Policy">Shipping Policy</a></small></div> ';

}*/
//* Add a message to shipping calculator on checkout, woocommerce
//add_action('woocommerce_after_shipping_calculator', 'rnr_zip_notification');
add_action('woocommerce_after_shipping_calculator', 'rnr_zip_notification');
function rnr_zip_notification() {

  //  echo '<p><!--Edit this in shipping-checkout-functions.php--><small>We not responsible for duties or taxes. See <a href="' . get_permalink(3019) . '" target="_blank" title="Shipping Policy">Shipping Policy</a></small></p> ';
	    echo '<div class="shipping-policy"><!--shipping-checkout-functions--> See <a href="' . get_permalink(3019) . '" target="_blank" title="Shipping Policy">Shipping Policy</a> </div> ';

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

//auto redirect to checkout when eval is added
//Redirect users after add to cart.
/*function my_custom_add_to_cart_redirect($url) {

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
add_filter('woocommerce_add_to_cart_redirect', 'my_custom_add_to_cart_redirect');*/

//////////////////////////////
//Apply swatch coupon to cart
add_action('woocommerce_update_cart_action_cart_updated', 'rnr_apply_coupon_if_swatch');
add_action('woocommerce_before_cart', 'rnr_apply_coupon_if_swatch');

function rnr_apply_coupon_if_swatch() {

    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");
    //echo "A: " . $non_virtual_subtotal;
    

    if ($non_virtual_subtotal >= "8") {

        // if product in the cart
        foreach (WC()->cart->get_cart() as $cart_item) {

            $product_in_cart = $cart_item['product_id'];
            $price = 0;
            // $quantity=0;
            if ($product_in_cart === 66707) { // if there is a swatch in the cart
                // $quantity =  $cart_item['quantity'];
                $price = $cart_item['data']->get_price();

                if ($price == .5) {
                    if (!WC()->cart->has_discount('free swatch')) {
                        WC()->cart->apply_coupon('free swatch');
						wc_clear_notices();
                    }
                }

                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('free swatch');
                    WC()->cart->calculate_totals();
                }
                if ($price == 1) {
                    if (!WC()->cart->has_discount('free swatches x2')) {
                        WC()->cart->apply_coupon('free swatches x2');
						wc_clear_notices();
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('free swatches x2');
                    WC()->cart->calculate_totals();
                }
                if ($price == 1.5) {
                    if (!WC()->cart->has_discount('free swatches x3')) {
                        WC()->cart->apply_coupon('free swatches x3');
						wc_clear_notices();
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('free swatches x3');
                    WC()->cart->calculate_totals();
                }
                if ($price >= 2) {
                    if (!WC()->cart->has_discount('free swatches x4')) {
                        WC()->cart->apply_coupon('free swatches x4');
						wc_clear_notices();
						
                    }
                }
                else { // if product changed or removed from cart we remove the coupon
                    WC()->cart->remove_coupon('free swatches x4');
                    WC()->cart->calculate_totals();
                }

            }
        }
    }

    else { //if less than 8 subtotal
        WC()->cart->remove_coupon('free swatch');
        WC()->cart->remove_coupon('free swatches x2');
        WC()->cart->remove_coupon('free swatches x3');
        WC()->cart->remove_coupon('free swatches x4');
        WC()->cart->calculate_totals();
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

// Move the Klaviyo email field.
/*add_action( 'plugins_loaded', 'rnr_remove_plugin_filter' );
function rnr_remove_plugin_filter() {
   remove_filter( 'woocommerce_checkout_fields', 'kl_checkbox_custom_checkout_field', 11 );
}*/

//DEPRECIATED OR NOT IN USE

//Show messages when virtual products are in the cart
/*add_action('woocommerce_before_cart', 'rnr_check_category_in_cart');

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

        wc_print_notice('<strong>Note: </strong>Color Matching Sevices do not count toward free shipping. ', 'notice');

    }

}*/

// Add colorado retail delivery fee
 //Disabled 7/12/2023 per email from Lesandre small businesses are exempt
/*
add_action('woocommerce_cart_calculate_fees', 'rnr_custom_co_tax');

function rnr_custom_co_tax() {

    $non_virtual_total = rnr_non_virtual_total("total");

    $non_virtual_subtotal = rnr_non_virtual_total("subtotal");

    global $woocommerce;

    if (is_admin() && !defined('DOING_AJAX')) return;

    //check if the coupon is applied and then add the co delivery fee
    if (!WC()->cart->has_discount('LocalsClub')) {
 
        $state = array(
            'CO'
        );
        $tax = .27;
        $customer_state = $woocommerce->customer->get_shipping_state();
 

        if ($non_virtual_total > 0) {

            if (in_array($customer_state, $state)) {

                $woocommerce->cart->add_fee('CO State Retail Delivery Tax', $tax, false, '');

            }
        }
    }
} */


} //woocommerce is active