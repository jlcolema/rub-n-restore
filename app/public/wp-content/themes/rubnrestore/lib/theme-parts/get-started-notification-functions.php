<?php 
    if (!defined('ABSPATH'))
        exit; // Exit if accessed directly

//Create the notification for the Get Started Form

//Objective - conditionaly output the notificaiton content based on admin and user selected choices in the form. Use a page with custom fields to add the text to the notification so it is editable on the front end. 

//Custom fields group: Get Started Notification Fields
//Output the group on page ID: 65703 | Get Started Notification Text Fields
//https://rubnrestore.com/get-started-notification-text-fields/

//https://docs.gravityforms.com/gform_notification/
//https://docs.gravityforms.com/notifications-object/
//https://docs.gravityforms.com/entry-object/
//https://docs.gravityforms.com/gform_pre_send_email/

/*
//gform_notification Parameters
$notification: array: An array of properties which make up a notification object. See /notifications-object/ for possible properties.
$form: The form object for which the notification is being sent.
$entry: Entry Object: The /entry-object/ for which the notification is being sent.
*/
    
//modify a notification before it is sent for from ID# 6 - Get Started Project Evaluationu

//https://iconicwp.com/blog/get-default-variation-id-variable-product-woocommerce/
//We need to create a function to feed our product variation in to get it's id

//Sections marked //customer input are dependant on the text value of the form field matching the conditional value in this document. Also the admin choices that have text names like: Full grain or top grain leather , will break the logic if that text value is changed in the form and not in this document. 


              
    function rnr_match_prod_var( $product, $attributes ) {

      foreach( $attributes as $key => $value ) {
          
          if( strpos( $key, 'attribute_' ) === 0 ) {
              continue;
          }
   
          unset( $attributes[ $key ] );
          $attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
          }
       
          if( class_exists('WC_Data_Store') ) { //WC3.0+
       
              $data_store = WC_Data_Store::load( 'product' );
              return $data_store->find_matching_product_variation( $product, $attributes );
       
          } else {
       
              return $product->get_matching_variation( $attributes );
       
          }
       
        }
        
//Function for the Product name
            
    function rnr_get_product_name($GFproductID){    
        
          $prodID =  preg_replace('/\D/', '', $GFproductID); // The ID must be in the field, strip everything else.
          
          $product = wc_get_product( $prodID ); // Now we have the product
          
          return $product->get_name(); //Here is the product name      
                            
        }
//Output the Product image
            
    function rnr_get_product_image($GFproductID){    
        
          $prodID =  preg_replace('/\D/', '', $GFproductID); 
          
          $product = wc_get_product( $prodID ); // Now we have the product
          
          return $product->get_image('shop_thumbnail'); //Here is the product image  
        
        }
    
//FUnction for the product variation ID - size as only variation       
    
function rnr_get_variation_ID($GFproductID, $GFsize){    //we need the product ID and the slug of the size attribute
        
          $prodID =  preg_replace('/\D/', '', $GFproductID); // The value field on the form must have the ID, other text is stripped out.
          
          $product = wc_get_product( $prodID ); // Now we have the product
            
          $variation_id = rnr_match_prod_var( $product, $GFsize ); // use the rnr_match function to output the variation ID
            
          return $variation_id;
          
                            
        }            
             

//FUnction for the product variation ID - filler size, tools, sandpaper  

// new as of 06/02/2022

function rnr_get_variation_ID_kit($GFproductID, $GFattributes){    //we need the product ID and the slug of the size, tools, and sandpaper attributes
        
          $prodID =  preg_replace('/\D/', '', $GFproductID); // The value field on the form must have the ID, other text is stripped out.
          
          $product = wc_get_product( $prodID ); // Now we have the product
            
          $variation_id = rnr_match_prod_var( $product, $GFattributes ); // use the rnr_match function to output the variation ID
            
          return $variation_id;
          
                            
        }   

//Set gravity form notification to multipart // THIS DOESN'T WORK YET, Possible plugin conflict.
/*add_filter( 'gform_notification_6', 'change_notification_format', 10, 3 );
function change_notification_format( $notification, $form, $entry ) {
    
    if ( $notification['name'] == 'Project Evaluation to Customer - New & Improved' ) {

        $notification['message_format'] = 'multipart';
    }
  
    return $notification;
}
*/
// Function to Create the notification email

add_filter( 'gform_notification_6', 'rnr_get_started_notification', 10, 3 );

