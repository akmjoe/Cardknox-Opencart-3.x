<?php

class ControllerExtensionCreditCardCardknox extends Controller {
    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('extension/credit_card/cardknox');

        $this->load->model('extension/credit_card/cardknox');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/credit_card/cardknox', '', true)
        );

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        } 

        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        } 

        $data['back'] = $this->url->link('account/account', '', true);

        $data['cards'] = array();

        foreach ($this->model_extension_credit_card_cardknox->getCards($this->customer->getId()) as $card) {
            $data['cards'][] = array(
                'text' => sprintf($this->language->get('text_card_info'), $card['card_type'], $card['pan'],substr($card['exp'],0,2),substr($card['exp'],2,2)),
                'delete' => $this->url->link('extension/credit_card/cardknox/forget', 'customer_card_id=' . $card['customer_card_id'], true)
            );
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        $this->response->setOutput($this->load->view('extension/credit_card/cardknox', $data));
    }

    public function forget() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('extension/credit_card/cardknox');

        $this->load->model('extension/credit_card/cardknox');


        $card_id = (int)$this->request->get['customer_card_id'];

        if ($this->model_extension_credit_card_cardknox->verifyCardCustomer($card_id, $this->customer->getId())) {
            $this->model_extension_credit_card_cardknox->deleteCard($card_id);
        }

        $this->response->redirect($this->url->link('extension/credit_card/cardknox', '', true));
    }
}