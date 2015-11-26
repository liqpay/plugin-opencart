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
 * OpenCart         2.x
 * LiqPay API       https://www.liqpay.com/ru/doc
 *
 */

/**
 * Payment method liqpay controller (catalog)
 *
 * @author      Liqpay <support@liqpay.com>
 */

class ControllerPaymentLiqPayCheckout extends Controller {
  public function index() {
    $data['button_confirm'] = $this->language->get('button_confirm');
    $order_id = $this->session->data['order_id'];

    $this->load->model('checkout/order');

    $order_info = $this->model_checkout_order->getOrder($order_id);

      $version     = '3';
      $description = 'Order #'.$order_id;
      $result_url  = $this->url->link('checkout/success', '', 'SSL');
      $server_url  = $this->url->link('payment/liqpay_checkout/callback', '', 'SSL');
      $private_key = $this->config->get('liqpay_checkout_signature');
      $public_key  = $this->config->get('liqpay_checkout_merchant');
      $action      = 'pay';

      $currency = $order_info['currency_code'];
      if ($currency == 'RUR') { $currency = 'RUB'; }
      $amount = $this->currency->format(
          $order_info['total'],
          $order_info['currency_code'],
          $order_info['currency_value'],
          false
      );

      $pay_way  = $this->config->get('liqpay_checkout_pay_way');
      $language = $this->config->get('liqpay_checkout_language');

      $send_data = array('version'    => $version,
                        'public_key'  => $public_key,
                        'amount'      => $amount,
                        'currency'    => $currency,
                        'description' => $description,
                        'order_id'    => $order_id,
                        'action'      => $action,
                        'language'    => $language,
                        'server_url'  => $server_url,
                        'result_url'  => $result_url);

      $pay_way  = $this->config->get('liqpay_checkout_pay_way');
      if(isset($pay_way)){
        $send_data['pay_way'] = $pay_way;
      }

      $liqpay_data       = base64_encode(json_encode($send_data));
      $liqpay_signature  = base64_encode(sha1($private_key.$liqpay_data.$private_key, 1));

      $data['data']      = $liqpay_data;
      $data['signature'] = $liqpay_signature;
      $data['action']    = $this->config->get('liqpay_checkout_api');

    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/liqpay_checkout.tpl')) {
      return $this->load->view($this->config->get('config_template') . '/template/payment/liqpay_checkout.tpl', $data);
    } else {
      return $this->load->view('default/template/payment/liqpay_checkout.tpl', $data);
    }
  }




  public function callback() {
    $data = $this->request->post['data'];
    $signature = base64_encode(sha1($this->config->get('liqpay_checkout_signature') . $data . $this->config->get('liqpay_checkout_signature'), true));
        
    $parsed_data = json_decode(base64_decode($data), true);
    $order_id    = $parsed_data['order_id'];
    
    if ($signature == $this->request->post['signature']) {
      $this->load->model('checkout/order');
      $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
      //here you can update your order status
    }
  }
}
