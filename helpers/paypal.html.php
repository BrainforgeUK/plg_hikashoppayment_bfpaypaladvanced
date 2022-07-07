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

        <!--
        <div>
            <input type="text"
                   id="card-billing-address-zip"
                   name="card-billing-address-zip"
                   autocomplete="off"
                   placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_ZIP'); ?>"
            />
        </div>
        -->

        <div>
            <label for="card-billing-address-country"><?php echo Text::_('PLG_BFPAYPALADVANCED_COUNTRYCODE'); ?></label>
            <input type="text"
                   id="card-billing-address-country"
                   name="card-billing-address-country"
                   autocomplete="off"
                   value="IE"
                   placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_COUNTRYCODE'); ?>"
            />
        </div>

        <br/><br/>

        <button value="submit" id="submit" class="btn">Pay</button>
    </form>
</div>
