<?php
/**
 * @package   Paypal advanced payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

class plgHikashoppaymentBfpaypaladvanced extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'bfpaypaladvanced';
	var $pluginConfig = array(
		'client_id' 			=> array("PLG_BFPAYPALADVANCED_CLIENTID", 'input'),
		'client_secret'			=> array("PLG_BFPAYPALADVANCED_SECRET",   'input'),
		'sandbox' 				=> array('SANDBOX', 'radio',
														array('1' => 'HIKASHOP_YES', '0' => 'HIKASHOP_NO', )),
		'order_status' 			=> array('ORDER_STATUS',    'orderstatus'),
		'paid_status'	 		=> array('VERIFIED_STATUS', 'orderstatus'),
		'invalid_status' 		=> array('INVALID_STATUS',  'orderstatus'),
		'status_notif_email' 	=> array('ORDER_STATUS_NOTIFICATION', 'boolean','0'),
		'return_url' 			=> array('RETURN_URL', 'input'),
		'debug' 				=> array('DEBUG', 'radio',
														array('1' => 'HIKASHOP_YES', '0' => 'HIKASHOP_NO', )),
		'notes'		 			=> array('JFIELD_NOTE_LABEL', 'textarea'),
	);

	var $app;

	/*
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		parent::loadLanguage('plg_hikashoppayment_bfpaypaladvanced', __DIR__);

		require_once __DIR__ . '/helpers/paypal.php';
	}

	/**
	 */
	public function pluginConfigDisplay($fieldType, $data, $type, $paramsType, $key, $element) {
		switch ($fieldType)
		{
			default:
				return '';
		}
	}

	/*
	 */
	function onPaymentDisplay(&$order, &$methods, &$usable_methods)
	{
		$result = parent::onPaymentDisplay($order, $methods, $usable_methods);

		$del = [];
		foreach($usable_methods as $key=>$usable_method)
		{
			if ($usable_method->payment_type != $this->name) continue;

			$this->app->setUserState('plghikashoppayment.bfpaypaladvanced', null);

			if (empty($usable_method->payment_params->client_id) ||
				empty($usable_method->payment_params->client_secret))
			{
				$del[] = $key;
			}
		}
		foreach($del as $key) unset($usable_methods[$key]);

		return $result;
	}

	/*
	 */
	function onBeforeOrderCreate(&$order, &$do)
	{
		$this->app->setUserState('bfpaypaladvanced.paypal_params', null);

		$cartOrder = $this->app->getUserState('bfpaypaladvanced.cartorder');
		if (!empty($cartOrder))
		{
			$this->app->setUserState('bfpaypaladvanced.cartorder', null);

			if (!empty($cartOrder[$order->cart->cart_id]))
			{
				// If user has refreshed page and created new order from the cart.
				// Need to cancel order previously created.
				$prevOrder = $this->getOrder($cartOrder[$order->cart->cart_id]);
				if (!empty($prevOrder))
				{
					$this->pluginParams($prevOrder->order_payment_id);
					switch($prevOrder->order_status)
					{
						case $this->plugin_params->order_status:
							$this->modifyOrder($prevOrder->order_id,
								$this->plugin_params->invalid_status,
								null,
								$this->plugin_params->status_notif_email);
							break;
						case $this->plugin_params->invalid_status:
						default:
							break;
					}
				}
			}
		}

		// Ensure cancel order and return to checkout button works
		$config =& hikashop_config();
		$config->set('clean_cart', 'order_confirmed');

		parent::onBeforeOrderCreate($order, $do);
	}

	/*
	 */
	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		$this->app->setUserState('bfpaypaladvanced.cartorder', array($order->cart->cart_id => $order->order_id));

		$this->pluginParams($order->order_payment_id);

		$this->order = $order;

		return $this->showPage('end');
	}

	/*
	 */
	function getPaymentDefaultValues(&$element)
	{
		$element->payment_name=Text::_('PLG_BFPAYPALADVANCED_NAME');
		$element->payment_description=Text::_('PLG_BFPAYPALADVANCED_DESCRIPTION');
		$element->payment_images='PayPal';

		$element->payment_params->sandbox = '0';
		$element->payment_params->order_status = 'created';
		$element->payment_params->paid_status = 'confirmed';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->status_notif_email = '1';
		$element->payment_params->debug = '0';
	}

	/*
	 */
	function onPaymentNotification(&$statuses)
	{
		$secretKey = $this->app->getUserState('plghikashoppayment.bfpaypaladvanced');
		if (empty($secretKey))
		{
			$this->returnNotificationError('001');
		}

		$this->order = $this->getOrder($this->app->input->getInt('order_id', 0));
		if (empty($this->order))
		{
			$this->returnNotificationError('002');
		}

		$method_id = $this->app->input->getInt('notif_id', 0);
		$this->pluginParams($method_id);
		if(empty($this->plugin_params))
		{
			$this->returnNotificationError('003');
		}

		switch ($this->order->order_status)
		{
			case $this->plugin_params->order_status:
				break;
			default:
				$this->returnNotificationError('004');
		}

		if($secretKey != $this->getSecretKey(false))
		{
			$this->returnNotificationError('005');
		}

		if (password_verify(
				$secretKey,
				base64_decode($this->app->input->getBase64('key'))) !== true)
		{
			$this->returnNotificationError('006');
		}

		switch($this->app->input->getString('action'))
		{
			case 'onSubmit':
				$captureResult = plgHikashoppaymentBfpaypaladvancedHelper::doCapture($this);
				if (empty($captureResult))
				{
					echo json_encode('');
					exit(0);
				}

				if (is_object($captureResult))
				{
					if ($this->plugin_params->debug)
					{
						echo json_encode($captureResult);
						exit(0);
					}

					echo json_encode('');
					exit(0);
				}

				$result = json_decode($captureResult, true);
				if (empty($result))
				{
					$this->returnNotificationError('008');
				}

				if ($result['intent'] == 'CAPTURE' && $result['status'] == 'COMPLETED')
				{
					$this->app->setUserState('plghikashoppayment.bfpaypaladvanced', null);
					$this->app->setUserState('bfpaypaladvanced.cartorder', null);

					$transaction = $captureResult['purchase_units'][0];

					$history = new stdClass();
					$history->amount = $transaction['amount']['value'];
					$history->data = serialize($result);
					$history->notified = 1;

					$this->modifyOrder($this->order->order_id,
										$this->plugin_params->paid_status,
										$history,
										$this->plugin_params->status_notif_email);

					$cartClass = hikashop_get('class.cart');
					$cartClass->cleanCartFromSession(false, true);
				}

				echo $captureResult;
				exit(0);

			case 'onCancel':
				$this->app->setUserState('plghikashoppayment.bfpaypaladvanced', null);
				$this->app->setUserState('bfpaypaladvanced.cartorder', null);
				$this->app->setUserState('bfpaypaladvanced.paypal_params', null);

				$this->modifyOrder($this->order->order_id,
					$this->plugin_params->invalid_status,
					null,
					$this->plugin_params->status_notif_email);

				$this->app->enqueueMessage(Text::sprintf('PLG_BFPAYPALADVANCED_ORDERCANCELLED', $this->order->order_number));

				$this->app->redirect('index.php?option=com_hikashop&view=checkout&layout=show');

				$this->returnNotificationError('009');
				break;

			default:
				$this->returnNotificationError('010');
		}
	}

	/*
	 */
	protected function returnNotificationError($errorCode)
	{
		$this->app->setUserState('plghikashoppayment.bfpaypaladvanced', null);

		parent::loadLanguage('plg_hikashoppayment_bfpaypaladvanced', __DIR__);

		$history = new stdClass();
		$history->data = Text::sprintf('PLG_BFPAYPALADVANCED_NOTIFICATIONERROR', $errorCode);
		$history->notified = 1;

		if (!empty($this->order))
		{
			$this->modifyOrder($this->order->order_id,
				$this->order->order_status,
				$history,
				$this->plugin_params->status_notif_email);
		}

		die($history->data);
	}

	/*
	 */
	function onHistoryDisplay(&$histories){
		foreach($histories as $key=>&$history){
			if($history->history_payment_method == $this->name && !empty($history->history_data)){
				$data = hikashop_unserialize($history->history_data);

				if (is_array($data))
				{
					$transaction = $data['purchase_units'][0]['payments']['captures'][0];
					$history->history_data = 'Transaction ' . $transaction['status'] . ' : ' . $transaction['id'] . '<br/>';

					// TODO - Add onclick display button, or read using browser source
					$history->history_data .= '<textarea id="historydata-' . $key . '" readonly="readonly" style="display:none;">' .
													htmlspecialchars(print_r($data, true)) .
											  '</textarea>';
				}
			}
		}
		unset($history);
	}

	/*
	 */
	public function getSecretKey($hashed=true)
	{
		$key = $this->app->getUserState('plghikashoppayment.bfpaypaladvanced');

		if (empty($key))
		{
			$key = $this->order->order_number . '/' .
				$this->order->order_status . '/' .
				$this->order->order_ip . '/' .
				$this->order->order_user_id . '/' .
				$this->plugin_params->client_id . '/' .
				$this->plugin_params->client_secret . '/' .
				rand(1000000,9999999);

			if ($hashed)
			{
				$this->app->setUserState('plghikashoppayment.bfpaypaladvanced', $key);
			}
		}

		return $hashed ? base64_encode(password_hash($key, PASSWORD_BCRYPT)) : $key;
	}

	/*
	 */
	public function getNotifyUrl($action)
	{
		$notifyScript = JPATH_ROOT . '/plghikashoppayment' . $this->name . '.php';
		if (!file_exists($notifyScript))
		{
			$script = [];

			$fd = fopen(__FILE__, "r");
			while (($line = fgets($fd)) !== false) {
				$script[] = rtrim($line);
				if (strpos($line, '*/') !== false)
				{
					break;
				}
			}
			fclose($fd);

			$values = [];
			$values["option"] 			= "'com_hikashop'";
			$values["tmpl"] 			= "'component'";
			$values["ctrl"] 			= "'checkout'";
			$values["task"] 			= "'notify'";
			$values["format"] 			= "'html'";
			$values["lang"] 			= "'en'";
			$values["notif_payment"] 	= "'" . $this->name . "'";
			$values["notif_id"] 		= '$_GET["pid"]';
			$values["order_id"] 		= '$_GET["oid"]';

			foreach(array('GET', 'REQUEST') as $type)
			{
				foreach($values as $option=>$value)
				{
					$script[] = '$_' . $type . "['${option}'] = ${value};";
				}
			}

			$script[] = 'include("index.php");';

			file_put_contents($notifyScript, implode("\n", $script), LOCK_EX);
		}

		$script = [];
		$script[] = 'action=' . $action;
		$script[] = 'pid=' . $this->order->order_payment_id;
		$script[] = 'oid=' . $this->order->order_id;
		$script[] = 'key=' . $this->getSecretKey();

		return Uri::root() . basename($notifyScript) . '?' . implode('&', $script);
	}
}
