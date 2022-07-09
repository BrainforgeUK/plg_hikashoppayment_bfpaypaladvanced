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
        let orderId = "<?php echo $paypalHelper->paypal_params->orderId; ?>";

        function initCardForm() {
            if (!paypal.HostedFields.isEligible()) {
                // Hides card fields if the merchant isn't eligible
                document.querySelector("#card-form").style = 'display: none';
                <?php echo $paypalHelper->consoleLog(null, 'PLG_BFPAYPALADVANCED_UNSUPPORTEDMERCHANT'); ?>
                return;
            }

            // Renders card fields
            paypal.HostedFields.render({
                // https://developer.paypal.com/sdk/js/reference/#link-paypalhostedfields
                createOrder: function () {
                    return orderId;
                },

                styles: {
                    '.valid': {
                        'color': 'green'
                    },
                    '.invalid': {
                        'color': 'red'
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

            }).then(function (cardFields) {
                document.querySelector("#card-form").addEventListener('submit', (event) => {
                    event.preventDefault();

                    cardFields.submit({
                        // Cardholder's first and last name
                        cardholderName: document.getElementById('card-holder-name').value,
                        <?php
                        /* TODO
                        // Billing Address
                        billingAddress: {
                            countryCodeAlpha2: document.getElementById('card-billing-address-country').value
                        }
                        */
                        ?>
                    }).then(function () {
                        fetch('<?php echo $paypalHelper->getNotifyUrl('capture'); ?>'
                        ).then(function(res) {
                            return res.json();
                        }).then(function (orderData) {
                            if (orderData.bfErrorMessage)
                            {
                                <?php echo $paypalHelper->consoleLog('orderData.bfErrorMessage', 'PLG_BFPAYPALADVANCED_CAPTUREERROR'); ?>
                                return null;
                            }

                            if(!orderData.details)
                            {
                                <?php echo $paypalHelper->consoleLog(null, 'PLG_BFPAYPALADVANCED_CAPTUREINVALID'); ?>
                                return null;
                            }

                            // Three cases to handle:
                            //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                            //   (2) Other non-recoverable errors -> Show a failure message
                            //   (3) Successful transaction -> Show confirmation or thank you

                            // Read a v2/checkout/orders capture response, propagated from the server
                            var errorDetail = Array.isArray(orderData.details) && orderData.details[0];

                            if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                                return actions.restart(); // Recoverable state, per:
                                // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                            }

                            if (errorDetail) {
                                var msg = '<?php Text::_('PLG_BFPAYPALADVANCED_PAYMENTNOTPROCESSED'); ?>';
                                if (errorDetail.description) msg += '\n\n' + errorDetail.description;
                                if (orderData.debug_id) msg += ' (' + orderData.debug_id + ')';
                                alert(msg);
                                return null;
                            }

                            // Show a success message or redirect
                            alert('Transaction completed!');
                            <?php echo $paypalHelper->onOrderCompleted(); ?>
                        }).catch(function (err) {
                            <?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALADVANCED_PAYMENTERROR'); ?>
                        });
                    }).catch(function (err) {
                            <?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALADVANCED_PAYMENTERROR'); ?>
                    });
                });
            });
        }

        initCardForm();
    </script>
</div>
