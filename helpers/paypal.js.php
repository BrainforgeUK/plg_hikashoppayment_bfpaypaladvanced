<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');

/** @var plgHikashoppaymentBfpaypaladvancedHelper $paypalHelper */

$jsArgs = [];
$jsArgs[] = 'components=hosted-fields';
$jsArgs[] = 'client-id=' . $paypalHelper->plugin_params->client_id;
$jsArgs[] = 'currency='  . $paypalHelper->order->order_currency_info->currency_code;
$jsArgs[] = 'intent=capture';
// See : https://developer.paypal.com/docs/multiparty/get-started#code-and-credential-reference
// $jsArgs[] = 'data-partner-attribution-id="<BN-Code>';
?>
<script src="https://www.paypal.com/sdk/js?<?php echo implode('&', $jsArgs);?>"
        data-client-token=<?php echo $paypalHelper->paypal_params->clientToken; ?>
></script>

<div id="card-button-container">
    <?php include __DIR__ . '/paypal.html.php'; ?>

    <script>
        let orderId = "<?php echo $paypalHelper->paypal_params->orderId; ?>";

        function initCardForm() {
            if (paypal.HostedFields.isEligible()) {
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
                            placeholder: "4111 1111 1111 1111"
                        },

                        cvv: {
                            selector: "#cvv",
                            placeholder: "123"

                        },

                        expirationDate: {
                            selector: "#expiration-date",
                            placeholder: "MM/YY"
                        }
                    }

                }).then(function (cardFields) {
                    document.querySelector("#card-form").addEventListener('submit', (event) => {
                        event.preventDefault();

                        cardFields.submit({
                            // Cardholder's first and last name
                            cardholderName: document.getElementById('card-holder-name').value,
                            // Billing Address
                            billingAddress: {
                                /*
                                // Street address, line 1
                                streetAddress: document.getElementById('card-billing-address-street').value,
                                // Street address, line 2 (Ex: Unit, Apartment, etc.)
                                extendedAddress: document.getElementById('card-billing-address-unit').value,
                                // State
                                region: document.getElementById('card-billing-address-state').value,
                                // City
                                locality: document.getElementById('card-billing-address-city').value,
                                */
                                // Postal Code
                                //postalCode: document.getElementById('card-billing-address-zip').value,
                                // Country Code
                                countryCodeAlpha2: document.getElementById('card-billing-address-country').value
                            }
                        }).then(function () {
                            fetch('<?php echo $paypalHelper->getNotifyUrl('onSubmit'); ?>'
                            ).then(function(res) {
                                return res.json();
                            }).then(function (orderData) {
                                if (orderData.bfErrorMessage)
                                {
                                    return alert("Error capturing payment.\n" + orderData.bfErrorMessage);
                                }

                                if(!orderData.details)
                                {
                                    return alert('Invalid payment capture response.');
                                }

                                // Three cases to handle:
                                //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                                //   (2) Other non-recoverable errors -> Show a failure message
                                //   (3) Successful transaction -> Show confirmation or thank you

                                // This example reads a v2/checkout/orders capture response, propagated from the server
                                // You could use a different API or structure for your 'orderData'
                                var errorDetail = Array.isArray(orderData.details) && orderData.details[0];

                                if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                                    return actions.restart(); // Recoverable state, per:
                                    // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                                }

                                if (errorDetail) {
                                    var msg = 'Sorry, your transaction could not be processed.';
                                    if (errorDetail.description) msg += '\n\n' + errorDetail.description;
                                    if (orderData.debug_id) msg += ' (' + orderData.debug_id + ')';
                                    return alert(msg); // Show a failure message
                                }

                                // Show a success message or redirect
                                alert('Transaction completed!');
                                <?php echo $paypalHelper->onOrderCompleted(); ?>
                            }).catch(function (err) {
                                alert('Payment could not be captured ' + err.message);
                            });
                        }).catch(function (err) {
                            alert('Payment could not be captured ' + err.message);
                        });
                    });
                });
            }
            else {
                // Hides card fields if the merchant isn't eligible
                document.querySelector("#card-form").style = 'display: none';
                alert('Merchant not eligible for card payments.');
            }
        }

        initCardForm();
    </script>
</div>
