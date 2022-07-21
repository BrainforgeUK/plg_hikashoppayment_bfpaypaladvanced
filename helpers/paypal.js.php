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

        const createOrderUrl        = '<?php echo $paypalHelper->getNotifyUrl('createorder'); ?>';
        const captureUrl            = '<?php echo $paypalHelper->getNotifyUrl('capture'); ?>';
        const textOrderError        = '<?php echo Text::_('PLG_BFPAYPALADVANCED_ORDERERROR'); ?>';
        const textPaymentError      = '<?php echo Text::_('PLG_BFPAYPALADVANCED_PAYMENTERROR'); ?>';
        const debug                 = <?php echo $paypalHelper->plugin_params->debug ?>;
        const usecardholderaddress  = <?php echo $paypalHelper->plugin_params->usecardholderaddress ?>;

        function enablePayButton()
        {
            let submitbtn = document.getElementById('bfpaypaladvanced-card-submitbtn');
            if (submitbtn) {
                submitbtn.disabled = '';
                submitbtn.classList.remove("submitted");
                document.getElementById('bfpaypaladvanced-card-submitbtn-busy').style.display = 'none';
            }
        }

        function disablePayButton()
        {
            let submitbtn = document.getElementById('bfpaypaladvanced-card-submitbtn');
            if (submitbtn) {
                submitbtn.disabled = 'disabled';
                submitbtn.classList.add("submitted");
                document.getElementById('bfpaypaladvanced-card-submitbtn-busy').style.display = '';
            }
        }

        function consoleLog(err) {
            if (debug) {
                console.log(err);
            }
        }

        function getCardHolderAddress() {
            if (!usecardholderaddress) {
                return null;
            }

            let cardHolderAddress = { };
            let el;
            <?php
            foreach (array(
                         'streetAddress'     => 'street',
                         'extendedAddress'   => 'unit',
                         'region'            => 'state',
                         'locality'          => 'city',
                         'postalCode'        => 'zip',
                         'countryCodeAlpha2' => 'country',
                     ) as $jsfield => $elid)
            {
                echo "
                    el = document.getElementById('card-billing-address-${elid}');
                    if (el) {
                        cardHolderAddress.${jsfield} = el.value;
                    }
                    else {
                        cardHolderAddress.${jsfield} = '';
                    }
                    ";
            }
            ?>
            return cardHolderAddress;
        }

        function initCardForm() {
            if (!paypal.HostedFields.isEligible()) {
                // Hides card fields if the merchant isn't eligible
                document.querySelector("#bfpaypaladvanced-card-form").style = 'display: none';
                alert('<?php echo Text::_('PLG_BFPAYPALADVANCED_UNSUPPORTEDMERCHANT'); ?>');
                return;
            }

            // Renders card fields
            paypal.HostedFields.render({
                createOrder: function () {
                    return fetch(createOrderUrl)
                        .then((res) => res.json())
                        .then((orderData) => {
                            orderId = orderData.id;
                            return orderData.id;
                    }).catch(function (err) {
                        consoleLog(err);
                        alert(textOrderError);
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
                        cardholderName: document.getElementById("card-holder-name").value,
                        billingAddress: getCardHolderAddress(),
                        // Trigger 3D Secure authentication
                        contingencies: ['<?php echo $paypalHelper->get3DSecureContingency(); ?>'],
                    }).catch(function (err) {
                        enablePayButton();
                    }).then(function (payload) {
                        if (payload == undefined) {
                            return;
                        }
                        fetch(captureUrl + '&payload=' + btoa(JSON.stringify(payload))
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
                                    alert(textPaymentError);
                                    break;
                            }
                            enablePayButton();

                        }).catch(function (err) {
                            consoleLog(err);
                            alert(textPaymentError);
                            enablePayButton();
                        });
                    });
                });
            });
        };

        initCardForm();
    </script>
</div>