function rnr_get_started_notification($notification, $form, $entry){
    
    
       if ( $notification['name'] == 'Project Evaluation to Customer - New & Improved'){ // We are only modifying a specific notification, the name must match exactly here.    
           
           //We will be mostly working with the ENTRY OBJECT
           
         $notification['message'] =''  ;//Set the message to blank. 
         
         //admin notes to customer
         $adminGREETING='';
         if(rgar( $entry, '155.3' )){
             
             $adminGREETING = '<h2>Hello ' . (rgar( $entry, '155.3' )) . ',</h2>' ;
            
          }
         if(rgar( $entry, '244' )){ 
             $adminGREETING .= '<p style="line-height:1.5; font-size:15px;">'. rgar( $entry, '244' ) . '</p>';
             
              $adminGREETING .= get_field('admin_message_signatures', 65703);
              
/*              '<p style="font-size:15px;">Please let us know how else we can help.</p>

                                <p style="font-size:15px;">Cheers,</p>

                                <p style="font-size:15px;">Lesandre & CC </p>';*/
             
             }   else{$adminGREETING .="<h2>Thank you for requesting a Rub 'n Restore Project Evaluation.</h2>"
                 
             
             ;}
             

//COLOR MATCHING
//no quantity no size    
        if( rgar( $entry, '285' ) ){//must have the product No size on simple products
        
            $GFproductID           =  rgar( $entry, '285' );//The GF color field
            $prodID               =  preg_replace('/\D/', '', $GFproductID);
            $product              =  wc_get_product( $prodID ); // Now we have the product
            $productCM0NAME       =  $product->get_name();
            $productCM0IMG        =  $product->get_image('shop_thumbnail');
            $productCM0QTY        =   rgar( $entry, '287' ); //the gF quantity field
			
			 if($productCM0QTY  =='')
            {$productCM0QTY       =  "1"  ;}
			
            $productCM0VARid      =  $prodID ;
            $productCM0SIZE       =  'N/A';        
            $productCM0addtoCART  =  $productCM0QTY  . 'x' . $productCM0VARid .',';
            $productCM0           =  'true';
            $hasPRODUCT              =  'true';
            
        }else{$productCM0addtoCART =  '' ;}        
        
//Product 1 - Color 1

        
        if( rgar( $entry, '253' ) && rgar( $entry, '257' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '253' );//The GF color field
            $ACFcolor1          =  preg_replace('/[0-9]+/', '', $GFproductID);//strip numbers
            $GFsize             = array( // feed the rnr_match_prod_var() function.
                'pa_size'        => rgar( $entry, '257' ),
                );    
            
            $product1VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product1NAME       =  rnr_get_product_name($GFproductID);
            $product1SIZE       =  rgar( $entry, '257' );    
            
            $product1QTY        =  rgar( $entry, '258' ); //the gF quantity field
            
            if($product1QTY =='')
            {$product1QTY       =  "1"  ;}
            
            $product1IMG        =  rnr_get_product_image($GFproductID);    
            $product1addtoCART  =  $product1QTY . 'x' . $product1VARid .',';
            $product1           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product1addtoCART =  '' ; $ACFcolor1 ='';
             
            }
            
//Product 2 - Color 2
    
        if( rgar( $entry, '262' ) && rgar( $entry, '261' )){//must have the product and size
        
            $GFproductID          =  rgar( $entry, '262' );//The GF color field
            $ACFcolor2          =  preg_replace('/[0-9]+/', '', $GFproductID);
            $GFsize              =  array( // to feed rnr_match_prod_var() function.
                'pa_size'         => rgar( $entry, '261' ),
                );    
            
            $product2VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product2NAME       =  rnr_get_product_name($GFproductID);
            $product2SIZE       =  rgar( $entry, '261' );    
            $product2QTY        =  rgar( $entry, '260' ); //the gF quantity field
            if($product2QTY =='')
            {$product2QTY       =  "1"  ;}
            $product2IMG        =  rnr_get_product_image($GFproductID);
            $product2addtoCART  =  $product2QTY . 'x' . $product2VARid . ','  ;
            $product2           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product2addtoCART =  '' ; $ACFcolor2 ='';    }        
        
//Product 3 - Color 3
    
        if( rgar( $entry, '265' ) && rgar( $entry, '264' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '265' );//The GF color field
            $ACFcolor3           =  preg_replace('/[0-9]+/', '', $GFproductID);
            $GFsize = array( //The GF size - we need this to feed the rnr_match_prod_var() function.
                'pa_size' => rgar( $entry, '264' ),
                );    
            
            $product3VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product3NAME       =  rnr_get_product_name($GFproductID);
            $product3SIZE       =  rgar( $entry, '264' );    
            $product3QTY        =  rgar( $entry, '263' ); //the gF quantity field
            if($product3QTY =='')
            {$product3QTY       =  "1"  ;}            
            $product3IMG        =  rnr_get_product_image($GFproductID);
            $product3addtoCART  =  $product3QTY . 'x' . $product3VARid . ',' ;
            $product3           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product3addtoCART =  '' ; $ACFcolor3 ='';    }        
        
//Product 4 - Color 4

        if( rgar( $entry, '272' ) && rgar( $entry, '271' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '272' );//The GF color field
            $ACFcolor4           =  preg_replace('/[0-9]+/', '', $GFproductID);
            $GFsize = array( //The GF size - we need this to feed the rnr_match_prod_var() function.
                'pa_size' => rgar( $entry, '271' ),
                );    
            
            $product4VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product4NAME       =  rnr_get_product_name($GFproductID);
            $product4SIZE       =  rgar( $entry, '271' );    
            $product4QTY        =  rgar( $entry, '270' ); //the gF quantity field
            if($product4QTY =='')
            {$product4QTY       =  "1"  ;}            
            $product4IMG        =  rnr_get_product_image($GFproductID);
            $product4addtoCART  =  $product4QTY . 'x' . $product4VARid . ',' ;
            $product4           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product4addtoCART =  '' ; $ACFcolor4 ='';    }    
        
//Product 4.1 Color 5

        if( rgar( $entry, '303' ) && rgar( $entry, '304' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '303' );//The GF color field
            $ACFcolor4_1           =  preg_replace('/[0-9]+/', '', $GFproductID);
            $GFsize = array( //The GF size - we need this to feed the rnr_match_prod_var() function.
                'pa_size' => rgar( $entry, '304' ),
                );    
            
            $product4_1VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product4_1NAME       =  rnr_get_product_name($GFproductID);
            $product4_1SIZE       =  rgar( $entry, '304' );    
            $product4_1QTY        =  rgar( $entry, '305' ); //the gF quantity field
            if($product4_1QTY =='')
            {$product4_1QTY       =  "1"  ;}            
            $product4_1IMG        =  rnr_get_product_image($GFproductID);
            $product4_1addtoCART  =  $product4_1QTY . 'x' . $product4_1VARid . ',' ;
            $product4_1           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product4_1addtoCART =  '' ; $ACFcolor4_1 ='';    }   
		
//Product 4.2 - Satin & Dull Sealer

        if( rgar( $entry, '344' ) && rgar( $entry, '346' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '344' );//The GF color field
            $ACFcolor4_12           =  preg_replace('/[0-9]+/', '', $GFproductID); //This strips the numbers and leaves the ID which must match the corresponding ACF field id.
            $GFsize = array( //The GF size - we need this to feed the rnr_match_prod_var() function.
                'pa_size' => rgar( $entry, '346' ),
                );    
            
            $product4_12VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);      
            $product4_12NAME       =  rnr_get_product_name($GFproductID);
            $product4_12SIZE       =  rgar( $entry, '346' );    
            $product4_12QTY        =  rgar( $entry, '345' ); //the gF quantity field
            if($product4_12QTY ==''){$product4_12QTY       =  "1"  ;}            
            $product4_12IMG        =  rnr_get_product_image($GFproductID);
            $product4_12addtoCART  =  $product4_12QTY . 'x' . $product4_12VARid . ',' ; // add this at line 986
            $product4_12           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product4_12addtoCART =  '' ; $ACFcolor4_12 ='';    }     		         

//Product 5 - Filler or Filler Kit 1
        
        if( rgar( $entry, '275' ) && rgar( $entry, '276' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '275' );
			
            $GFattributes = array( //The attributes - we need this to feed the rnr_match_prod_var() function.
                'attribute_pa_size' => rgar( $entry, '276' ),
				'attribute_pa_tools' => rgar( $entry, '353' ),
				'attribute_pa_sandpaper' => rgar( $entry, '354' ),
                );    
	
            $product5VARid      =  rnr_get_variation_ID_kit($GFproductID, $GFattributes);  // access the function with kit attributes   
            $product5NAME       =  rnr_get_product_name($GFproductID);
            $product5SIZE      =  rgar( $entry, '276' );    
			
			$product5tools       =  rgar( $entry, '353' ); // new as of 06/02/2022
			
			$tools5='';
			if ($product5tools == 'needle-card')
				{$tools5='Needle Tool & Spreader Card';}
			if ($product5tools == 'knife-card')
				{$tools5='Palette Knife & Spreader Card';}
			if ($product5tools == 'needle-knife-card')
				{$tools5='Needle Tool, Palette Knife & Spreader Card';}
			
			$product5sandpaper  =  rgar( $entry, '354' );   // new as of 06/02/2022
			
			$sandpaper5='';
			if ($product5sandpaper == 'array-sandpaper')
				{$sandpaper5='<br>and Sandpaper: 220, 320 & 500 grit';}
					
			
            $product5QTY        =  rgar( $entry, '277' ); //the gF quantity field
            if($product5QTY =='')
            {$product5QTY       =  "1"  ;}
            $product5IMG        =  rnr_get_product_image($GFproductID);
            $product5addtoCART  =  $product5QTY . 'x' . $product5VARid . ',' ;
            $product5           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product5addtoCART =  '' ;}


//Product 5.01 - Filler or Filler Kit 2
        
        if( rgar( $entry, '300' ) && rgar( $entry, '301' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '300' );
			
            $GFattributes = array( //The attributes - we need this to feed the rnr_match_prod_var() function.
                'attribute_pa_size' => rgar( $entry, '301' ),
				'attribute_pa_tools' => rgar( $entry, '355' ),
				'attribute_pa_sandpaper' => rgar( $entry, '356' ),
                );    
	
            $product5_01VARid      =  rnr_get_variation_ID_kit($GFproductID, $GFattributes);  // access the function with kit attributes   
            $product5_01NAME       =  rnr_get_product_name($GFproductID);
            $product5_01SIZE      =  rgar( $entry, '301' );    
			
			$product5_01tools       =  rgar( $entry, '355' ); // new as of 06/02/2022
			
			$tools5_01='';
			if ($product5_01tools == 'needle-card')
				{$tools5_01='Needle Tool & Spreader Card';}
			if ($product5_01tools == 'knife-card')
				{$tools5_01='Palette Knife & Spreader Card';}
			if ($product5_01tools == 'needle-knife-card')
				{$tools5_01='Needle Tool, Palette Knife & Spreader Card';}
			
			$product5_01sandpaper  =  rgar( $entry, '356' );   // new as of 06/02/2022
			
			$sandpaper5_01='';
			if ($product5_01sandpaper == 'array-sandpaper')
				{$sandpaper5_01='<br>and Sandpaper: 220, 320 & 500 grit';}
					
			
            $product5_01QTY        =  rgar( $entry, '302' ); //the gF quantity field
            if($product5_01QTY =='')
            {$product5_01QTY       =  "1"  ;}
            $product5_01IMG        =  rnr_get_product_image($GFproductID);
            $product5_01addtoCART  =  $product5_01QTY . 'x' . $product5_01VARid . ',' ;
            $product5_01           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product5_01addtoCART =  '' ;}

/*//Product 5.01 - Filler or Filler KIT 2 // old version without attributes
        
        if( rgar( $entry, '300' ) && rgar( $entry, '301' )){//must have the product and size
        
            $GFproductID = rgar( $entry, '300' );//The GF color field
            $GFsize = array( //The GF size - we need this to feed the rnr_match_prod_var() function.
                'pa_size' => rgar( $entry, '301' ),
                );    
            
            $product5_01VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);     
            $product5_01NAME       =  rnr_get_product_name($GFproductID);
            $product5_01SIZE       =  rgar( $entry, '301' );    
            $product5_01QTY        =  rgar( $entry, '302' ); //the gF quantity field
            if($product5_01QTY =='')
            {$product5_01QTY       =  "1"  ;}
            $product5_01IMG        =  rnr_get_product_image($GFproductID);
            $product5_01addtoCART  =  $product5_01QTY . 'x' . $product5_01VARid . ',' ;
            $product5_01           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product5_01addtoCART =  '' ;}        */



//Product 6 SINGLE and VARIABLE products MIXED
// Subpatch,  Subpatch KIT, 3m Glue 1       
       if( rgar( $entry, '279' ) ){//must have the product. No size on simple products
        
            $GFproductID         =  rgar( $entry, '279' );
            $prodID             =  preg_replace('/\D/', '', $GFproductID); 
            
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product6NAME       =  $product->get_name();
            $product6IMG        =  $product->get_image('shop_thumbnail');
            $product6QTY        =  rgar( $entry, '281' ); //the gF quantity field
            if($product6QTY =='')
            {$product6QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
              if($prodID == '44745'){$product6SIZE       =  '1-oz' ;}
             
              $product6VARid      =  $prodID ;
                } 
                
                elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '280' ) ){//get the size field for variable
                    
                $GFsize = array( //The GF size 
                    
                'pa_size' => rgar( $entry, '280' ),
                 
                 );    
                
                $product6VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product6SIZE       =  rgar( $entry, '280' );    
                
                }
                
            $product6addtoCART  =  $product6QTY . 'x' . $product6VARid . ',' ;
            $product6           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product6addtoCART =  '' ;}
		
		                          

