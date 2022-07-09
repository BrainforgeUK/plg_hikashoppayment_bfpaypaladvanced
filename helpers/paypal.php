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

class plgHikashoppaymentBfpaypaladvancedHelper
{
	protected $plugin;

	public $plugin_params;
	public $order;
	public $paypal_params;

	protected function __construct($plugin)
	{
		$this->plugin 			= $plugin;
		$this->plugin_params 	= $plugin->plugin_params;
		$this->order            = $plugin->order;
	}

	/*
	 */
	public static function newCardForm($plugin)
	{
		try
		{
			$paypalHelper = new plgHikashoppaymentBfpaypaladvancedHelper($plugin);

			$paypalHelper->paypal_params = $plugin->app->getUserState('plghikashoppayment.bfpaypaladvanced.paypal_params');
			if (empty($paypalHelper->paypal_params->status))
			{
				$paypalHelper->paypal_params = new stdClass();
				$paypalHelper->paypal_params->userpwd    = $plugin->plugin_params->client_id . ':' . $plugin->plugin_params->client_secret;
				$paypalHelper->paypal_params->bnCode     = 'BrainforgeUK';
				$paypalHelper->paypal_params->customerId = "ORDER_USER_ID-" . $plugin->order->order_user_id;

				$paypalHelper->paypal_params->accessToken = $paypalHelper->createToken();
				$paypalHelper->paypal_params->clientToken = $paypalHelper->createClientToken();
				$paypalHelper->paypal_params->orderId     = $paypalHelper->createOrder();

				$paypalHelper->paypal_params->status = true;

				$plugin->app->setUserState('plghikashoppayment.bfpaypaladvanced.paypal_params', $paypalHelper->paypal_params);
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			return false;
		}

        include __DIR__ . '/paypal.js.php';

		return $paypalHelper;
	}

	/*
	 */
	public static function doCapture($plugin)
	{
		try
		{
			$paypalHelper = new plgHikashoppaymentBfpaypaladvancedHelper($plugin);

			$paypalHelper->paypal_params = $plugin->app->getUserState('plghikashoppayment.bfpaypaladvanced.paypal_params');
			if (empty($paypalHelper->paypal_params->status))
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			return false;
		}

		return $paypalHelper->capturePaymentForOrder();
	}

	/*
	 */
	protected function createToken()
	{
		$url = 'https://' . $this->getPayPalApiUrl() . '/v1/oauth2/token';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'Accept-Language: en_US',
			'PayPal-Partner-Attribution-Id: ' . $this->paypal_params->bnCode,
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		//Specify that we want access credentials returned
		$vars['grant_type'] = 'client_credentials';

		//build and set the request
		$req = http_build_query($vars);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

		//get the response back
		$response = curl_exec($ch);
		if (empty($response))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '001'));
		}

		//parse json into php array
		$outArray = json_decode($response, true);

		//Close the connection
		curl_close($ch);

        if (empty($outArray['access_token']))
		{
            echo '<pre>';
			print_r($outArray);
			echo '</pre>';
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '002'));
		}

		//Extract the access token from the response so it can be used in the createOrder file
		return $outArray['access_token'];
	}

    /*
     */
    protected function createClientToken()
	{
		if (empty($this->paypal_params->accessToken))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '011'));
		}

		$url = 'https://' . $this->getPayPalApiUrl() . '/v1/identity/generate-token';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Accept-Language: en_US',
			'Authorization: Bearer ' . $this->paypal_params->accessToken

		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		$vars = [];
		$vars['customer_id'] = $this->paypal_params->customerId;
		$json = json_encode($vars);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        //get the response back
		$response = curl_exec($ch);
		if (empty($response))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '012'));
		}

        //parse json into php array
		$outArray = json_decode($response, true);

        //Close the connection
		curl_close($ch);

        if (empty($outArray['client_token']))
		{
			echo '<pre>';
			print_r($outArray);
			echo '</pre>';
			return false;
		}

		//Extract the access token from the response so it can be used in the createOrder file
		return $outArray['client_token'];
	}

	/*
	 */
	protected function createOrder()
	{
		if (empty($this->paypal_params->clientToken))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '021'));
		}

		$url = 'https://' . $this->getPayPalApiUrl() . '/v2/checkout/orders';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Accept-Language: en_US',
			'Authorization: Bearer ' . $this->paypal_params->accessToken,
			'PayPal-Partner-Attribution-Id: ' . $this->paypal_params->bnCode,
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		$amount = [];
		$amount['currency_code'] = $this->order->order_currency_info->currency_code;
		$amount['value'] = $this->order->order_full_price;

		$purchase_units = [];
		$purchase_units['amount'] = $amount;

		$vars = [];
		$vars['intent'] = 'CAPTURE';
		$vars['purchase_units'] = array($purchase_units);

		$json = json_encode($vars);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		//get the response back
		$response = curl_exec($ch);
		if (empty($response))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '022'));
		}

		//parse json into php array
		$outArray = json_decode($response, true);

		//Close the connection
		curl_close($ch);

		if (empty($outArray['id']))
		{
			echo '<pre>';
			print_r($outArray);
			echo '</pre>';
			return false;
		}

		//Extract the order id from the response so it can be used later
		return $outArray['id'];
	}

	/*
	 */
	protected function capturePaymentForOrder()
	{
		if (empty($this->paypal_params->orderId))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALADVANCED_ERROR', '031'));
		}

		$url = 'https://' . $this->getPayPalApiUrl() . '/v2/checkout/orders/' . $this->paypal_params->orderId . '/capture';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Accept-Language: en_US',
			'Authorization: Bearer ' . $this->paypal_params->accessToken,
			//'PayPal-Request-Id: 7b92603e-77ed-4896-8e78-5dea2050476a',
			'PayPal-Partner-Attribution-Id: ' . $this->paypal_params->bnCode,
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		//get the response back
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		//Close the connection
		curl_close($ch);

		if (empty($response))
		{
			$response =new stdClass();
			$response->bfErrorMessage = 'Headers : ' . print_r($headers, true) . "\n" .
											'Info : ' . print_r($info, true);
		}

		return $response;
	}

	/*
	 */
	protected function curlInit($url)
	{
		//Initiate CURL and set the url endpoint
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'])
		{
			// Assume development environment
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		//Set the user and password
		curl_setopt($ch, CURLOPT_USERPWD, $this->paypal_params->userpwd);

		//Keep connection open until we get data back
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		return $ch;
	}

	/*
	 */
	protected function getPayPalApiUrl()
    {
        return $this->plugin_params->sandbox ? 'api.sandbox.paypal.com' : 'api.paypal.com';
	}

	/*
	 */
	public function onOrderCompleted()
	{
		$message = Text::sprintf('PLG_BFPAYPALADVANCED_ORDERCOMPLETED', $this->order->order_number);

		$message = str_replace("'", "&#39;", $message);

		return "document.getElementById('bfpaypaladvanced-end').innerHTML = '${message}';";
	}

	/*
	 */
	public function getNotifyUrl($action)
	{
		return $this->plugin->getNotifyUrl($action);
	}

	/*
	 */
	public function consoleLog($log=null, $alert=null)
	{
		$result = [];

		if (!empty($alert))
		{
			$result[] = 'alert("' . str_replace('"', '\\"', Text::_($alert)) .'");';
		}

		if ($this->plugin_params->debug && !empty($log))
		{
			$result[] = 'console.log(' . implode(',', (array)$log) . ');';
		}

		return implode("\n", $result);
	}

	/*
	 */
	public function getAddressInfo($type='shipping')
	{
		$fields = array(
			'address_line_1' => 'address_street',
			'address_line_2' => 'address_street2',
			'admin_area_2'   => 'address_city',
			'admin_area_1'   => 'address_state',
			'postal_code'    => 'address_post_code',
			'country_code'   => 'address_country',
		);

		switch($type)
		{
			case 'billing':
				$cartField = 'billing_address';
				break;
			default:
				return false;
		}

		$zoneClass = hikashop_get('class.zone');

		$result = [];
		foreach($fields as $paypalField=>$addressField)
		{
			switch($paypalField)
			{
				case 'address_line_1':
				case 'admin_area_2':
				case 'postal_code':
					$addressField = trim(@$this->order->cart->$cartField->$addressField);
					if (empty($addressField)) return false;
					break;
				case 'country_code':
					$addressField = @$this->order->cart->$cartField->$addressField;
					if (empty($addressField)) return false;
					$addressField = $zoneClass->getZones(array($addressField[0]), 'zone_code_2', 'zone_namekey', true)[0];
					if (empty($addressField)) return false;
					break;
				case 'admin_area_1':
					$addressField = @$this->order->cart->$cartField->$addressField;
					if (empty($addressField)) continue 2;
					$addressField = $zoneClass->getZones(array($addressField[0]), 'zone_name', 'zone_namekey', true)[0];
					break;
			}

			$result[$paypalField] =  $addressField;
		}

		return $result;
	}
}
