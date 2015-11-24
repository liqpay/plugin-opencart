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
 * Payment method liqpay controller (admin)
 *
 * @author      Liqpay <support@liqpay.com>
 */
class ControllerPaymentLiqPay extends Controller
{
	private $error = array();


    /**
     * Index action
     *
     * @return void
     */
 	public function index()
	{
		$this->language->load('payment/liqpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('liqpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_buy'] = $this->language->get('text_buy');
		$this->data['text_donate'] = $this->language->get('text_donate');

		$this->data['entry_public_key'] = $this->language->get('entry_public_key');
		$this->data['entry_private_key'] = $this->language->get('entry_private_key');
		$this->data['entry_action'] = $this->language->get('entry_action');
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_pay_way'] = $this->language->get('entry_pay_way');
		$this->data['entry_language'] = $this->language->get('entry_language');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['public_key'])) {
			$this->data['error_public_key'] = $this->error['public_key'];
		} else {
			$this->data['error_public_key'] = '';
		}

		if (isset($this->error['private_key'])) {
			$this->data['error_private_key'] = $this->error['private_key'];
		} else {
			$this->data['error_private_key'] = '';
		}

		if (isset($this->error['action'])) {
			$this->data['error_action'] = $this->error['action'];
		} else {
			$this->data['error_action'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/liqpay', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/liqpay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['liqpay_public_key'])) {
			$this->data['liqpay_public_key'] = $this->request->post['liqpay_public_key'];
		} else {
			$this->data['liqpay_public_key'] = $this->config->get('liqpay_public_key');
		}

		if (isset($this->request->post['liqpay_private_key'])) {
			$this->data['liqpay_private_key'] = $this->request->post['liqpay_private_key'];
		} else {
			$this->data['liqpay_private_key'] = $this->config->get('liqpay_private_key');
		}

		if (isset($this->request->post['liqpay_action'])) {
			$this->data['liqpay_action'] = $this->request->post['liqpay_action'];
		} else {
			$this->data['liqpay_action'] = $this->config->get('liqpay_action');
			if (empty($this->data['liqpay_action'])) {
				$this->data['liqpay_action'] = 'https://www.liqpay.com/api/3/checkout';
			}
		}

		if (isset($this->request->post['liqpay_total'])) {
			$this->data['liqpay_total'] = $this->request->post['liqpay_total'];
		} else {
			$this->data['liqpay_total'] = $this->config->get('liqpay_total');
		}

		if (isset($this->request->post['liqpay_order_status_id'])) {
			$this->data['liqpay_order_status_id'] = $this->request->post['liqpay_order_status_id'];
		} else {
			$this->data['liqpay_order_status_id'] = $this->config->get('liqpay_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['liqpay_geo_zone_id'])) {
			$this->data['liqpay_geo_zone_id'] = $this->request->post['liqpay_geo_zone_id'];
		} else {
			$this->data['liqpay_geo_zone_id'] = $this->config->get('liqpay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['liqpay_status'])) {
			$this->data['liqpay_status'] = $this->request->post['liqpay_status'];
		} else {
			$this->data['liqpay_status'] = $this->config->get('liqpay_status');
		}

		if (isset($this->request->post['liqpay_sort_order'])) {
			$this->data['liqpay_sort_order'] = $this->request->post['liqpay_sort_order'];
		} else {
			$this->data['liqpay_sort_order'] = $this->config->get('liqpay_sort_order');
		}

		if (isset($this->request->post['liqpay_pay_way'])) {
			$this->data['liqpay_pay_way'] = $this->request->post['liqpay_pay_way'];
		} else {
			$this->data['liqpay_pay_way'] = $this->config->get('liqpay_pay_way');
		}

		if (isset($this->request->post['liqpay_language'])) {
			$this->data['liqpay_language'] = $this->request->post['liqpay_language'];
		} else {
			$this->data['liqpay_language'] = $this->config->get('liqpay_language');
		}

		$this->template = 'payment/liqpay.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


    /**
     * Validate input data
     *
     * @return boolean
     */
	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/liqpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['liqpay_public_key']) {
			$this->error['public_key'] = $this->language->get('error_public_key');
		}

		if (!$this->request->post['liqpay_private_key']) {
			$this->error['private_key'] = $this->language->get('error_private_key');
		}

		if (!$this->request->post['liqpay_action']) {
			$this->error['action'] = $this->language->get('error_action');
		}

		return !$this->error;
	}
}
