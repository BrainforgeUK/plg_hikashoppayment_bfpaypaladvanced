<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;

/** @var object $billing_address */
/** @var object $zoneClass */

defined('_JEXEC') or die('Restricted access');
?>
<div id="card-billing-address-wrap-street">
    <input type="text" id="card-billing-address-street"
           name="card-billing-address-street"
           autocomplete="off"
           value="<?php echo htmlentities(@$billing_address->address_street); ?>"
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
           value="<?php echo htmlentities(@$billing_address->address_city); ?>"
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
