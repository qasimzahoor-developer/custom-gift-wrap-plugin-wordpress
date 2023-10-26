<?php
session_start();
$settings = array(
			'USER'         => esc_attr( get_option('gftw_paymeny_pp_api_user') ),
			'PWD'          => esc_attr( get_option('gftw_paymeny_pp_api_password') ), 
			'SIGNATURE'    =>  esc_attr(get_option('gftw_paymeny_pp_api_key') ),
			'VERSION'      => '109.0',
			'BUTTONSOURCE' => 'Giftwrap_1.0.1'
		);
$paypal_api_url = (1 == esc_attr( get_option('gftw_paymeny_pp_api_sandbox')))? 'https://api-3t.sandbox.paypal.com/nvp': 'https://api-3t.paypal.com/nvp';
$paypal_req_url = (1 == esc_attr( get_option('gftw_paymeny_pp_api_sandbox')))? 'https://www.sandbox.paypal.com/': 'https://www.paypal.com/'; 
$info = array (
  'METHOD' => 'SetExpressCheckout',
  'MAXAMT' => 1500.0,
  'RETURNURL' => get_site_url().'?payment&PayPal&return',
  'CANCELURL' => get_site_url().'/cart/', 
  'REQCONFIRMSHIPPING' => 0,
  'NOSHIPPING' => 0,
  'LOCALECODE' => 'EN',
  'LANDINGPAGE' => 'Login',
  //'HDRIMG' => 'https://www.masterjackets.com/image/cache/catalog/logo-750x90.png',
  'PAYFLOWCOLOR' => '',
  'CHANNELTYPE' => 'Merchant',
  'ALLOWNOTE' => '0',
  'PAYMENTREQUEST_0_SHIPTONAME' => $data['first_name'].' '.$data['last_name'] ,
  'PAYMENTREQUEST_0_SHIPTOSTREET' => $data['address'],
  'PAYMENTREQUEST_0_SHIPTOSTREET2' => $data['address2'],
  'PAYMENTREQUEST_0_SHIPTOCITY' => $data['city'],
  'PAYMENTREQUEST_0_SHIPTOSTATE' => $data['state'],
  'PAYMENTREQUEST_0_SHIPTOZIP' => $data['zip_code'],
  'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'USA',
  'PAYMENTREQUEST_0_SHIPPINGAMT' => '',
  'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
  'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale');
  $items = array(); $i=0; $total =0; $prOptions = '';
  foreach($Cart as $item):
  	  $prOptions = ''; foreach(json_decode($item->product_opt) as $optK=>$optV){ $prOptions.= $optK.':'.$optV.', ';}
  	  $total += get_post_meta($item->product, 'giftwrap_price', true )*$item->qty;
	  $items['L_PAYMENTREQUEST_0_DESC'.$i] = rtrim($prOptions,', ');
	  $items['L_PAYMENTREQUEST_0_NAME'.$i] = get_the_title($item->product);
	  $items['L_PAYMENTREQUEST_0_NUMBER'.$i] = $item->product; 
	  $items['L_PAYMENTREQUEST_0_AMT'.$i] = get_post_meta($item->product, 'giftwrap_price', true ); 
	  $items['L_PAYMENTREQUEST_0_QTY'.$i] = $item->qty;
	  $items['L_PAYMENTREQUEST_0_ITEMURL'.$i] = get_term_link(intval($item->bundle)).get_post_field('post_name',intval($item->product)).'/';
	  $i++;
  endforeach;
  //$total;
  $shipping = esc_attr( get_option('gftw_shipping_per_order') );
  $shipping =array( 
  'L_PAYMENTREQUEST_0_NUMBER'.$i => 'shipping',
  'L_PAYMENTREQUEST_0_NAME'.$i => 'Flat Shipping Rate',
  'L_PAYMENTREQUEST_0_AMT'.$i => $shipping,
  'L_PAYMENTREQUEST_0_QTY'.$i => 1,
  'PAYMENTREQUEST_0_ITEMAMT' => $total += $shipping, 
  'PAYMENTREQUEST_0_AMT' => $total  
); 
$_SESSION['toPay'] = $total;
$data = array_merge($info, $items, $shipping);

$defaults = array(
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $paypal_api_url,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_POSTFIELDS => http_build_query(array_merge($data, $settings), '', "&"),
		);
$ch = curl_init();
curl_setopt_array($ch, $defaults); 
$result = curl_exec($ch); 
if(empty($result))die("Error: No response.");
else 
{   
 	$result = explode('&',$result);
	$_SESSION['token'] = str_replace('TOKEN=', '',  $result[0]); 
    header('Location: '.$paypal_req_url.'cgi-bin/webscr?cmd=_express-checkout&useraction=commit&' . $result[0]);      
}

curl_close($ch); 

?>