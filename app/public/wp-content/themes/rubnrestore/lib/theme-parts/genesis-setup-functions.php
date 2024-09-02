<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

//Genesis functions



// Adds the Genesis Connect WooCommerce notice.

require_once get_stylesheet_directory() . '/lib/woocommerce/woocommerce-notice.php';


//Disable xmlrpc for site speed and security 
add_filter( 'xmlrpc_enabled', '__return_false' );

//*GUTENBERG support
//Add gutenberg editor style
add_theme_support( 'editor-styles' );
add_editor_style( 'css/editor-style.css' );

// Adds theme support for wide and full Gutenberg blocks.
add_theme_support( 'align-wide' );

// Adds a `gutenberg-page` class to the pages using Gutenberg.
add_action(
    'body_class', function( $classes ) {
        if ( function_exists( 'the_gutenberg_project' ) && gutenberg_post_has_blocks( get_the_ID() ) ) {
            $classes[] = 'gutenberg-page';
        }

        return $classes;
    }
);



// Adds Gutenberg opt-in features and styling.

function genesis_child_gutenberg_support() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- using same in all child themes to allow action to be unhooked.
	require_once get_stylesheet_directory() . '/lib/gutenberg/init.php';
}




// Adds support for HTML5 markup structure.

add_theme_support(

	'html5', array(

		'caption',

		'comment-form',

		'comment-list',

		'gallery',

		'search-form',

	)

);



// Adds support for accessibility.

add_theme_support(

	'genesis-accessibility', array(

		'404-page',

		'drop-down-menu',

		'headings',

		'rems',

		'search-form',

		//'skip-links',

	)

);



// Adds viewport meta tag for mobile browsers.

add_theme_support(

	'genesis-responsive-viewport'

);


// Removes header right widget area.

unregister_sidebar( 'header-right' );

// Removes secondary sidebar.

unregister_sidebar( 'sidebar-alt' );

//* Force full-width-content layout setting
add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

// Removes site layouts.

genesis_unregister_layout( 'content-sidebar-sidebar' );

genesis_unregister_layout( 'sidebar-content-sidebar' );

genesis_unregister_layout( 'sidebar-sidebar-content' );

genesis_unregister_layout( 'content-sidebar' );
 
genesis_unregister_layout( 'sidebar-content' );

//* Remove the entry header markup (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );

//* Remove the entry title (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

//* Remove the entry meta in the entry header (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

add_action( 'genesis_theme_settings_metaboxes', 'tdt_remove_metaboxes' );

// Removes output of primary navigation right extras.

remove_filter( 'genesis_nav_items', 'genesis_nav_right', 10, 2 );

remove_filter( 'wp_nav_menu_items', 'genesis_nav_right', 10, 2 );

// * Removes output of unused admin settings metaboxes.


add_action( 'genesis_theme_settings_metaboxes', 'rubnrestore_remove_metaboxes' );

function rubnrestore_remove_metaboxes( $_genesis_admin_settings ) {

	remove_meta_box( 'genesis-theme-settings-header', $_genesis_admin_settings, 'main' );

	remove_meta_box( 'genesis-theme-settings-nav', $_genesis_admin_settings, 'main' );


}

add_filter( 'genesis_customizer_theme_settings_config', 'rubnrestore_remove_customizer_settings' );

// Removes output of header settings in the Customizer.

function rubnrestore_remove_customizer_settings( $config ) {


	unset( $config['genesis']['sections']['genesis_header'] );

	return $config;

}

//Register the sidecart widget
genesis_register_sidebar( array(
	'id'		=> 'side-cart-widget',
	'name'		=> __( 'Side Cart Widget Area', 'rnr' ),
	'description'	=> __( 'This is the widget area in the side-cart area.', 'rnr' ),
) );

//Hook into the Sidecart 

add_action ('woopack_offcanvas_cart_after_items', 'rnr_side_cart_extras');

function rnr_side_cart_extras(){
	if ( function_exists( 'WC' ) ) {
	genesis_widget_area ('side-cart-widget', array(
        'before' => '<div class="side-cart-widget"><div class="wrap">',
        'after' => '</div></div>',
	) );
	}
	}
//* add additional media sizes 
add_image_size( 'medium_square', 300, 300, array( 'center', 'center' ) ); // Hard crop center
	
add_filter( 'image_size_names_choose', 'rnr_custom_sizes' );
 
function rnr_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'shop_catalog' => __( 'Large Square' ),
		 'medium_square' => __( 'Medium Square' ),
    ) );
}	

//Enable WOocommerce galery functionality with genesis
add_action( 'after_setup_theme', 'genesis_theme_setup' );
function genesis_theme_setup() {
//add_theme_support( 'wc-product-gallery-zoom' );
//add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );
}
