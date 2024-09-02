<?Php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
	
	//misc theme and plugin customizations
//add mime times for image upload
function rivendellweb_mime_types( $mime_types ) {
  // WebP images
 // $mime_types['webp'] = 'image/webp';
  // HEIF Images
  $mime_types['heic'] = 'image/heic';
  $mime_types['heif'] = 'image/heif';
  // HEIF Image Sequence
 // $mime_types['heics'] = 'image/heic-sequence';
 // $mime_types['heifs'] = 'image/heif-sequence';
  // AVIF Images
 // $mime_types['avif'] = 'image/avif';
  // AVIF Image Seuqence
//  $mime_types['avis'] = 'image/avif-sequence';

  return $mime_types;
}
add_filter( 'upload_mimes', 'rivendellweb_mime_types', 1, 1 );	

//*Add menu order to posts so we can create a specific order for help/faq etc
add_action( 'init', 'posts_order_rnr' );
function posts_order_rnr() 
{
    add_post_type_support( 'post', 'page-attributes' );
}

//* Boost exact search matches
//add_filter('relevanssi_results', 'rlv_exact_boost');
/*function rlv_exact_boost($results) {
	$query = strtolower(get_search_query());
	foreach ($results as $post_id => $weight) {
		$post = relevanssi_get_post($post_id);
 
                // Boost exact title matches
		if (stristr($post->post_title, $query) != false) $results[$post_id] = $weight * 50;
 
                // Boost exact matches in post content
		if (stristr($post->post_content, $query) != false) $results[$post_id] = $weight * 50;
	}
	return $results;
}*/

 
//Phone Orders Pro
	
