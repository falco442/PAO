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
				'PAYMENTREQUESTID'=>'CART'.$order_info['order_id'].'-PAYMENT0',
				'PAYMENTACTION'=>'Sale'
			),
			array(
				'SELLERPAYPALACCOUNTID'=>$onlus['paypal_id'],
				'DESC'=>$onlus['name'],
				'AMT'=>$totalAmountToOnlus,
				'PAYMENTREQUESTID'=>'CART'.$order_info['order_id'].'-PAYMENT1',
				'PAYMENTACTION'=>'Sale'
			)
		);
		
		$returnUrl = $server.'index.php?route=payment/pp_adap/commit';
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
		$this->session->data['paypal_data'] = $data;
		
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
	

	function commit(){
		if (!$this->config->get('pp_adap_test')) {
			$url = 'https://api-3t.paypal.com/nvp';
		} else {
			$url = 'https://api-3t.sandbox.paypal.com/nvp';
		}
		
		$header = array(
			"application/x-www-form-urlencoded",
		);
		
		$token = $this->request->get['token'];
		$payerId = $this->request->get['PayerID'];
		
		$data = $this->session->data['paypal_data'];
		unset($this->session->data['paypal_data']);
		
		$data['PAYERID'] = $payerId;
		$data['TOKEN'] = $token;
		$data['METHOD'] = 'DoExpressCheckoutPayment';
		
		$curl = curl_init($url);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($data));
		$response = curl_exec($curl);
		curl_close($curl);
		
		$array = array();
		parse_str(urldecode($response),$array);
		
		if(isset($array['ACK']) && $array['ACK']=='Success'){
		
			$message = '';
			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));

			if (isset($response['TRANSACTIONID'])) {
				$message .= 'TRANSACTIONID: ' . $response['TRANSACTIONID'] . "\n";
			}

			$this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('pp_adap_order_status_id'), $message, false);
		
			$this->redirect($this->url->link('checkout/success'));
		}
		else{
			die(print_r($array));
		}
	}
}
?>