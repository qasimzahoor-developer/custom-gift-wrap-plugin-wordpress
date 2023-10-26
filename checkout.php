<?php  //print_r($_SESSION); exit;
//Post Checkout

send_order_email(44); exit;

$Cart = getCart(); 
if(is_array($Cart) AND count($Cart) < 1){ wp_redirect(home_url().'/cart'); die(); }
if($_POST){
	global $error;
	global $wpdb;
	
	$errors = new WP_Error();
	$data = wc_clean($_POST);  
	if(empty($data['first_name'])) $errors->add('first_name','First name is required.');
	if(empty($data['last_name'])) $errors->add('last_name','Last name is required.');
	if(empty($data['address']) OR strlen($data['address']) < 8) $errors->add('address','Valid address is required.');   
	if(empty($data['city'])) $errors->add('city','City is required.');
	if(empty($data['state'])) $errors->add('state','State is required.');
	if(empty($data['zip_code']) OR !preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$data['zip_code'])) $errors->add('zip_code','Valid Zip Code is required.');
	if(empty($data['phone_number'])) $errors->add('phone_number','Phone is required.');
	if(empty($data['email_address']) OR is_email($data['email_address'])==false) $errors->add('email_address','Valid email is required.');
	if(!is_wp_error($uploaded) AND empty($errors->errors)):
	$cartMeta = array();
	foreach($Cart as $cmeta){ 
		//$product = get_post($cmeta->product);
		$varaints = json_decode(get_post_meta($cmeta->product, 'giftwrap_varaints', true));    
		$pOption = json_decode($cmeta->product_opt);
		$pData = json_decode($cmeta->data);   
		$cartMeta[$cmeta->product]['id'] = $cmeta->id; 
		$cartMeta[$cmeta->product]['name'] = get_the_title($cmeta->product);
		$cartMeta[$cmeta->product]['price'] = get_post_meta($cmeta->product, 'giftwrap_price', true );  
		$cartMeta[$cmeta->product]['qty'] =$cmeta->qty;  
		$cartMeta[$cmeta->product]['shipping'] =  esc_attr(get_option('gftw_shipping_per_order')); 
		$cartMeta[$cmeta->product]['img'] = designed_image($cmeta->bundle, $cmeta->product);     
		$cartMeta[$cmeta->product]['options'] =  $pOption;   
		$cartMeta[$cmeta->product]['printimage'] = designed_image($cmeta->bundle, $cmeta->product, $pData->{$cmeta->bundle}->image); 
		$cartMeta[$cmeta->product]['printtext'] = array($pData->{$cmeta->bundle}->text1,  $pData->{$cmeta->bundle}->text2);
		foreach($varaints as $varaint){    
			if($varaint->color == $pOption->opt_color){ 
				$cartMeta[$cmeta->product]['img'] = designed_image($cmeta->bundle, $cmeta->product, $varaint->url);
			}  
		}              			  
	}    
	$cartMeta = designed_images_save($cartMeta); 
	$table_name = $wpdb->prefix . "gftw_orders"; 
	$wpdb->insert($table_name, array('order_no'=>date('m').date('Y'), 'first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'address'=>$data['address'], 'city'=>$data['city'], 'state'=>$data['state'], 'zipcode'=>$data['zip_code'], 'phone'=>$data['phone_number'], 'email'=>$data['email_address'], 'cart_meta'=>json_encode($cartMeta), 'payment_status'=>'Pending', 'order_status'=>'Pending'));
	$_SESSION['orderNow'] = $wpdb->insert_id;  
	require_once(ABSPATH . 'wp-content/plugins/giftwrap/Payments/PayPal/index.php');  
	endif;//not error 
	
}   

//Sucess Payment Checkout 
if(isset($_GET['CheckoutSucess']))
{ 
	if(!isset($_SESSION['orderNow'])){ wp_redirect(home_url()); die(); } 
	global $wpdb;     
	$table_name = $wpdb->prefix . "gftw_orders";    
	$wpdb->query("UPDATE ".$table_name." SET order_no=CONCAT(".$_SESSION['orderNow'].", order_no), payment_status='Complete', order_status='Waiting', payment_meta='".esc_sql(json_encode($_SESSION['payment_response']))."'  WHERE id=".$_SESSION['orderNow'].""); 
	$orderNo = $wpdb->get_results('SELECT `order_no` FROM '.$table_name.' WHERE `id`="'.$_SESSION['orderNow'].'";', 'ARRAY_A');  
	$upload_dir = wp_upload_dir();    
	$orderPath = $upload_dir['basedir'].'/giftwrap_temp/orders/'.session_id().'/';    
	$newOrderPath = $upload_dir['basedir'].'/giftwrap/orders/order_'.$orderNo[0]['order_no'].'/';       
	//if (!file_exists($newOrderPath)) { mkdir($newOrderPath, 0777, true); } 
	if (file_exists($orderPath)) recurse_copy($orderPath, $newOrderPath);  
	 
	$table_name = $wpdb->prefix . "gftw_cart";    
  	$wpdb->query('DELETE FROM `'.$table_name.'` WHERE session_id="'.session_id().'";');
	//if($wpdb->last_error !== ''){ var_dump($wpdb->last_error); }
//Prepa	
    //send emails  
	send_order_email($_SESSION['orderNow']);  exit;  
  rrmdir($orderPath); rrmdir($upload_dir['basedir'].'/giftwrap_temp/'.session_id().'/');  
  unset($_SESSION['payment_response']);
  unset($_SESSION['design']);
  unset($_SESSION['printImage']);      
  unset($_SESSION['toPay']);
  unset($_SESSION['token']); 
  unset($_SESSION['orderNow']); 
  unset($_SESSION['orderNow']);
  unset($_SESSION['orderNow']);
  unset($_SESSION['orderNow']);

  require_once(ABSPATH . 'wp-content/plugins/giftwrap/templates/template-checkout-success.php');  
  die();
} 


//Add Template
$states = getStates();
require_once(ABSPATH . 'wp-content/plugins/giftwrap/templates/template-checkout.php'); 

function getStates(){
	global $wpdb;
	$table_name = $wpdb->prefix . "gftw_states";
	return $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `country_id`=1;', 'ARRAY_A');	
}
function getCart(){ 
	global $wpdb;
	$table_name = $wpdb->prefix . "gftw_cart";  
	$Cart = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `session_id`="'.session_id().'";');
	return $Cart ;
}

function designed_image($cID, $pID, $url=false){  
	$upload_dir = wp_upload_dir();  
	if(isset($_SESSION['printImage'][$cID]) AND is_array($_SESSION['printImage'][$cID])){ 
		$imgPath = $upload_dir['basedir'].'/giftwrap_temp/'.session_id().'/'.$cID.'/'; 
	}else{  
		$imgPath = $upload_dir['basedir'].'/giftwrap/products/'.$pID.'/'; 
	} 
	if($url !== false) return $imgPath.basename($url);
	return $imgPath.basename(get_attached_file(get_post_thumbnail_id($pID)));	
}

function designed_images_save($data){    
	$upload_dir = wp_upload_dir();  
	$savePath = $upload_dir['basedir'].'/giftwrap_temp/orders/'.session_id().'/';
	$saveUrl = $upload_dir['baseurl'].'/giftwrap_temp/orders/'.session_id().'/';      
	if (!file_exists($savePath)) { mkdir($savePath, 0777, true); } 
	foreach($data as $key=>$dItem){
		if (file_exists($dItem['img']) AND is_file($dItem['img']))
		{ 
			copy($dItem['img'], $savePath.'p_'.$key.'_'.basename($dItem['img']));
			$data[$key]['img'] = $saveUrl.'p_'.$key.'_'.basename($dItem['img']);
		}else{ $data[$key]['img']=''; }
		if (file_exists($dItem['printimage']) AND is_file($dItem['printimage'])){
			 copy($dItem['printimage'], $savePath.'print_'.$key.'_'.basename($dItem['printimage']));   
			$data[$key]['printimage'] = $saveUrl.'print_'.$key.'_'.basename($dItem['printimage']);  
		}else{ $data[$key]['printimage']=''; }
	}
	return $data; 
}

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst,0777, true);   
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . $file) ) { 
                recurse_copy($src .  $file,$dst . $file); 
            } 
            else { 
                copy($src .  $file,$dst . $file); 
            } 
        }   
    } 
    closedir($dir); 
}
function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
           rrmdir($dir."/".$object); 
        else unlink   ($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
 }
 