if( is_plugin_active( 'phone-orders-for-woocommerce-pro/phone-orders-for-woocommerce-pro.php' ) ) {
	
 
	
//Allow Phone orders to update cart amount automatically and allow customized shipping
add_filter('wpo_custom_price_for_shipping_in_loaded_order_enabled', function($isCustomPriceEnabled) {
    return false;
}, 10, 1);
} //phone orders pro is active

 
//Relevanssi 
if(in_array('relevanssi/relevanssi.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
	
	
//Set placeholder text for different relevanssi search forms
add_filter( 'relevanssi_search_form', function( $form ) {
	
	$posttype1 = 'how_to';
	$posttype2 = 'product';
	
	if ( strpos( $form, $posttype1 ) !== false) {
		
      $form = str_replace( 'placeholder="Search this website', 'placeholder="Search FAQs', $form );
   }
  	if ( strpos( $form, $posttype2 ) !== false) {
		
      $form = str_replace( 'placeholder="Search this website', 'placeholder="Search our Catalog', $form );
   }
  
  return $form;
} );

//Increasee weight for all hits in custom taxonomies
add_filter( 'relevanssi_match', 'rlv_all_taxonomy_boost' );
function rlv_all_taxonomy_boost( $match ) {
	if ( $match->taxonomy > 0 ) {
	
		$match->weight = $match->weight * 20 * $match->taxonomy;
		
	}
	return $match;
}

//search all custom fields and boost - see settings in admin for which fields are indexed
add_filter( 'relevanssi_match', 'rlv_customfield_boost' );
function rlv_customfield_boost( $match ) {
	if ( $match->customfield > 0 ) {
		$match->weight *= 10000;
	}
	return $match;
}

/// don't index content in products
add_filter( 'relevanssi_post_content', 'rlv_no_content', 10, 2 );
function rlv_no_content( $content, $post ) {
    if ( 'product' === $post->post_type ) {
        $content = '';
    }
    return $content;
}

}// relevanssi is active


 
//Woopack
if(in_array('woopack/woopack.php', apply_filters('active_plugins', get_option('active_plugins')))){
	 
	
//remove no-lazy from shop
add_filter( 'woopack_product_image_html_attrs', function($attrs) {
   if ( isset( $attrs['data-no-lazy'] ) ) {
      unset( $attrs['data-no-lazy'] );
   }
   return  $attrs;
} );

 //Link to single product from woopack single product module
 
add_action( 'woopack_single_product_before_title_wrap', 'link_woopack_single_product_open', 11, 2 );
add_action( 'woopack_single_product_before_image_wrap', 'link_woopack_single_product_open', 11, 2 );
function link_woopack_single_product_open( $settings, $product ) {
	echo '<a href="' . get_permalink( $product->get_id() ) . '" style="display: block;">';
}


add_action( 'woopack_single_product_after_title_wrap', 'link_woopack_single_product_close', 10, 2 );
add_action( 'woopack_single_product_after_image_wrap', 'link_woopack_single_product_close', 10, 2 );
function link_woopack_single_product_close( $settings, $product ) {
	echo '</a>';
}


}//woopack is active


//*******************************************
//Shortcode for HTML SiteMap

// HTML Site Map 
add_shortcode('sitemap', 'sitemap');
function sitemap() {
    $sitemap = '';
/*    

	$sitemap .= '<h4>Articles </h4>';
    $sitemap .= '<ul class="sitemapul">';
    $posts_array = get_posts();
    foreach ($posts_array as $spost):
        $sitemap .='<div class="blockArticle">
            <h3><a href="' . $spost->guid . '" rel="bookmark" class="linktag">' . $spost->post_title . '</a> </h3>
        </div>';
    endforeach;
    $sitemap .= '</ul>';
    $sitemap .= '<h4>Category</h4>';
    $sitemap .= '<ul class="sitemapul">';
    $args = array(
        'offset' => 0,
        'category' => '',
        'category_name' => '',
        'orderby' => 'date',
        'order' => 'DESC',
        'include' => '',
        'exclude' => '',
        'meta_key' => '',
        'meta_value' => '',
        'post_type' => 'post',
        'post_mime_type' => '',
        'post_parent' => '',
        'author' => '',
        'post_status' => 'publish',
        'suppress_filters' => true
    );
    $cats = get_categories($args);
    foreach ($cats as $cat) :
        $sitemap .= '<li class="pages-list"><a href="' . get_category_link($cat->term_id) . '">' . $cat->cat_name . '</a></li>';
    endforeach;
	
    $sitemap .= '</ul>';
	
	*/
	
	
	//Pages
    $pages_args = array(
		'orderby' 		=> 'title',
		'order' 		=> 'ASC',
	    'numberposts'		=> -1, //all
		//'include' => '1',
        'exclude' => '24861, 24288, 25173,373,374,375', // ID of pages to be excluded, separated by comma///
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    $sitemap .= '<h3>Pages</h3>';
    $sitemap .= '<ul>';
    $pages = get_pages($pages_args);
    foreach ($pages as $page) :
        $sitemap .= '<li class="pages-list"><a href="' . get_page_link($page->ID) . '" rel="bookmark">' . $page->post_title . '</a></li>';
    endforeach;
    $sitemap .= '</ul>';
	
	
	//Products
    $products_args = array(
		'orderby' 		=> 'title',
		'order' 		=> 'ASC',
	    'numberposts'		=> -1, //all
		'include' => '44640,44592,66707,44758,44632,44782,44638,64729,44492,44773,44600,44499,44518,44722,44509,44526,44642,44535,44574, 44698,44567,44649,44623,44542, 44558,44616,44704, 44656, 44677, 44608, 44580, 44671, 44586, 44684, 44664, 44691 ,44765, 44761, 44731, 44729, 44751, 44738, 44733, 44745, 44749, 44743, 44747, 44718, 44756',
       // 'exclude' => '', // ID of products to be excluded, separated by comma///
        'post_type' => 'product',
        'post_status' => 'publish'
    );
    $sitemap .= '<h3>Products</h3>';
    $sitemap .= '<ul>';
    $products = get_posts($products_args);
    foreach ($products as $product) :
        $sitemap .= '<li class="pages-list"><a href="' . get_page_link($product->ID) . '" rel="bookmark">' . $product->post_title . '</a></li>';
    endforeach;
    $sitemap .= '</ul>';	
	
	

	
/*    $sitemap .= '<h4>Tags</h4>';
    $sitemap .= '<ul class="sitemapul">';
    $tags = get_tags();
    foreach ($tags as $tag) {
        $tag_link = get_tag_link($tag->term_id);
        $sitemap .= "<li class='pages-list'><a href='{$tag_link}' title='{$tag->name} Tag' class='{$tag->slug}'>";
        $sitemap .= $tag->name . '</a></li>';
    }*/

    return$sitemap;
}

add_shortcode('howtositemap', 'howtositemap');
function howtositemap() {
    $sitemap2 = '';
			//How-to
    $howto_args = array(
		'orderby' 		=> 'title',
		'order' 		=> 'ASC',
	    'numberposts'		=> -1, //all
        'exclude' => '', // ID of pages to be excluded, separated by comma///
        'post_type' => 'how_to',
        'post_status' => 'publish'
    );
    $sitemap2 .= '<h3>How-to Resources</h3>';
    $sitemap2 .= '<ul>';
    $howtos = get_posts($howto_args);
    foreach ($howtos as $howto) :
        $sitemap2 .= '<li class="pages-list"><a href="' . get_permalink($howto->ID) . '" rel="bookmark">' . $howto->post_title . '</a></li>';
    endforeach;
    $sitemap2 .= '</ul>';	
	    return$sitemap2;
}

 
//Beaver Builder
//if(in_array('bb-plugin/fl-builder.php', apply_filters('active_plugins', get_option('active_plugins')))){
	 

//Make all image sizes avaialble in beaver builder

function insert_custom_image_sizes($sizes) {
  global $_wp_additional_image_sizes;
  if (empty($_wp_additional_image_sizes)) {
    return $sizes;
  }

  foreach ($_wp_additional_image_sizes as $id => $data) {
    if (!isset($sizes[$id])) {
      $sizes[$id] = ucfirst(str_replace('-', ' ', $id));
    }
  }

  return $sizes;
}
add_filter('image_size_names_choose', 'insert_custom_image_sizes');

//} //beaver builder is active



//force how-to resources to order by menu order
add_action('parse_query', 'rnr_sort_custom_posts');

function rnr_sort_custom_posts($query)
{
    if(!$query->is_main_query() || is_admin())
        return;
    
    if(
        !is_post_type_archive('how_to')
		 &&      !is_tax(array('how_to_tag', 'how_to_category'))
    ) return;

    $query->set('orderby', 'menu_order');
    $query->set('order', 'ASC');
}
//change search form placeholder text
/*function rnr_search_form( $html ) {
	
	if ( 'how_to' == get_post_type() || is_post_type_archive('how_to')
		 ||      is_tax(array('how_to_tag', 'how_to_category')) ) {

        $html = str_replace( 'placeholder="Search this website', 'placeholder="Search FAQs ', $html );

        return $html;
	}
	else {
		 return $html;
		 }
}
add_filter( 'get_search_form', 'rnr_search_form' );*/

//YITH Affiliates
 
//if( function_exists( 'yith_affiliates_constructor' ) ) {


//Filter text on YITH woo affiliates plugin registration form

add_filter ('yith_wcaf_payment_email_label_address_register_form', 'rnr_yith_text_payment_email');

function rnr_yith_text_payment_email(){
	
	 $paymentEmail= 'Email address associated with your PayPal account to receive your commissions'; 
    
	return $paymentEmail;
	}
//} //YIYH AFFILIATS


// Modify GUtenberg embed blocks to use REL-=0
// https://wpforthewin.com/remove-related-videos-wp-gutenberg-embed-blocks/
function rnr_modest_youtube_player( $block_content, $block ) {
  if( ( "core-embed/youtube" === $block['blockName'] ) || ( "core/embed" === $block['blockName'] ) ) {
    $block_content = str_replace( '?feature=oembed', '?feature=oembed&modestbranding=1&showinfo=0&rel=0', $block_content );
  }
  return $block_content;
}
add_filter( 'render_block', 'rnr_modest_youtube_player', 10, 3);

// Simple history extend history to 90 days  
add_filter( "simple_history/db_purge_days_interval", function( $days ) {

	$days = 45;

	return $days;

} );