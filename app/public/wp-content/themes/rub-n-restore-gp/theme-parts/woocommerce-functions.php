<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
	
	//woocommerce customization
	
//Woocommerce	
if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	
//Notes on cart updates Oct 25, 2023 - AJ DESIGNS
//Move template override JS to Hooked Element
//return page to theme version, no builder
//remove from full width hook and set on page to remove sidebars
//add custom snippets  below 



///old code below

/*add_filter( 'woocommerce_get_item_data', 'customizing_cart_item_data', 10, 2 );
function customizing_cart_item_data( $cart_data, $cart_item ) {
    $description = $cart_item['data']->get_description(); // Get the product description

    // For product variations when description is empty
    if( $cart_item['variation_id'] > 0 && empty( $description ) ){
        // Get the parent variable product object
        $parent_product = wc_get_product( $cart_item['product_id'] );
        // Get the variable product description
        $description = $parent_product->get_description();
    }

    // If product or variation description exists we display it
    if( ! empty( $description ) ){
        $cart_data[] = array(
            'key'      => __( 'Description', 'woocommerce' ),
            'value'    => $description,
            'display'  => $description,
        );
    }
    return $cart_data;
}	*/
	

	
//Add revision support on products	
add_filter( 'woocommerce_register_post_type_product', 'rnr_add_revision_support' );

function rnr_add_revision_support( $supports ) {
     $supports['supports'][] = 'revisions';

     return $supports;
}
		

if(!is_admin()){
//trim zero's from price 
 add_filter( 'woocommerce_price_trim_zeros', '__return_true' );	
}


//Remove zoom gallery support 
function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
    remove_theme_support( 'wc-product-gallery-lightbox' );
}
add_action( 'wp', 'remove_image_zoom_support', 100 );
 
	
//remove some bulk options
add_filter( 'bulk_actions-edit-shop_order', 'remove_a_bulk_order_action', 20, 1 );
function remove_a_bulk_order_action( $actions ) {
	  unset( $actions['send_reminder'], $actions['cancel_reminder'] );


    return $actions;
}

//edit the bulk options (doesn't work for custom status)
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 100000, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
    //$actions['mark_custom_1'] = __( 'Printed for Shipment', 'woocommerce' );
    //$actions['mark_processing_swatch']    = __( 'Processing Swatch Order', 'woocommerce' );
    $actions['trash']  = __( 'Trash', 'woocommerce' );

    return $actions;
}
//
//set default sort orderto menu_order for product search results

add_action( 'pre_get_posts', 'rnr_search_product_order' );

function rnr_search_product_order(){
if ( is_search() ) {
	$matchFound = (isset($_GET["post_types"]) && trim($_GET["post_types"]) == 'product');//get query var from url
	$isproductSearch = $matchFound ? trim($_GET["post_types"]) : '';

		if ($isproductSearch=="product"){
			add_filter( 'relevanssi_orderby', function() { return array( 'menu_order' => 'asc' ); } );
		}
	}
}
//change add to cart button text on category pages
//add_filter( 'woocommerce_loop_add_to_cart_link', 'ts_replace_add_to_cart_button', 10, 2 );
function ts_replace_add_to_cart_button( $button, $product ) {
	
if (is_product_category() //|| is_shop()
)
 {
	 remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
	/*// $product->get_id()=$prodID;
	
	$prodID =  $product->get_id();
	 
	 if ($prodID=='44632' || $prodID=='44758'){
		 
$button_text = __("Learn More", "woocommerce");
$button_link = $product->get_permalink();
$button = '<a class="button" href="' . $button_link . '">' . $button_text . '</a>';

	}

if ($prodID=='66707'){
		 
$button_text = __("Choose Colors", "woocommerce");
$button_link = $product->get_permalink();
$button = '<a class="button" href="' . $button_link . '">' . $button_text . '</a>';

	}	
if (is_product_category('leather-vinyl-dyes') ){
	$button_text = __("Buy", "woocommerce");
     $button_link = $product->get_permalink();
     $button = '<a class="button" href="' . $button_link . '">' . $button_text . '</a>';
	
	}
	 
return $button;
*/
}

}


