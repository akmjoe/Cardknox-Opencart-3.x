<?php

class ModelExtensionCreditCardCardknox extends Model {

    public function addCard($customer_id, $data) {
		$this->db->query("INSERT INTO ".DB_PREFIX."cardknox_card set token = '".$this->db->escape($data['token'])."', exp = '".$this->db->escape($data['exp'])."', pan = '".$this->db->escape($data['pan'])."', card_type = '".$this->db->escape($data['card_type'])."', customer_id = '".(int)$customer_id."', status = '".(int)$data['status']."'");
        return $this->db->getLastId();
    }

    public function getCard($id) {
        return $this->db->query("SELECT * FROM ".DB_PREFIX."cardknox_card where customer_card_id = '".(int)$id."' and status = 1")->row;
    }
    
    public function getCards($customer_id) {
		return $this->db->query("SELECT * FROM ".DB_PREFIX."cardknox_card where customer_id = '".(int)$customer_id."' and status = 1")->rows;
    }

    public function getToken($customer_id, $id) {
        $result = $this->db->query("select * from ".DB_PREFIX."cardknox_card where customer_card_id = '".(int)$id."' and customer_id = '".(int)$customer_id."' and status = 1");
		return $result->row['token'];
    }

	public function verifyCardCustomer($id, $customer_id) {
		$result = $this->db->query("select * from ".DB_PREFIX."cardknox_card where customer_card_id = '".(int)$id."' and customer_id = '".(int)$customer_id."' and status = 1");
		return $result->num_rows;
	}

    public function deleteCard($id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "cardknox_card` WHERE customer_card_id='" . (int)$id . "'");
    }
	
}