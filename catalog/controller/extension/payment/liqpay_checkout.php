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
 * OpenCart         2.3.X
 * LiqPay API       https://www.liqpay.com/ru/doc
 *
 */

/**
 * Payment method liqpay controller (catalog)
 *
 * @author      Liqpay <support@liqpay.com>
 */
class ControllerExtensionPaymentLiqPayCheckout extends Controller
{
    private $version = 3;
    private $action = 'pay';

    public function index()
    {
        $this->load->language('extension/payment/liqpay_checkout');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        // Collect info about the order to be sent to the API

        $description = $this->language->get('text_order_number') . $order_id;
        $result_url = $this->url->link('checkout/success', '', 'SSL');
        $server_url = $this->url->link('extension/payment/liqpay_checkout/callback', '', 'SSL');
        $private_key = $this->config->get('liqpay_checkout_private_key');
        $public_key = $this->config->get('liqpay_checkout_public_key');
        $currency = $order_info['currency_code'];
        if ($currency == 'RUR') {
            $currency = 'RUB';
        }
        $amount = $this->currency->format(
            $order_info['total'],
            $order_info['currency_code'],
            $order_info['currency_value'],
            false
        );

        $pay_way = $this->config->get('liqpay_checkout_pay_way');
        $language = $this->config->get('liqpay_checkout_language');

        $send_data = array('version' => $this->version,
            'public_key' => $public_key,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'order_id' => $order_id,
            'action' => $this->action,
            'language' => $language,
            'server_url' => $server_url,
            'result_url' => $result_url);


        $liqpay_data = base64_encode(json_encode($send_data));
        $liqpay_signature = $this->calculateSignature($liqpay_data, $private_key);

        $data['data'] = $liqpay_data;
        $data['signature'] = $liqpay_signature;
        $data['action'] = $this->config->get('liqpay_checkout_api');
        $view_path = 'extension/payment/liqpay_checkout';

        return $this->load->view($view_path, $data);
    }

    private function calculateSignature($data, $private_key)
    {
        return base64_encode(sha1($private_key . $data . $private_key, true));
    }

    public function callback()
    {
        $data = $this->request->post['data'];
        $private_key = $this->config->get('liqpay_checkout_private_key');
        $signature = $this->calculateSignature($data, $private_key);
        $parsed_data = json_decode(base64_decode($data), true);
        $order_id = $parsed_data['order_id'];

        if ($signature == $this->request->post['signature']) {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('liqpay_checkout_order_status_id'));
            //here you can update your order status
        }
    }
}
