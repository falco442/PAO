<?php
class ControllerPaymentPpAdap extends Controller {
	protected function index() {
		$this->language->load('payment/pp_adap');

		$this->data['text_credit_card'] = $this->language->get('text_credit_card');
		$this->data['text_start_date'] = $this->language->get('text_start_date');
		$this->data['text_issue'] = $this->language->get('text_issue');
		$this->data['text_wait'] = $this->language->get('text_wait');

		$this->data['entry_cc_type'] = $this->language->get('entry_cc_type');
		$this->data['entry_cc_number'] = $this->language->get('entry_cc_number');
		$this->data['entry_cc_start_date'] = $this->language->get('entry_cc_start_date');
		$this->data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
		$this->data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');
		$this->data['entry_cc_issue'] = $this->language->get('entry_cc_issue');
		$this->data['entry_choose_onlus'] = sprintf(
			$this->language->get('entry_choose_onlus'),
			$this->currency->format(
				$this->config->get('pp_adap_onlus_amount'),
				$this->config->get('pp_adap_currency_code'),
				false,
				true
			)
		);
		
		$this->data['entry_form_onlus_title'] = $this->language->get('entry_form_onlus_title');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['cards'] = array();

		$this->data['cards'][] = array(
			'text'  => 'Visa', 
			'value' => 'VISA'
		);

		$this->data['cards'][] = array(
			'text'  => 'MasterCard', 
			'value' => 'MASTERCARD'
		);

		$this->data['cards'][] = array(
			'text'  => 'Discover Card', 
			'value' => 'DISCOVER'
		);

		$this->data['cards'][] = array(
			'text'  => 'American Express', 
			'value' => 'AMEX'
		);

		$this->data['cards'][] = array(
			'text'  => 'Maestro', 
			'value' => 'SWITCH'
		);

		$this->data['cards'][] = array(
			'text'  => 'Solo', 
			'value' => 'SOLO'
		);		

		$this->data['months'] = array();

		for ($i = 1; $i <= 12; $i++) {
			$this->data['months'][] = array(
				'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)), 
				'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$this->data['year_valid'] = array();

		for ($i = $today['year'] - 10; $i < $today['year'] + 1; $i++) {	
			$this->data['year_valid'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)), 
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
			);
		}

		$this->data['year_expire'] = array();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$this->data['year_expire'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)) 
			);
		}
		
		$this->load->model('onlus/onlus');
		$this->data['onlus'] = $this->model_onlus_onlus->getAllOnlus();

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/pp_adap.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/pp_adap.tpl';
		} else {
			$this->template = 'default/template/payment/pp_adap.tpl';
		}

		$this->render();		
	}
	
	public function getKey(){
		if (!$this->config->get('pp_adap_test')) {
			$url = 'https://api.paypal.com';
		} else {
			$url = 'https://api.sandbox.paypal.com';
		}
		$sandbox_account = 'pagamenti-facilitator@hosmesso.eu';
		$client_id = 'Afzny8sw_gDBC19lKcWPsGq1Fr-_ZIoj_R9n1WCCWwcI7HVs2oE4Y2-qLJIUWEzEQKWVwgw4-tj9kdVe';
		$secret = 'EJ8h4JS9vk6ms8lKK9LCooVQSMN2eRm19Hm8UnD7kGzDDc9lcQJENA3y92aWcW5B6jQ0HRNQ_HCb_RQO';
		$url .= '/v1/oauth2/token';
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_POSTFIELDS,"grant_type=client_credentials");
		curl_setopt($curl,CURLOPT_USERPWD,"$client_id:$secret");
		curl_setopt($curl,CURLOPT_HTTPHEADER,array(
			"Accept: application/json",
			"Accept-Language: en_US",
			"application/x-www-form-urlencoded"
		));
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
		$response = curl_exec($curl);
		curl_close($curl);
		if(isset($response['access_token']))
			return $response['access_token'];
		return false;
	}

	public function send() {
		if (!$this->config->get('pp_adap_transaction')) {
			$payment_type = 'Authorization';	
		} else {
			$payment_type = 'Sale';
		}

		$this->load->model('checkout/order');
		$this->load->model('account/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$orderProducts = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
		
		$onlusId = $this->request->post['onlus_id'];
		$totalQuantity = 0;
		if(isset($orderProducts) && !empty($orderProducts)){
			foreach($orderProducts as $p){
				$totalQuantity += $p['quantity'];
			}
		}

		$totalAmountToOnlus = $this->currency->format(
			$this->currency->convert(
				$this->config->get('pp_adap_onlus_amount'),
				$this->config->get('pp_adap_currency_code'),
				$order_info['currency_code']
			),
			$order_info['currency_code'],
			false,
			false
		);
		if(!isset($totalQuantity))
			$totalQuantity = 1;
			
		$totalAmountToOnlus = $totalAmountToOnlus * $totalQuantity;
// 		$response = array(compact('totalAmountToOnlus','orderProducts','order_info','onlusId'));
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 200 OK');
		$this->response->setOutput(json_encode($response));
		return;

		$request  = 'METHOD=DoDirectPayment';
		$request .= '&VERSION=51.0';
		$request .= '&USER=' . urlencode($this->config->get('pp_adap_username'));
		$request .= '&PWD=' . urlencode($this->config->get('pp_adap_password'));
		$request .= '&SIGNATURE=' . urlencode($this->config->get('pp_adap_signature'));
		$request .= '&CUSTREF=' . (int)$order_info['order_id'];
		$request .= '&PAYMENTACTION=' . $payment_type;
		$request .= '&AMT=' . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		$request .= '&CREDITCARDTYPE=' . $this->request->post['cc_type'];
		$request .= '&ACCT=' . urlencode(str_replace(' ', '', $this->request->post['cc_number']));
		$request .= '&CARDSTART=' . urlencode($this->request->post['cc_start_date_month'] . $this->request->post['cc_start_date_year']);
		$request .= '&EXPDATE=' . urlencode($this->request->post['cc_expire_date_month'] . $this->request->post['cc_expire_date_year']);
		$request .= '&CVV2=' . urlencode($this->request->post['cc_cvv2']);

		if ($this->request->post['cc_type'] == 'SWITCH' || $this->request->post['cc_type'] == 'SOLO') { 
			$request .= '&CARDISSUE=' . urlencode($this->request->post['cc_issue']);
		}

		$request .= '&FIRSTNAME=' . urlencode($order_info['payment_firstname']);
		$request .= '&LASTNAME=' . urlencode($order_info['payment_lastname']);
		$request .= '&EMAIL=' . urlencode($order_info['email']);
		$request .= '&PHONENUM=' . urlencode($order_info['telephone']);
		$request .= '&IPADDRESS=' . urlencode($this->request->server['REMOTE_ADDR']);
		$request .= '&STREET=' . urlencode($order_info['payment_address_1']);
		$request .= '&CITY=' . urlencode($order_info['payment_city']);
		$request .= '&STATE=' . urlencode(($order_info['payment_iso_code_2'] != 'US') ? $order_info['payment_zone'] : $order_info['payment_zone_code']);
		$request .= '&ZIP=' . urlencode($order_info['payment_postcode']);
		$request .= '&COUNTRYCODE=' . urlencode($order_info['payment_iso_code_2']);
		$request .= '&CURRENCYCODE=' . urlencode($order_info['currency_code']);

		if ($this->cart->hasShipping()) {
			$request .= '&SHIPTONAME=' . urlencode($order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname']);
			$request .= '&SHIPTOSTREET=' . urlencode($order_info['shipping_address_1']);
			$request .= '&SHIPTOCITY=' . urlencode($order_info['shipping_city']);
			$request .= '&SHIPTOSTATE=' . urlencode(($order_info['shipping_iso_code_2'] != 'US') ? $order_info['shipping_zone'] : $order_info['shipping_zone_code']);
			$request .= '&SHIPTOCOUNTRYCODE=' . urlencode($order_info['shipping_iso_code_2']);
			$request .= '&SHIPTOZIP=' . urlencode($order_info['shipping_postcode']);
		} else {
			$request .= '&SHIPTONAME=' . urlencode($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname']);
			$request .= '&SHIPTOSTREET=' . urlencode($order_info['payment_address_1']);
			$request .= '&SHIPTOCITY=' . urlencode($order_info['payment_city']);
			$request .= '&SHIPTOSTATE=' . urlencode(($order_info['payment_iso_code_2'] != 'US') ? $order_info['payment_zone'] : $order_info['payment_zone_code']);
			$request .= '&SHIPTOCOUNTRYCODE=' . urlencode($order_info['payment_iso_code_2']);
			$request .= '&SHIPTOZIP=' . urlencode($order_info['payment_postcode']);			
		}		

		if (!$this->config->get('pp_adap_test')) {
			$curl = curl_init('https://api-3t.paypal.com/nvp');
		} else {
			$curl = curl_init('https://api-3t.sandbox.paypal.com/nvp');
		}

		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

		$response = curl_exec($curl);

		curl_close($curl);

		if (!$response) {
			$this->log->write('DoDirectPayment failed: ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
		}

		$response_info = array();

		parse_str($response, $response_info);

		$json = array();

		if (($response_info['ACK'] == 'Success') || ($response_info['ACK'] == 'SuccessWithWarning')) {
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));

			$message = '';

			if (isset($response_info['AVSCODE'])) {
				$message .= 'AVSCODE: ' . $response_info['AVSCODE'] . "\n";
			}

			if (isset($response_info['CVV2MATCH'])) {
				$message .= 'CVV2MATCH: ' . $response_info['CVV2MATCH'] . "\n";
			}

			if (isset($response_info['TRANSACTIONID'])) {
				$message .= 'TRANSACTIONID: ' . $response_info['TRANSACTIONID'] . "\n";
			}

			$this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('pp_adap_order_status_id'), $message, false);

			$json['success'] = $this->url->link('checkout/success');
		} else {
			$json['error'] = $response_info['L_LONGMESSAGE0'];
		}

		$this->response->setOutput(json_encode($json));
	}
	
	
	function setExpressCheckout(){
	
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
	
		$this->load->model('checkout/order');
		$this->load->model('account/order');
		$this->load->model('onlus/onlus');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$orderProducts = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
		$onlus = $this->model_onlus_onlus->getOnlus($this->request->post['onlus_id']);
		
		$totalQuantity = 0;
		if(isset($orderProducts) && !empty($orderProducts)){
			foreach($orderProducts as $p){
				$totalQuantity += $p['quantity'];
			}
		}

		$onlusAmount = $this->currency->convert(
			$this->config->get('pp_adap_onlus_amount'),
			$this->config->get('pp_adap_currency_code'),
			$order_info['currency_code']
		);
		$totalAmountToOnlus = $this->currency->format(
			$onlusAmount,
			$order_info['currency_code'],
			false,
			false
		);
		if(!isset($totalQuantity))
			$totalQuantity = 1;
	
	
		if (!$this->config->get('pp_adap_test')) {
			$url = 'https://api-3t.paypal.com/nvp';
		} else {
			$url = 'https://api-3t.sandbox.paypal.com/nvp';
		}
		
		$header = array(
			"X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
			"application/x-www-form-urlencoded",
		);
		
		$amount = $this->currency->format($order_info['total']-$onlusAmount, $order_info['currency_code'], false, false);
		
		
		$receivers = array(
			array(
				'SELLERPAYPALACCOUNTID'=>$this->config->get('pp_adap_paypal_id'),
				'DESC'=>'HOSMESSO',
				'AMT'=>$amount,
				'PAYMENTREQUESTID'=>'CART'.$order_info['order_id'].'-PAYMENT0'
			),
			array(
				'SELLERPAYPALACCOUNTID'=>$onlus['paypal_id'],
				'DESC'=>$onlus['name'],
				'AMT'=>$totalAmountToOnlus,
				'PAYMENTREQUESTID'=>'CART'.$order_info['order_id'].'-PAYMENT1'
			)
		);
		
		$returnUrl = $server.'index.php?route=pp_adap/getExpressCheckoutDetails';
		$cancelUrl = $server.'index.php?route=checkout/checkout';
		
		$data = array(
			'USER'=>urlencode($this->config->get('pp_adap_username')),
			'PWD'=>urlencode($this->config->get('pp_adap_password')),
			'SIGNATURE'=>urlencode($this->config->get('pp_adap_signature')),
			'METHOD'=>'SetExpressCheckout',
			'RETURNURL'=>$returnUrl,
			'CANCELURL'=>$cancelUrl,
			'VERSION'=>93,
			'PAYMENTREQUEST_0_CURRENCYCODE'=>$order_info['currency_code'],
			'PAYMENTREQUEST_1_CURRENCYCODE'=>$order_info['currency_code'],
			'REQCONFIRMSHIPPING'=>0,
			'NOSHIPPING'=>1,
		);
		
		if($this->config->get('config_logo')){
			$data['LOGOIMG'] = $server.'image/'.$this->config->get('config_logo');
		}
		
		foreach($receivers as $key=>$receiver){
			foreach($receiver as $field=>$value){
				$data['PAYMENTREQUEST_'.$key.'_'.$field] = $value;
			}
		}
		
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($data));
		$response = curl_exec($curl);
		curl_close($curl);
		
		$array = array();
		parse_str(urldecode($response),$array);
		
		if(isset($array['ACK']) && $array['ACK']=='Success'){
			$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
			$return = array('url'=>$url.$array['TOKEN']);
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 200 OK');
			$this->response->setOutput(json_encode($return));
			return;
		}
		else
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 500 Internal Server Error');
		
		$this->response->setOutput(json_encode(compact('array','data')));
	}
	
	function getExpressCheckoutDetails(){
	
		if (!$this->config->get('pp_adap_test')) {
			$curl = curl_init('https://api-3t.paypal.com/nvp');
		} else {
			$curl = curl_init('https://api-3t.sandbox.paypal.com/nvp');
		}
	
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
	
		$token = $this->request->get['token'];
		
		$data = array(
			'USER'		=>urlencode($this->config->get('pp_adap_username')),
			'PWD'		=>urlencode($this->config->get('pp_adap_password')),
			'SIGNATURE'	=>urlencode($this->config->get('pp_adap_signature')),
			'METHOD'	=>'GetExpressCheckoutDetails',
			'VERSION'	=>93,
			'TOKEN'		=>$token
		);
		
		$header = array(
			"X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
			"application/x-www-form-urlencoded",
		);
	}

}
?>