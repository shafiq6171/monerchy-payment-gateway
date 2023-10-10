<?php 
function check_payment_monerchy_gateway($transaction,$authorization){
require_once( ABSPATH . 'wp-includes/functions.php' );

	$url = 'https://sdk.monerchy.com/transactions/'.$transaction;

	$headers = array(
		'Authorization' => $authorization,
	);

	$response = wp_remote_get( $url, array(
		'headers' => $headers,
	) );
	if(!is_wp_error( $response ) ) {
		$body = wp_remote_retrieve_body( $response );
		return $body;
	}
}
//$ksf = get_option('monerchy_cancel_return_handler');
//print_r($ksf);