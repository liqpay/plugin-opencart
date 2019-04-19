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
 * LiqPay API       https://www.liqpay.ua/documentation/ru
 *
 */
?>

<form method="POST" action="<?=$action?>" id="liqpay_checkout" accept-charset="utf-8">
    <input type="hidden" name="data"  value="<?=$data?>" />
    <input type="hidden" name="signature" value="<?=$signature?>" />
    <div class="buttons">
        <div class="right">
            <input type="submit" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
        </div>
    </div>
</form>