<?php

class controllerExtensionEventCardknox extends Controller {
	public function clean(&$route, &$data, &$output = null) {
		if (!empty($this->request->get['token']) || $route != 'account/login') {
			unset($this->session->data['card']);
			unset($this->session->data['card_token']);
		}
	}
	
	public function secure(&$route, &$data = array(), &$output = null) {
		// secure sensitive data
		$result = $this->db->query("select customer_id from ".DB_PREFIX."customer where email = '".$this->db->escape($data[0])."'");
		$this->db->query("update ".DB_PREFIX."cardknox_card set status = 0 where customer_id = '".(int)$result->row['customer_id']."'");
	}
}