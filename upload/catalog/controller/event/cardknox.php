<?php

class ControllerEventCardknox extends Controller {
	public function clean(&$route, &$data, &$output = null) {
		if (!empty($this->request->get['token']) || $route != 'account/login') {
			unset($this->session->data['card']);
			unset($this->session->data['card_token']);
		}
	}
}