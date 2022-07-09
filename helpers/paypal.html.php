<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

Factory::getDocument()->addStyleSheet('https://www.paypalobjects.com/webstatic/en_US/developer/docs/css/cardfields.css');

// TODO
/** @var plgHikashoppaymentBfpaypaladvancedHelper $paypalHelper */
//$addressInfo = $paypalHelper->getAddressInfo('billing');
//$addressInfo['country_code'] = 'IE';
?>
<div class="card_container">
    <form id="card-form">
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

        <label for="card-holder-name"><?php echo Text::_('PLG_BFPAYPALADVANCED_NAMEONCARD'); ?></label>
        <input type="text"
               id="card-holder-name"
               name="card-holder-name"
               autocomplete="off"
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_NAMEONCARD'); ?>"
        />

        <?php
        /* TODO
        ?>
        <label for="card-billing-address-country"><?php echo Text::_('PLG_BFPAYPALADVANCED_COUNTRYCODE'); ?></label>
        <input type="text"
               id="card-billing-address-country"
               name="card-billing-address-country"
               autocomplete="off"
               value="<?php echo $addressInfo['country_code']; ?>"
        />
        */
        ?>

        <br/><br/>

        <button value="submit" id="submit" class="btn">Pay</button>
    </form>
</div>
