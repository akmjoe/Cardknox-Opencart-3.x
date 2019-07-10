<?php
class ControllerExtensionPaymentCardknox extends Controller {
	public function index() {

		$this->load->language('extension/payment/cardknox');
		$data['months'] = array();

		for ($i = 1; $i <= 12; $i++) {
			$data['months'][] = array(
				'text'  => strftime('%m %B', mktime(0, 0, 0, $i, 1, 2000)),
				'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$data['year_expire'] = array();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
			);
		}
		$data['card_save'] = false;
		$data['card_save_option'] = false;
		if ($this->customer->isLogged() && $this->config->get('payment_cardknox_card')) {
			$data['card_save'] = true;
			$data['card_save_option'] = $this->config->get('payment_cardknox_card')==1;
			
            $this->load->model('extension/credit_card/cardknox');

            $cards = $this->model_extension_credit_card_cardknox->getCards($this->customer->getId());

		
            foreach ($cards as $card) {
				if(substr($card['exp'],2,2) < date('y') || (substr($card['exp'],2,2) == date('y') && substr($card['exp'],0,2) < date('m'))) continue;// expired - skip
                $data['cards'][] = array(
                    'id' => $card['customer_card_id'],
                    'text' => sprintf($this->language->get('text_card'), $card['pan'], substr($card['exp'],0,2), substr($card['exp'],2,2))
                );
            }
        }
		$data['manage'] = $this->url->link('extension/credit_card/cardknox', '', true);
		$data['text_manage'] = $this->language->get('text_manage');
		
		$data['cardknox_token_key'] = $this->config->get('payment_cardknox_token_key');
		// $this->document->addScript('https://cdn.cardknox.com/ifields/ifields.min.js');
		return $this->load->view('extension/payment/cardknox', $data);
	}