// Change 'Choose an option' to use attribute name to be more user friendly.
// Inspired by: https://stackoverflow.com/a/34713246/8605943
/*add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'am_change_option_none_text' );
function am_change_option_none_text( $args ) {
	$args['show_option_none'] = 'Choose ' . wc_attribute_label( $args[ 'attribute' ] );
	
	return $args;
}*/


//Show CHeck Payment Gateway to Admin
add_filter( 'woocommerce_available_payment_gateways', 'bbloomer_paypal_enable_manager' );
 
function bbloomer_paypal_enable_manager( $available_gateways ) {
global $woocommerce;
if ( isset( $available_gateways['cheque'] ) && !current_user_can('administrator') ) {
unset( $available_gateways['cheque'] );
} 
return $available_gateways;
}


//Add the seqential order data meta to Woocommerce search - needed this during the site migration from Bigcommerce

function woocommerce_shop_order_search_order_total( $search_fields ) {

  $search_fields[] = '_order_number';

  return $search_fields;
}
add_filter( 'woocommerce_shop_order_search_fields', 'woocommerce_shop_order_search_order_total' );



//***********************************************
//*Use Free instead of 0 in products (except swatches)
add_filter( 'woocommerce_get_price_html', 'rnr_price_free_zero_empty', 100, 2 );
  
function rnr_price_free_zero_empty( $price, $product ){
 
 
 if(66707===$product->id  ){
	  $price = '<span class="woocommerce-Price-amount amount">$0.50 ea</span>';
 }
	 
 else if ( '' === $product->get_price() || 0 == $product->get_price() ) {
    $price = '<span class="woocommerce-Price-amount amount">Free</span>';
 } 
 
 
return $price;
}


//remove image link in single products
add_filter('woocommerce_single_product_image_thumbnail_html','wc_remove_link_on_thumbnails' );
 
function wc_remove_link_on_thumbnails( $html ) {
     return strip_tags( $html,'<div><img>' );
}

//***************************************************
//Turn Off Woocommerce HTML stripping
add_filter( 'woocommerce_gforms_strip_meta_html', 'configure_woocommerce_gforms_strip_meta_html' );
function configure_woocommerce_gforms_strip_meta_html( $strip_html ) {
    $strip_html = false;
    return $strip_html;
}


//***************************************************
//* Allow processing orders to be edited

add_filter( 'wc_order_is_editable', 'wc_make_processing_orders_editable', 10, 2 );
function wc_make_processing_orders_editable( $is_editable, $order ) {
    if ( $order->get_status() == 'processing' ) {
        $is_editable = true;
    }

    return $is_editable;
}


//******************************
//Remove Results and sorting from shop
//remove display notice - Showing all x results
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
//remove default sorting drop-down from WooCommerce
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );




//Add payment method column to woo order admin screen
add_filter( 'manage_edit-shop_order_columns', 'add_payment_method_column', 20 );
function add_payment_method_column( $columns ) {
 $new_columns = array();
 foreach ( $columns as $column_name => $column_info ) {
 $new_columns[ $column_name ] = $column_info;
 if ( 'order_total' === $column_name ) {
 $new_columns['order_payment'] = __( 'Payment Method', 'my-textdomain' );
 }
 }
 return $new_columns;
}
add_action( 'manage_shop_order_posts_custom_column', 'add_payment_method_column_content' );
function add_payment_method_column_content( $column ) {
 global $post;
 if ( 'order_payment' === $column ) {
 $order = wc_get_order( $post->ID );
 echo $order->payment_method_title;
 }
}

add_action('woocommerce_cart_heading', 'cartHeader', 1);

function cartHeader() {
    echo '<h1>hello</h1>';
}
//Add QTY label to quantity selector
 //add_action( 'woocommerce_before_add_to_cart_quantity', 'rnr_qty_front_add_cart' );


