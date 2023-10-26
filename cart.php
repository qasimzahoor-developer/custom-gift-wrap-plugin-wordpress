<?php 
 
if(!isset($mesage)) $mesage=array(); 
//Add to cart
if($_POST AND isset($_REQUEST['addtocart'])){ 
	$data = array(); 
	$data['arr'] = wc_clean($_POST);
	$data['session'] = 	session_id(); 
	$data['qty'] = $data['arr']['qty'];
	addCart($data);
}
//Delete cart item
if($_POST AND isset($_REQUEST['updateitems'])){ 
	updateCart(wc_clean($_POST)); 
}
//Delete cart item
if($_POST AND isset($_REQUEST['deleteitem'])){  
	deleteCart($_REQUEST['id']); 
}

//Add Template
$Cart = getCart();
require_once(ABSPATH . 'wp-content/plugins/giftwrap/templates/template-cart.php');

//Cart Functions
function getCart(){ 
	global $wpdb;
	$table_name = $wpdb->prefix . "gftw_cart";
	$Cart = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `session_id`="'.session_id().'";');
	return $Cart ;
}
function addCart($data){
	global $wpdb;
	$table_name = $wpdb->prefix . "gftw_cart";
	$cartKey = cart_key($data['arr']);  
	$exists = $wpdb->get_results('SELECT `id` FROM '.$table_name.' WHERE `session_id`="'.$data['session'].'" AND `cart_key`="'.$cartKey.'";');
	if(empty($exists)){
		$wpdb->insert($table_name, array('session_id'=>$data['session'], 'cart_key'=>$cartKey, 'product'=>$data['arr']['id'], 'product_opt'=>cart_product_options($data['arr']), 'bundle'=>$data['arr']['bundle'], 'data'=>json_encode($_SESSION['design']), 'qty'=>$data['qty'])); 
	}
	else{       
		$wpdb->query("UPDATE ".$table_name." SET qty=qty + ".$data['qty']." WHERE id=".$exists[0]->id."");
	} 
}

function updateCart($data){ 
	if(isset($data['qty']) AND !empty($data['qty'])){
		global $wpdb;
		$error = false;
		$table_name = $wpdb->prefix . "gftw_cart";
		foreach($data['qty'] as $key=>$value){
			$wpdb->update($table_name, array('qty'=>$value), array('id'=>$key)); 
			if($wpdb->last_error !== ''){ $error = true; }
		} 
		if($error){
			setAlert(['type'=>'error', 'msg'=>'something is wrong']); 
			exit; 
		}else{
			setAlert(['type'=>'sucess', 'msg'=>'cart update']);  
			exit;
		}
	}
}

function deleteCart($id){
	global $wpdb;
	$wpdb->delete("{$wpdb->prefix}gftw_cart",[ 'id' => $id ], [ '%d' ]); 
	if($wpdb->last_error !== ''){
		setAlert(['type'=>'error', 'msg'=>'item not deleted']);
		//wp_redirect(home_url().'/cart');
		exit; 
	}else{
		setAlert(['type'=>'sucess', 'msg'=>'item deleted']);
		//wp_redirect(home_url().'/cart'); 
		exit;
	} 
	
}

function cart_key($data) { 
	$ckey = '';
	foreach($data as $key=>$value){
		if($key!=='qty') $ckey.= $value;  
	}
	return sanitize_title($data['id'].$ckey);
}
function cart_product_options($data) {
	$rdata = array(); 
	foreach($data as $key=>$value){ 
		$key = (strpos($key, 'opt_')!==false)? ucfirst(str_replace('opt_', '', $key)) : NULL;
		if($key!==NULL) $rdata[$key] = $value;  
	}
	return json_encode($rdata); 
}


