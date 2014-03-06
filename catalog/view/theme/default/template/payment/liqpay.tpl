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
 * @version         0.0.1
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
?>

<form method="POST" action="<?=$action?>" id="liqpay">
    <input type="hidden" name="public_key" value="<?=$public_key?>" />
    <input type="hidden" name="amount" value="<?=$amount?>" />
    <input type="hidden" name="currency" value="<?=$currency?>" />
    <input type="hidden" name="description" value="<?=$description?>" />
    <input type="hidden" name="order_id" value="<?=$order_id?>" />
    <input type="hidden" name="result_url" value="<?=$result_url?>" />
    <input type="hidden" name="server_url" value="<?=$server_url?>" />
    <input type="hidden" name="type" value="<?=$type?>" />
    <input type="hidden" name="signature" value="<?=$signature?>" />
    <input type="hidden" name="language" value="<?=$language?>" />
    <div class="buttons">
        <div class="right">
            <input type="submit" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
        </div>
    </div>
</form>

<script>
$("input#button-confirm").click(function() {
    $.ajax({
        type: 'get',
        url: '<?=$url_confirm?>',
        success: function() {
            $("form#liqpay").submit();
        }
    });
    return false;
});
</script>
