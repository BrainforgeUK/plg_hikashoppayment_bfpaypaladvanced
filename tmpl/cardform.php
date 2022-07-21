<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var object $plugin */
/** @var object $paypalHelper */

defined('_JEXEC') or die('Restricted access');
?>
<div id="card-number-wrap">
    <label for="card-number"><?php echo Text::_('PLG_BFPAYPALADVANCED_CARDNUMBER'); ?></label>
    <div id="card-number" class="card_field"></div>
</div>

<div id="expiration-date-wrap">
    <label for="expiration-date"><?php echo Text::_('PLG_BFPAYPALADVANCED_EXPIRATIONDATE'); ?></label>
    <div id="expiration-date" class="card_field"></div>
</div>

<div id="cvv-wrap">
    <label for="cvv"><?php echo Text::_('PLG_BFPAYPALADVANCED_CVV'); ?></label>
    <div id="cvv" class="card_field"></div>
</div>

<?php
$billing_address = $plugin->order->cart->billing_address;
?>
<label for="card-holder-name"><?php echo Text::_('PLG_BFPAYPALADVANCED_NAMEONCARD'); ?></label>
<input type="text"
       id="card-holder-name"
       name="card-holder-name"
       autocomplete="off"
       value="<?php echo htmlentities(empty($billing_address->address_company)
                            ? $billing_address->address_firstname . ' ' . $billing_address->address_lastname
                            : $billing_address->address_company); ?>"
       placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_NAMEONCARD_PLACEHOLDER'); ?>"
/>

<style>
    .card-billing-address-wrap input,
    .card-billing-address-wrap select { height: 40px; }
    .card-billing-address-wrap .chzn-container-single { height: 50px; }
    .card-billing-address-wrap .chzn-single { height: 40px; padding: 5px; }
</style>

<?php
if ($paypalHelper->plugin_params->usecardholderaddress) {
	$zoneClass = hikashop_get('class.zone');
    ?>
    <div id="card-billing-address-wrap" class="card-billing-address-wrap">
        <?php
        switch($paypalHelper->plugin_params->usecardholderaddress) {
            case '2':
                ?>
                <label for="card-billing-address-zip">
                    <?php echo Text::_('PLG_BFPAYPALADVANCED_CARDHOLDERADDRESS'); ?>
                </label>
                <?php
				include __DIR__ . '/cardform-postcode.php';
                break;
			default:
				?>
                <label for="card-billing-address-street">
					<?php echo Text::_('PLG_BFPAYPALADVANCED_CARDHOLDERADDRESS'); ?>
                </label>
				<?php
				include __DIR__ . '/cardform-address.php';
				include __DIR__ . '/cardform-postcode.php';
				break;
        }
        ?>
    </div>
    <?php
}
?>

<div id="bfpaypaladvanced-card-submit">
    <button value="submit"
            id="bfpaypaladvanced-card-submitbtn"
            class="hikabtn hikacart"
            style="position:relative;">
        <?php echo Text::_('PLG_BFPAYPALADVANCED_PAYWITHCARD'); ?>
        <img id="bfpaypaladvanced-card-submitbtn-busy"
             src="<?php echo Uri::root() . '/plugins/hikashoppayment/bfpaypaladvanced/images/spinner.gif'; ?>"
             style="position:absolute;right:1em;display:none;height:25px;"/>
    </button>
</div>