//Product 6.01 SINGLE and VARIABLE products MIXED
// Subpatch,  Subpatch KIT, 3m Glue 2       
       if( rgar( $entry, '312' ) ){//must have the product. No size on simple products
        
            $GFproductID         =  rgar( $entry, '312' );
            $prodID             =  preg_replace('/\D/', '', $GFproductID); 
            
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product6_1NAME       =  $product->get_name();
            $product6_1IMG        =  $product->get_image('shop_thumbnail');
            $product6_1QTY        =  rgar( $entry, '314' ); //the gF quantity field
            if($product6_1QTY =='')
            {$product6_1QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
              if($prodID == '44745'){$product6_1SIZE       =  '1-oz' ;}
             
              $product6_1VARid      =  $prodID ;
                } 
                
                elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '313' ) ){//get the size field for variable
                    
                $GFsize = array( //The GF size 
                    
                'pa_size' => rgar( $entry, '313' ),
                 
                 );    
                
                $product6_1VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product6_1SIZE       =  rgar( $entry, '313' );    
                
                }
                
            $product6_1addtoCART  =  $product6_1QTY . 'x' . $product6_1VARid . ',' ;
            $product6_1           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product6_1addtoCART =  '' ;}

//Product 7 SINGLE and VARIABLE products MIXED
//Sandpaper and Superglue 1
//we need to set the GFsize to grit for sandpaper        
        if( rgar( $entry, '282' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '282' );//The GF color field
            $prodID             =  preg_replace('/\D/', '', $GFproductID); 
            
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product7NAME       =  $product->get_name();
            $product7IMG        =  $product->get_image('shop_thumbnail');
            $product7QTY        =  rgar( $entry, '284' ); //the gF quantity field
            if($product7QTY =='')
            {$product7QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
              if($prodID == '44749'){$product7SIZE       =  '2-gram' ;}
              if($prodID == '44743'){$product7SIZE       =  'KIT' ;}
              $product7VARid      =  $prodID ;
                } 
                
                elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '283' ) ){
                    
                $GFsize = array( //The GF size 
                    
                'pa_grit' => rgar( $entry, '283' ),
                 
                 );    
                
                $product7VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product7SIZE       =  rgar( $entry, '283' );    
                
                }
                
            $product7addtoCART  =  $product7QTY . 'x' . $product7VARid . ',' ;
            $product7           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product7addtoCART =  '' ;}

//Product 8 Sandpaper and Superglue 2 - SINGLE and VARIABLE products MIXED
//we need to set the GFsize to grit for sandpaper        
        if( rgar( $entry, '288' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '288' );//The GF color field
            $prodID             =  preg_replace('/\D/', '', $GFproductID);
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product8NAME       =  $product->get_name();
            $product8IMG        =  $product->get_image('shop_thumbnail');
            $product8QTY        =  rgar( $entry, '290' ); //the gF quantity field
            if($product8QTY =='')
            {$product8QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
              if($prodID == '44749'){$product8SIZE       =  '2-gram' ;}
              if($prodID == '44743'){$product8SIZE       =  'KIT' ;}
              $product8VARid      =  $prodID ;
                } 
                
                elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '289' ) ){
                    
                $GFsize = array( //The GF size 
                    
                'pa_grit' => rgar( $entry, '289' ),
                 
                 );    
                
                $product8VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product8SIZE       =  rgar( $entry, '289' );    
                
                }
                
            $product8addtoCART  =  $product8QTY . 'x' . $product8VARid . ',' ;
            $product8           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product8addtoCART =  '' ;}        
        
//Product 9 Tools 1
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '291' ) ){//must have the product No size on simple products
        
            $GFproductID        =  rgar( $entry, '291' );//The GF color field
            $prodID             =  preg_replace('/\D/', '', $GFproductID); //Strip all but numbers
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product9NAME       =  $product->get_name();
            $product9IMG        =  $product->get_image('shop_thumbnail');
            $product9QTY        =  rgar( $entry, '293' ); //the gF quantity field
            if($product9QTY =='')
            {$product9QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640'){$product9SIZE       =  '16oz ' ;}//Flite
              
              if($prodID == '44731'){$product9SIZE       =  '7in' ;}//Pallate
              
              if($prodID == '44729'){$product9SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747'){$product9SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756'){$product9SIZE       =  '3"x4"x1"' ;}//Steelwool       
               
              $product9VARid      =  $prodID ;
			  
                } 
                
            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '292' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '292' ),
                 
                 );    
                
                $product9VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product9SIZE       =  rgar( $entry, '292' );    
                
                }
                
            $product9addtoCART  =  $product9QTY . 'x' . $product9VARid . ',' ;
            $product9           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product9addtoCART =  '' ;}            

