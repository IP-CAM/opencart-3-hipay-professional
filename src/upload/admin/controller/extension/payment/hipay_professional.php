<?php

class ControllerExtensionPaymentHipayProfessional extends Controller {

    private $error = array();
    private $extension_version = "1.0.0.1";

    public function index() {
        $this->load->language('extension/payment/hipay_professional');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_hipay_professional', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['bank'])) {
            $data['error_bank'] = $this->error['bank'];
        } else {
            $data['error_bank'] = array();
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
            'href' => $this->url->link('extension/payment/hipay_professional', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/hipay_professional', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $this->load->model('localisation/language');

        $data['payment_hipay_professional_extension_version'] = $this->extension_version;
        $data['payment_hipay_professional_currencies'] = ["EUR", "GBP", "USD", "SEK", "CAD", "CHF", "AUD"];
        $data['payment_hipay_professional_ratings'] = ["ALL", "+18", "+16", "+12"];

        $data['payment_hipay_professional_soap'] = 0;
        if (extension_loaded('soap')) {
            $data['payment_hipay_professional_soap'] = 1;
        }

        $data['payment_hipay_professional_simplexml'] = 0;
        if (extension_loaded('soap')) {
            $data['payment_hipay_professional_simplexml'] = 1;
        }

        $data['payment_hipay_professional_curl'] = 0;
        if (function_exists('curl_version')) {
            $data['payment_hipay_professional_curl'] = 1;
        }

        if (isset($this->request->post['payment_hipay_professional_sandbox'])) {
            $data['payment_hipay_professional_sandbox'] = $this->request->post['payment_hipay_professional_sandbox'];
        } else {
            $data['payment_hipay_professional_sandbox'] = $this->config->get('payment_hipay_professional_sandbox');
        }

        if (isset($this->request->post['payment_hipay_professional_api_user'])) {
            $data['payment_hipay_professional_api_user'] = $this->request->post['payment_hipay_professional_api_user'];
        } else {
            $data['payment_hipay_professional_api_user'] = $this->config->get('payment_hipay_professional_api_user');
        }

        if (isset($this->request->post['payment_hipay_professional_api_password'])) {
            $data['payment_hipay_professional_api_password'] = $this->request->post['payment_hipay_professional_api_password'];
        } else {
            $data['payment_hipay_professional_api_password'] = $this->config->get('payment_hipay_professional_api_password');
        }

        if (isset($this->request->post['payment_hipay_professional_website'])) {
            $data['payment_hipay_professional_website'] = $this->request->post['payment_hipay_professional_website'];
        } else {
            $data['payment_hipay_professional_website'] = $this->config->get('payment_hipay_professional_website');
        }

        if (isset($this->request->post['payment_hipay_professional_website_category'])) {
            $data['payment_hipay_professional_website_category'] = $this->request->post['payment_hipay_professional_website_category'];
        } else {
            $data['payment_hipay_professional_website_category'] = $this->config->get('payment_hipay_professional_website_category');
        }

        if (isset($this->request->post['payment_hipay_professional_shopid'])) {
            $data['payment_hipay_professional_shopid'] = $this->request->post['payment_hipay_professional_shopid'];
        } else {
            $data['payment_hipay_professional_shopid'] = $this->config->get('payment_hipay_professional_shopid');
        }

        if (isset($this->request->post['payment_hipay_professional_rating'])) {
            $data['payment_hipay_professional_rating'] = $this->request->post['payment_hipay_professional_rating'];
        } else {
            $data['payment_hipay_professional_rating'] = $this->config->get('payment_hipay_professional_rating');
        }

        if (isset($this->request->post['payment_hipay_professional_currency'])) {
            $data['payment_hipay_professional_currency'] = $this->request->post['payment_hipay_professional_currency'];
        } else {
            $data['payment_hipay_professional_currency'] = $this->config->get('payment_hipay_professional_currency');
        }

        if (isset($this->request->post['payment_hipay_professional_order_logo'])) {
            $data['payment_hipay_professional_order_logo'] = $this->request->post['payment_hipay_professional_order_logo'];
        } else {
            $data['payment_hipay_professional_order_logo'] = $this->config->get('payment_hipay_professional_order_logo');
        }

        if (isset($this->request->post['payment_hipay_professional_order_title'])) {
            $data['payment_hipay_professional_order_title'] = $this->request->post['payment_hipay_professional_order_title'];
        } else {
            $data['payment_hipay_professional_order_title'] = $this->config->get('payment_hipay_professional_order_title');
        }

        if (isset($this->request->post['payment_hipay_professional_order_info'])) {
            $data['payment_hipay_professional_order_info'] = $this->request->post['payment_hipay_professional_order_info'];
        } else {
            $data['payment_hipay_professional_order_info'] = $this->config->get('payment_hipay_professional_order_info');
        }

        if (isset($this->request->post['payment_hipay_professional_email'])) {
            $data['payment_hipay_professional_email'] = $this->request->post['payment_hipay_professional_email'];
        } else {
            $data['payment_hipay_professional_email'] = $this->config->get('payment_hipay_professional_email');
        }

        if (isset($this->request->post['payment_hipay_professional_total_min'])) {
            $data['payment_hipay_professional_total_min'] = $this->request->post['payment_hipay_professional_total_min'];
        } else {
            $data['payment_hipay_professional_total_min'] = $this->config->get('payment_hipay_professional_total_min');
        }

        if (isset($this->request->post['payment_hipay_professional_total_max'])) {
            $data['payment_hipay_professional_total_max'] = $this->request->post['payment_hipay_professional_total_max'];
        } else {
            $data['payment_hipay_professional_total_max'] = $this->config->get('payment_hipay_professional_total_max');
        }

        if (isset($this->request->post['payment_hipay_professional_debug'])) {
            $data['payment_hipay_professional_debug'] = $this->request->post['payment_hipay_professional_debug'];
        } else {
            $data['payment_hipay_professional_debug'] = $this->config->get('payment_hipay_professional_debug');
        }

        if (isset($this->request->post['payment_hipay_professional_order_status_id_paid'])) {
            $data['payment_hipay_professional_order_status_id_paid'] = $this->request->post['payment_hipay_professional_order_status_id_paid'];
        } else {
            $data['payment_hipay_professional_order_status_id_paid'] = $this->config->get('payment_hipay_professional_order_status_id_paid');
        }

        if (isset($this->request->post['payment_hipay_professional_order_status_id_pending'])) {
            $data['payment_hipay_professional_order_status_id_pending'] = $this->request->post['payment_hipay_professional_order_status_id_pending'];
        } else {
            $data['payment_hipay_professional_order_status_id_pending'] = $this->config->get('payment_hipay_professional_order_status_id_pending');
        }

        if (isset($this->request->post['payment_hipay_professional_order_status_id_failed'])) {
            $data['payment_hipay_professional_order_status_id_failed'] = $this->request->post['payment_hipay_professional_order_status_id_failed'];
        } else {
            $data['payment_hipay_professional_order_status_id_failed'] = $this->config->get('payment_hipay_professional_order_status_id_failed');
        }

        if (isset($this->request->post['payment_hipay_professional_order_status_id_cancel'])) {
            $data['payment_hipay_professional_order_status_id_cancel'] = $this->request->post['payment_hipay_professional_order_status_id_cancel'];
        } else {
            $data['payment_hipay_professional_order_status_id_cancel'] = $this->config->get('payment_hipay_professional_order_status_id_cancel');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_hipay_professional_geo_zone_id'])) {
            $data['payment_hipay_professional_geo_zone_id'] = $this->request->post['payment_hipay_professional_geo_zone_id'];
        } else {
            $data['payment_hipay_professional_geo_zone_id'] = $this->config->get('payment_hipay_professional_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_hipay_professional_status'])) {
            $data['payment_hipay_professional_status'] = $this->request->post['payment_hipay_professional_status'];
        } else {
            $data['payment_hipay_professional_status'] = $this->config->get('payment_hipay_professional_status');
        }

        if (isset($this->request->post['payment_hipay_professional_sort_order'])) {
            $data['payment_hipay_professional_sort_order'] = $this->request->post['payment_hipay_professional_sort_order'];
        } else {
            $data['payment_hipay_professional_sort_order'] = $this->config->get('payment_hipay_professional_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/hipay_professional', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/hipay_professional')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

}
