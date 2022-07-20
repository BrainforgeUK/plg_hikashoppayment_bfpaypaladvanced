<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

Factory::getDocument()->addStyleSheet('https://www.paypalobjects.com/webstatic/en_US/developer/docs/css/cardfields.css');
?>
<div class="card_container">
    <form id="bfpaypaladvanced-card-form">
        <?php
		$templateFile = JPATH_THEMES . '/' . Factory::getApplication()->getTemplate(false) .
                                                '/html/plg_hikashoppayment_bfpaypaladvanced/cardform.php';
        if (!is_file($templateFile))
		{
			$templateFile = dirname(__DIR__) . '/tmpl/cardform.php';
        }

        include $templateFile;
        ?>
    </form>
</div>