/*  add_action('woocommerce_before_quantity_input_field', 'rnr_text_after_quantity');

 function rnr_text_after_quantity() {
     
        echo '<span class="grid-qty">Qty</span>';
    
} */
//insert QTY in woopack single product module.
/*add_action('woocommerce_before_quantity_input_field', 'rnr_text_after_quantity');
add_action('woopack_loop_before_product_quantity', 'rnr_text_after_quantity');
function rnr_text_after_quantity() {
    echo '<span class="grid-qty">Qty</span>';
}*/ 


//Change choose option text on color to just Size
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'cinchws_filter_dropdown_args', 10 );

function cinchws_filter_dropdown_args( $args ) {
    //$var_tax = get_taxonomy( $args['attribute'] );
    //$args['show_option_none'] = apply_filters( 'the_title', $var_tax->labels->name );
	
	$args['show_option_none'] = 
	//'Choose ' . 
	 wc_attribute_label( $args[ 'attribute' ] );
    return $args;
}
//Customize my account section
add_filter ( 'woocommerce_account_menu_items', 'misha_remove_my_account_links' );
function misha_remove_my_account_links( $menu_links ){
	
	//unset( $menu_links['edit-address'] ); // Addresses
	
	
	//unset( $menu_links['dashboard'] ); // Remove Dashboard
	//unset( $menu_links['payment-methods'] ); // Remove Payment Methods
	//unset( $menu_links['orders'] ); // Remove Orders
	//unset( $menu_links['downloads'] ); // Disable Downloads
	//unset( $menu_links['edit-account'] ); // Remove Account details tab
	//unset( $menu_links['customer-logout'] ); // Remove Logout link
	
	return $menu_links;
	
}
//Move coupon on checkout
//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
//add_action( 'woocommerce_review_order_before_order_total', 'woocommerce_checkout_coupon_form' );


// add custom endpoint for My Account menu
add_filter ( 'woocommerce_account_menu_items', 'rnr_customize_account_menu_items' );
function rnr_customize_account_menu_items( $menu_items ){
     // Add new Custom URL in My Account Menu 
    $new_menu_item = array('affiliates'=>'Affiliates');  // Define a new array with cutom URL slug and menu label text
    $new_menu_item_position=6; // Define Position at which the New URL has to be inserted
    
    array_splice( $menu_items, ($new_menu_item_position-1), 0, $new_menu_item );
    return $menu_items;
}
// point the endpoint to a custom URL
add_filter( 'woocommerce_get_endpoint_url', 'rnr_custom_woo_endpoint', 10, 2 );
function rnr_custom_woo_endpoint( $url, $endpoint ){
     if( $endpoint == 'affiliates' ) {
        $url = 'https://rubnrestore.com/affiliate-dashboard/'; // Your custom URL to add to the My Account menu
    }
    return $url;
}

//Add brand and mpn for schema

function custom_woocommerce_structured_data_product ($data) {
	global $product;
	
	$data['brand'] = 'Rub \'n Restore®';
	$data['mpn'] = $product->get_sku();
	
	return $data;
}
add_filter( 'woocommerce_structured_data_product', 'custom_woocommerce_structured_data_product' );

} // woocommerce is installed

function custom_format_list_of_items( $items ) {
    $item_list = '';
    $count = count( $items );
    foreach ( $items as $index => $item ) {
        $item_list .= $item;
        if ( $index < $count - 2 ) {
            $item_list .= ', ';
        } elseif ( $index === $count - 2 ) {
            $item_list .= ' and ';
        }
    }
    return $item_list;
}

function custom_add_to_cart_message( $message, $products ) {
    $titles = array();
    $count  = 0;

    foreach ( $products as $product_id => $quantity ) {
        $titles[] = '<span class="custom-product-title">' . get_the_title( $product_id ) . '</span>';
        $count += $quantity;
    }

    $titles = array_filter( $titles );
    $added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce' ), custom_format_list_of_items( $titles ) );
    
    $message = sprintf( '%s <a href="%s" class="button wc-forward wp-element-button woo_pdp_view_cart">%s</a>', wp_kses_post( $added_text ), esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'woocommerce' ) );

    return $message;
}
add_filter( 'wc_add_to_cart_message_html', 'custom_add_to_cart_message', 10, 2 );