function send_order_email($id){ 
	global $wpdb;$arrVars = array();
	$table_name = $wpdb->prefix . "gftw_orders"; 
	$Order = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `id`="'.$id.'";');
	$payment =  extract_payment($Order[0]->payment_meta);
	$cart = json_decode($Order[0]->cart_meta);
	$uploads = wp_upload_dir();
	$template = nl2br(get_option('gftw_order_main'));
	$arrVars['{{first_name}}'] =   $Order[0]->first_name; 
	$arrVars['{{last_name}}'] =   $Order[0]->last_name; 
	$arrVars['{{site_name}}'] =   esc_attr( get_option('gftw_shop_name')); 
	$arrVars['{{shipping_detail}}'] ='
	<h4>Shipping Address</h4><br>
	Name: '.$Order[0]->first_name . ' ' .$Order[0]->last_name.'<br/>
	Email: '.$Order[0]->email.'<br/>
	Phone: '.$Order[0]->phone.'<br/>
	Address: <br/>'.$Order[0]->address.'<br/> '.$Order[0]->city.'<br/> '.$Order[0]->zipcode.'<br/>'.$Order[0]->state.'<br/> USA
	
	'; 
	
	$order_items = '<table class="wp-list-table widefat fixed striped striped">
    <tr><td>Image</td><td>ID</td><td>Name</td><td>Price</td><td>Qty.</td><td>Sub Total</td></tr>';
	$subtotal = 0; 
	foreach($cart as $cItem):
		$subtotal+=$cItem->price*$cItem->qty;
		$order_items .='<td><img width="150" src="'.$uploads['baseurl'].'/giftwrap/orders/order_'.$Order[0]->order_no.'/'.basename($cItem->img).'" /></td>';
		$order_items .='<td>'.$cItem->id.'</td>';
		$order_items .='<td><strong>'.$cItem->name.'</strong><br />';
		foreach($cItem->options as $oKey=>$oVal):
			$order_items .='<small>'.$oKey.': <i>'.$oVal.'</i> </small><br/>';
		endforeach;	
		if(isset($cItem->printtext) AND $cItem->printtext[0]->text !==null){ $i=1; foreach($cItem->printtext as $print):
			$text = trim($print->text);
			if(!empty($text)){
				$order_items .='<small>Text '.$i.': <i>'.$text.'</i> </small><br/>';
                $order_items .='<small>Color '.$i.': <i>'.$print->css->fontColor.'</i> </small><br/>';
                $order_items .='<small>Font '.$i.': <i>'.$print->css->fontfamily.'</i> </small><br/>';  
		} $i++; endforeach; }
		$order_items .='</td>';
        $order_items .='<td>'.$cItem->price.'</td>';
        $order_items .='<td>'.$cItem->qty.'</td>';
        $order_items .='<td>'.$cItem->price*$cItem->qty.'</td></tr>';
	endforeach;
	$order_items .='</table><br>';
	$order_items .='<h4>Order Sub Total: '.$subtotal.' &#36; </h4>';
	$order_items .='<h4>Order Shipping: '.$cItem->shipping.' &#36; </h4>';
	$order_items .='<h4>Order Grand Total: '.($subtotal+$cItem->shipping).' &#36; </h4>';
	$arrVars['{{order_items}}'] = $order_items;
	$email_body = strtr($template , $arrVars);
	$to = $Order[0]->email;
	$subject = 'Your order #'.$Order[0]->order_no.' on '. esc_attr( get_option('gftw_shop_name'));
	$body = $email_body;
	$headers[] = 'From: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Bcc: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8'; 
	wp_mail( $to, $subject, $body, $headers );
 
 }
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 function extract_payment($var){
		$var = urldecode($var); 
		parse_str($var, $arr);
		return $arr;
	}