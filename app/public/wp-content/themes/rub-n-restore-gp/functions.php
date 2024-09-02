<?php

//Custom Generatepress child theme

	 add_action( 'wp_enqueue_scripts', 'rub_n_restore_gp_enqueue_styles' );
	 function rub_n_restore_gp_enqueue_styles() {
 		  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
 		  }


		    //get theme parts
			get_template_part('theme-parts/get-started-notification', 'functions');  // The Evaluation Notification
			get_template_part('theme-parts/woocommerce', 'functions');  // Woo customizations (See also Woocommerce folder)
			get_template_part('theme-parts/admin', 'functions'); // Styles and changes to admin area
			get_template_part('theme-parts/shipping-checkout', 'functions'); // Customizations for checkout
			get_template_part('theme-parts/gravityforms', 'functions'); // Customizations for Gravityforms
			get_template_part('theme-parts/plugin-custom', 'functions'); // Customizations for misc plugins
			get_template_part('theme-parts/styles-scripts', 'functions'); // Loading styles and scripts



//Generatepress specific functions
//[year]
function year_shortcode() {
  $year = date('Y');
  return $year;
}
add_shortcode('year', 'year_shortcode');
//change coments title
add_filter( 'generate_comment_form_title', function() {
    $comments_number = get_comments_number();

    return sprintf(
        esc_html( _nx(
         '%1$s comments',
	     '%1$s comments',
            $comments_number,
            'comments title',
            'generatepress'
        ) ),
        number_format_i18n( $comments_number ),
	get_the_title()
    );
} );

//Generatepress 404 full width
	add_filter( 'generate_sidebar_layout', function( $layout ) {
    // If we are viewing search results, set the sidebar
    if  (is_404() ) {
        return 'no-sidebar';
    }

    // Or else, set the regular layout
    return $layout;
 } );

 /* Custom Variation */

 // Displays the price before the "Add to Cart" text on products with variations.

 function enqueue_custom_variation_script() {
    wp_register_script('custom-variation-script', get_stylesheet_directory_uri() . '/js/custom-variation.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('custom-variation-script');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_variation_script');

function localize_variation_script() {
    global $product;

    // Check if we're on a single product page and if the global $product is a product
    if (is_product() && is_a($product, 'WC_Product')) {
        $translation_array = array(
            'add_to_cart_text' => esc_js($product->single_add_to_cart_text())
        );
        wp_localize_script('custom-variation-script', 'wc_custom_params', $translation_array);
    }
}

add_action('woocommerce_before_single_product', 'localize_variation_script');




 ?>