//Product 10 tools 2
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '294' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '294' );//The GF color field
            $prodID             =  preg_replace('/\D/', '', $GFproductID);
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product10NAME       =  $product->get_name();
            $product10IMG        =  $product->get_image('shop_thumbnail');
            $product10QTY        =  rgar( $entry, '296' ); //the gF quantity field
            if($product10QTY =='')
            {$product10QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640'){$product10SIZE       =  '16oz ' ;}//Flite
              
              if($prodID == '44731'){$product10SIZE       =  '7in' ;}//Pallate
              
              if($prodID == '44729'){$product10SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747'){$product10SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756'){$product10SIZE       =  '3"x4"x1"' ;}//Steelwool    
			  if($prodID == '100778'){$product10SIZE       =  '3"x2"x.5"' ;}//Needle
			  if($prodID == '101847'){$product10SIZE       =  '9.5"x.5"x.5"' ;}//Paintbrush    
			                 
              $product10VARid      =  $prodID ;
                } 
                
            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '295' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '295' ),
                 
                 );    
                
                $product10VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product10SIZE       =  rgar( $entry, '295' );    
                
                }
                
            $product10addtoCART  =  $product10QTY . 'x' . $product10VARid . ',' ;
            $product10           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product10addtoCART =  '' ;}        
        
//Product 11 tools 3
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '297' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '297' ); // The field entry
            $prodID             =  preg_replace('/\D/', '', $GFproductID); //Strip text, get the id in the Value field
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product11NAME       =  $product->get_name();
            $product11IMG        =  $product->get_image('shop_thumbnail');
            $product11QTY        =  rgar( $entry, '299' ); //the gF quantity field
            if($product11QTY =='')
            {$product11QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640'){$product11SIZE       =  '16oz ' ;}//Flite
              
              if($prodID == '44731'){$product11SIZE       =  '7in' ;}//Pallate
              
              if($prodID == '44729'){$product11SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747'){$product11SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756'){$product11SIZE       =  '3"x4"x1"' ;}//Steelwool    
			  if($prodID == '100778'){$product11SIZE       =  '3"x2"x.5"' ;}//Needle
			  if($prodID == '101847'){$product11SIZE       =  '9.5"x.5"x.5"' ;}//Paintbrush    
			                 
              $product11VARid      =  $prodID ;
                } 
                
            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '298' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '298' ),
                 
                 );    
                
                $product11VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product11SIZE       =  rgar( $entry, '298' );    
                
                }
                
            $product11addtoCART  =  $product11QTY . 'x' . $product11VARid . ',' ;
            $product11           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product11addtoCART =  '' ;}    
		
		
//Product 12 tools 4
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '317' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '317' );//The GF color field
            $prodID             =  preg_replace('/\D/', '', $GFproductID);
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product12NAME       =  $product->get_name();
            $product12IMG        =  $product->get_image('shop_thumbnail');
            $product12QTY        =  rgar( $entry, '319' ); //the gF quantity field
            if($product12QTY =='')
            {$product12QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640'){$product12SIZE       =  '16oz ' ;}//Flite
              
              if($prodID == '44731'){$product12SIZE       =  '7in' ;}//Pallate
              
              if($prodID == '44729'){$product12SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747'){$product12SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756'){$product12SIZE       =  '3"x4"x1"' ;}//Steelwool    
			  if($prodID == '100778'){$product12SIZE       =  '3"x2"x.5"' ;}//Needle
			  if($prodID == '101847'){$product12SIZE       =  '9.5"x.5"x.5"' ;}//Paintbrush       
               
              $product12VARid      =  $prodID ;
                } 
                
            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '317' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '298' ),
                 
                 );    
                
                $product12VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product12SIZE       =  rgar( $entry, '318' );    
                
                }
                
            $product12addtoCART  =  $product12QTY . 'x' . $product12VARid . ',' ;
            $product12           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product12addtoCART =  '' ;}   		                
                             

//Product 13 - Tools 5 - simple products only
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '347' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '347' );//The GF Tools 5 field #
            $prodID             =  preg_replace('/\D/', '', $GFproductID); // Strip the name use the number in the GF field
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product13NAME       =  $product->get_name();
            $product13IMG        =  $product->get_image('shop_thumbnail');
            $product13QTY        =  rgar( $entry, '349' ); //the gF quantity field
           
		    if($product13QTY =='')
                {$product13QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640') {$product13SIZE       =  '16oz ' ;}//Flite
              if($prodID == '44731') {$product13SIZE       =  '7in' ;}//Pallate
              if($prodID == '44729') {$product13SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747') {$product13SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756') {$product13SIZE       =  '3"x4"x1"' ;}//Steelwool     
			  if($prodID == '100778'){$product13SIZE       =  '3"x2"x.5"' ;}//Needle
			  if($prodID == '101847'){$product13SIZE       =  '9.5"x.5"x.5"' ;}//Paintbrush    
               
              $product13VARid      =  $prodID ;
                
				} 
                
/*            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '347' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '298' ),
                 
                 );    
                
                $product13VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product13SIZE       =  rgar( $entry, '351' );    
                
                }*/
                
            $product13addtoCART  =  $product13QTY . 'x' . $product13VARid . ',' ;
            $product13           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product13addtoCART =  '' ;}   	
		
		
//Product 14 - Tools 6 - simple products only
//These do not have a size, quantity only
//Size must be set programatically    

        if( rgar( $entry, '350' ) ){//must have the product No size on simple products
        
            $GFproductID         =  rgar( $entry, '350' );//The GF Tools 5 field #
            $prodID             =  preg_replace('/\D/', '', $GFproductID); // Strip the name use the number in the GF field
            $product            =  wc_get_product( $prodID ); // Now we have the product
            $product14NAME       =  $product->get_name();
            $product14IMG        =  $product->get_image('shop_thumbnail');
            $product14QTY        =  rgar( $entry, '352' ); //the gF quantity field
           
		    if($product14QTY =='')
                {$product14QTY       =  "1"  ;}
            
            if( $product->is_type( 'simple' ) ){
                
              if($prodID == '44640') {$product14SIZE       =  '16oz ' ;}//Flite
              if($prodID == '44731') {$product14SIZE       =  '7in' ;}//Pallate
              if($prodID == '44729') {$product14SIZE       =  '~3.5"x2"x.5mm' ;}//card
              if($prodID == '44747') {$product14SIZE       =  '~5"x4"x.5"' ;}//sponge
              if($prodID == '44756') {$product14SIZE       =  '3"x4"x1"' ;}//Steelwool     
			  if($prodID == '100778'){$product14SIZE       =  '3"x2"x.5"' ;}//Needle
			  if($prodID == '101847'){$product14SIZE       =  '9.5"x.5"x.5"' ;}//Paintbrush    
               
              $product14VARid      =  $prodID ;
                
				} 
                
/*            elseif( $product->is_type( 'variable' ) &&  rgar( $entry, '347' ) ){
                    
                $GFsize = array( //The GF size or other attribute
                    
                'pa_grit' => rgar( $entry, '298' ),
                 
                 );    
                
                $product14VARid      =  rnr_get_variation_ID($GFproductID, $GFsize);    
                 
                $product14SIZE       =  rgar( $entry, '351' );    
                
                }*/
                
            $product14addtoCART  =  $product14QTY . 'x' . $product14VARid . ',' ;
            $product14           =  'true';
            $hasPRODUCT            =  'true';
            
        }else{$product14addtoCART =  '' ;}  

