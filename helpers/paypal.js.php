<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;defined('_JEXEC') or die('Restricted access');

/** @var plgHikashoppaymentBfpaypaladvancedHelper $paypalHelper */

$jsArgs = [];
$jsArgs[] = 'components=hosted-fields';
$jsArgs[] = 'client-id=' . $paypalHelper->plugin_params->client_id;
$jsArgs[] = 'currency='  . $paypalHelper->order->order_currency_info->currency_code;
$jsArgs[] = 'intent=capture';
?>
<script src="https://www.paypal.com/sdk/js?<?php echo implode('&', $jsArgs);?>"
        data-client-token="<?php echo $paypalHelper->paypal_params->clientToken; ?>"
></script>

<div id="card-button-container">
    <?php include __DIR__ . '/paypal.html.php'; ?>

    <script>
        let orderId = "";

        function enablePayButton()
        {
            document.getElementById('bfpaypaladvanced-card-submitbtn').disabled = '';
            document.getElementById('bfpaypaladvanced-card-submitbtn').classList.remove("submitted");
            document.getElementById('bfpaypaladvanced-card-submitbtn-busy').style.display = 'none';
        }

        function disablePayButton()
        {
            document.getElementById('bfpaypaladvanced-card-submitbtn').disabled = 'disabled';
            document.getElementById('bfpaypaladvanced-card-submitbtn').classList.add("submitted");
            document.getElementById('bfpaypaladvanced-card-submitbtn-busy').style.display = '';
        }

        function initCardForm() {
            if (!paypal.HostedFields.isEligible()) {
                // Hides card fields if the merchant isn't eligible
                document.querySelector("#bfpaypaladvanced-card-form").style = 'display: none';
                <?php echo $paypalHelper->consoleLog(null, 'PLG_BFPAYPALADVANCED_UNSUPPORTEDMERCHANT'); ?>
                return;
            }

            // Renders card fields
            paypal.HostedFields.render({
                createOrder: function () {
                    return fetch('<?php echo $paypalHelper->getNotifyUrl('createorder'); ?>')
                        .then((res) => res.json())
                        .then((orderData) => {
                            orderId = orderData.id;
                            return orderData.id;
                    }).catch(function (err) {
                        <?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALADVANCED_ORDERERROR'); ?>
                    });
                },

                styles: {
                    '.valid': {
                        'color': 'green',
                    },
                    '.invalid': {
                        'color': 'red',
                    },
                },

                fields: {
                    number: {
                        selector: "#card-number",
                        placeholder: "<?php echo Text::_('PLG_BFPAYPALADVANCED_CARDNUMBER_PLACEHOLDER'); ?>"
                    },

                    cvv: {
                        selector: "#cvv",
                        placeholder: "<?php echo Text::_('PLG_BFPAYPALADVANCED_CVV_PLACEHOLDER'); ?>"
                    },

                    expirationDate: {
                        selector: "#expiration-date",
                        placeholder: "<?php echo Text::_('PLG_BFPAYPALADVANCED_EXPIRATIONDATE_PLACEHOLDER'); ?>"
                    }
                }

            }).then(function (hf) {
                document.querySelector('#bfpaypaladvanced-card-form').addEventListener('submit', (event) => {

                    event.preventDefault();

                    disablePayButton();

                    hf.submit({
                        // Trigger 3D Secure authentication
                        contingencies: ['<?php echo $paypalHelper->get3DSecureContingency(); ?>'],
                    }).catch(function (err) {
                        enablePayButton();
                    }).then(function (payload) {
                        if (payload == undefined) {
                            return;
                        }
                        fetch('<?php echo $paypalHelper->getNotifyUrl('capture'); ?>&payload=' + btoa(JSON.stringify(payload))
                        ).then(function(res) {
                            return res.json();
                        }).then(function (response) {
                            switch(response.status)
                            {
                                case '-1':
                                    if (response.result)
                                    {
                                        console.log(response.result);
                                    }
                                    alert(response.message);
                                    break;
                                case '0':
                                    alert(response.message);
                                    break;
                                case '1':
                                    document.getElementById('bfpaypaladvanced-end').innerHTML = response.message;
                                    break;
                                case '2':
                                    window.location.href = response.url;
                                    break;
                                default:
                            		<?php echo $paypalHelper->consoleLog(null, 'PLG_BFPAYPALADVANCED_PAYMENTERROR'); ?>
                                    break;
                            }
                            enablePayButton();

                        }).catch(function (err) {
                            <?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALADVANCED_PAYMENTERROR'); ?>
                            enablePayButton();
                        });
                    });
                });
            });
        };

        initCardForm();
    </script>
</div>
