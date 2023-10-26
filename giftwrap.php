<?php
/**
 * @package Gift_Wrap 
 * @version 1.0.1
 */ 
/*
Plugin Name: Gift Wrap
Plugin URI: https://github.com/qasimzahoor-developer/custom-gift-wrap-plugin-wordpress
Description: Custom Printed Gift Items Shop
Armstrong: 
Author: Qasim Zahoor
Version: 1.0.1
Author URI: https://www.linkedin.com/in/qasimzahoor/
*/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
ob_start();
if (!session_id()) {
  session_start(); 
}  
//Widgets 
//require_once('widget-products.php');	 
//add_action( 'widgets_init', function() { register_widget( 'Gftw_Products_Widget' ); } );

//admin jquery Ui
// in constructor
add_action( 'admin_enqueue_scripts', 'enqueue_scripts');
add_action( 'admin_enqueue_scripts', 'enqueue_styles');
function enqueue_scripts()
{
    $wp_scripts = wp_scripts();
	wp_enqueue_script('jquery-ui-theme-smoothness', sprintf('//ajax.googleapis.com/ajax/libs/jqueryui/%s/jquery-ui.min.js', $wp_scripts->registered['jquery-ui-core']->ver) );
	wp_enqueue_script('nice-select', plugins_url('giftwrap/templates/js/jquery.nice-select.min.js'));
	wp_enqueue_script('spectrum', plugins_url('giftwrap/templates/js/spectrum.js'));
	wp_enqueue_script('jquery-ui-custom-objects', plugins_url('assts/js/designer_objects.js',__FILE__ ));
	wp_enqueue_script('jquery-ui-a-script', plugins_url('assts/js/a_script.js',__FILE__ ));
}
function enqueue_styles()   
{
    $wp_scripts = wp_scripts();
	wp_enqueue_style('nice-select', plugins_url('giftwrap/templates/css/nice-select.css'));
	wp_enqueue_style('spectrum', plugins_url('giftwrap/templates/css/spectrum.css'));
    wp_enqueue_style(
      'jquery-ui-theme-smoothness',
      sprintf(
        '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', // working for https as well now
        $wp_scripts->registered['jquery-ui-core']->ver
      )
    );
}

//Images Sizes
function giftwrap_image_sizes(){
	add_image_size( 'giftwrap-product100', 1000, 1000, false );
	add_image_size( 'giftwrap-product500', 500, 500, false );
	add_image_size( 'giftwrap-product300', 300, 300, false ); 
	add_image_size( 'giftwrap-thumbnail', 100, 100, false );
}
add_action('init', 'giftwrap_image_sizes', 10, 0); 
//Remove default fetaured image
function remove_meta_boxes() {
        remove_meta_box('postimagediv', 'products', 'side');
}
add_action('do_meta_boxes','remove_meta_boxes'); 
 
//inc file and assign menus
require_once('settings/gftw_settings.php');
require_once('settings/gftw_payment_shipping.php');
require_once('settings/gftw_settings_email.php');
if($_GET['page']=='gftw_manage_font') require_once('settings/gftw_manage_font.php');
if(strpos($_GET['page'], 'orders')==0) require_once('orders/orders.php');  
//Add Menu
function gftw_register_menu() { 
	    
	add_menu_page('Manage Order', 'Orders', 'administrator' , 'orders', 'list_orders','dashicons-media-spreadsheet', 3);
	add_submenu_page('orders', 'Printtech Orders', 'Printtech Orders', 'administrator' , 'orders_printtech', 'list_printtech','dashicons-admin-generic', 3);
	//add_submenu_page('Order', 'Manage Orders', 'administrator' , 'orders', 'list_orders','dashicons-admin-generic', 3);
	//Settings
	add_menu_page('Gift Wrap Settings', 'Gift Wrap Settings', 'administrator' , 'gift_wrap_settings', 'gftw_settings_page' ,'dashicons-admin-generic', 3);
	add_action( 'admin_init', 'register_gftw_settings' ); 
	add_submenu_page('gift_wrap_settings', 'Manage Shipping & payment', 'Manage Shipping & payment', 'administrator' , 'gftw_settings_ps', 'gftw_settings_page_ps' ,'dashicons-admin-generic');
	add_action( 'admin_init', 'register_gftw_settings_ps' );
	add_submenu_page('gift_wrap_settings', 'Manage Email Templates', 'Manage Email Templates', 'administrator' , 'gftw_settings_email', 'gftw_settings_page_email' ,'dashicons-admin-generic'); 
	add_action( 'admin_init', 'register_gftw_settings_email' );   
	add_submenu_page( 'gift_wrap_settings' , 'Manage Fonts', 'Manage Fonts', 'administrator' , 'gftw_manage_font', 'gftw_font_page'); 

	  
}  
add_action( 'admin_menu', 'gftw_register_menu' );


 
//Cart
function shop_func( $atts ){
	ob_start(); 
	require_once(ABSPATH . 'wp-content/plugins/giftwrap/shop.php');
	return ob_get_clean();
}
add_shortcode( 'giftwrap_shop', 'shop_func' );

