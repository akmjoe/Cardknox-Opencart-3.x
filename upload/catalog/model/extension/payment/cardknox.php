<?php
class ModelExtensionPaymentCardknox extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/cardknox');

		$status = true;

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_cardknox_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_cardknox_total') > 0 && $this->config->get('payment_cardknox_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_cardknox_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'cardknox',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_cardknox_sort_order')
			);
		}

		return $method_data;
	}
	
	public function saveTransaction($order_id, $data, $card_id = 0) {
		if(count($data)) {
			$this->db->query("INSERT INTO ".DB_PREFIX."cardknox_transaction set card_id = '".(int)$card_id."', refnum = '".$this->db->escape($data['xRefNum'])."', authcode = '".$this->db->escape($data['xAuthCode'])."', avs = '".$this->db->escape($data['xAvsResultCode'])."', cvv = '".$this->db->escape($data['xCvvResultCode'])."', amount = '".$this->db->escape($data['xAuthAmount'])."', token = '".$this->db->escape($data['xToken'])."', pan = '".$this->db->escape($data['xMaskedCardNumber'])."', card_type = '".$this->db->escape($data['xCardType'])."', order_id = '".(int)$order_id."'");
		} else {
			$this->db->query("INSERT INTO ".DB_PREFIX."cardknox_transaction set card_id = '".(int)$card_id."', order_id = '".(int)$order_id."'");
		}
        return $this->db->getLastId();
	}
}