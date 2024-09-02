<?php
/**
* Template Name: Subscription Managment Page
* Description: https://rubnrestore.com/wp-admin/admin.php?page=stcr_management_page
*/

//Make this the subscription managment page
if (isset($wp_subscribe_reloaded)){ 



global $posts; 

//$posts= '<div style="margin:60px;"><h1> Manage Subscriptions</h1>';

$posts = $wp_subscribe_reloaded->stcr->subscribe_reloaded_manage(); 

//$posts .= '</div>';


} 

genesis();