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

defined('_JEXEC') or die('Restricted access');
?>
<label for="card-number"><?php echo Text::_('PLG_BFPAYPALADVANCED_CARDNUMBER'); ?></label>
<div id="card-number" class="card_field"></div>

<div>
    <label for="expiration-date"><?php echo Text::_('PLG_BFPAYPALADVANCED_EXPIRATIONDATE'); ?></label>
    <div id="expiration-date" class="card_field"></div>
</div>

<div>
    <label for="cvv"><?php echo Text::_('PLG_BFPAYPALADVANCED_CVV'); ?></label>
    <div id="cvv" class="card_field"></div>
</div>

<?php
$zoneClass = hikashop_get('class.zone');
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

<div id="card-billing-address-wrap" class="card-billing-address-wrap">
    <div id="card-billing-address-wrap-street">
        <label for="card-billing-address-street"><?php echo Text::_('PLG_BFPAYPALADVANCED_CARDHOLDERADDRESS'); ?></label>
        <input type="text" id="card-billing-address-street"
               name="card-billing-address-street"
               autocomplete="off"
               value="<?php echo htmlentities($billing_address->address_street); ?>"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_ADDRESS_PLACEHOLDER'); ?>"
    </div>

    <div id="card-billing-address-wrap-unit">
        <input type="text"
               id="card-billing-address-unit"
               name="card-billing-address-unit"
               autocomplete="off"
               value="<?php echo htmlentities(@$billing_address->address_street2); ?>"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_UNIT_PLACEHOLDER'); ?>"
    </div>

    <div id="card-billing-address-wrap-city">
        <input type="text"
               id="card-billing-address-city"
               name="card-billing-address-city"
               autocomplete="off"
               value="<?php echo htmlentities($billing_address->address_city); ?>"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_CITY_PLACEHOLDER'); ?>"
    </div>

    <div id="card-billing-address-wrap-state">
        <input type="text"
               id="card-billing-address-state"
               name="card-billing-address-state"
               autocomplete="off"
               value="<?php echo htmlentities(@$zoneClass->getZones(array(@$billing_address->address_state[0]),
                                                               'zone_name_english', 'zone_namekey', true)[0]); ?>"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_STATE_PLACEHOLDER'); ?>"
    </div>

    <div id="card-billing-address-wrap-zip">
        <input type="text"
               id="card-billing-address-zip"
               name="card-billing-address-zip"
               autocomplete="off"
               value="<?php echo htmlentities($billing_address->address_post_code); ?>"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_ZIP_PLACEHOLDER'); ?>"
    </div>

    <div id="card-billing-address-wrap-country">
        <?php
        $countries = $zoneClass->getZones(array('country'), 'zone_code_2, zone_name_english', 'zone_type');
        usort($countries, function($a, $b)
		{
            return strcmp($a->zone_name_english, $b->zone_name_english);
        });

        $options = array('' => Text::_('PLG_BFPAYPALADVANCED_COUNTRY_PLACEHOLDER'));
        foreach($countries as $country)
		{
			$options[] = JHTML::_('select.option', $country->zone_code_2, $country->zone_name_english);
		}

        echo JHTML::_('select.genericlist', $options, 'card-billing-address-country', '', 'value','text',
			@$zoneClass->getZones(array(@$billing_address->address_country[0]), 'zone_code_2', 'zone_namekey', true)[0]);
        ?>
    </div>
</div>

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