	public function send() {
		$url = 'https://x1.cardknox.com/gateway';

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data = array();

		$data['xKey'] = $this->config->get('payment_cardknox_transaction_key');
		$data['xVersion'] = '4.5.8';
		$data['xSoftwareName'] = 'OpenCart';
		$data['xSoftwareVersion'] = '1.0.2';

		$data['xBillFirstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		$data['xBillLastname'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$data['xBillCompany'] = html_entity_decode($order_info['payment_company'], ENT_QUOTES, 'UTF-8');
		$data['xBillStreet'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		$data['xBillCity'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
		$data['xBillState'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
		$data['xBillZip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
		$data['xBillCountry'] = html_entity_decode($order_info['payment_country'], ENT_QUOTES, 'UTF-8');
		$data['xBillPhone'] = $order_info['telephone'];
		$data['xIP'] = $this->request->server['REMOTE_ADDR'];
		$data['xEmail'] = $order_info['email'];
		$data['xDescription'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		$data['xAmount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false);
		$data['xCurrency'] = $this->session->data['currency'];
		$data['xCommand'] = ($this->config->get('payment_cardknox_method') == 'capture') ? 'cc:sale' : 'cc:authonly';
		if($this->request->post['cc_from'] == 'existing' && $this->request->post['cc_id']) {
			$this->load->model('extension/credit_card/cardknox');
			$data['xToken'] = $this->model_extension_credit_card_cardknox->getToken($this->customer->getID(), $this->request->post['cc_id']);
		} else {
			$data['xCardNum'] = $this->request->post['xCardNum'];
			$data['xExp'] = $this->request->post['cc_expire_date_month'] . substr($this->request->post['cc_expire_date_year'],2,2);
			$data['xCVV'] = $this->request->post['xCVV'];
		}
		$data['xInvoice'] = $this->session->data['order_id'];

		/* Customer Shipping Address Fields */
		if ($order_info['shipping_method']) {
			$data['xShipFirstname'] = html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8');
			$data['xShipLastname'] = html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
			$data['xShipCompany'] = html_entity_decode($order_info['shipping_company'], ENT_QUOTES, 'UTF-8');
			$data['xShipStreet'] = html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['shipping_address_2'], ENT_QUOTES, 'UTF-8');
			$data['xShipCity'] = html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
			$data['xShipState'] = html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
			$data['xShipZip'] = html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
			$data['xShipCountry'] = html_entity_decode($order_info['shipping_country'], ENT_QUOTES, 'UTF-8');
		} else {
			$data['xShipFirstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['xShipLastname'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['xShipCompany'] = html_entity_decode($order_info['payment_company'], ENT_QUOTES, 'UTF-8');
			$data['xShipStreet'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['xShipCity'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['xShipState'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
			$data['xShipZip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['xShipCountry'] = html_entity_decode($order_info['payment_country'], ENT_QUOTES, 'UTF-8');
		}
		
		$replacements = array('xKey' => "******", 'xCardNum' => "******", 'xCVV' => "******");	

		$this->logger(array_replace($data, $replacements));
		
		if($this->config->get('payment_cardknox_debug')) {
			$this->log->write($this->request->post);
		}
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		$apiResponse = curl_exec($curl);
		$json = array();

		if (curl_error($curl)) {
			$json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);

			$this->logger('CARDKNOX CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl));
		} elseif ($apiResponse) {
			$response_info = array();
			parse_str($apiResponse, $response_info);
			$this->logger($response_info);
			if (( $response_info['xResult'] == 'A') ){
				$message = '';
				$comment = '';
				if (isset($response_info['xAuthCode'])) {
					$message .= 'Authorization Code: ' . $response_info['xAuthCode'] . "\n";
				}

				if (isset($response_info['xAvsResult'])) {
					$message .= 'AVS Response: ' . $response_info['xAvsResult'] . "\n";
				}

				if (isset($response_info['xRefNum'])) {
					$message .= 'Reference Number: ' . $response_info['xRefNum'] . "\n";
				}

				if (isset($response_info['xCVVResult'])) {
					$message .= 'Card Code Response: ' . $response_info['xCVVResponse'] . "\n";
				}
				if (isset($response_info['xStatus']) ) {
					$message .= 'Status: ' . $response_info['xStatus'] . "\n";;
				}
				// now save transaction info
				$this->db->query("update ".DB_PREFIX."order set token = '".$this->db->escape($response_info['xToken'])."', ref_num = '".$this->db->escape($response_info['xRefNum'])."', payment_mode = '".($this->config->get('payment_cardknox_method') == 'capture'? '': 'auth')."' where order_id = ".(int)$this->session->data['order_id']);
				$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cardknox_order_status_id'), $message, true);
				// save card token
				if($this->request->post['cc_from'] == 'new' && $this->request->post['cc_save']) {
					$this->load->model('extension/credit_card/cardknox');
					$data = array(
							'token' => $response_info['xToken'],
							'exp' => $response_info['xExp'],
							'pan' => $response_info['xMaskedCardNumber'],
							'card_type' => $response_info['xCardType']
					);
					$this->model_extension_credit_card_cardknox->addCard($this->customer->getID(), $data);
				}
				// save token to session for split_order
				$this->session->data['card_token'] = $response_info['xToken'];
				
				$json['redirect'] = $this->url->link('checkout/success', '', true);
			} else {
				$json['error'] = $response_info['xError'];
			}
		} else {
			$json['error'] = 'Empty Gateway Response';

			$this->logger('Cardknox CURL ERROR: Empty Gateway Response');
		}
		curl_close($curl);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function error() {
		$json = array();
		$this->logger($this->request->post);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput('');
	}
        
	public function card() {
		$this->load->language('extension/payment/cardknox');
		$this->load->model('extension/credit_card/cardknox');
		
		if($this->request->post['cc_from'] == 'existing') {// old saved card
			$token = $this->model_extension_credit_card_cardknox->getToken($this->customer->getID(), $this->request->post['cc_id']);
		} elseif(isset($this->session->data['card_token'])) {// card not saved, but authorized
			$token = $this->session->data['card_token'];
		} else {// card only
			$token = $this->saveCard();
		}
		// now process the order
		if($token) {
			$message = 'Using saved card without authorizing.';
			// now save transaction info
			$this->db->query("update ".DB_PREFIX."order set token = '".$this->db->escape($token)."', payment_mode = 'card' where order_id = ".(int)$this->session->data['order_id']);
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cardknox_order_status_id'), $message, true);
			$json['redirect'] = $this->url->link('checkout/success', '', true);
		} else {
			$json['error'] = $this->language->get('error_save');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function saveCard() {
		$url = 'https://x1.cardknox.com/gateway';
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$data = array();

		$data['xKey'] = $this->config->get('payment_cardknox_transaction_key');
		$data['xVersion'] = '4.5.8';
		$data['xSoftwareName'] = 'OpenCart';
		$data['xSoftwareVersion'] = '1.0.2';

		$data['xName'] = html_entity_decode($this->request->post['cc_owner'], ENT_QUOTES, 'UTF-8');
		$data['xStreet'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		$data['xZip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
		$data['xIP'] = $this->request->server['REMOTE_ADDR'];
		$data['xCommand'] = 'cc:save';
		$data['xCardNum'] = $this->request->post['xCardNum'];
		$data['xExp'] = $this->request->post['cc_expire_date_month'] . substr($this->request->post['cc_expire_date_year'],2,2);

		
		$replacements = array('xKey' => "******", 'xCardNum' => "******", 'xCVV' => "******");	

		$this->logger(array_replace($data, $replacements));
		
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));

		$apiResponse = curl_exec($curl);

		$return = false;
		
		if (curl_error($curl)) {
			$this->logger('CARDKNOX CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl));
			
		} elseif ($apiResponse) {
			$response_info = array();
			parse_str($apiResponse, $response_info);
			$this->logger($response_info);
			if (( $response_info['xResult'] == 'A') ){
				// now save card info
				$return = $response_info['xToken'];
			}
		} else {
			$this->logger('Cardknox CURL ERROR: Empty Gateway Response');
		}
		curl_close($curl);
		return $return;
	}
	

	public function logger($message) {
		$log = new Log('cardknox.log');
		$log->write($message);
	}
}
?>