<?php

/*
|--------------------------------------------------------------------------
| General settings
|--------------------------------------------------------------------------
*/

$config['donation_currency'] = ""; // Remember to change the currency ON PayGol as well!
$config['donation_currency_sign'] = " تومان";

/*
|--------------------------------------------------------------------------
| ZarinPal Donate System
|--------------------------------------------------------------------------
*/

$config['donate_zarinpal'] = array(
	'use' => true, // true: enable | false: disable
	'postback_url' => "http://domain.ir/donate/zarinpalreturnback",
	'email' => "info@ltiny.ir",
	'Mobile' => "09300000000", // Support Mobile
	'MerchantID' => '00000000-0000-0000-0000-000000000000', // ZarinPal Merchant ID

	'values' => array(

		// Format: PRICE => DP
		// Example: 5 => 15 which would cause 5 USD
		// (or your specified currency) to give 15 DP

		1000 => 1000,
		2000 => 2000,
		3000 => 3000,
		5000 => 5000,
		1000 => 1000,
		15000 => 15000,
		20000 => 20000,
		25000 => 25000,
		30000 => 30000,
		35000 => 35000,
		40000 => 40000,
		50000 => 50000,
		100000 => 100000,
		150000 => 150000,
		200000 => 200000,
		250000 => 250000,
		300000 => 300000,
		350000 => 350000,
		400000 => 400000,
		450000 => 450000,
		500000 => 500000,
		//100 => 300
	),

);

/*
|--------------------------------------------------------------------------
| PayGol Donation (www.paygol.com)
|--------------------------------------------------------------------------
*/

$config['donate_paygol'] = array(
	'use' => true, // true: enable | false: disable
	'service_id' => 123456, // Your PayGol service ID
	'cancel_url' => "http://YOURSERVER.COM/donate",
	'return_url' => "http://YOURSERVER.COM/donate/success",

	'values' => array(

		// Format: PRICE => DP
		// Example: 5 => 15 which would cause 5 USD
		// (or your specified currency) to give 15 DP

		20 => 30,
		30 => 40,
		40 => 60,
		60 => 90,
	),

);

/*******************************************************************/
/******************* Only Jesper allowed ***************************/
/*******************************************************************/

// Touch it and I'll kill you! >:(
$config['force_code_editor'] = true;