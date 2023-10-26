<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}    

function list_orders($status='waiting'){
	require_once('orders_list.php');   
	$urls_obj = new Orders_List(); 
}

function list_printtech(){
	
	print 'printtech here i am'; 
}
