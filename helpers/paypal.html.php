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
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

Factory::getDocument()->addStyleSheet('https://www.paypalobjects.com/webstatic/en_US/developer/docs/css/cardfields.css');
?>
<div class="card_container">
    <form id="bfpaypaladvanced-card-form">
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
               placeholder="<?php echo Text::_('PLG_BFPAYPALADVANCED_NAMEONCARD_PLACEHOLDER'); ?>"
        />

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
    </form>
</div>