//insert the colors text in cart area from ACF colors section 
    
    if($ACFcolor1!=='' && trim($ACFcolor1)!=='clear_prep'&& $ACFcolor1!=='mixing_bottle'){$product1DESC = get_field(trim($ACFcolor1), 65703);}  
    
    if($ACFcolor2!=='' && trim($ACFcolor2)!=='clear_prep'&& $ACFcolor2!=='mixing_bottle'){$product2DESC .= get_field(trim($ACFcolor2), 65703);} 
    
    if($ACFcolor3!=='' && trim($ACFcolor3)!=='clear_prep'&& $ACFcolor3!=='mixing_bottle'){$product3DESC .= get_field(trim($ACFcolor3), 65703);} 
    
    if($ACFcolor4!=='' && trim($ACFcolor4)!=='clear_prep'&& $ACFcolor4!=='mixing_bottle'){$product4DESC .= get_field(trim($ACFcolor4), 65703);}     

    if($ACFcolor4_1!=='' && trim($ACFcolor4_1)!=='clear_prep'&& $ACFcolor4_1!=='mixing_bottle'){$product4_1DESC .= get_field(trim($ACFcolor4_1), 65703);}    
	if($ACFcolor4_12!==''){$product4_12DESC .= get_field(trim($ACFcolor4_12), 65703);}  
        
    
    
//****************************************************************************
//Notification message output:


//Header
$notification['message'] .= ' 
        
        <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color:#fff9ef;border:0; padding:20px;">
        <tr>
        <td> <!--containter table -->    
        
        
        <table width="600" border="1" cellspacing="0" cellpadding="12" style="background-color:#45201a;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;text-align:center">
          <tr>
            <td style="background-color:#45201a;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;border:0;"><a href="https://rubnrestore.com" title="Rub n Restore"><img src="https://rubnrestore.com/wp-content/uploads/rubnrestore-logo-long-white-300x41.png" alt="Rub \'n Restore Logo" width="300" height="41" /> </td>
          </tr>
          <tr>
            <td style="background-color:#45201a;color:#ffffff;border:0;font-weight:bold;line-height:100%;vertical-align:middle;" ><h1 style="font-size:30px;font-family:Alice; line-height:150%;margin:0;text-align:center;color:#ffffff">How To Do It & Product Suggestions</h1></td>
          </tr>
        
        </table>
        
    <!-- heading-->';


//Message from admin

$notification['message'] .= ' 

    <!-- Admin Message-->
    
    <table width="600" border="0" cellspacing="1" style="margin-bottom:40px; background-color:#fff; font-size:18px;">
      <tr>
        <td style="padding:20px;border:0;">  ' . $adminGREETING . '  </td>
      </tr>
    
    </table>';


    
//Recommended products and shopping cart

if ($hasPRODUCT=='true'){
    
$notification['message'] .= ' 

<!-- Recommended Products-->

    
<table width="600" border="0" cellspacing="1" style="padding:12px; background-color:#fff; font-size:15px;border-top:1px solid;border-left:1px solid;border-right:1px solid; ">
  <tr>
    <td style="padding:12px;border:0;"><h2>Products we recommend for you:</h2></td>
  </tr>

</table>';

}
if ($hasPRODUCT=='true'){
    $notification['message'] .= ' 
    
    <!-- Content-->
    
    <table width="600" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:40px ; background-color:#fff;font-size:15px;border-left:1px solid;border-bottom:1px solid; border-right:1px solid;">
     ';
 }
 
 if ($hasPRODUCT=='true'){//header
 
     $notification['message'] .= ' 
      <tr>
        <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;">Product</th>
        <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;">Quantity</th>
        <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;">Size</th>
        <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;">Image</th>
      </tr>';
 }
//product CM0
 if ($productCM0=='true'){
    $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $productCM0NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $productCM0QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $productCM0SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $productCM0IMG  . '</td>
      </tr>';
     } 
//product 1
 if ($product1=='true'){
    $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product1NAME . '<br /> ' .$product1DESC .  ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product1QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product1SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product1IMG  . '</td>
      </tr>';
     }
//product 2
 if ($product2=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product2NAME .  '<br /> ' .$product2DESC . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product2QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product2SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product2IMG  . '</td>
      </tr>';
     }
//product 3     
 if ($product3=='true'){//product 3
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product3NAME .  '<br /> ' .$product3DESC . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product3QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product3SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product3IMG  . '</td>
      </tr>';
     } 
//product 4
 if ($product4=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4NAME .  '<br /> ' .$product4DESC . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product4IMG  . '</td>
      </tr>';
     } 
//product 4.1
 if ($product4_1=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_1NAME . ' <br /> ' . $product4_1DESC . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_1QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_1SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product4_1IMG  . '</td>
      </tr>';
     }  
	 
//product 4.12
 if ($product4_12=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_12NAME . ' <br /> ' . $product4_12DESC . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_12QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product4_12SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product4_12IMG  . '</td>
      </tr>';
     }  	             
//product 5      
 if ($product5=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5NAME . 
 
		 '<br>with: ' . $tools5 . $sandpaper5 .		
		
		' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product5IMG  . '</td>
      </tr>';
     }
	 

//product 5 .01     
 if ($product5_01=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5_01NAME .
		
		 '<br>with: ' . $tools5_01 . $sandpaper5_01 .	
		 
		 ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5_01QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product5_01SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product5_01IMG  . '</td>
      </tr>';
     }               
//product 6      
 if ($product6=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product6IMG  . '</td>
      </tr>';
     } 
//product 6_1      
 if ($product6_1=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6_1NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6_1QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product6_1SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product6_1IMG  . '</td>
      </tr>';
     }	 
//product 7         
 if ($product7=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product7NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product7QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product7SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product7IMG  . '</td>
      </tr>';
     }     
//product 8     
 if ($product8=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product8NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product8QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product8SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'   . $product8IMG  . '</td>
      </tr>';
     }          
//product 9         
 if ($product9=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product9NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product9QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product9SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product9IMG  . '</td>
      </tr>';
     }          
//product 10     
 if ($product10=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product10NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product10QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product10SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product10IMG  . '</td>
      </tr>';
     }          

//product 11    
 if ($product11=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product11NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product11QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product11SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product11IMG  . '</td>
      </tr>';
     }   
	 
//product 12   
 if ($product12=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product12NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product12QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product12SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product12IMG  . '</td>
      </tr>';
     }    

//product 13   
 if ($product13=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product13NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product13QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product13SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product13IMG  . '</td>
      </tr>';
     }  	 
	 
//product 14  
 if ($product14=='true'){
        $notification['message'] .= '   
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product14NAME . ' </td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product14QTY  . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3;">'                        . $product14SIZE . '</td>
        <td style="padding:12px;border: 1px solid #e2ddd3; text-align:center;">'     . $product14IMG  . '</td>
      </tr>';
     }  

	       	        