//Cart
function cart_func( $atts ){
	ob_start(); 
	require_once(ABSPATH . 'wp-content/plugins/giftwrap/cart.php');
	return ob_get_clean();
}
add_shortcode( 'giftwrap_cart', 'cart_func' );

//Checkout
function checkout_func( $atts ){
	ob_start();
	require_once(ABSPATH . 'wp-content/plugins/giftwrap/checkout.php');
	return ob_get_clean();
}
add_shortcode( 'giftwrap_checkout', 'checkout_func' );

//Wordpress array sanitize
function wc_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wc_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( esc_sql($var) ) : $var; 
	}
}

//Front End Scripts
function frontend_assts(){
	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
	echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>';
	echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">';
	echo '<link rel="stylesheet" type="text/css" media="all" href="'.plugins_url('giftwrap/templates/css/style.css').'"/>';
	echo '<script type="text/javascript" src="'.plugins_url('giftwrap/templates/js/script.js').'"></script>';
	
	}
add_action('wp_head', 'frontend_assts'); 
//frontend alerts
function setAlert($data){
	$_SESSION['message'] = $data;
}
function displayAlert(){
	if(isset($_SESSION['message']['displayed']) AND $_SESSION['message']['displayed'] === 'true'){ 
		unset($_SESSION['message']);
		return;
	}
	if(isset($_SESSION['message'])){
		$_SESSION['message']['displayed'] = 'true';
		return $_SESSION['message'];
	}
	return ;
}
//forntend ajax
add_filter( 'query_vars', 'giftwrap_add_query_vars');
function giftwrap_add_query_vars($vars){
	$vars[] = "ajax";
	$vars[] = "payment";
    return $vars;  
} 
add_action('template_redirect', 'giftwrap_call_ajax');
function giftwrap_call_ajax($template) { 
	global $wp_query; //print_r($wp_query->query_vars); exit;
	if (isset($wp_query->query_vars['ajax'])):   
		if($_REQUEST['path'] =='cart') require_once(ABSPATH . 'wp-content/plugins/giftwrap/cart.php');
		if($_REQUEST['path'] =='checkout') require_once(ABSPATH . 'wp-content/plugins/giftwrap/checkout.php');
		if($_REQUEST['path'] =='customizer') require_once(ABSPATH . 'wp-content/plugins/giftwrap/customizer.php');  
		die();
	endif;
	if (isset($wp_query->query_vars['payment'])):  
		if(isset($_REQUEST['PayPal']) AND isset($_REQUEST['return'])) require_once(ABSPATH . 'wp-content/plugins/giftwrap/Payments/PayPal/return.php'); 
		die();
	endif;
	return $template;
}

//Rewrite Rules 
function bundels_rewrite() { 
  add_rewrite_tag('%product%', '([^&]+)');
  add_rewrite_rule('^ajax/?', 'index.php?ajax', 'top');
  add_rewrite_rule('^bundle/([^/]*)/([^/]*)/?','index.php?bundles=$matches[1]&product=$matches[2]','top');
  flush_rewrite_rules();
}
add_action('init', 'bundels_rewrite', 10, 0); 

//Custom Post & Texonomy
require_once(ABSPATH . 'wp-content/plugins/giftwrap/custom_post/texonomy.php');
require_once(ABSPATH . 'wp-content/plugins/giftwrap/custom_post/products.php');


//Installer
register_activation_hook( __FILE__, 'gftw_create_db' );
function gftw_create_db() {
	global $wpdb; 
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'gftw_cart'; 
	dbDelta( $sql ); 
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (
	  `id` int(11)  NOT NULL AUTO_INCREMENT,
	  `session_id` varchar(255) NOT NULL,
	  `cart_key` varchar(500) NOT NULL,
	  `product` int(11) NOT NULL,
	  `product_opt` varchar(500) DEFAULT NULL,
	  `bundle` int(11) NOT NULL,
	  `data` text,
	  `qty` int(11) NOT NULL,
	  PRIMARY KEY  (id)
	) '.$charset_collate; 
	dbDelta( $sql );
	
	$table_name = $wpdb->prefix . 'gftw_fonts';
	dbDelta( $sql );
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` ( 
	  `id` int(11)  NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) NOT NULL,
	  `name2` varchar(255) NOT NULL,
	  `file` varchar(500) NOT NULL,
	  `preview` varchar(500) NOT NULL,
	  PRIMARY KEY  (id)
	) '.$charset_collate;
	dbDelta( $sql );    
	register_uninstall_hook( __FILE__, 'gftw_uninsall_db' );
}

function gftw_uninsall_db(){
    if (! defined('WP_UNINSTALL_PLUGIN')) { exit; }  
	global $wpdb;
	$table_name = $wpdb->prefix . 'gftw_cart'; 
	$wpdb->query('DROP TABLE IF EXISTS `'.$table_name.'`;');  
	$table_name = $wpdb->prefix . 'gftw_fonts';
	$wpdb->query('DROP TABLE IF EXISTS `'.$table_name.'`;'); 

}

?>
