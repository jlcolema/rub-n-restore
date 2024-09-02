<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

	

add_action( 'wp_enqueue_scripts', 'rubnrestore_enqueue_scripts_styles' );

//Enqueues scripts and styles.

function rubnrestore_enqueue_scripts_styles() {
 
  if (is_product()|| is_product_category()){ //stylesheet for products  
	   
		wp_enqueue_style(

		'woo-shop', get_stylesheet_directory_uri() . '/css/woo-products.css',

		false, //no dependancies
		null, // version
		'screen'//media type 
		
		);
		
		}

}



//Remove dashicons

/*function rnr_dequeue_dashicon() {
        if (current_user_can( 'update_core' )) {
            return;
        }
        wp_deregister_style('dashicons');
}
add_action( 'wp_enqueue_scripts', 'rnr_dequeue_dashicon' );
*/