//addto cart row                
if ($hasPRODUCT=='true'){  

 $notification['message'] .= '  
    <tr>
        <th  scope="row" colspan="2" style="vertical-align:middle; padding:30px; text-align:left;border: 1px solid #e2ddd3; font-style:bold;">See prices and shipping in the cart --> </th>
             
        <td  colspan="2" style="vertical-align:middle;padding:30px;text-align:left;border:1px solid #e2ddd3;">
                
                <a target="_blank" rel="noopener" href="https://rubnrestore.com/cart/?wpo_fill_cart=' . $productCM0addtoCART . $product1addtoCART .  $product2addtoCART . $product3addtoCART . $product4addtoCART . $product4_1addtoCART . $product4_12addtoCART . $product5addtoCART . $product5_01addtoCART . $product6addtoCART . $product6_1addtoCART . $product7addtoCART . $product8addtoCART . $product9addtoCART . $product10addtoCART . $product11addtoCART . $product12addtoCART . $product13addtoCART . $product14addtoCART .'" style="    padding: 10px;    background: #016ab5;    text-align: center;    color: #fff;    font-weight: bold;    font-size: 16px;    display: block;    border-radius: 4px;">View in Cart
                </a>
            
            </td>
       </tr>

        
        </table>
            
';
}
//Materials Section    - connect the gravity form entries to the custom fields

//Add a section to check admin material selection for field #183


//if(rgar( $entry, '183' ) =='Unsure of material - need more photos or sample'){ $materialUnsure = "true";} else {$materialUnsure='';}
	


//admin inputs

    $materialCONTENT ='';
	
	/*if (rgar( $entry, '183' ) =='Unsure of material - need more photos or sample'){
		
		$materialCONTENT = get_field('material-unknown', 65703);
		
	} 
	
	else {*/
	
	if(rgar( $entry, '7' ) =="I'm not certain, continue with material evaluation." ){ $materialCONTENT = get_field('material-unknown', 65703);} 
	
    if(rgar( $entry, '7' ) =='Aniline'){ $materialCONTENT = get_field('aniline_leather', 65703);} 
    
    if(rgar( $entry, '7' ) =='Semi-Aniline'){ $materialCONTENT = get_field('semi_aniline_leather', 65703);}
    
    if(rgar( $entry, '7' ) =='Full grain or top grain leather'){ $materialCONTENT = get_field('full_grain_or_top_grain_leather', 65703);}
    
    if(rgar( $entry, '7' ) =='Corrected grain leather'){ $materialCONTENT = get_field('corrected_grain_leather', 65703);}
    
    if(rgar( $entry, '7' ) =='Split leather'){ $materialCONTENT = get_field('split_leather', 65703);}
    
    if(rgar( $entry, '7' ) =='Suede nubuck or brushed leather'){ $materialCONTENT = get_field('about_nubuck_suede', 65703); $badMaterial='yes';}
    
    if(rgar( $entry, '7' ) =='Vinyl  (PolyVinyl Chloride, PVC)'){ $materialCONTENT = get_field('about_vinyl', 65703);}
    
    if(rgar( $entry, '7' ) =='Leather match (leather and vinyl)'){ $materialCONTENT = get_field('leather_and_vinyl', 65703);}
    
    if(rgar( $entry, '7' ) =='Bi-cast'){ $materialCONTENT = get_field('about_bicast_leather', 65703);$badMaterial='yes';}
    
    if(rgar( $entry, '7' ) =='Bonded, Faux or polyurethane (PU) Leather'){ $materialCONTENT = get_field('about_bonded_leather', 65703); $badMaterial='yes';}
    
    if(rgar( $entry, '7' ) =='Microfiber, microsuede or polyester'){ $materialCONTENT = get_field('about_microfiber_polyester', 65703); $badMaterial='yes';}
	
	//}
    
    //if((rgar( $entry, '7' ) != "I'm not certain, continue with material evaluation.") || (rgar( $entry, '7' )=='')){
    
$notification['message'] .= ' 

    <!--type of material-->
    
    <table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px ; background-color:#fff;font-size:15px;">
    
      <tr>
        <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"><h2 style="font-size:34px; margin:5px;">Your Material</h2></th>
      </tr>
      
      <tr>
        <td style="padding:12px;border: 1px solid #e2ddd3;"> ' . $materialCONTENT . $upholsteryCONTENT . '   </td>
        
      </tr>
      
    </table>';
//}

//************************************
//Cleaning Section

    $hasCleaning="";
    $cleaningCONTENT="";
    
    //Customer input
            //checkbox GF field
            $field_id = 102; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue102 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
            
    //repair_oil_stains
    $oilStains='';
    if (strpos($customerValue102, 'Oil stains') !== false)
    { $oilStains   =   "true";  }
    
    //repair_water_stains // removed 5-27-2020
/*    $waterMarks='';
    if (strpos($customerValue102, 'Water marks') !== false)
    { $waterMarks  =   "true";  }*/
    
    //repair_wine_coffee_stains
    $wineCoffee='';
    if (strpos($customerValue102, 'Water, wine, coffee, juice or cola stains') !== false)
    { $wineCoffee  =   "true" ; }
    
    //repair_ink_blue_jean_dye
    $blueJean='';
    if (strpos($customerValue102, 'Ink or blue-jean dye') !== false)
    { $blueJean =      "true";  }
    
    //repair_mold_mildew
    $moldMildew='';
    if (strpos($customerValue102, 'Mold or mildew') !== false)
    { $moldMildew =    "true"; }
    
    //repair_urine
    $petStains='';
    if (strpos($customerValue102, 'Milk, urine, vomit or pet stains') !== false)
    { $petStains =     "true"; }
	
	//Cigarette smoke
    $cigSmoke='';
    if (strpos($customerValue102, 'Cigarette smoke or odor') !== false)
    {  $cigSmoke =     "true"; }

//From the admin fields in the gravity form    

          //checkbox GF field
            $field_id = 250; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $value250 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
    
    if (strpos($value250, 'oil stains') !== false || $oilStains =="true"){ $cleaningCONTENT .= get_field('repair_oil_stains', 65703); $hasCleaning="true";}        

   /* if (strpos($value250, 'water marks') !== false || $waterMarks=="true"){ $cleaningCONTENT .= get_field('repair_water_stains', 65703); $hasCleaning="true";}  */  //removed 5-27-2020
    
    if (strpos($value250, 'water, wine, coffee, juice or cola') !== false || $wineCoffee=="true"){ $cleaningCONTENT .= get_field('repair_wine_coffee_stains', 65703); $hasCleaning="true";}    
    
    if (strpos($value250, 'ink or blue jean dye stains') !== false || $blueJean=="true"){ $cleaningCONTENT .= get_field('repair_ink_blue_jean_dye', 65703); $hasCleaning="true";}    
    
    if (strpos($value250, 'mold or mildew problems') !== false || $moldMildew=="true"){ $cleaningCONTENT .= get_field('repair_mold_mildew', 65703); $hasCleaning="true";}    
    
    if (strpos($value250, 'milk or pet stains') !== false || $petStains=="true"){ $cleaningCONTENT .= get_field('repair_urine', 65703); $hasCleaning="true";}  
	  
        if (strpos($value250, 'cig smoke') !== false || $cigSmoke=="true"){ $cleaningCONTENT .= get_field('cigarette_smoke', 65703); $hasCleaning="true";} 
		    
    if (strpos($value250, 'sticky leather or vinyl') !== false || rgar( $entry, '91' ) =='Yes'){ $cleaningCONTENT .= get_field('repair_sticky_leather', 65703); $hasCleaning="true";}    
        
    if (strpos($value250, 'peeling or chemical damaged finish') !== false){ $cleaningCONTENT .= get_field('repair_peeling_coating', 65703); $hasCleaning="true";}   
	
	//new 1/29/2022
		
	 if (strpos($value250, 'remove faux leather patches') !== false){ $cleaningCONTENT .= get_field('remove-faux-leather-patches', 65703); $hasCleaning="true";} 
	 
    if (strpos($value250, 'absorbent leathers') !== false){ $cleaningCONTENT .= get_field('clean_absorbent_leather', 65703); $hasCleaning="true";}
    
    if (strpos($value250, 'non-absorbent leather and vinyl') !== false ){ $cleaningCONTENT .= get_field('clean_non_absorbent_leather_vinyl', 65703); $hasCleaning="true";}  		

            
        
