<?php 
class ControllerPaymentPpAdap extends Controller {
	private $error = array(); 

	public function index() {
		$this->language->load('payment/pp_adap');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_adap', $this->request->post);				

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_authorization'] = $this->language->get('text_authorization');
		$this->data['text_sale'] = $this->language->get('text_sale');

		$this->data['entry_username'] = $this->language->get('entry_username');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_signature'] = $this->language->get('entry_signature');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_transaction'] = $this->language->get('entry_transaction');
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['text_onlus'] = $this->language->get('text_onlus');
		$this->data['text_onlus_paypal_id'] = $this->language->get('text_onlus_paypal_id');
		$this->data['text_onlus_add'] = $this->language->get('text_onlus_add');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['username'])) {
			$this->data['error_username'] = $this->error['username'];
		} else {
			$this->data['error_username'] = '';
		}

		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

		if (isset($this->error['signature'])) {
			$this->data['error_signature'] = $this->error['signature'];
		} else {
			$this->data['error_signature'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/pp_adap', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/pp_adap', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['pp_adap_username'])) {
			$this->data['pp_adap_username'] = $this->request->post['pp_adap_username'];
		} else {
			$this->data['pp_adap_username'] = $this->config->get('pp_adap_username');
		}

		if (isset($this->request->post['pp_adap_password'])) {
			$this->data['pp_adap_password'] = $this->request->post['pp_adap_password'];
		} else {
			$this->data['pp_adap_password'] = $this->config->get('pp_adap_password');
		}

		if (isset($this->request->post['pp_adap_signature'])) {
			$this->data['pp_adap_signature'] = $this->request->post['pp_adap_signature'];
		} else {
			$this->data['pp_adap_signature'] = $this->config->get('pp_adap_signature');
		}

		if (isset($this->request->post['pp_adap_test'])) {
			$this->data['pp_adap_test'] = $this->request->post['pp_adap_test'];
		} else {
			$this->data['pp_adap_test'] = $this->config->get('pp_adap_test');
		}

		if (isset($this->request->post['pp_adap_method'])) {
			$this->data['pp_adap_transaction'] = $this->request->post['pp_adap_transaction'];
		} else {
			$this->data['pp_adap_transaction'] = $this->config->get('pp_adap_transaction');
		}

		if (isset($this->request->post['pp_adap_total'])) {
			$this->data['pp_adap_total'] = $this->request->post['pp_adap_total'];
		} else {
			$this->data['pp_adap_total'] = $this->config->get('pp_adap_total'); 
		} 

		if (isset($this->request->post['pp_adap_order_status_id'])) {
			$this->data['pp_adap_order_status_id'] = $this->request->post['pp_adap_order_status_id'];
		} else {
			$this->data['pp_adap_order_status_id'] = $this->config->get('pp_adap_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['pp_adap_geo_zone_id'])) {
			$this->data['pp_adap_geo_zone_id'] = $this->request->post['pp_adap_geo_zone_id'];
		} else {
			$this->data['pp_adap_geo_zone_id'] = $this->config->get('pp_adap_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['pp_adap_status'])) {
			$this->data['pp_adap_status'] = $this->request->post['pp_adap_status'];
		} else {
			$this->data['pp_adap_status'] = $this->config->get('pp_adap_status');
		}

		if (isset($this->request->post['pp_adap_sort_order'])) {
			$this->data['pp_adap_sort_order'] = $this->request->post['pp_adap_sort_order'];
		} else {
			$this->data['pp_adap_sort_order'] = $this->config->get('pp_adap_sort_order');
		}
		
		$this->data['onlus'] = $this->model_onlus_onlus->getOnlus();

		$this->template = 'payment/pp_adap.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/pp_adap')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['pp_adap_username']) {
			$this->error['username'] = $this->language->get('error_username');
		}

		if (!$this->request->post['pp_adap_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (!$this->request->post['pp_adap_signature']) {
			$this->error['signature'] = $this->language->get('error_signature');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	
	public function install(){
		$this->createTables();
	}
	
	public function uninstall(){
		$this->dropTables();
	}
	
	protected function createTables(){
		$sql = "
			CREATE TABLE IF NOT EXISTS `".DB_PREFIX."onlus` (
			`onlus_id` int(10) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			`paypal_id` varchar(255) NOT NULL,
			PRIMARY KEY (`onlus_id`)
			);
		";
		$this->db->query($sql);
	}
	
	protected function dropTables(){
		$sql = "DROP TABLE IF EXISTS ``";
	}
}
?>