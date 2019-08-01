<?php

class ControllerExtensionPaymentHipayProfessional extends Controller {

    const HIPAY_GENERATE_PRODUCTION = "https://ws.hipay.com/soap/payment-v2/generate?wsdl";
    const HIPAY_GENERATE_SANDBOX = "https://test-ws.hipay.com/soap/payment-v2/generate?wsdl";
    const HIPAY_TRANSACTION_PRODUCTION = "https://ws.hipay.com/soap/transaction-v2?wsdl";
    const HIPAY_TRANSACTION_SANDBOX = "https://test-ws.hipay.com/soap/transaction-v2?wsdl";

    private $endpoint;

    public function index() {
        $this->load->language('extension/payment/hipay_professional');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_title'] = $this->language->get('text_title');

        return $this->load->view('extension/payment/hipay_professional', $data);
    }

    public function payment() {

        $json = array();

        if ($this->session->data['payment_method']['code'] == 'hipay_professional') {
            $this->load->language('extension/payment/hipay_professional');
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/hipay_professional');

            $data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if (!$this->config->get('payment_hipay_professional_sandbox')) {
                $this->endpoint = self::HIPAY_GENERATE_PRODUCTION;
            } else {
                $this->endpoint = self::HIPAY_GENERATE_SANDBOX;
            }

            $json['redirect'] = $this->url->link('checkout/failure');

            try {
                $client = new SoapClient($this->endpoint);
                $language = $this->get_current_local_for_hipay();
                $ip = $_SERVER['REMOTE_ADDR'];
                $currentDate = date('Y-m-dTH:i:s');

                if (isset($this->session->data['guest'])) {
                    $customer_email = $this->session->data['guest']['email'];
                } else {
                    $customer_email = $this->customer->getEmail();
                }

                $parameters = new stdClass();
                $parameters->parameters = array(
                    'wsLogin' => $this->config->get('payment_hipay_professional_api_user'),
                    'wsPassword' => $this->config->get('payment_hipay_professional_api_password'),
                    'websiteId' => $this->config->get('payment_hipay_professional_website'),
                    'categoryId' => $this->config->get('payment_hipay_professional_website_category'),
                    'currency' => $this->config->get('payment_hipay_professional_currency'),
                    'amount' => number_format($data['total'], 2, ".", ""),
                    'rating' => $this->config->get('payment_hipay_professional_rating'),
                    'locale' => $language,
                    'customerIpAddress' => $ip,
                    'merchantReference' => $data['order_id'],
                    'description' => $this->config->get('payment_hipay_professional_order_title'),
                    'executionDate' => $currentDate,
                    'manualCapture' => 0,
                    'customerEmail' => $customer_email,
                    'merchantComment' => $this->config->get('payment_hipay_professional_order_info'),
                    'emailCallback' => $this->config->get('payment_hipay_professional_email'),
                    'urlCallback' => $this->url->link('extension/payment/hipay_professional/notification'),
                    'urlAccept' => $this->url->link('extension/payment/hipay_professional/success'),
                    'urlDecline' => $this->url->link('extension/payment/hipay_professional/decline'),
                    'urlCancel' => $this->url->link('extension/payment/hipay_professional/cancel')
                );

                if ($this->config->get('payment_hipay_professional_order_logo') != "") {
                    $parameters->parameters["urlLogo"] = $this->config->get('payment_hipay_professional_order_logo');
                } else {
                    $parameters->parameters["urlLogo"] = $data["store_url"] . "image" . DIRECTORY_SEPARATOR . $this->config->get('config_logo');
                }

                if (!filter_var($parameters->parameters["urlLogo"], FILTER_VALIDATE_URL)) {
                    $parameters->parameters["urlLogo"] = "";
                }

                if ($this->config->get('payment_hipay_professional_shopid') != "") {
                    $parameters->parameters["shopId"] = $this->config->get('payment_hipay_professional_shopid');
                }

                $this->model_extension_payment_hipay_professional->logger(json_encode($parameters));

                $result = $client->generate($parameters);
                $this->model_extension_payment_hipay_professional->logger(json_encode($result));

                if ($result->generateResult->code == 0) {
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_hipay_professional_order_status_id_pending'), $this->language->get('hipay_pending'), true);
                    $this->cart->clear();
                    $json['redirect'] = $result->generateResult->redirectUrl;
                } else {
                    $json['error'] = $result->generateResult->description;
                }
            } catch (Exception $e) {
                $this->model_extension_payment_hipay_professional->logger(json_encode($e));
                $json['redirect'] = $this->url->link('checkout/decline');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function get_current_local_for_hipay() {

        switch ($this->language->get('code')) {
            case 'pt':
                return "pt_PT";
                break;
            case 'fr':
                return "fr_FR";
                break;
            case 'es':
                return "es_ES";
                break;
            case 'de':
                return "de_DE";
                break;
            case 'it':
                return "it_IT";
                break;
            case 'nl':
                return "nl_BE";
                break;
            default:
                return "en_GB";
                break;
        }
    }

    public function notification() {

        if (!isset($_POST["xml"])) {
            exit;
        }
        $xml = $_POST['xml'];

        $this->load->model('extension/payment/hipay_professional');
        $this->model_extension_payment_hipay_professional->logger("[NOTIFICATION] " . $xml);

        $operation = '';
        $status = '';
        $date = '';
        $time = '';
        $transid = '';
        $origAmount = '';
        $origCurrency = '';
        $idformerchant = '';
        $merchantdatas = array();
        $ispayment = true;

        try {
            $obj = new SimpleXMLElement(trim($xml));
        } catch (Exception $e) {
            $ispayment = false;
        }
        if (isset($obj->result[0]->operation))
            $operation = $obj->result[0]->operation;
        else
            $ispayment = false;

        if (isset($obj->result[0]->status))
            $status = $obj->result[0]->status;
        else
            $ispayment = false;

        if (isset($obj->result[0]->date))
            $date = $obj->result[0]->date;
        else
            $ispayment = false;

        if (isset($obj->result[0]->time))
            $time = $obj->result[0]->time;
        else
            $ispayment = false;

        if (isset($obj->result[0]->transid))
            $transid = $obj->result[0]->transid;
        else
            $ispayment = false;

        if (isset($obj->result[0]->origAmount))
            $origAmount = $obj->result[0]->origAmount;
        else
            $ispayment = false;

        if (isset($obj->result[0]->origCurrency))
            $origCurrency = $obj->result[0]->origCurrency;
        else
            $ispayment = false;

        if (isset($obj->result[0]->idForMerchant))
            $idformerchant = $obj->result[0]->idForMerchant;
        else
            $ispayment = false;

        if (isset($obj->result[0]->merchantDatas)) {
            $d = $obj->result[0]->merchantDatas->children();
            foreach ($d as $xml2) {
                if (preg_match('#^_aKey_#i', $xml2->getName())) {
                    $indice = substr($xml2->getName(), 6);
                    $valeur = (string) $xml2[0];
                    $merchantdatas[$indice] = $valeur;
                }
            }
        }


        if ($ispayment === true) {

            $this->load->language('extension/payment/hipay_professional');
            $this->load->model('checkout/order');

            if ($status == "ok" && $operation == "capture") {


                if (!$this->config->get('payment_hipay_professional_sandbox')) {
                    $this->endpoint = self::HIPAY_TRANSACTION_PRODUCTION;
                } else {
                    $this->endpoint = self::HIPAY_TRANSACTION_SANDBOX;
                }

                $client = new SoapClient($this->endpoint);
                $parameters = new stdClass();
                $parameters->parameters = array(
                    'wsLogin' => $this->config->get('payment_hipay_professional_api_user'),
                    'wsPassword' => $this->config->get('payment_hipay_professional_api_password'),
                    'transactionPublicId' => $transid
                );

                $result = $client->getDetails($parameters);

                if ($result->getDetailsResult->code == "0" && $result->getDetailsResult->amount == $origAmount && $result->getDetailsResult->currency == $origCurrency && strtolower($result->getDetailsResult->transactionStatus) == "captured") {
                    $this->model_checkout_order->addOrderHistory($idformerchant, $this->config->get('payment_hipay_professional_order_status_id_paid'), $this->language->get('hipay_success'));
                } else {
                    $this->model_extension_payment_hipay_professional->logger("[NOTIFICATION] Checking transaction on Hipay returned code: " . $result->getDetailsResult->code . " Status: " . $result->getDetailsResult->transactionStatus . " Description: " . $result->getDetailsResult->description);
                }
            } elseif ($status == "waiting") {

                $this->model_checkout_order->addOrderHistory($idformerchant, $this->config->get('payment_hipay_professional_order_status_id_pending'), $this->language->get('hipay_waiting'));
            } elseif ($operation != "authorization") {

                $this->model_checkout_order->addOrderHistory($idformerchant, $this->config->get('payment_hipay_professional_order_status_id_failed'), $operation . $this->language->get('hipay_error_ack'));
            }
        }

        return true;
    }

    public function success() {
        if ($this->session->data['payment_method']['code'] == 'hipay_professional') {
            $this->load->language('extension/payment/hipay_professional'); // 
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_hipay_professional_order_status_id_pending'), $this->language->get('hipay_waiting'));
        }
        $this->response->redirect($this->url->link('checkout/success'));
    }

    public function decline() {
        if ($this->session->data['payment_method']['code'] == 'hipay_professional') {
            $this->load->language('extension/payment/hipay_professional'); // 
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_hipay_professional_order_status_id_failed'), $this->language->get('hipay_error'));
        }
        $this->response->redirect($this->url->link('checkout/failure'));
    }

    public function cancel() {
        if ($this->session->data['payment_method']['code'] == 'hipay_professional') {
            $this->load->language('extension/payment/hipay_professional'); // 
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_hipay_professional_order_status_id_cancel'), $this->language->get('hipay_cancel'));
        }
        $this->response->redirect($this->url->link('checkout/checkout'));
    }

}
