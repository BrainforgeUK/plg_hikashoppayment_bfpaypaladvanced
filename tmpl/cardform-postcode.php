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
<div id="card-billing-address-wrap-zip">
    <input type="text"
           id="card-billing-address-zip"
           name="card-billing-address-zip"
           autocomplete="off"
           value="<?php echo htmlentities(@$billing_address->address_post_code); ?>"
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
