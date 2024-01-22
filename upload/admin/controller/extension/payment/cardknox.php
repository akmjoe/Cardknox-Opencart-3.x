<?php
class ControllerExtensionPaymentCardknox extends Controller {
	private $error = array();
	private $version = 1.0;

	public function index() {
		$this->load->language('extension/payment/cardknox');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->upgrade();
			$this->model_setting_setting->editSetting('payment_cardknox', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['login'])) {
			$data['error_login'] = $this->error['login'];
		} else {
			$data['error_login'] = '';
		}

		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/cardknox', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/cardknox', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_cardknox_transaction_key'])) {
			$data['payment_cardknox_transaction_key'] = $this->request->post['payment_cardknox_transaction_key'];
		} else {
			$data['payment_cardknox_transaction_key'] = $this->config->get('payment_cardknox_transaction_key');
		}

		if (isset($this->request->post['payment_cardknox_token_key'])) {
			$data['payment_cardknox_token_key'] = $this->request->post['payment_cardknox_token_key'];
		} else {
			$data['payment_cardknox_token_key'] = $this->config->get('payment_cardknox_token_key');
		}

		if (isset($this->request->post['payment_cardknox_server'])) {
			$data['payment_cardknox_server'] = $this->request->post['payment_cardknox_server'];
		} else {
			$data['payment_cardknox_server'] = $this->config->get('payment_cardknox_server');
		}

		if (isset($this->request->post['payment_cardknox_method'])) {
			$data['payment_cardknox_method'] = $this->request->post['payment_cardknox_method'];
		} else {
			$data['payment_cardknox_method'] = $this->config->get('payment_cardknox_method');
		}

		if (isset($this->request->post['payment_cardknox_total'])) {
			$data['payment_cardknox_total'] = $this->request->post['payment_cardknox_total'];
		} else {
			$data['payment_cardknox_total'] = $this->config->get('payment_cardknox_total');
		}

		if (isset($this->request->post['payment_cardknox_order_status_id'])) {
			$data['payment_cardknox_order_status_id'] = $this->request->post['payment_cardknox_order_status_id'];
		} else {
			$data['payment_cardknox_order_status_id'] = $this->config->get('payment_cardknox_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		
		if (isset($this->request->post['payment_cardknox_geo_zone_id'])) {
			$data['payment_cardknox_geo_zone_id'] = $this->request->post['payment_cardknox_geo_zone_id'];
		} else {
			$data['payment_cardknox_geo_zone_id'] = $this->config->get('payment_cardknox_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['payment_cardknox_card'])) {
			$data['payment_cardknox_card'] = $this->request->post['payment_cardknox_card'];
		} else {
			$data['payment_cardknox_card'] = $this->config->get('payment_cardknox_card');
		}
		
		if (isset($this->request->post['payment_cardknox_debug'])) {
			$data['payment_cardknox_debug'] = $this->request->post['payment_cardknox_debug'];
		} else {
			$data['payment_cardknox_debug'] = $this->config->get('payment_cardknox_debug');
		}
		
		if (isset($this->request->post['payment_cardknox_fraud'])) {
			$data['payment_cardknox_fraud'] = $this->request->post['payment_cardknox_fraud'];
		} else {
			$data['payment_cardknox_fraud'] = $this->config->get('payment_cardknox_fraud');
		}
		
		if (isset($this->request->post['payment_cardknox_brute_time'])) {
			$data['payment_cardknox_brute_time'] = $this->request->post['payment_cardknox_brute_time'];
		} else {
			$data['payment_cardknox_brute_time'] = $this->config->get('payment_cardknox_brute_time');
		}
		
		if (isset($this->request->post['payment_cardknox_brute_count'])) {
			$data['payment_cardknox_brute_count'] = $this->request->post['payment_cardknox_brute_count'];
		} else {
			$data['payment_cardknox_brute_count'] = $this->config->get('payment_cardknox_brute_count');
		}
		
		if (isset($this->request->post['payment_cardknox_status'])) {
			$data['payment_cardknox_status'] = $this->request->post['payment_cardknox_status'];
		} else {
			$data['payment_cardknox_status'] = $this->config->get('payment_cardknox_status');
		}

		if (isset($this->request->post['payment_cardknox_sort_order'])) {
			$data['payment_cardknox_sort_order'] = $this->request->post['payment_cardknox_sort_order'];
		} else {
			$data['payment_cardknox_sort_order'] = $this->config->get('payment_cardknox_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/cardknox', $data));
	}
	
	
	public function install() {
		$this->db->query("CREATE TABLE ".DB_PREFIX."cardknox_card (customer_card_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, customer_id int(11) NOT NULL, token varchar(128) NOT NULL, exp varchar(4), pan varchar(24), `card_type` varchar(24), status tinyint(1))");
		$this->db->query("CREATE TABLE ".DB_PREFIX."cardknox_transaction (transaction_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, card_id int(11), order_id int(11) NOT NULL, refnum varchar(24), authcode varchar(24), avs varchar(3), cvv varchar(1), amount float(16,2), `card_type` varchar(24), pan varchar(24), token varchar(128) NOT NULL, tran_date datetime default CURRENT_TIMESTAMP)");
		// set up event handlers
		$this->load->model('setting/event');
		// cleanup after order place/logout
		$this->model_setting_event->addEvent('cardknox', 'catalog/controller/checkout/success/after', 'extension/event/cardknox/clean');
		$this->model_setting_event->addEvent('cardknox', 'catalog/controller/account/logout/after', 'extension/event/cardknox/clean');
		$this->model_setting_event->addEvent('cardknox', 'catalog/controller/account/login/after', 'extension/event/cardknox/clean');
		// erase cards on password reset
		$this->model_setting_event->addEvent('cardknox', 'catalog/model/account/customer/editCode/after', 'extension/event/cardknox/secure');
	}
	
	public function uninstall() {
		$this->db->query("DROP TABLE ".DB_PREFIX."cardknox_card");
		$this->db->query("DROP TABLE ".DB_PREFIX."cardknox_transaction");
		if((float)$this->config->get('payment_cardknox_version') > 0) {
			$this->db->query("DROP TABLE ".DB_PREFIX."cardknox_log");
		}
		// Remove event handlers
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('cardknox');
	}
	
	public function upgrade() {
		// upgrade if required
		$current = (float)$this->config->get('payment_cardknox_version');
		if($current < $this->version) {
			if($current < 1) {
				// add brute force logging table
				$this->db->query("CREATE TABLE ".DB_PREFIX."cardknox_log (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ip varchar(40), tran_date datetime default CURRENT_TIMESTAMP)");
			}
		}
		// update version number
		$this->request->post['payment_cardknox_version'] = $this->version;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/cardknox')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_cardknox_transaction_key']) {
			$this->error['login'] = $this->language->get('error_transaction_key');
		}

		if (!$this->request->post['payment_cardknox_token_key']) {
			$this->error['key'] = $this->language->get('error_token_key');
		}

		return !$this->error;
	}
}
