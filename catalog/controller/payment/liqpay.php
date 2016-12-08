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
        /*set order_id to global variable*/
        $this->data['order_id'] = $this->session->data['order_id'];

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
        //$language = $this->language->get('code');

        //$language = $language == 'ru' ? 'ru' : 'en';
        $pay_way  = $this->config->get('liqpay_pay_way');
        $language = $this->config->get('liqpay_language');

        $send_data = array('version'    => $version,
                          'public_key'  => $public_key,
                          'amount'      => $amount,
                          'currency'    => $currency,
                          'description' => $description,
                          'order_id'    => $order_id,
                          'type'        => $type,
                          'language'    => $language,
                          'server_url'  => $server_url,
                          'result_url'  => $result_url);
        if(isset($pay_way)){
          $send_data['pay_way'] = $pay_way;
        }

        $data = base64_encode(json_encode($send_data));

        $signature = base64_encode(sha1($private_key.$data.$private_key, 1));

        $this->data['action']         = $this->config->get('liqpay_action');
        $this->data['signature']      = $signature;
        $this->data['data']           = $data;
        $this->data['button_confirm'] = 'Оплатить';
        $this->data['url_confirm']    = $this->url->link('payment/liqpay/confirm', '', 'SSL');
        
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
        $this->load->model('checkout/order'); 
        $order = $this->session->data['order_id'];
        //$this->model_checkout_order->confirm($order, $this->config->get('config_order_status_id'), 'unpaid');
        $this->model_checkout_order->confirm($this->session->data['order_id'], 2);
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
                $_POST['data'],
                $_POST['signature'],
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
        if (!$posts = $this->getPosts()) { die('Posts error'); }

        list(
            $data,
            $signature
        ) = $posts;
        
        if(!$data || !$signature) {die("No data or signature");}

        $parsed_data = json_decode(base64_decode($data), true);

        $received_public_key = $parsed_data['public_key'];
        $order_id            = $parsed_data['order_id'];
        $status              = $parsed_data['status'];
        $sender_phone        = $parsed_data['sender_phone'];
        $amount              = $parsed_data['amount'];
        $currency            = $parsed_data['currency'];
        $transaction_id      = $parsed_data['transaction_id'];

        $real_order_id = $this->getRealOrderID($order_id);

        if ($real_order_id <= 0) { die("Order_id real_order_id < 0"); }

        $this->load->model('checkout/order');
        if (!$this->model_checkout_order->getOrder($real_order_id)) { die("Order_id fail");}

        $private_key = $this->config->get('liqpay_private_key');
        $public_key  = $this->config->get('liqpay_public_key');

        $generated_signature = base64_encode(sha1($private_key.$data.$private_key, 1));

        if ($signature  != $generated_signature) { die("Signature secure fail"); }
        if ($public_key != $received_public_key) { die("public_key secure fail"); }

        if ($status == 'success') {
            // there you can update your order
        }

    }
}
