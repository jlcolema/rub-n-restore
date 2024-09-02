<?php

/**

 * Rub 'n Restore

 * This file adds functions to the Rub n Restore Theme.

 * @package Rub 'n Restore

 * @author  Basewheel LLC

 * @license GPL-2.0+

 * @link    https://basewheel.com

 */


// Starts the engine.

require_once get_template_directory() . '/lib/init.php';


// Sets up the Theme.

//require_once get_stylesheet_directory() . '/lib/theme-defaults.php';


add_action( 'after_setup_theme', 'rubnrestore_localization_setup' );

/**

 * Sets localization (do not remove).

 * @since 1.0.0

 */

function rubnrestore_localization_setup() {
	
	load_child_theme_textdomain( 'rubnrestore', get_stylesheet_directory() . '/languages' );
}





// Defines the child theme (do not remove).

define( 'CHILD_THEME_NAME', 'Rub n Restore' );

define( 'CHILD_THEME_URL', 'https://www.studiopress.com/' );

define( 'CHILD_THEME_VERSION', '2.6.0' );

//get theme parts

get_template_part('lib/theme-parts/get-started-notification', 'functions'); //Get started forms functions
get_template_part('lib/theme-parts/genesis-setup', 'functions');  
get_template_part('lib/theme-parts/styles-scripts', 'functions'); 
get_template_part('lib/theme-parts/woocommerce', 'functions'); 
get_template_part('lib/theme-parts/admin', 'functions'); 
get_template_part('lib/theme-parts/shipping-checkout', 'functions'); 
get_template_part('lib/theme-parts/gravityforms', 'functions'); 
get_template_part('lib/theme-parts/theme-plugin-custom', 'functions');