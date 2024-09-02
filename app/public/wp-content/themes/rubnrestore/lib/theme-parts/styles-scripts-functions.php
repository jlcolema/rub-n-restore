<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
	
	//all the genesis theme functions
	

add_action( 'wp_enqueue_scripts', 'rubnrestore_enqueue_scripts_styles' );

//Enqueues scripts and styles.

function rubnrestore_enqueue_scripts_styles() {



	wp_enqueue_style(

		'rubnrestore-fonts',

		'//fonts.googleapis.com/css?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400&display=swap|Alice&display=swap',

		array(),

		CHILD_THEME_VERSION

	);
	
	wp_enqueue_style(

		'quadmenu-cleaned-css', get_stylesheet_directory_uri() . '/css/quadmenu-cleaned.css',

		array(),

		CHILD_THEME_VERSION );
		
   if (!is_front_page()){
	   
			wp_enqueue_style(

		'non-home-css', get_stylesheet_directory_uri() . '/css/non-home.css',

		array(),

		CHILD_THEME_VERSION );
		}




/*	wp_enqueue_script( //moved to footer in genesis scripts area

		'rubnrestore',

		get_stylesheet_directory_uri() . '/js/rubnrestore-scripts.js',

		array( 'jquery' ),

		CHILD_THEME_VERSION,

		true

	);*/



}

//Load jquery from google
add_action('init', 'load_google_jquery');
function load_google_jquery () {

        if (is_admin()) {

                return;

        }

        global $wp_scripts;

        if (isset($wp_scripts->registered['jquery']->ver)) {

                $ver = $wp_scripts->registered['jquery']->ver;

                $ver = str_replace("-wp", "", $ver);

        } else {

                $ver = '1.12.4';

        }

        wp_deregister_script('jquery');

        wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/$ver/jquery.min.js", false, $ver);

}


//Remove dashicons

function rnr_dequeue_dashicon() {
        if (current_user_can( 'update_core' )) {
            return;
        }
        wp_deregister_style('dashicons');
}
add_action( 'wp_enqueue_scripts', 'rnr_dequeue_dashicon' );
