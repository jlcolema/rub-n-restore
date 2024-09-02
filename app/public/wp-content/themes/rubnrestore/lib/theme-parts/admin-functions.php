<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
	
	//admin area modifications
	
//ADMIN STYLES
add_action('admin_head', 'rnr_admin_styles');

function rnr_admin_styles() {
	
	
  echo '<style>

  /*styles for the gravity form admin area*/
  
   .forms_page_gf_entries .detail-view img {
    width: 100px;
   }
  
 .forms_page_gf_entries .form-table.entry-details tr {
    float: left;
   
}
.forms_page_gf_entries .form-table select {
    width: 100%;
}

td#field_6_285, td#field_6_275, td#field_6_279, td#field_6_282, td#field_6_288, td#field_6_291, td#field_6_294,td#field_6_300,td#field_6_297, td#field_6_303, td#field_6_312, td#field_6_312, td#field_6_344 {
    min-width: 280px;
}


td#field_6_276, td#field_6_286, td#field_6_257, td#field_6_261, td#field_6_264 , td#field_6_271, td#field_6_276, td#field_6_283, td#field_6_289, td#field_6_292 , td#field_6_295,  td#field_6_301,td#field_6_298,td#field_6_304, td#field_6_313, td#field_6_346 {
    min-width: 138px;
}

td#field_6_287, td#field_6_260, td#field_6_258, td#field_6_263, td#field_6_270 , td#field_6_277, td#field_6_281, td#field_6_284, 
td#field_6_290, td#field_6_293, td#field_6_296,td#field_6_302,td#field_6_299, td#field_6_305, td#field_6_311,td#field_6_314, td#field_6_345 {
  padding-right: 320px; min-width:48px
}
td#field_6_252 {
    padding-right: 600px;
}

td#field_6_354, td#field_6_356 {
    padding-right: 300px;
}

textarea#input_244 {
    min-height: 400px;
}


td#field_6_252 label {
    margin: 0;
    width: 300px;
}


td#field_6_244 {
    width: 1000px;
}

/*.forms_page_gf_entries .form-table tr:nth-child(-n+34) {
    float:left;
    border: 1px solid #ccc;
    height: 80px;
    margin: 0 0 8px 0;
    
    background: #f3f3f3;
    border-right: none;
    border-left: none;
}*/

.forms_page_gf_entries .form-table tr:first-of-type {
    display: block ;
    border: none;
    width: auto;
    margin: 0;
	background: #fff;
    height: auto;
	float: none;
}

/*.forms_page_gf_entries .form-table tr:nth-last-of-type(3n+1) {
    
    clear: left;
}*/
@media only screen and (min-width: 1344px)  {
/*.forms_page_gf_entries .form-table select {
    width: 100% !important;
}

.forms_page_gf_entries .form-table td {

        width: 240px;
}*/
}
  </style>';
  
  
}

//Remove items from wp admin bar top
add_action( 'wp_before_admin_bar_render', 'rnr_admin_bar' );
function rnr_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('updraft_admin_node ');
	$wp_admin_bar->remove_menu('updraft_admin_node');
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('new-content');
	$wp_admin_bar->remove_menu('updates');
	$wp_admin_bar->remove_menu('customize');
	$wp_admin_bar->remove_menu('quadmenu_options');


}

//Add admin bar link for Phone Order - Manual and PHone order plugin
add_action('admin_bar_menu', 'add_toolbar_items', 100);
function add_toolbar_items($admin_bar){
    $admin_bar->add_menu( array(
        'id'    => 'create-phone-order',
        'title' => 'New Phone Order',
        'href'  => 'https://rubnrestore.com/wp-admin/admin.php?page=phone-orders-for-woocommerce#add-order',
        'meta'  => array(
            'title' => __('Create a Phone Order'),            
        ),
    ));
	 $admin_bar->add_menu( array(
        'id'    => 'wc-orders',
        'title' => 'View Orders',
        'href'  => 'https://rubnrestore.com/wp-admin/edit.php?post_type=shop_order',
        'meta'  => array(
            'title' => __('View Orders'),            
        ),
    ));
   
}

///////////////////////////////////////////////////////////////////
//Add a filter to the How-to Resources admin page to filter by custom taxonomy

add_action( 'restrict_manage_posts', 'rnr_restrict_manage_posts' );
function rnr_restrict_manage_posts() {
    global $typenow;
    $taxonomy = 'how_to_category'; // Change this
    if( $typenow == 'how_to' ){
        $filters = array($taxonomy);
        foreach ($filters as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            $tax_name = $tax_obj->labels->name;
            $terms = get_terms($tax_slug);
            echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
            echo "<option value=''>Show All $tax_name</option>";
            foreach ($terms as $term) { 
                $label = (isset($_GET[$tax_slug])) ? $_GET[$tax_slug] : ''; // Fix
                echo '<option value='. $term->slug, $label == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
            }
            echo "</select>";
        }
    }
}

//Stop spam

//remove the author url field
add_filter('comment_form_default_fields', 'unset_url_field');
function unset_url_field($fields){
    if(isset($fields['url']))
        unset($fields['url']);
    return $fields;
}
