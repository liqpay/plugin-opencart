<?php
/**
 * Liqpay Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category        Liqpay
 * @package         Payment
 * @version         3.0
 * @author          Liqpay
 * @copyright       Copyright (c) 2014 Liqpay
 * @license         http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * EXTENSION INFORMATION
 *
 * OpenCart         1.5.6
 * LiqPay API       https://www.liqpay.com/ru/doc
 *
 */

/**
 * Payment method liqpay controller (catalog)
 *
 * @author      Liqpay <support@liqpay.com>
 */
class ControllerPaymentLiqpay extends Controller
{

    /**
     * Index action
     *
     * @return void
     */
    protected function index()
    {
        $this->load->model('checkout/order');

        $order_id = $this->session->data['order_id'];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $description = 'Order #'.$order_id;

        $order_id .= '#'.time();
        $result_url = $this->url->link('checkout/success', '', 'SSL');
        $server_url = $this->url->link('payment/liqpay/server', '', 'SSL');

        $private_key = $this->config->get('liqpay_private_key');
        $public_key = $this->config->get('liqpay_public_key');
        $type = 'buy';
        $currency = $order_info['currency_code'];
        if ($currency == 'RUR') { $currency = 'RUB'; }
        $amount = $this->currency->format(
            $order_info['total'],
            $order_info['currency_code'],
            $order_info['currency_value'],
            false
        );
        $version  = '3';
        $language = $this->language->get('code');
        $language = $language == 'ru' ? 'ru' : 'en';

        $data = base64_encode(
                    json_encode(
                            array('version'     => $version,
                                  'public_key'  => $public_key,
                                  'amount'      => $amount,
                                  'currency'    => $currency,
                                  'description' => $description,
                                  'order_id'    => $order_id,
                                  'type'        => $type,
                                  'language'    => $language)
                                )
                            );

        $signature = base64_encode(sha1($private_key.$data.$private_key, 1));

        $this->data['action'] = $this->config->get('liqpay_action');
        $this->data['public_key'] = $public_key;
        $this->data['amount'] = $amount;
        $this->data['currency'] = $currency;
        $this->data['description'] = $description;
        $this->data['order_id'] = $order_id;
        $this->data['result_url'] = $result_url;
        $this->data['server_url'] = $server_url;
        $this->data['type'] = $type;
        $this->data['signature'] = $signature;
        $this->data['language'] = $language;
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['url_confirm'] = $this->url->link('payment/liqpay/confirm');

        $this->template = $this->config->get('config_template').'/template/payment/liqpay.tpl';

        if (!file_exists(DIR_TEMPLATE.$this->template)) {
            $this->template = 'default/template/payment/liqpay.tpl';
        }

        $this->render();
    }


    /**
     * Confirm action
     *
     * @return void
     */
    public function confirm()
    {
        $this->load->model('checkout/order'); echo $this->session->data['order_id'];
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'), 'unpaid');
    }


    /**
     * Check and return posts data
     *
     * @return array
     */
    private function getPosts()
    {
        $success =
            isset($_POST['data']) &&
            isset($_POST['signature']);

        if ($success) {
            return array(
                $_POST['signature'],
                $_POST['data']
            );
        }
        return array();
    }


    /**
     * get real order ID
     *
     * @return string
     */
    public function getRealOrderID($order_id)
    {
        $real_order_id = explode('#', $order_id);
        return $real_order_id[0];
    }


    /**
     * Server action
     *
     * @return void
     */
    public function server()
    {
        if (!$posts = $this->getPosts()) { die(); }

        list($data, $signature) = $posts;
        $parsed_data = json_decode(base64_decode($data));

        $received_public_key = $parsed_data['public_key'];
        $order_id            = $parsed_data['order_id'];
        $status              = $parsed_data['status'];
        $sender_phone        = $parsed_data['sender_phone'];
        $amount              = $parsed_data['amount'];
        $currency            = $parsed_data['currency'];
        $transaction_id      = $parsed_data['transaction_id'];

        $real_order_id = $this->getRealOrderID($order_id);

        if ($real_order_id <= 0) { die(); }

        $this->load->model('checkout/order');
        if (!$this->model_checkout_order->getOrder($real_order_id)) { die(); }

        $private_key = $this->config->get('liqpay_private_key');
        $public_key  = $this->config->get('liqpay_public_key');

        $generated_signature = base64_encode(sha1($private_key.$data.$private_key, 1));

        if ($signature != $generated_signature || $public_key != $received_public_key) { die(); }

        if ($status == 'success') {
            $this->model_checkout_order->update($real_order_id, $this->config->get('liqpay_order_status_id'),'paid');
        }
    }
}
