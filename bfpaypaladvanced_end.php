<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

if (strpos($this->plugin_params->return_url, 'PLG_BFPAYPALADVANCED_RETURNURL') === 0)
{
	$this->plugin_params->return_url = Text::sprintf($this->plugin_params->return_url, $this->order->order_id);
}

?>
<div id="bfpaypaladvanced-end">
    <h3>
		<?php
		$this->currencyHelper = hikashop_get('class.currency');
		echo Text::sprintf('PLG_BFPAYPALADVANCED_PAYMENTDUE',
			$this->order->order_number,
			$this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id),
			$this->order->order_currency_info->currency_code);
		?>
    </h3>
    <?php
    plgHikashoppaymentBfpaypaladvancedHelper::newCardForm($this);

    $cancelUrl = $this->getNotifyUrl('cancel');
    ?>
    <div id="bfpaypaladvanced-cancelbtn">
        <a href="<?php echo $cancelUrl; ?>">
            <button class="hikabtn hikacart"
                    style="width:fit-content;">
				<?php echo Text::_('PLG_BFPAYPALADVANCED_CANCEL_ORDER'); ?>
            </button>
        </a>
    </div>

	<?php
	if ($this->plugin_params->sandbox)
	{
		?>
        <hr/>
		<?php echo Text::_('PLG_BFPAYPALADVANCED_SANDBOXNOTES'); ?>
        <pre><?php echo $this->plugin_params->notes; ?></pre>
        <br/>
		<?php
	}
	?>
</div>