//Output to the email        
    if($hasCleaning=="true"){    
    $notification['message'] .= ' 
    
        <!--Cleaning-->
        <table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px ; background-color:#fff" ; font-size:15px;">
  <tr>
          <tr>
            <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"> '. get_field('cleaning_leather_heading', 65703) . ' </th>
          </tr>
          
          <tr>
            <td style="padding:12px;border: 1px solid #e2ddd3;">' . $cleaningCONTENT .' </td>
          </tr>
          
        </table>';
    }
    
//*********************************
//Repair Section    

//Customer input

    //checkbox GF field
            $field_id = 152; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue152 = is_object( $field ) ? $field->get_value_export( $entry ) : '';

    //repair_perforated_material
    $perforated='';
    if (strpos($customerValue152, 'The material is perforated.') !== false)
    { $perforated="true";}    
    
    //repair_restitch_seam
    $restitch='';
    if (strpos($customerValue152, 'Stitching is needed on a torn seam.') !== false)
    { $restitch="true";}  
	
	//how_to_repair_piping
    $piping='';
    if (strpos($customerValue152, 'There is piping that needs repair.') !== false)
    { $piping="true";}  
     
    //checkbox GF field
            $field_id = 242; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue242 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	
	//scracthing_post
    $scratchingPost='';
    if (strpos($customerValue242, 'Yes, one section was used repeatedly as a scratching post.') !== false)
    {$scratchingPost="true";}  	
	
    //checkbox GF field
            $field_id = 73; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue73 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	
	//minor_dog_scratches
    $visibledogScratches='';
    if (strpos($customerValue73, 'Yes, there are visible scratches, but they can not be felt by touch.') !== false)
    {$visibledogScratches="true";} 
	
	$deepdogScratches='';
    if (strpos($customerValue73, 'Yes, there are deeper scratches that have worn or thinned the material.') !== false)
    {$deepdogScratches="true";}    
	
    //checkbox GF field
            $field_id = 54; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue54 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	
	//thin_weak_leather // removed 5-27-2020
    $thinweakLeather='';
    if (strpos($customerValue54, 'Yes, fuzzy suede is exposed or an area feels thin and weak.') !== false)
    {$thinweakLeather="true";} 
	
    //checkbox GF field
            $field_id = 58; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $customerValue58 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	
	//stuffing
    $interiorStuffing='';
    if (strpos($customerValue58, 'Yes, there are cuts or holes, and I can access the interior stuffing.') !== false)
    {$interiorStuffing="true";} 	
	
	
//Admin checkboxes in gravity form
    
          //checkbox GF field
            $field_id = 245; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $value245 = is_object( $field ) ? $field->get_value_export( $entry ) : '';
            //returns a comma seperated list of the values of the selected choices.
            //turn the list into an array if necessary
            //$checkboxes = explode(" ", $value245);


$repairCONTENT='';
$hasRepair='';
	
	//Logic below that uses || (OR) operator will output in the report if the admin selects the value in the admin recommendations form field or if the customer selectes the value in the original form.
	
	
	if (strpos($value245, 'About Filler and Filler Quantity') !== false)
	{ $repairCONTENT .= get_field('about_filler_quantity', 65703); $hasRepair="true";}
	
    if (strpos($value245, 'Re-Stitch a Seam') !== false  ||  $restitch=="true")
    { $repairCONTENT .= get_field('repair_restitch_seam', 65703); $hasRepair="true";}  
	
    if (strpos($value245, 'Dog Scratches Minor or Scaling, Cracking Leather') !== false //|| $visibledogScratches=="true" 
	)
    { $repairCONTENT .= get_field('repair_dog_scratches', 65703); $hasRepair="true";}
    
    if (strpos($value245, 'Dog Scratches Severe or Weak Leather') !== false //|| $deepdogScratches=="true" || $thinweakLeather=="true"
	)
    { $repairCONTENT .= get_field('repair_deep_dog_scratches', 65703); $hasRepair="true";}	
   
    if (strpos($value245, 'Cat Scratches, Minor Gouges on Leather') !== false)
    { $repairCONTENT .= get_field('repair_minor_gouges_cat_scratches_leather', 65703); $hasRepair="true";}
    
    if (strpos($value245, 'Cat Scratches, Minor Gouges on Vinyl') !== false)
    { $repairCONTENT .= get_field('repair_minor_gouges_cat_scratches_vinyl', 65703); $hasRepair="true";}
	    
    if (strpos($value245, 'Cat Scratching Post Severe Filler') !== false //|| $scratchingPost=="true"
	)
    { $repairCONTENT .= get_field('repair_cat_scratching_post_leather', 65703); $hasRepair="true";}  
  
  /*  if (strpos($value245, 'Cracking or Scaly Leather') !== false)
	{ $repairCONTENT .= get_field('repair_cracking_scaly_leather', 65703); $hasRepair="true";}*/ //removed 5-27-2020
    
/*    if (strpos($value245, 'Thin, Weak Leather') !== false || $thinweakLeather=="true")
    { $repairCONTENT .= get_field('repair_thin_weak_leather', 65703); $hasRepair="true";}*/ //removed 5-27-2020
    
    if (strpos($value245, 'Scaly or Sun Rotted Vinyl') !== false)
    { $repairCONTENT .= get_field('repair_scaly_sun_rot_vinyl', 65703); $hasRepair="true";}
   
    if (strpos($value245, 'Cracking Vinyl (Cars)') !== false)
    { $repairCONTENT .= get_field('repair_cracking_vinyl', 65703); $hasRepair="true";}
	    
    if (strpos($value245, 'Tears in Vinyl, Backing is Intact (Furniture)') !== false)
    { $repairCONTENT .= get_field('repair_cuts_vinyl_backing_intact', 65703); $hasRepair="true";}
	
	if (strpos($value245, 'Cuts, Tears or Holes Through the Material') !== false )
	{ $repairCONTENT .= get_field('repair_cuts_tears_through_material', 65703); $hasRepair="true";}
	 
	if (strpos($value245, 'Foam Repair or Top Patch for Giant Holes') !== false)
    { $repairCONTENT .= get_field('how_to_repair_damaged_foam', 65703); $hasRepair="true";}

    if (strpos($value245, 'Perforated Material') !== false || $perforated=="true")
    { $repairCONTENT .= get_field('repair_perforated_material', 65703); $hasRepair="true";}
	
	if (strpos($value245, 'Piping Repair') !== false || $piping=="true")
    { $repairCONTENT .= get_field('how_to_repair_piping', 65703); $hasRepair="true";}

    if (strpos($value245, 'Tolex') !== false)
    { $repairCONTENT .= get_field('repair_tolex', 65703); $hasRepair="true";}	
	
    if (strpos($value245, 'Leather Steering Wheel') !== false)
    { $repairCONTENT .= get_field('repair_leather_steering_wheel', 65703); $hasRepair="true";}	
	
        
    
