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

		$data = array(
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN'  => $_GET['token']   
		);
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
curl_close($ch);
if(empty($result))die("Error: No response.");
else
{
 	$result = explode('&',$result);
	foreach($result as $resultArr){
		$result2[] = explode('=',$resultArr); 
	}
	
	$data = array(); 
	foreach($result2 as $result2Arr){
		$data[$result2Arr[0]]  = $result2Arr[1];	
	}

	$paypal_data = array(
				'TOKEN'                      => $_GET['token'],
				'PAYERID'                    => $_GET['PayerID'], 
				'METHOD'                     => 'DoExpressCheckoutPayment',
				//'PAYMENTREQUEST_0_NOTIFYURL' => 'http://localhost/paypal/notifier.php',  
				'RETURNFMFDETAILS'           => 1, 
				'PAYMENTACTION'=>'sale',
				'AMT'=>$_SESSION['toPay'],
				'CURRENCYCODE'> $data['CURRENCYCODE']
				
			);
			
//$paypal_data = array_merge($paypal_data, $data); 
	
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
			CURLOPT_POSTFIELDS => http_build_query(array_merge($paypal_data, $settings), '', "&"),
		);
	
	$ch = curl_init();
	curl_setopt_array($ch, $defaults); 
	$result3 = curl_exec($ch);
	curl_close($ch);
	
	$_SESSION['payment_response'] =  $result3.http_build_query($data);     
    header('Location:'.get_site_url() . '/checkout/?CheckoutSucess');

}



?>













