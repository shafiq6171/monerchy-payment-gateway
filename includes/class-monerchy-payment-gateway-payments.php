<?php
if (!class_exists('WC_Payment_Gateway')) {
    return;
}

class WC_Monerchy_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'monerchy';
        $this->icon = MONERCHY_PAYMENT_GATEWAY_DIR_URL.'public/css/monerchy.svg'; // URL to your payment gateway's icon
        $this->has_fields = false;
        $this->method_title = 'Monerchy';
        $this->method_description = 'Pay with Monerchy';

        // Define your settings here
        $this->init_form_fields();
        $this->init_settings();
		$whitelist 			= $this->get_option('bankCodesWhitelist');
	
		
        $this->title 				= $this->get_option('title');
        $this->description 			= $this->get_option('description');
        $this->merchant_id 			= $this->get_option('merchant_id');
        $this->api_key 				= $this->get_option('api_key');
        $this->api_secret 			= $this->get_option('api_secret');
        $this->conversion 			= $this->get_option('amountConversion');
        $this->whitelist_bank 		= !empty($whitelist)? explode(',',$whitelist):array();

        // Add actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

		add_action("woocommerce_api_return", [$this, "monerchy_return_handler"]);
		add_action('woocommerce_api_callback', [$this, 'monerchy_callback_handler']);
		
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    }

    // Define your form fields
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable','monerchy-payment-gateway'),
                'label' => __('Enable Monerchy Payment Gateway','monerchy-payment-gateway'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Title','monerchy-payment-gateway'),
                'type' => 'text',
                'description' => __('This controls the title that the user sees during checkout.','monerchy-payment-gateway'),
                'default' => 'Monerchy',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description','monerchy-payment-gateway'),
                'type' => 'textarea',
                'description' => __('This controls the description that the user sees during checkout.','monerchy-payment-gateway'),
                'default' => __('Pay with Monerchy','monerchy-payment-gateway'),
            ),
            'merchant_id' => array(
                'title' => 'Merchant ID',
                'type' => 'text',
                'description' => __('Your Monerchy Merchant ID.','monerchy-payment-gateway'),
            ),
            'api_key' => array(
                'title' => __('API Key','monerchy-payment-gateway'),
                'type' => 'text',
                'description' => __('Your Monerchy API Key.','monerchy-payment-gateway'),
            ),
            'api_secret' => array(
                'title' => __('API Secret','monerchy-payment-gateway'),
                'type' => 'text',
                'description' => __('Your Monerchy API Secret.','monerchy-payment-gateway'),
            ), 
			'amountConversion' => array(
                'title' => __('Currency Conversion','monerchy-payment-gateway'),
                'type' => 'select',
				'options'=>array(
					'disabled'		=>'Disabled',
					'market-rates'	=>'Market Rates',
					'fixed-amounts'	=>'Fixed amount',
				),
                'description' => sprintf("See details  <a href='%s' target='_blank'>here</a>",'https://sdk.monerchy.com/docs/#section/Widget/Reference'),
            ),
			'bankCodesWhitelist' => array(
                'title' => __('White List Bank','monerchy-payment-gateway'),
                'type' => 'textarea',
                'description' => __('ex: type tokenio_ob-revolut-eea and seperate by comma','monerchy-payment-gateway'),
            )
        );
    }

	public function payment_scripts() {
	/*
		// we need JavaScript to process a token only on cart/checkout pages, right?
		if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
			return;
		}

		// if our payment gateway is disabled, we do not have to enqueue JS too
		if ( 'no' === $this->enabled ) {
			return;
		}
        */

	}
   

    // Process the payment and handle the callback
    public function process_payment($order_id) {
		$order = wc_get_order($order_id);
		$description = "";
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$item_name = $cart_item['data']->get_title();
			$quantity = $cart_item['quantity'];
			$price = $cart_item['data']->get_price();
			$description .= $item_name." ".$price." x ".$quantity;
		}
		
		$return_url 			= site_url('wc-api/return');
		$callback_url 			= site_url('wc-api/callback');
        
		$currentTimestamp = time();
		// Add 3 hours (3 * 60 minutes * 60 seconds) to the current timestamp
		$newTimestamp = $currentTimestamp + (3 * 60 * 60);

		// Convert the new timestamp to a date and time string
		$newDateTime = date('Y-m-d H:i:s', $newTimestamp);
		$data = array(
			"amount" => WC()->cart->cart_contents_total,
			"currency" => "EUR",
			"returnUrl" => $return_url.'?order_id='.$order_id,
			"description" => $description,
			//"callbackUrl" => $callback_url.'?order_id='.$order_id,
			"settings" => array(
				"skipAuth" => true,
				"amountConversion" => array(
					"type" => $this->conversion
				)
			),
			"metadata" => (object)array(),
			"expiresAt" => $newDateTime,
		);

		// Convert the PHP array to JSON
		$jsonData = json_encode($data);
		$authorization = base64_encode($this->merchant_id . ":" . $this->api_key);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://sdk.monerchy.com/transactions',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>$jsonData,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Basic '.$authorization
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		update_option("update_payment_process",$response);
		if($response){
			$responseObj = json_decode($response);
			if ($responseObj && isset($responseObj->payload->paymentUrl)) {
				$paymentUrl 		= $responseObj->payload->paymentUrl;
				$transactions_id 	= $responseObj->payload->id;
				$order->update_meta_data('awating_payment_id', $transactions_id);
				$order->save();
				return [
					"result" => "success",
					"redirect" => $paymentUrl,
				];
			}
		}else{
			return [
				"result" => "success",
				"redirect" => $this->get_return_url($order),
			];
		}
		 
    }
	 /*
     * In case you need a webhook, like PayPal IPN etc
     */
     public function monerchy_callback_handler(){
		 update_option("callback_handler", $_GET);
		//exit;
        	
    }
    public function monerchy_return_handler(){
		global $woocommerce;
        $order_id = sanitize_text_field($_GET['order_id']);
		$order = wc_get_order($order_id);
		$transaction =  get_post_meta($order_id, 'awating_payment_id', true);
		$authorization = 'Basic ' . base64_encode($this->merchant_id . ':' . $this->api_key );
		$response =  check_payment_monerchy_gateway($transaction,$authorization);
		$responseObj = json_decode($response);
       
		if(!empty($responseObj) && $responseObj->error ==""){
			$status = $responseObj->payload->status;
			if ($status === "PAYMENT_SUCCESS" || $status === 'PAYMENT_ACCEPTED') {
				delete_post_meta($order_id, 'awating_payment_id');
				$order->update_meta_data('transaction_id', $transaction);

				$order->reduce_order_stock();
				$order->payment_complete(); 
				$woocommerce->cart->empty_cart();
				$redirect_url = $this->get_return_url($order);
				$order->add_order_note(__("Payment is successfull! transaction id ".$transaction , "monerchy-payment-gateway"), true);
				$order->save();
				wp_redirect($redirect_url);
				exit;
			}else{
                wp_delete_post($order_id,true);
				wp_redirect(wc_get_checkout_url());
                exit;
            }
		}
    }
}