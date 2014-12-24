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
 * Payment method liqpay model (catalog)
 *
 * @author      Liqpay <support@liqpay.com>
 */
class ModelPaymentLiqPay extends Model
{

    /**
     * Index action
     *
     * @return void
     */
 	public function getMethod($address, $total)
	{
		$this->language->load('payment/liqpay');

		$tbl_zone_to_geo_zone = DB_PREFIX.'zone_to_geo_zone';
		$liqpay_geo_zone_id = (int)$this->config->get('liqpay_geo_zone_id');
		$country_id = (int)$address['country_id'];
		$zone_id = (int)$address['zone_id'];

		$sql = "
				SELECT *
				FROM {$tbl_zone_to_geo_zone}
				WHERE geo_zone_id = '{$liqpay_geo_zone_id}'
				AND country_id = '{$country_id}'
				AND (zone_id = '{$zone_id}' OR zone_id = '0')
		";

		$query = $this->db->query($sql);

		if ($this->config->get('liqpay_total') > 0 && $this->config->get('liqpay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('liqpay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'liqpay',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('liqpay_sort_order')
			);
		}

		return $method_data;
	}
}