if($hasRepair=="true"){        
$notification['message'] .= ' 

<!--Repair-->
<table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px; background-color:#fff; font-size:15px;">
  <tr>
    <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"> ' . get_field('repairs_heading', 65703) . '</th>
  </tr>
  <tr>
    <td style="padding:12px;border: 1px solid #e2ddd3;"> ' . $repairCONTENT .' </td>
  </tr>
</table>';
}

//****************************************************************************

//Colors 

//user input 

//Customer input

            //checkbox GF field
            $field_id = 152; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $colorValue152 = is_object( $field ) ? $field->get_value_export( $entry ) : '';

    
    //repair_decorative_nail_heads
    $nailHeads='';
    if (strpos($colorValue152, 'The furniture has decorative nail heads.') !== false)
    { $nailHeads="true";}
    
    //repair_tufted_upholstery
    $tufted='';
    if (strpos($colorValue152, 'The upholstery is tufted.') !== false)
    { $tufted="true";}
    
    //repair_different_colored_panels
    $panels='';
    if (strpos($colorValue152, 'The upholstery has different colored stitching, piping or panels.') !== false)
    { $panels="true";}
    

//Colors admin field
            //checkbox GF field
            $field_id = 252; // Update this number to your field id number
            $field = RGFormsModel::get_field( $form, $field_id );
            $Value252 = is_object( $field ) ? $field->get_value_export( $entry ) : '';

$colorsCONTENT ='';
$hasColor='';
        
	if (strpos($Value252, 'Swatch Coupon Code') !== false)
    { $colorsCONTENT .= get_field('swatch_coupon_code', 65703); $hasColor="true";}	

    if (strpos($Value252, 'Send a Swatch for Color Matching') !== false)
    { $colorsCONTENT .= get_field('how_to_send_swatch_color_match', 65703); $hasColor="true";}

    if (strpos($Value252, 'About Colors and Quantity') !== false)
    { $colorsCONTENT .= get_field('about_color_dyes', 65703); $hasColor="true";}

    if (strpos($Value252, 'Mix Colors') !== false)
    { $colorsCONTENT .= get_field('how_to_mix_colors', 65703); $hasColor="true";}   		
	
    if (strpos($Value252, 'About Clear Prep+Finish') !== false)
    { $colorsCONTENT .= get_field('about_clear_prep_finish', 65703); $hasColor="true";}

    if (strpos($Value252, 'About Satin Sealer') !== false)
    { $colorsCONTENT .= get_field('about_satin_sealer', 65703); $hasColor="true";}	
	
    if (strpos($Value252, 'Application Basics') !== false)
    { $colorsCONTENT .= get_field('application-basics', 65703); $hasColor="true";}	
	
    if (strpos($Value252, 'Brass tacks') !== false ||  $nailHeads=="true")
    { $colorsCONTENT .= get_field('repair_decorative_nail_heads', 65703); $hasColor="true";}	

    if (strpos($Value252, 'Tufted upholstery') !== false ||  $tufted=="true")
    { $colorsCONTENT .= get_field('repair_tufted_upholstery', 65703); $hasColor="true";}    	
	
    if (strpos($Value252, 'Different colored panels, stitching') !== false || $panels=="true")
    { $colorsCONTENT .= get_field('repair_different_colored_panels', 65703); $hasColor="true";}
	
	if (strpos($Value252, 'Plastic Primer') !== false)
    { $colorsCONTENT .= get_field('primer_plastic', 65703); $hasColor="true";}
		   
    if (strpos($Value252, 'Correct Fading or Discoloration with Matching Color') !== false)
    { $colorsCONTENT .= get_field('how_to_correct_fading', 65703); $hasColor="true";}
    
    if (strpos($Value252, 'Change Color') !== false)
    { $colorsCONTENT .= get_field('how_to_change_color', 65703); $hasColor="true";}

    if (strpos($Value252, 'Mix a Glaze') !== false)
    { $colorsCONTENT .= get_field('how_to_mix_glaze', 65703); $hasColor="true";}		    

    if (strpos($Value252, 'Mimic the Multi-toned, Mottled or Distressed Look') !== false)
    { $colorsCONTENT .= get_field('how_to_distressed_look', 65703); $hasColor="true";}    
    
    if (strpos($Value252, 'Refinish Fabrics') !== false)
    { $colorsCONTENT .= get_field('refinish_fabric', 65703); $hasColor="true";}
	
	if ($hasColor=="true") //auto populate in the evaluation email if any color process is included
    { $colorsCONTENT .= get_field('dry_cure_time', 65703);}
     
       
if( $hasColor=="true"){
    
        $notification['message'] .= ' 

<!--Colors-->
<table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px; background-color:#fff; font-size:15px;">
  <tr>
    <th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"><h2 style="font-size:34px; margin:5px;">' . get_field('choosing_colors_heading', 65703) . '</h2></th>
  </tr>
  <tr>
    <td style="padding:12px;border: 1px solid #e2ddd3;"> ' . $colorsCONTENT . '</td>
  </tr>
</table>';

}

//Maintenance - This is included in all evaluations automatically. It has no admin or customer input. Added 1/20/2020

$MaintenanceCONTENT ='';
$hasMaintenance="true";	      

	$MaintenanceCONTENT .= get_field('future_cleaning_and_conditioning', 65703); 
	
	$MaintenanceCONTENT .= get_field('touching_up_color', 65703);
	
		
if( $hasMaintenance=="true"){
	
        $notification['message'] .= ' 

<!--maintenance-->
<table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px; background-color:#fff; font-size:15px;">
  <tr>
	<th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"><h2style="font-size:34px; margin:5px;">' . get_field('maintenance_heading', 65703) . '</h2></th>
  </tr>
  <tr>
	<td style="padding:12px;border: 1px solid #e2ddd3;"> ' . $MaintenanceCONTENT . '</td>
  </tr>
</table>';
	
	}

//Shipping- This is included in all evaluations automatically. It has no admin or customer input. Added 1/20/2020

$ShippingCONTENT ='';
$hasShipping="true";	      

	$ShippingCONTENT .= get_field('shipping', 65703); 
	
	//$ShippingCONTENT .= get_field('touching_up_color', 65703);
	
		
if( $hasShipping=="true"){
	
        $notification['message'] .= ' 

<!--shipping-->
<table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px; background-color:#fff; font-size:15px;">
  <tr>
	<th scope="col" style="padding:12px;border: 1px solid #e2ddd3;"><h2 style="font-size:34px; margin:5px;">' . get_field('shipping_heading', 65703) . '</h2></th>
  </tr>
  <tr>
	<td style="padding:12px;border: 1px solid #e2ddd3;"> ' . $ShippingCONTENT . '</td>
  </tr>
</table>';
	
	}	

//Footer

$notification['message'] .= ' 

<!-- footer table-->
<table width="600" border="1" cellspacing="0" cellpadding="0" style="margin-bottom:40px; background-color:#fff; font-size:15px;">
  <tr>
    <td  valign="middle" id="credit" style="border:0;text-align:center;padding:50px;"> ' 
    . 
    
    get_field('form_footer_content', 65703)
    
    . '    
    </td>
  </tr>
</table>

        <!--containter table -->    
            </td>
       </tr>       
    </table>


';
         
        }
        
    return $notification ;


}
